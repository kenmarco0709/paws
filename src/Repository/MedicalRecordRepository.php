<?php

namespace App\Repository;

use App\Entity\MedicalRecordTypeEntity;

/**
 * MedicalRecordRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class MedicalRecordRepository extends \Doctrine\ORM\EntityRepository
{
  
    public function ajax_list(array $get){


        $columns = array(
            array('DATE_FORMAT(mr.`created_at`, "%m/%d/%Y")', 'DATE_FORMAT(mr.`created_at`, "%m/%d/%Y")', 'medicalRecordDate'),
            array('mr.`primary_complain`', 'mr.`primary_complain`', 'primaryComplain'),
            array('mr.`id`', "mr.`id`")
        );
        $asColumns = array();

        $select = "SELECT";
        $from = "FROM `medical_record` mr";
        $joins = " LEFT JOIN `admission_pet` ap ON ap.`id` = mr.`admission_pet_id`";
        $joins .= " LEFT JOIN `admission` a ON a.`id` = ap.`admission_id`";
        $joins .= " LEFT JOIN `admission_type` at ON at.`id` = a.`admission_type_id`";
        $joins .= " LEFT JOIN `pet` p ON p.`id` = ap.`pet_id`";
        $joins .= " LEFT JOIN (
            SELECT 
                GROUP_CONCAT(s.`description`) AS services,
                mrs.`medical_record_id`
            FROM `medical_record_service` mrs 
            LEFT JOIN `service` s ON s.`id` = mrs.`service_id`
            GROUP BY mrs.`medical_record_id`
        ) mrs ON mrs.`medical_record_id` = mr.`id`";
        $joins .= " LEFT JOIN (
            SELECT 
                GROUP_CONCAT( mrl.`file_desc`) AS laboratories,
                mrl.`medical_record_id`
            FROM `medical_record_laboratory` mrl 
            GROUP BY mrl.`medical_record_id`
        ) mrl ON mrl.`medical_record_id` = mr.`id`";


        $sqlWhere = " WHERE ap.`pet_id` =:petId ";
        $groupBy = "";
        $orderBy = "";
        $limit = "";
        $stmtParams = array();

        if(isset($get['confinementType'])){

            $sqlWhere.= ' AND at.`description` =:admissionType';
            $stmtParams['admissionType'] = $get['confinementType'];
        }

        $stmtParams['petId'] = $get['petId'];

        foreach($columns as $key => $column) {
            $select .= ($key > 0 ? ', ' : ' ') . $column[1] . (isset($column[2]) ? ' AS ' . $column[2] : '');
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

        return array(
            'results' => $result,
            'total' => count($result_count)
        );
    }

    public function shelter_ajax_list(array $get, array $userData){


        $columns = array(
            array("DATE_FORMAT(mr.`created_at`, '%m/%d/%Y')", "DATE_FORMAT(mr.`created_at`, '%m/%d/%Y')", 'createdDate'),
            array('at.`description`', 'at.`description`', 'admissionType'),
            array('mr.`remarks`', 'mr.`remarks`', 'remarks'),
            array('mr.`id`', 'mr.`id`'),
            array('mr.`shelter_admission_id`', 'mr.`shelter_admission_id`', 'shelterAdmissionId'),
            array('at.`id`', 'at.`id`', 'admissionTypeId'),


        );

        $asColumns = array();
        $select = "SELECT ";
        $from = "FROM `medical_record` mr";
        $joins = " LEFT JOIN `admission_type` at ON at.`id` = mr.`admission_type_id`";
        $sqlWhere = ' WHERE mr.`shelter_admission_id` IN (
            SELECT 
                sa.`id`
            FROM `shelter_admission` sa
            WHERE sa.`pet_id` =:petId
        )';
        $sqlWhere .= ' AND mr.`is_deleted` != 1';
        $groupBy = "";
        $orderBy = "";
        $limit = "";
        $stmtParams = array();
        
        if(isset($get['petId'])){
            
          //  $sqlWhere.= " AND pt.`pet_id` = :petId";
            $stmtParams['petId'] = base64_decode($get['petId']);
        }

        foreach($columns as $key => $column) {
            $select .= ($key > 0 ? ', ' : ' ') . $column[1] . (isset($column[2]) ? ' AS ' . $column[2] : '');
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
        $formUrl = $url . 'shelter_medical_record/ajax_form';  
        $hasUpdate = $userData['type'] == 'Super Admin'  || in_array('Shelter Admission Details Medical Record Update', $userData['accesses']) ? true : false;

        foreach($result as $row) {

            $id = base64_encode($row['id']);
            $petId = $get['petId'];
            $admissionId = base64_encode($row['shelterAdmissionId']);
            $admissionTypeId = base64_encode($row['admissionTypeId']);


            $action ='';
      
                $action .= $hasUpdate ? '<a href="javascript:void(0);" class="href-modal action-btn action-button-style btn btn-primary table-btn fullscreen" data-admissiontype="'.$admissionTypeId.'" data-admissionid="'.$admissionId.'" data-id="'.$id.'" data-petid="'. $petId .'" data-url="'.$formUrl.'" data-action="u">Update</a>' : "";
                $values = array(
                    $row['createdDate'],
                    $row['admissionType'],
                    '',
                    $action
                );

            $output['data'][] = $values;
        }

        unset($result);

        return $output;
    }

    public function report($startDate, $endDate, $admissionType, $userData) {
       
        $stmtParams = array();
        $andWhere = "";

        if(!is_null($startDate) && !empty($startDate) ){

            $stmtParams['startDate'] = date_format(date_create($startDate),"Y-m-d 00:00:00");
            $andWhere.= " AND mr.`created_at` >= :startDate";
        }
        if(!is_null($endDate) && !empty($endDate) ){

            $stmtParams['endDate'] = date_format(date_create($endDate),"Y-m-d 23:59:59");
            $andWhere.= " AND mr.`invoiccreated_ate_date` <= :endDate";
        }
        if($admissionType != 'all' ){

            $stmtParams['admissionType'] = $admissionType;
            $andWhere.= " AND a.`admissiont_type_id` = :admissionType";

        }
        
        $sql = "
            SELECT
                DATE_FORMAT(mr.`created_at`, '%m/%d/%Y') AS admissionDate,
                CONCAT(c.`first_name` , ' ' ,  c.`last_name`) AS client,
                p.`name` AS pet,
                mr.`weight`,
                mr.`temperature`,
                mr.`primary_complain`,
                mr.`medical_interpretation`,
                mr.`diagnosis`,
                DATE_FORMAT(mr.`vaccine_dute_date`, '%m/%d/%Y') AS vaccineDueDate,
                DATE_FORMAT(mr.`returned_date`, '%m/%d/%Y') AS returnedDate,
                mr.`vaccine_lot_no`,
                mr.`vaccine_batch_no`,
                DATE_FORMAT(mr.`vaccine_expiration_date`, '%m/%d/%Y') AS vaccineExpirationDate
            FROM `medical_record` mr
            LEFT JOIN `admission_pet` ap ON ap.`id` = mr.`admission_pet_id`
            LEFT JOIN `pet` p ON p.`id` = ap.`pet_id`
            LEFT JOIN `client_pet` cp ON cp.`pet_id` = p.`id`
            LEFT JOIN `client` c ON c.`id` = cp.`client_id` 
            LEFT JOIN `admission` a ON a.`id` = ap.`admission_id`
            AND mr.`is_deleted` = 0
            ORDER BY mr.`created_at` DESC
        ";


        $stmtParams['branchId'] = $userData['branchId'];
        $query = $this->getEntityManager()->getConnection()->prepare($sql);

        foreach($stmtParams as $k => $v){
            $query->bindValue($k, $v);

        }

        $res = $query->executeQuery();
        $results = $res->fetchAllAssociative();

        return $results;
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


}
