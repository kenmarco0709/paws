<?php

namespace App\Repository;

use App\Entity\InventoryItemAdjustmentEntity;

/**
 * InventoryItemAdjustmentRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class InventoryItemAdjustmentRepository extends \Doctrine\ORM\EntityRepository
{
    public function validate($form) {
        $errors = array();

        $action = $form['action'];

        if($form['adjustment_type'] != 'Others'){

            if(empty($form['quantity'])){

                $errors[] = 'Please put quantity to adjust.';
            }
        }

        return $errors;
    }

    public function ajax_list(array $get){

        $columns = array(
            array('DATE_FORMAT(iia.`created_at`, "%m/%d/%Y")', 'DATE_FORMAT(iia.`created_at`, "%m/%d/%Y")', 'adjustmentDate'),
            array('iia.`adjustment_type`', 'iia.`adjustment_type`'),
            array('iia.`low_quantity`', 'iia    .`low_quantity`', 'lowQuantity'),
            array('iia.`quantity`', 'iia.`quantity`'),
            array('iia.`selling_price`', 'iia.`selling_price`'),
            array('iia.`buying_price`', 'iia.`buying_price`'),
            array('iia.`remarks`', "iia.`remarks`")
        );
        $asColumns = array();

        $select = "SELECT";
        $from = "FROM `inventory_item_adjustment` iia";
        $joins = "";
        $sqlWhere = "WHERE iia.`is_deleted` = 0";
        $groupBy = "";
        $orderBy = "";
        $limit = "";
        $stmtParams = array();

        $sqlWhere.= " AND iia.`inventory_item_id` = :inventoryItemId";
        $stmtParams['inventoryItemId'] = base64_decode($get['invetoryItemId']);

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

    public function autocomplete_suggestions($q, array $userData) {

        $stmtParams = array(
            'q' => "%" . $q['query'] . "%"
        );
        $andWhere = '';

        $sql = "
            SELECT
                c.`id`,
                c.`description` AS data,
                c.`description` AS value
            FROM `item` c
            WHERE c.`description` LIKE :q
            AND c.`is_deleted` = 0
            ORDER BY c.`description`
            LIMIT 0,20
        ";

        $query = $this->getEntityManager()->getConnection()->prepare($sql);

        foreach($stmtParams as $k => $v){
            $query->bindValue($k, $v);

        }
       
        $res = $query->executeQuery();
        $result = $res->fetchAllAssociative();
        return $result;
    }

}
