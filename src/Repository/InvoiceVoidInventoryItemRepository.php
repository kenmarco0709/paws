<?php

namespace App\Repository;

use App\Entity\InvoiceVoidInventoryItemEntity;

/**
 * InvoiceVoidInventoryItemRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class InvoiceVoidInventoryItemRepository extends \Doctrine\ORM\EntityRepository
{

    public function inventory_item_ajax_list(array $get){

        $columns = array(
            array('DATE_FORMAT(ivii.`created_at`, "%m/%d/%Y")', 'DATE_FORMAT(ivii.`created_at`, "%m/%d/%Y")', 'date'),
            array('ivii.`id`', 'ivii.`id`'),
            array('ivii.`quantity`', 'ivii.`quantity`'),
        );
        $asColumns = array();

        $select = "SELECT";
        $from = "FROM `invoice_void_inventory_item` ivii";
        $joins = "";
        $sqlWhere = "WHERE ivii.`is_deleted` = 0";
        $groupBy = "";
        $orderBy = "";
        $limit = "";
        $stmtParams = array();

        $sqlWhere.= " AND ivii.`inventory_item_id` = :inventoryItemId";
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

   
}
