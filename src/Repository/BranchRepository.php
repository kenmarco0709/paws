<?php

namespace App\Repository;

use App\Entity\BranchEntity;
use App\Entity\CompanyEntity;


/**
 * BranchRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class BranchRepository extends \Doctrine\ORM\EntityRepository
{
    public function validate($form) {

        $errors = array();


        $action = $form['action'];

        // d = delete
        if($action !== 'd') {

            
            $branchExist = $this->getEntityManager()->getRepository(BranchEntity::class)
                ->createQueryBuilder('c')
                ->where('c.id != :id')
                ->andWhere('c.description = :description')
                ->andWhere('c.isDeleted = :is_deleted')
                ->andWhere('c.company = :company')
                ->setParameters(array(
                    'id' => $form['id'],
                    'description' => $form['description'],
                    'is_deleted' => false,
                    'company' => base64_decode($form['company'])
                ))
                ->getQuery()->getResult();
            
            if($action != 'u' && $branchExist){
                $errors[] = 'Branch already exist.';
            }

            if(empty($form['description'])){
                $errors[] = 'Description should not be blank.';
            }
        }

        return $errors;
    }

    public function ajax_list(array $get, array $userData){

        $columns = array(
            array('c.`code`', 'c.`code`'),
            array('c.`description`', 'c.`description`'),
            array('c.`id`', "c.`id`")
        );
        $asColumns = array();

        $select = "SELECT";
        $from = "FROM `branch` c";
        $joins = "";
        $sqlWhere = "WHERE c.`is_deleted` = 0";
        $groupBy = "";
        $orderBy = "";
        $limit = "";
        $stmtParams = array();

        if($userData['type'] != 'Super Admin' || $get['companyId']){

            $sqlWhere .= " AND c.`company_id` = :companyId";
            $stmtParams['companyId'] =  base64_decode($get['companyId']);
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
        $hasUpdate = false;

        if($userData['type'] == 'Super Admin'|| in_array('Company View Branch Update', $userData['accesses'])){

            $formUrl = $url . 'company/' .$get['companyId']. '/branch/form';  
            $hasUpdate = true;
        }

        foreach($result as $row) {

            $id = base64_encode($row['id']);

            $action = $hasUpdate ? "<a class='action-button-style btn btn-primary' href='$formUrl/u/$id'>Update</a>" : "";

            $values = array(
                $row['code'],
                $row['description'],
                $action
            );

            $output['data'][] = $values;
        }

        unset($result);

        return $output;
    }

    public function autocomplete_suggestions($q, array $userData) {
       
        $stmtParams = array();

        $qs = $q['query'];

        $where = ' WHERE b.`description` LIKE :description';
        $stmtParams['description'] =  "%$qs%"; 

        if($userData['type'] != 'Super Admin' || !empty($q['companyId'])){

            $where.= ' AND b.`company_id` = :companyId';
            $stmtParams['companyId'] = base64_decode($q['companyId']);
        }
      
        $sql = "
            SELECT
                 b.`id`,
                 b.`description` AS data,
                 b.`description` AS value
            FROM `branch` b
            ". $where ."
            AND b.`is_deleted` = 0
            ORDER BY b.`description`
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
