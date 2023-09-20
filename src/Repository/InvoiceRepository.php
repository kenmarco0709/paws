<?php

namespace App\Repository;

use App\Entity\InvoiceEntity;
use App\Entity\InventoryItemEntity;

/**
 * InvoiceRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class InvoiceRepository extends \Doctrine\ORM\EntityRepository
{

    public function validate($invoice_form, $invoiceFormData) {

        $errors = array();

        $action = $invoice_form['action'];

        // d = delete
        if(!in_array($invoice_form['action'], ['d', 'n']) ) {
            
            if(empty($invoice_form['invoice_date'])){
                $errors[] = 'Invoice date should not be empty.';
            }
            if(empty($invoice_form['client'])){
                $errors[] = 'Please select a client';
            }

            if((!isset($invoiceFormData['service']) && !isset($invoiceFormData['item']) && !isset($invoiceFormData['admission_item']) && !isset($invoiceFormData['admission_service']) )){
                $errors[] = 'Theres no service or item to be invoice.';
            }

        
            if(isset($invoiceFormData['item']) &&  count($invoiceFormData['item'])){
                
                foreach($invoiceFormData['item'] as $k => $item){
                    
                    $inventoryItem = $this->getEntityManager()->getRepository(InventoryItemEntity::class)->find($item['id']);        
                    if(floatval($item['quantity']) > floatval($inventoryItem->getQuantity())){

                        $errors[] = $inventoryItem->getItem()->getDescription() . '  dont have enough stocks.';
                    }
                }

            }
        }

        return $errors;
    }

    public function ajax_list(array $get, $userData){

        $columns = array(
            array('a.`id`', "a.`id`"),
            array("CONCAT(c.`first_name`, ' ', c.`last_name`)", "CONCAT(c.`first_name`, ' ', c.`last_name`)", 'client'),
            array('a.`status`', "a.`status`", "status"),
            array('DATE_FORMAT(a.`invoice_date`, "%m/%d/%Y")', 'DATE_FORMAT(a.`invoice_date`, "%m/%d/%Y")', 'invoiceDate'),
            array('a.`id`', "a.`id`", "aId"),
        );
        $asColumns = array();

        $select = "SELECT";
        $from = "FROM `invoice` a";
        $sqlWhere = " WHERE a.`is_deleted` = 0";
        $joins = " LEFT JOIN `client` c ON c.`id` = a.`client_id`";
        $joins .= "";

        $groupBy = "";
        $orderBy = "";
        $limit = "";
        $stmtParams = array();

        foreach($columns as $key => $column) {
            $select .= ($key > 0 ? ', ' : ' ') . $column[1] . (isset($column[2]) ? ' AS ' . $column[2] : '');
        }


        if($userData['type'] != 'Super Admin' || $userData['branchId']){

            $sqlWhere .= " AND c.`branch_id` = :branchId";
            $stmtParams['branchId'] = $userData['branchId'];
        }

        /*
         * Ordering
         */
        foreach($get['columns'] as $key => $column) {
            if($column['orderable']=='true') {
                if(isSet($get['order'])) {
                    foreach($get['order'] as $order) {
                        if($order['column']==$key) {
                            $orderBy .= (!empty($orderBy) ? ', ' : 'ORDER BY ') . $columns[$key][0] . (!empty($order['dir']) ? ' ' . $order['dir'] : '');
                        }
                    }
                }
            }
        }

        /*
         * Filtering
         */
        if(isset($get['search']) && $get['search']['value'] != ''){
            $aLikes = array();
            foreach($get['columns'] as $key => $column) {
                if($column['searchable']=='true') {
                    $aLikes[] = $columns[$key][0] . ' LIKE :searchValue';
                }
            }
            foreach($asColumns as $asColumn) {
                $aLikes[] = $asColumn . ' LIKE :searchValue';
            }
            if(count($aLikes)) {
                $sqlWhere .= (!empty($sqlWhere) ? ' AND ' : 'WHERE ') . '(' . implode(' OR ', $aLikes) . ')';
                $stmtParams['searchValue'] = "%" . $get['search']['value'] . "%";
            }
        }

        /* Set Limit and Length */
        if(isset( $get['start'] ) && $get['length'] != '-1'){
            $limit = 'LIMIT ' . (int)$get['start'] . ',' . (int)$get['length'];
        }

        $sql = "$select $from $joins $sqlWhere $groupBy $orderBy";
        $query = $this->getEntityManager()->getConnection()->prepare($sql);
     

        foreach($stmtParams as $k => $v){
            $query->bindValue($k, $v);

        }
        $res = $query->executeQuery();
        $result_count = $res->fetchAllAssociative();
        $sql = "$select $from $joins $sqlWhere $groupBy $orderBy $limit";
        $query = $this->getEntityManager()->getConnection()->prepare($sql);
        foreach($stmtParams as $k => $v){
            $query->bindValue($k, $v);

        }
        $res = $query->executeQuery();
        $result = $res->fetchAllAssociative();

        /* Data Count */
        $recordsTotal = count($result_count);

        /*
         * Output
         */
        $output = array(
            "draw" => intval($get['draw']),
            "recordsTotal" => $recordsTotal,
            "recordsFiltered" => $recordsTotal,
            "data" => array()
        );

        $url = $get['url'];
        $formUrl = '';
        $hasUpdate = false;
        $hasDownload = false;
        $downloadUrl = '';
        $printUrl = '';


        if($userData['type'] == 'Super Admin'  || in_array('Invoice Update', $userData['accesses'])){

            $formUrl = $url . 'invoice/form';  
            $hasUpdate = true;
        }

        if($userData['type'] == 'Super Admin'  || in_array('Invoice Download', $userData['accesses'])){

            $downloadUrl = $url . 'invoice/download';  
            $hasDownload = true;
            $printUrl = $url . 'print/download';
        }


        foreach($result as $row) {

            $id2 = base64_encode($row['aId']);
        
            $action = $hasUpdate ? "<a class='action-button-style btn btn-primary table-btn' href='$formUrl/u/$id2'>Update</a>" : "";
            $downloadAction = $hasDownload ? "<a class='action-button-style btn btn-primary table-btn' href='$downloadUrl/$id2'>Download</a> <a class='action-button-style btn btn-primary table-btn href-print' href='javascript:void(0);' data-id='$id2'>Print</a>" : "";


            $values = array(
                $row['id'],
                $row['client'],
                $row['status'],
                $row['invoiceDate'],
                $action . ' ' . $downloadAction,
            );

            $output['data'][] = $values;
        }

        unset($result);

        return $output;
    }

    public function report($startDate, $endDate, $userData) {
       
        $stmtParams = array();
        $andWhere = "";

        if(!is_null($startDate) && !empty($startDate) ){

            $stmtParams['startDate'] = date_format(date_create($startDate),"Y-m-d 00:00:00");
            $andWhere.= " AND i.`invoice_date` >= :startDate";
        }
        if(!is_null($endDate) && !empty($endDate) ){

            $stmtParams['endDate'] = date_format(date_create($endDate),"Y-m-d 23:59:59");
            $andWhere.= " AND i.`invoice_date` <= :endDate";

        }

        $sql = "
            SELECT
                i.`id` AS invoiceId,
                CONCAT(c.`first_name`, ' ', c.`last_name`) AS client,
                DATE_FORMAT(i.`invoice_date`, '%m/%d/%Y') AS invoiceDate,
                CAST( (IFNULL(iaii.admissionInventoryItemDiscount, 0) + IFNULL(ias.admissionServiceDiscount, 0) + IFNULL(ins.invoiceServiceDiscount, 0) + IFNULL(iii.invoiceInventoryItemDiscount,0)) AS DECIMAL(6,2)) AS itemServiceDiscount,
                CAST(IFNULL(iaii.admissionInventoryItemPrice,0) + IFNULL(ias.admissionServicePrice, 0) + IFNULL(ins.invoiceServicePrice, 0) + IFNULL(iii.invoiceInventoryItemPrice, 0) AS DECIMAL(6,2)) AS totalPrice,
                CAST(IFNULL(iaii.admissionInventoryItemPrice,0) + IFNULL(ias.admissionServicePrice, 0) + IFNULL(ins.invoiceServicePrice, 0) + IFNULL(iii.invoiceInventoryItemPrice, 0) AS DECIMAL(6,2)) - CAST( (IFNULL(iaii.admissionInventoryItemDiscount, 0) + IFNULL(ias.admissionServiceDiscount, 0) + IFNULL(ins.invoiceServiceDiscount, 0) + IFNULL(iii.invoiceInventoryItemDiscount,0)) AS DECIMAL(6,2)) AS totalPriceAfterItemDiscount, 
                CAST((IFNULL(iaii.admissionInventoryItemDiscount, 0) + IFNULL(ias.admissionServiceDiscount, 0) + IFNULL(ins.invoiceServiceDiscount, 0) + IFNULL(iii.invoiceInventoryItemDiscount,0)) AS DECIMAL(6,2)) + CAST(CAST(CAST(CAST(IFNULL(iaii.admissionInventoryItemPrice,0) + IFNULL(ias.admissionServicePrice, 0) + IFNULL(ins.invoiceServicePrice, 0) + IFNULL(iii.invoiceInventoryItemPrice, 0) AS DECIMAL(6,2)) - CAST( (IFNULL(iaii.admissionInventoryItemDiscount, 0) + IFNULL(ias.admissionServiceDiscount, 0) + IFNULL(ins.invoiceServiceDiscount, 0) + IFNULL(iii.invoiceInventoryItemDiscount,0)) AS DECIMAL(6,2)) AS integer)  * IFNULL(i.`discount`, 0) AS integer ) / 100 AS DECIMAL(6,2))   AS grandDiscount,
                CAST(IFNULL(p.paymentAmount,0) AS DECIMAL(6,2)) AS paymentAmount,
                CAST(IFNULL(rp.reimbursedAmount,0) AS DECIMAL(6,2)) AS reimbursedAmount,
                i.`status` AS invoiceStatus,
                CAST(IFNULL(i.`amount_due`, 0) AS DECIMAL(6,2)) AS remainingBalance   
            FROM `invoice` i
            LEFT JOIN (
                SELECT 
                    SUM(((iaii.`quantity` * ii.`selling_price`) * iaii.`discount`) / 100) AS admissionInventoryItemDiscount,
                    SUM(iaii.`quantity` * ii.`selling_price`) AS admissionInventoryItemPrice,
                    iaii.`invoice_id`
                FROM `invoice_admission_inventory_item` iaii
                LEFT JOIN `inventory_item` ii ON ii.`id` = iaii.`inventory_item_id`   
                GROUP BY iaii.`invoice_id`  
            ) iaii ON iaii.`invoice_id` = i.`id`
            LEFT JOIN (
                SELECT 
                    SUM(((ias.`quantity` * s.`price`) * ias.`discount`) / 100) AS admissionServiceDiscount,
                    SUM(ias.`quantity` * s.`price`) AS admissionServicePrice,
                    ias.`invoice_id`
                FROM `invoice_admission_service` ias
                LEFT JOIN `service` s ON s.`id` = ias.`service_id`   
                GROUP BY ias.`invoice_id`  
            ) ias ON ias.`invoice_id` = i.`id`
            LEFT JOIN (
                SELECT 
                    SUM(((ins.`quantity` * s.`price`) * ins.`discount`) / 100) AS invoiceServiceDiscount,
                    SUM(ins.`quantity` * s.`price`) AS invoiceServicePrice,
                    ins.`invoice_id`
                FROM `invoice_service` ins
                LEFT JOIN `service` s ON s.`id` = ins.`service_id`   
                GROUP BY ins.`invoice_id`  
            ) ins ON ins.`invoice_id` = i.`id`
            LEFT JOIN (
                SELECT 
                    SUM(((iii.`quantity` * ii.`selling_price`) * iii.`discount`) / 100) AS invoiceInventoryItemDiscount,
                    SUM(iii.`quantity` * ii.`selling_price`) AS invoiceInventoryItemPrice,
                    iii.`invoice_id`
                FROM `invoice_inventory_item` iii
                LEFT JOIN `inventory_item` ii ON ii.`id` = iii.`inventory_item_id`   
                GROUP BY iii.`invoice_id`  
            ) iii ON iii.`invoice_id` = i.`id`
            LEFT JOIN (
                SELECT 
                    SUM(p.`amount`) AS paymentAmount,
                    p.`invoice_id`
                FROM `payment` p
                GROUP BY p.`invoice_id`  
            ) p ON p.`invoice_id` = i.`id`
            LEFT JOIN (
                SELECT 
                    SUM(rp.`amount`) AS reimbursedAmount,
                    rp.`invoice_id`
                FROM `reimbursed_payment` rp
                GROUP BY rp.`invoice_id`  
            ) rp ON rp.`invoice_id` = i.`id`
            LEFT JOIN `client` c ON c.`id` = i.`client_id`
            WHERE i.`branch_id` = :branchId 
            ".$andWhere."
            ORDER BY i.`invoice_date` DESC
        ";


        $stmtParams['branchId'] = $userData['branchId'];
        $query = $this->getEntityManager()->getConnection()->prepare($sql);

        foreach($stmtParams as $k => $v){
            $query->bindValue($k, $v);

        }

        $res = $query->executeQuery();
        $result = $res->fetchAllAssociative();

        return $result;
    }

    public function agingReport($userData) {
       
        $stmtParams = array();
        $andWhere = "";

        $sql = "
            SELECT
                i.`id` AS invoiceId,
                CONCAT(c.`first_name`, ' ', c.`last_name`) AS client,
                DATE_FORMAT(i.`invoice_date`, '%m/%d/%Y') AS invoiceDate,
                CAST( (IFNULL(iaii.admissionInventoryItemDiscount, 0) + IFNULL(ias.admissionServiceDiscount, 0) + IFNULL(ins.invoiceServiceDiscount, 0) + IFNULL(iii.invoiceInventoryItemDiscount,0)) AS DECIMAL(6,2)) AS itemServiceDiscount,
                CAST(IFNULL(iaii.admissionInventoryItemPrice,0) + IFNULL(ias.admissionServicePrice, 0) + IFNULL(ins.invoiceServicePrice, 0) + IFNULL(iii.invoiceInventoryItemPrice, 0) AS DECIMAL(6,2)) AS totalPrice,
                CAST(IFNULL(iaii.admissionInventoryItemPrice,0) + IFNULL(ias.admissionServicePrice, 0) + IFNULL(ins.invoiceServicePrice, 0) + IFNULL(iii.invoiceInventoryItemPrice, 0) AS DECIMAL(6,2)) - CAST( (IFNULL(iaii.admissionInventoryItemDiscount, 0) + IFNULL(ias.admissionServiceDiscount, 0) + IFNULL(ins.invoiceServiceDiscount, 0) + IFNULL(iii.invoiceInventoryItemDiscount,0)) AS DECIMAL(6,2)) AS totalPriceAfterItemDiscount, 
                CAST((IFNULL(iaii.admissionInventoryItemDiscount, 0) + IFNULL(ias.admissionServiceDiscount, 0) + IFNULL(ins.invoiceServiceDiscount, 0) + IFNULL(iii.invoiceInventoryItemDiscount,0)) AS DECIMAL(6,2)) + CAST(CAST(CAST(CAST(IFNULL(iaii.admissionInventoryItemPrice,0) + IFNULL(ias.admissionServicePrice, 0) + IFNULL(ins.invoiceServicePrice, 0) + IFNULL(iii.invoiceInventoryItemPrice, 0) AS DECIMAL(6,2)) - CAST( (IFNULL(iaii.admissionInventoryItemDiscount, 0) + IFNULL(ias.admissionServiceDiscount, 0) + IFNULL(ins.invoiceServiceDiscount, 0) + IFNULL(iii.invoiceInventoryItemDiscount,0)) AS DECIMAL(6,2)) AS integer)  * IFNULL(i.`discount`, 0) AS integer ) / 100 AS DECIMAL(6,2))   AS grandDiscount,
                CAST(IFNULL(p.paymentAmount,0) AS DECIMAL(6,2)) AS paymentAmount,
                CAST(IFNULL(rp.reimbursedAmount,0) AS DECIMAL(6,2)) AS reimbursedAmount,
                i.`status` AS invoiceStatus,
                CAST(IFNULL(i.`amount_due`, 0) AS DECIMAL(6,2)) AS remainingBalance   
            FROM `invoice` i
            LEFT JOIN (
                SELECT 
                    SUM(((iaii.`quantity` * ii.`selling_price`) * iaii.`discount`) / 100) AS admissionInventoryItemDiscount,
                    SUM(iaii.`quantity` * ii.`selling_price`) AS admissionInventoryItemPrice,
                    iaii.`invoice_id`
                FROM `invoice_admission_inventory_item` iaii
                LEFT JOIN `inventory_item` ii ON ii.`id` = iaii.`inventory_item_id`   
                GROUP BY iaii.`invoice_id`  
            ) iaii ON iaii.`invoice_id` = i.`id`
            LEFT JOIN (
                SELECT 
                    SUM(((ias.`quantity` * s.`price`) * ias.`discount`) / 100) AS admissionServiceDiscount,
                    SUM(ias.`quantity` * s.`price`) AS admissionServicePrice,
                    ias.`invoice_id`
                FROM `invoice_admission_service` ias
                LEFT JOIN `service` s ON s.`id` = ias.`service_id`   
                GROUP BY ias.`invoice_id`  
            ) ias ON ias.`invoice_id` = i.`id`
            LEFT JOIN (
                SELECT 
                    SUM(((ins.`quantity` * s.`price`) * ins.`discount`) / 100) AS invoiceServiceDiscount,
                    SUM(ins.`quantity` * s.`price`) AS invoiceServicePrice,
                    ins.`invoice_id`
                FROM `invoice_service` ins
                LEFT JOIN `service` s ON s.`id` = ins.`service_id`   
                GROUP BY ins.`invoice_id`  
            ) ins ON ins.`invoice_id` = i.`id`
            LEFT JOIN (
                SELECT 
                    SUM(((iii.`quantity` * ii.`selling_price`) * iii.`discount`) / 100) AS invoiceInventoryItemDiscount,
                    SUM(iii.`quantity` * ii.`selling_price`) AS invoiceInventoryItemPrice,
                    iii.`invoice_id`
                FROM `invoice_inventory_item` iii
                LEFT JOIN `inventory_item` ii ON ii.`id` = iii.`inventory_item_id`   
                GROUP BY iii.`invoice_id`  
            ) iii ON iii.`invoice_id` = i.`id`
            LEFT JOIN (
                SELECT 
                    SUM(p.`amount`) AS paymentAmount,
                    p.`invoice_id`
                FROM `payment` p
                GROUP BY p.`invoice_id`  
            ) p ON p.`invoice_id` = i.`id`
            LEFT JOIN (
                SELECT 
                    SUM(rp.`amount`) AS reimbursedAmount,
                    rp.`invoice_id`
                FROM `reimbursed_payment` rp
                GROUP BY rp.`invoice_id`  
            ) rp ON rp.`invoice_id` = i.`id`
            LEFT JOIN `client` c ON c.`id` = i.`client_id`
            WHERE i.`branch_id` = :branchId 
            AND i.`status` != 'Paid Payment'
            AND i.`invoice_date` < CURDATE() + INTERVAL 1 DAY
            ".$andWhere."
            ORDER BY i.`invoice_date` DESC
        ";


        $stmtParams['branchId'] = $userData['branchId'];
        $query = $this->getEntityManager()->getConnection()->prepare($sql);

        foreach($stmtParams as $k => $v){
            $query->bindValue($k, $v);

        }

        $res = $query->executeQuery();
        $result = $res->fetchAllAssociative();

        return $result;
    }

    public function sales_income_ajax_list(array $get, $userData){

        $columns = array(
            array('DATE_FORMAT(i.`invoice_date`, "%m/%d/%Y")', 'DATE_FORMAT(i.`invoice_date`, "%m/%d/%Y")', 'invoiceDate'),
            array('i.`id`', "i.`id`"),
            array("CONCAT(c.`first_name`, ' ', c.`last_name`)", "CONCAT(c.`first_name`, ' ', c.`last_name`)", 'client'),
            array("(IFNULL(iii.grossAmt,0) + IFNULL(iaii.grossAmt2,0))", "(IFNULL(iii.grossAmt,0) + IFNULL(iaii.grossAmt2,0))", 'grossAmt'),
            array("(IFNULL(iii.netAmt,0) + IFNULL(iaii.netAmt2,0) + IFNULL(ias.netAmt3,0) + IFNULL(ise.netAmt4,0))", "(IFNULL(iii.netAmt,0) + IFNULL(iaii.netAmt2,0) + IFNULL(ias.netAmt3,0) + IFNULL(ise.netAmt4,0))", 'netAmt'),
            array("(IFNULL(p.`payment`,0) - IFNULL(rp.`reimpursedPayment`,0))", "(IFNULL(p.`payment`,0) - IFNULL(rp.`reimpursedPayment`,0))", 'paymentAmt'),
            array("IFNULL(i.`amount_due`,0)", "IFNULL(i.`amount_due`,0)", 'receivable'),

        );
        $asColumns = array();

        $select = "SELECT";
        $from = "FROM `invoice` i";
        $sqlWhere = " WHERE i.`is_deleted` = 0";
        $joins = " LEFT JOIN `client` c ON c.`id` = i.`client_id`";
        $joins .= " LEFT JOIN (
            SELECT 
                iii.`invoice_id`,
                SUM(((iii.`quantity` * iii.`selling_price`) - (iii.`quantity` * iii.`buying_price`)) - (((iii.`quantity` * iii.`selling_price`) * iii.`discount`) / 100)) AS netAmt,
                SUM(iii.`quantity` * iii.`selling_price`) AS grossAmt
            FROM `invoice_inventory_item` iii
            GROUP BY iii.`invoice_id`
        ) iii ON iii.`invoice_id` = i.`id` ";
        
        $joins .= " LEFT JOIN (
            SELECT 
                iaii.`invoice_id`,
                SUM(((iaii.`quantity` * iaii.`selling_price`) - (iaii.`quantity` * iaii.`buying_price`)) - (((iaii.`quantity` * iaii.`selling_price`) * iaii.`discount`) / 100)) AS netAmt2,
                SUM(iaii.`quantity` * iaii.`selling_price`) AS grossAmt2
            FROM `invoice_admission_inventory_item` iaii
            GROUP BY iaii.`invoice_id`
        ) iaii ON iaii.`invoice_id` = i.`id` ";

        $joins .= " LEFT JOIN (
            SELECT 
                ias.`invoice_id`,
                SUM(ias.`quantity` * ias.`amount`) AS netAmt3
            FROM `invoice_admission_service` ias
            GROUP BY ias.`invoice_id`
        ) ias ON ias.`invoice_id` = i.`id` ";

        $joins .= " LEFT JOIN (
            SELECT 
                ise.`invoice_id`,
                SUM(ise.`quantity` * ise.`amount`) AS netAmt4
            FROM `invoice_service` ise
            GROUP BY ise.`invoice_id`
        ) ise ON ise.`invoice_id` = i.`id` ";
        
        $joins .= " LEFT JOIN (
            SELECT 
                p.`invoice_id`,
                SUM(p.`amount`) AS payment
            FROM `payment` p
            GROUP BY p.`invoice_id`
        ) p ON p.`invoice_id` = i.`id` ";

        
        $joins .= " LEFT JOIN (
            SELECT 
                rp.`invoice_id`,
                SUM(rp.`amount`) AS reimpursedPayment
            FROM `reimbursed_payment` rp
            GROUP BY rp.`invoice_id`
        ) rp ON rp.`invoice_id` = i.`id` ";

        $groupBy = "";
        $orderBy = "";
        $limit = "";
        $stmtParams = array();

        if(isset($get['startDate']) && !empty($get['startDate']) ){

            $stmtParams['startDate'] = date_format(date_create($get['startDate']),"Y-m-d 00:00:00");
            $sqlWhere.= " AND i.`invoice_date` >= :startDate";
        }
        if(isset($get['endDate']) && !empty($get['endDate']) ){

            $stmtParams['endDate'] = date_format(date_create($get['endDate']),"Y-m-d 23:59:59");
            $sqlWhere.= " AND i.`invoice_date` <= :endDate";

        }

        foreach($columns as $key => $column) {
            $select .= ($key > 0 ? ', ' : ' ') . $column[1] . (isset($column[2]) ? ' AS ' . $column[2] : '');
        }


        if($userData['type'] != 'Super Admin' || $userData['branchId']){

            $sqlWhere .= " AND c.`branch_id` = :branchId";
            $stmtParams['branchId'] = $userData['branchId'];
        }

        /*
         * Ordering
         */
        foreach($get['columns'] as $key => $column) {
            if($column['orderable']=='true') {
                if(isSet($get['order'])) {
                    foreach($get['order'] as $order) {
                        if($order['column']==$key) {
                            $orderBy .= (!empty($orderBy) ? ', ' : 'ORDER BY ') . $columns[$key][0] . (!empty($order['dir']) ? ' ' . $order['dir'] : '');
                        }
                    }
                }
            }
        }

        /*
         * Filtering
         */
        if(isset($get['search']) && $get['search']['value'] != ''){
            $aLikes = array();
            foreach($get['columns'] as $key => $column) {
                if($column['searchable']=='true') {
                    $aLikes[] = $columns[$key][0] . ' LIKE :searchValue';
                }
            }
            foreach($asColumns as $asColumn) {
                $aLikes[] = $asColumn . ' LIKE :searchValue';
            }
            if(count($aLikes)) {
                $sqlWhere .= (!empty($sqlWhere) ? ' AND ' : 'WHERE ') . '(' . implode(' OR ', $aLikes) . ')';
                $stmtParams['searchValue'] = "%" . $get['search']['value'] . "%";
            }
        }

        /* Set Limit and Length */
        if(isset( $get['start'] ) && $get['length'] != '-1'){
            $limit = 'LIMIT ' . (int)$get['start'] . ',' . (int)$get['length'];
        }

        $sql = "$select $from $joins $sqlWhere $groupBy $orderBy";
        $query = $this->getEntityManager()->getConnection()->prepare($sql);
     

        foreach($stmtParams as $k => $v){
            $query->bindValue($k, $v);

        }
        $res = $query->executeQuery();
        $result_count = $res->fetchAllAssociative();
        $sql = "$select $from $joins $sqlWhere $groupBy $orderBy $limit";
        $query = $this->getEntityManager()->getConnection()->prepare($sql);
        foreach($stmtParams as $k => $v){
            $query->bindValue($k, $v);

        }
        $res = $query->executeQuery();
        $result = $res->fetchAllAssociative();

        /* Data Count */
        $recordsTotal = count($result_count);

        /*
         * Output
         */
        $output = array(
            "draw" => intval($get['draw']),
            "recordsTotal" => $recordsTotal,
            "recordsFiltered" => $recordsTotal,
            "data" => array()
        );

        $url = $get['url'];
        

        foreach($result as $row) {

            $id = base64_encode($row['id']);
        
            $values = array(
                $row['invoiceDate'],
                '<a href="javascript:void(0);" data-id="'.$row['id'].'" class="href-modal">'.$row['id'].'</a>',
                $row['client'],
                number_format($row['grossAmt'],2,'.', ','),
                number_format($row['netAmt'],2,'.', ','),
                number_format($row['paymentAmt'],2,'.', ','),
                number_format($row['receivable'],2,'.', ',')
            );

            $output['data'][] = $values;
        }

        unset($result);

        return $output;
    }

    public function sales_income_report($startDate, $endDate, $userData) {
       
        $stmtParams = array();
        $andWhere = "";

        if(!is_null($startDate) && !empty($startDate) ){

            $stmtParams['startDate'] = date_format(date_create($startDate),"Y-m-d 00:00:00");
            $andWhere.= " AND i.`invoice_date` >= :startDate";
        }
        if(!is_null($endDate) && !empty($endDate) ){

            $stmtParams['endDate'] = date_format(date_create($endDate),"Y-m-d 23:59:59");
            $andWhere.= " AND i.`invoice_date` <= :endDate";
        }
        
        $sql = "
            SELECT
                i.`id` AS invoiceId,
                CONCAT(c.`first_name` , ' ', c.`last_name`) AS client,
                iii.invoiceInventoryItemDescription,
                iii.invoiceInventoryItemBuyingPrice,
                iii.invoiceInventoryItemSellingPrice,
                iii.invoiceInventoryItemDiscount,
                iii.invoiceInventoryItemQuantity,
                iaii.invoiceAdmissionInventoryItemDescription,
                iaii.invoiceAdmissionInventoryItemBuyingPrice,
                iaii.invoiceAdmissionInventoryItemSellingPrice,
                iaii.invoiceAdmissionInventoryItemDiscount,
                iaii.invoiceAdmissionInventoryItemQuantity,
                ise.invoiceServiceDescription,
                ise.invoiceServiceQuantity,
                ise.invoiceServicePrice,
                ise.invoiceServiceDiscount,
                ids.invoiceAdmissionServiceDescription,
                ids.invoiceAdmissionServiceQuantity,
                ids.invoiceAdmissionServicePrice,
                ids.invoiceAdmissionServiceDiscount,
                IFNULL(p.`payment`,0) - IFNULL(rp.`reimpursedPayment`,0) AS paymentAmt,
                IFNULL(i.`amount_due`,0) AS dueAmt
            FROM `invoice` i
            LEFT JOIN `client` c ON c.`id` = i.`client_id`
            LEFT JOIN (
                SELECT 
                    iii.`invoice_id`,
                    GROUP_CONCAT(i.`description`) AS invoiceInventoryItemDescription,
                    GROUP_CONCAT(iii.`quantity`) AS invoiceInventoryItemQuantity,
                    GROUP_CONCAT(iii.`buying_price`) AS invoiceInventoryItemBuyingPrice,
                    GROUP_CONCAT(iii.`selling_price`) AS invoiceInventoryItemSellingPrice,
                    GROUP_CONCAT((((iii.`quantity` * iii.`selling_price`) * iii.`discount`) / 100)) AS invoiceInventoryItemDiscount
                FROM `invoice_inventory_item` iii
                LEFT JOIN `inventory_item` ii ON ii.`id` =  iii.`inventory_item_id`
                LEFT JOIN `item` i ON i.`id` = ii.`item_id`
                GROUP BY iii.`invoice_id`
            ) iii ON iii.`invoice_id` = i.`id`
            LEFT JOIN (
                SELECT 
                    iaii.`invoice_id`,
                    GROUP_CONCAT(i.`description`) AS invoiceAdmissionInventoryItemDescription,
                    GROUP_CONCAT(iaii.`quantity`) AS invoiceAdmissionInventoryItemQuantity,
                    GROUP_CONCAT(iaii.`buying_price`) AS invoiceAdmissionInventoryItemBuyingPrice,
                    GROUP_CONCAT(iaii.`selling_price`) AS invoiceAdmissionInventoryItemSellingPrice,
                    GROUP_CONCAT((((iaii.`quantity` * iaii.`selling_price`) * iaii.`discount`) / 100)) AS invoiceAdmissionInventoryItemDiscount
                FROM `invoice_admission_inventory_item` iaii
                LEFT JOIN `inventory_item` ii ON ii.`id` =  iaii.`inventory_item_id`
                LEFT JOIN `item` i ON i.`id` = ii.`item_id`
                GROUP BY iaii.`invoice_id`
            ) iaii ON iaii.`invoice_id` = i.`id`
            LEFT JOIN (
                SELECT 
                    ise.`invoice_id`,
                    GROUP_CONCAT(s.`description`) AS invoiceServiceDescription,
                    GROUP_CONCAT(ise.`quantity`) AS invoiceServiceQuantity,
                    GROUP_CONCAT(ise.`amount`) AS invoiceServicePrice,
                    GROUP_CONCAT((((ise.`quantity` * ise.`amount`) * ise.`discount`) / 100)) AS invoiceServiceDiscount
                FROM `invoice_service` ise
                LEFT JOIN `service` s ON s.`id` =  ise.`service_id`
                GROUP BY ise.`invoice_id`
            ) ise ON ise.`invoice_id` = i.`id`
            LEFT JOIN (
                SELECT 
                    ids.`invoice_id`,
                    GROUP_CONCAT(s.`description`) AS invoiceAdmissionServiceDescription,
                    GROUP_CONCAT(ids.`quantity`) AS invoiceAdmissionServiceQuantity,
                    GROUP_CONCAT(ids.`amount`) AS invoiceAdmissionServicePrice,
                    GROUP_CONCAT((((ids.`quantity` * ids.`amount`) * ids.`discount`) / 100)) AS invoiceAdmissionServiceDiscount
                FROM `invoice_admission_service` ids
                LEFT JOIN `service` s ON s.`id` =  ids.`service_id`
                GROUP BY ids.`invoice_id`
            ) ids ON ids.`invoice_id` = i.`id`
            LEFT JOIN (
                SELECT 
                    p.`invoice_id`,
                    SUM(p.`amount`) AS payment
                FROM `payment` p
                GROUP BY p.`invoice_id`
            ) p ON p.`invoice_id` = i.`id` 
            LEFT JOIN (
                SELECT 
                    rp.`invoice_id`,
                    SUM(rp.`amount`) AS reimpursedPayment
                FROM `reimbursed_payment` rp
                GROUP BY rp.`invoice_id`
            ) rp ON rp.`invoice_id` = i.`id`
            WHERE i.`branch_id` = :branchId 
            ".$andWhere."
            AND i.`is_deleted` = 0
            ORDER BY i.`invoice_date` DESC
        ";


        $stmtParams['branchId'] = $userData['branchId'];
        $query = $this->getEntityManager()->getConnection()->prepare($sql);

        foreach($stmtParams as $k => $v){
            $query->bindValue($k, $v);

        }

        $res = $query->executeQuery();
        $results = $res->fetchAllAssociative();

        return $this->formatSalesIncomeData($results);
    }

    private function formatSalesIncomeData($results){
        $data = [];
       
        foreach($results as $k => $result){

            $data[$k] = [
                'invoice_id' => $result['invoiceId'],
                'client' => $result['client'],
                'due_amt' => $result['dueAmt'],
                'payment_amt' => $result['paymentAmt'],
                'products' => []
            ];
            
            $dataFields = [ 'invoiceInventoryItem', 'invoiceAdmissionInventoryItem', 'invoiceService', 'invoiceAdmissionService'];

            foreach($dataFields as $dataField){
                
                if(!is_null($result[$dataField.'Description'])){
                
                    if(count($data[$k]['products']) > 0){                    
                        $toPushData = $this->salesIncomeProductArray($result, $dataField);
                        array_push($data[$k]['products'], $toPushData[0]);
                    } else {
                        $data[$k]['products'] = $this->salesIncomeProductArray($result, $dataField);
                    }
                }
            }
        }
        
        return $data;
    }

    private function salesIncomeProductArray( $result, $productField){
        
        $data = [];

        $descriptions  = $this->toArray($result[$productField.'Description']);
        $discounts     = $this->toArray($result[$productField.'Discount']);
        $quantities    = $this->toArray($result[$productField.'Quantity']);

        if(in_array($productField, ['invoiceService', 'invoiceAdmissionService'])){
            
            $buyingPrices  = 0;
            $sellingPrices = $this->toArray($result[$productField.'Price']);
        } else {
           
            $buyingPrices  = $this->toArray($result[$productField.'BuyingPrice']);
            $sellingPrices = $this->toArray($result[$productField.'SellingPrice']);
        }
        
    
        foreach ($descriptions as  $l => $v) {
            $buyingPrice = !empty($buyingPrices[$l]) && isset($buyingPrices[$l]) ? $buyingPrices[$l] : 0;
            $discount = !empty($discounts[$l]) && isset($discounts[$l]) ? $discounts[$l] : 0;
            $sellingPrice = !empty($sellingPrices[$l]) && isset($sellingPrices[$l]) ? $sellingPrices[$l] : 0;

            $data[$l] = [
                'description' => $v,
                'buying_price' => $buyingPrice,
                'selling_price' => $sellingPrice,
                'discount' => $discount,
                'quantity' => $quantities[$l],
                'gross' =>  $quantities[$l] * $sellingPrice,
                'net' => ((($quantities[$l] * $sellingPrice) - ($quantities[$l] *  $buyingPrice)) - ((($quantities[$l] * $sellingPrice) * $discount) / 100)  ) 
            ];
        }
        return $data;

    }

    private function toArray($data){
        return explode(',', $data);
    }

}