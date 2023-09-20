<?php

namespace App\Repository;

use App\Entity\MedicalRecordTypeEntity;

/**
 * CabinetFormRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class CabinetFormRepository extends \Doctrine\ORM\EntityRepository
{

    function validate($form){
        $errors = [];

        return $errors;
    }
  
    public function ajax_list(array $get, $userData){

        $columns = array(
            array('DATE_FORMAT(cb.`created_at`, "%m/%d/%Y")', 'DATE_FORMAT(cb.`created_at`, "%m/%d/%Y")', "createdAt"),
            array('cb.`form_type`', "cb.`form_type`", "formType"),
            array('cb.`file_desc`', "cb.`file_desc`", "fileDesc"),
            array('cb.`parsed_file_desc`', "cb.`parsed_file_desc`", "parsedFileDesc"),
        );
        $asColumns = array();

        $select = "SELECT";
        $from = "FROM `cabinet_form` cb";
        $joins = '';
        $sqlWhere = " WHERE cb.`is_deleted` = 0";
        $groupBy = "";
        $orderBy = "";
        $limit = "";
        $stmtParams = array();

        if(isset($get['petId'])){
            $sqlWhere .= ' AND cb.`pet_id` = :petId';
            $stmtParams['petId'] = $get['petId'];

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
        $formUrl = '';

        foreach($result as $row) {
    
            $values = array(
              $row['createdAt'],
              $row['formType'],
              "<a href='".$url."uploads/file/form_cabinet/".$row['parsedFileDesc']."' target='_blank'>".$row['fileDesc']."</a>"
            );

            $output['data'][] = $values;
        }

        unset($result);

        return $output;
    }

}