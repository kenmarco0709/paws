<?php

namespace App\Repository;

use App\Entity\PetPhotoEntity;


/**
 * PetPhotoRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class PetPhotoRepository extends \Doctrine\ORM\EntityRepository
{
    public function validate($form) {

        $errors = array();

        $action = $form['action'];

        // d = delete
        if($action != 'd') {

            // if(empty($form['behavior_record_date'])){
            //     $errors[] = 'Date should not be blank.';
            // }

        } 

        return $errors;
    }



    public function ajax_list(array $get, array $userData){


        $columns = array(
            array("DATE_FORMAT(pt.`created_at`, '%m/%d/%Y')", "DATE_FORMAT(pt.`created_at`, '%m/%d/%Y')", 'petPhotoDate'),
            array('pt.`before_description`', 'pt.`before_description`'),
            array('pt.`after_description`', 'pt.`after_description`'),
            array('pt.`remarks`', 'pt.`remarks`'),
            array('pt.`id`', 'pt.`id`'),
            array('pt.`pet_id`', "pt.`pet_id`"),
            array('pt.`parsed_before_description`', "pt.`parsed_before_description`"),
            array('pt.`parsed_after_description`', "pt.`parsed_after_description`"),




        );


        $asColumns = array();
        $select = "SELECT ";
        $from = "FROM `pet_photo` pt";
        $joins = "";
        $sqlWhere = ' WHERE pt.`is_deleted` != 1';
        $groupBy = "";
        $orderBy = "";
        $limit = "";
        $stmtParams = array();
        
        if(isset($get['petId'])){
            
            $sqlWhere.= " AND pt.`pet_id` = :petId";
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
        $formUrl = $url . 'pet_photo/ajax_form';  
        $hasUpdate = $userData['type'] == 'Super Admin'  || in_array('Shelter Admission Details Pet Photos Update', $userData['accesses']) ? true : false;

        foreach($result as $row) {

            $id = base64_encode($row['id']);
            $petId = base64_encode($row['pet_id']);


            $action ='';
      

                $action .= $hasUpdate ? '<a href="javascript:void(0);" class="href-modal action-btn action-button-style btn btn-primary table-btn" data-id="'.$id.'" data-petid="'. $petId .'" data-url="'.$formUrl.'" data-action="u">Update</a>' : "";

                $values = array(
                    $row['petPhotoDate'],
                    "<a href='".$url."uploads/photo/".$row['parsed_before_description']."' target='_blank'>".$row['before_description']."</a>",
                    "<a href='".$url."uploads/photo/".$row['parsed_after_description']."' target='_blank'>".$row['after_description']."</a>",
                    $row['remarks'],
                    $action
                );

            $output['data'][] = $values;
        }

        unset($result);

        return $output;
    }


}
