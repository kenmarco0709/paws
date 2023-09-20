<?php

namespace App\Repository;

use App\Entity\ScheduleEntity;

/**
 * ScheduleRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class VetScheduleRepository extends \Doctrine\ORM\EntityRepository
{

    public function validate($form) {


        $errors = array();

        $action = $form['action'];

        // d = delete
        if(!in_array($form['action'], ['d']) ) {
            
            if(empty($form['vet'])){
                $errors[] = 'Please select a vet';
            }

            if(empty($form['schedule_date_from'])){
                $errors[] = 'Please select a start date';
            }

            if(strtotime(date('m/d/Y')) > strtotime(($form['schedule_date_from']))){
                $errors[] = 'System cant schedule past days.';

            }
        }

        return $errors;
    }

       public function branchVetSchedules($dates, array $userData){

        $result =[];
        $stmtParams =[];
        $andWhere = '';

        if($userData['type'] != 'Super Admin'){

             $andWhere.= ' WHERE s.branch_id = :branchId'; 
             $stmtParams['branchId'] = $userData['branchId'];   
        }


        $query = $this->getEntityManager()->getConnection()->prepare("
              SELECT 
                TO_BASE64(s.`id`) AS id,
                DATE_FORMAT(s.`schedule_date_from` ,'%Y') AS scheduleFromYear,
                DATE_FORMAT(s.`schedule_date_from` , '%m') AS scheduleFromMonth,
                DATE_FORMAT(s.`schedule_date_from` , '%d') AS scheduleFromDay,
                DATE_FORMAT(s.`schedule_date_to` ,'%Y') AS scheduleToYear,
                DATE_FORMAT(s.`schedule_date_to` , '%m') AS scheduleToMonth,
                DATE_FORMAT(s.`schedule_date_to` , '%d') AS scheduleToDay,
                s.`schedule_time_from` AS scheduleFromTime,
                s.`schedule_time_to` AS scheduleToTime,
                s.`schedule_type` AS scheduleType,
                CONCAT(attendingVet.`first_name`, ' ' , attendingVet.`last_name`) AS vet
              FROM `vet_schedule` s
              LEFT JOIN `user` attendingVet ON attendingVet.`id` = s.`vet_id`
              $andWhere
        ");

        foreach($stmtParams as $k => $v){
            $query->bindValue($k, $v);
        }
       
        $res = $query->executeQuery();
        $result = $res->fetchAllAssociative();
        return $result;

    }


}
