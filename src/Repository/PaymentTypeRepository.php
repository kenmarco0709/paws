<?php

namespace App\Repository;

use App\Entity\PaymentTypeEntity;

/**
 * PaymentTypeRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class PaymentTypeRepository extends \Doctrine\ORM\EntityRepository
{
    public function validate($form) {

        $errors = array();

        $action = $form['action'];

        // d = delete
        if($action !== 'd') {

            
            $payment_typeExist = $this->getEntityManager()->getRepository(PaymentTypeEntity::class)
                ->createQueryBuilder('c')
                ->where('c.id != :id')
                ->andWhere('c.description = :description')
                ->andWhere('c.isDeleted = :is_deleted')
                ->andWhere('c.branch = :branchId')

                ->setParameters(array(
                    'id' => $form['id'],
                    'description' => $form['description'],
                    'is_deleted' => false,
                    'branchId' => $form['branch'],
                ))
                ->getQuery()->getResult();
            
            if($action != 'u' && $payment_typeExist){
                $errors[] = 'Payment Type already exist.';
            }

            if(empty($form['description'])){
                $errors[] = 'Description should not be blank.';
            }
        }

        return $errors;
    }

    public function ajax_list(array $get){

        $columns = array(
            array('c.`code`', 'c.`code`'),
            array('c.`description`', 'c.`description`'),
            array('c.`id`', "c.`id`")
        );
        $asColumns = array();

        $select = "SELECT";
        $from = "FROM `payment_type` c";
        $joins = " ";
        $sqlWhere = "WHERE c.`is_deleted` = 0";
        $groupBy = "";
        $orderBy = "";
        $limit = "";
        $stmtParams = array();

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
                s.`id`,
                s.`description` AS data,
                s.`description` AS value
            FROM `payment_type` s
            WHERE s.`description` LIKE :q
            $andWhere
            AND s.`is_deleted` = 0
            ORDER BY s.`description`
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
