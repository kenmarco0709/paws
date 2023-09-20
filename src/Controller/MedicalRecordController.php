<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;

use App\Entity\AdmissionTypeEntity;
use App\Entity\MedicalRecordEntity;
use App\Service\AuthService;
use App\Form\MedicalRecordForm;
use App\Entity\MedicalRecordLaboratoryEntity;
use App\Entity\MedicalRecordPrescriptionInventoryItemEntity;
use App\Entity\InventoryItemEntity;
use App\Entity\ScheduleEntity;
use App\Entity\SchedulePetEntity;


/**
 * @Route("/medical_record")
 */
class MedicalRecordController extends AbstractController
{
    
    /**
     * @Route("/ajax_list", name="medical_record_ajax_list")
     */
    public function ajax_list(Request $request, AuthService $authService) {

        $get = $request->query->all();

        $result = array(
            "draw" => intval($get['draw']),
            "recordsTotal" => 0,
            "recordsFiltered" => 0,
            "data" => array()
        );

        if($authService->isLoggedIn()) {

            $downloadMedicalRecord = $authService->getUser()->getType() == 'Super Admin'  ||  $authService->isUserHasAccesses(array('Pet Details Medical Records Download')) ? true : false;
            
            $data = $this->getDoctrine()->getManager()->getRepository(MedicalRecordEntity::class)->ajax_list($get, $this->get('session')->get('userData'));
            $url = $get['url'] . 'admission/print/medical_record';

            foreach($data['results'] as $row) {

                $id = base64_encode($row['id']);
                $action = '';

                $action .= $downloadMedicalRecord ?  '<a href="'.$url.'/'.$id.'" class="action-button-style btn btn-primary table-btn" target="_blank" > Download</a>' : "";
                $action .= ' <a href="/medical_record/form/u/'.$id.'" class="action-button-style btn btn-success table-btn" > Update</a>';
                $action .= ' <a href="javascript:void(0);" class="href-modal action-button-style btn btn-warning table-btn" data-type="send-medical-record" data-id="'.$id.'"> Send</a>';
                $values = array(
                    $row['medicalRecordDate'],
                    $row['primaryComplain'],
                    $action
                );

                $result['data'][] = $values;
            }

            $result['recordsTotal'] = $data['total'];
            $result['recordsFiltered'] = $data['total'];
        }

        return new JsonResponse($result);
    }

           /**
     * @Route(
     *      "/form/{action}/{id}",
     *      defaults = {
     *          "action":  "n",
     *          "id": 0
     *      },
     *      requirements = {
     *          "action": "n|u"
     *      },
     *      name = "medical_record_form"
     * )
     */
    public function formAction($action, $id, Request $request, AuthService $authService) {

        if(!$authService->isLoggedIn()) return $authService->redirectToLogin();
        if(!$authService->getUser()->getType() == 'Super Admin') return $authService->redirectToHome();

        $em = $this->getDoctrine()->getManager();

        $medicalRecord = $em->getRepository(MedicalRecordEntity::class)->find(base64_decode($id));
        if(!$medicalRecord) {
            $medicalRecord = new MedicalRecordEntity();
        }

        $formOptions = array('action' => $action);
        $form = $this->createForm(MedicalRecordForm::class, $medicalRecord, $formOptions);
        
        if($request->getMethod() === 'POST') {

            $medicalRecord_form = $request->get($form->getName());

            
            $result = $this->processForm($medicalRecord_form, $medicalRecord, $form, $request);
            if($result['success']) {
                if($result['redirect'] === 'pet details') {
                    return $this->redirect($this->generateUrl('pet_details' , array( 'id' => $result['id'])), 302);
                } else if($result['redirect'] === 'form') {
                    return $this->redirect($this->generateUrl('medical_record_form', array(
                        'id' => $id,
                        'action' => $action
                    )), 302);
                } else if($result['redirect'] === 'form with error') {
                    return $this->redirect($this->generateUrl('medical_record_form', array(
                        'id' => $id,
                        'action' => $action
                    )), 302);
                }
            } else {
                $form->submit($medicalRecord_form, false);
            }
        }

        $title = 'Update Medical Record';

        return $this->render('MedicalRecord/form.html.twig', array(
            'title' => $title,
            'page_title' => $title,
            'form' => $form->createView(),
            'action' => $action,
            'id' => $id,
            'javascripts' => array('/js/medical_record/form.js'),
            'medicalRecord' => $medicalRecord
        ));
    }

    private function processForm($medical_record_form, $medicalRecord ,$form, Request $request) {

        $em = $this->getDoctrine()->getManager();

        $errors = [];
        $result = [];
        if(!count($errors)) {
            switch($medical_record_form['action']) {
             
                case 'u': // update

                    $result['success'] = true;
                    $form->handleRequest($request);

                    $em->flush();

                    $medicalRecordFormData = $request->request->get('medical_record');
                    if(isset($_FILES['medical_record']) && !empty($_FILES['medical_record']['name']['laboratory']) && count($_FILES['medical_record']['name']['laboratory']) && isset($_FILES['medical_record']['name']['laboratory'])){
                        
                        foreach($_FILES['medical_record']['name']['laboratory'] as $lk => $record){

                            $baseName = $medicalRecord->getId() . '-' . time() . '.' . pathinfo($_FILES['medical_record']['name']['laboratory'][$lk], PATHINFO_EXTENSION);
                            $uploadFile = $medicalRecord->getUploadRootDir() . '/' . $baseName;
            
                            if(move_uploaded_file($_FILES['medical_record']['tmp_name']['laboratory'][$lk], $uploadFile)) {
                            
                                $medicalRecordLaboratory = new MedicalRecordLaboratoryEntity();
                                $medicalRecordLaboratory->setFileDesc($_FILES['medical_record']['name']['laboratory'][$lk]);
                                $medicalRecordLaboratory->setParsedFiledDesc($baseName);
                                $medicalRecordLaboratory->setMedicalRecord($medicalRecord);
                                $em->persist($medicalRecordLaboratory);
                                $em->flush();
                            
                            }
                        }
                    }

                    if(isset($medicalRecordFormData['prescription_item'])){
                        //Process Inventory Items 

                        foreach($medicalRecordFormData['prescription_item'] as $item){

                            switch ($medicalRecord->getAdmissionPet()->getAdmission()->getAdmissionType()->getDescription()){
                                case 'Confinement':
                                case 'Consultation':
                                    if(!in_array($item['id'], $medicalRecord->medicalRecordPrescriptionItemIdsArray())){
                                    
                                        $medicalItem = new MedicalRecordPrescriptionInventoryItemEntity();
                                        $medicalItem->setInventoryItem($em->getReference(InventoryItemEntity::class, $item['id']));
                                        $medicalItem->setMedicalRecord($medicalRecord);
                                
                                    } else {

                                        $medicalItem = $em->getRepository(MedicalRecordPrescriptionInventoryItemEntity::class)->findOneBy(array('medicalRecord' => $medicalRecord->getId(), 'inventoryItem' => $item['id']));
                                    }

                                    $medicalItem->setQuantity($item['quantity']);
                                    $medicalItem->setRemarks($item['remarks']);
                                    $em->persist($medicalItem);
                                    $em->flush();

                                    break;        
                            }
                        }
                    }

                    $this->processNextSchedule($em, $medicalRecord, $medicalRecordFormData);
                    
                    $result['redirect'] = 'pet details';
                    $result['id'] = $medicalRecord->getAdmissionPet()->getPet()->getIdEncoded();
                    $this->get('session')->getFlashBag()->set('success_messages', 'Record Successfully updated.');

                   
                    break;    
                case 'd': // delete
                    $form->handleRequest($request);


                    if ($form->isValid()) {

                        $medicalRecord->setIsDeleted(true);
                        $em->flush();

                        $this->get('session')->getFlashBag()->set('success_messages', 'Record Successfully Deleted.');

                        $result['redirect'] = 'index';

                    } else {

                        $this->get('session')->getFlashBag()->set('error_messages', 'Something went wrong. Please try again.');
                        $result['redirect'] = 'form with error';
                    }

                    break;
            }

            $result['success'] = true;

        } else {

            foreach($errors as $error) {
                $this->get('session')->getFlashBag()->add('error_messages', $error);
            }

            $result['redirect'] = 'index';
            $result['success'] = false;
        }

        return $result;
    }

    private function processNextSchedule($em , $newMedicalRecord, $admissionFormData){

        if(isset($admissionFormData['schedule'])){
            
            foreach($admissionFormData['schedule'] as $scheduleD){
                if(!empty($scheduleD['remarks'])){     
                    $schedule  = new ScheduleEntity();
                    if(isset($scheduleD['id'])){
                        $schedule = $em->getRepository(ScheduleEntity::class)->find(base64_decode($scheduleD['id']));
                    }

                    $admissionPet = $newMedicalRecord->getAdmissionPet();
                    $schedule->setBranch($admissionPet->getAdmission()->getBranch());
                    $schedule->setAdmissionType($admissionPet->getAdmission()->getAdmissionType());
                    $schedule->setStatus('New');
                    $schedule->setScheduleDate(new \DateTime($scheduleD['returned_date']));
                    $schedule->setSmsStatus('Unsent');
                    $schedule->setMedicalRecord($newMedicalRecord);
                    $schedule->setClient($admissionPet->getAdmission()->getClient());
                    $schedule->setRemarks($scheduleD['remarks']);
                    $em->persist($schedule);
                    $em->flush();
                    
                    if(!in_array($admissionPet->getPet()->getId(), $schedule->schedulePetIds())){

                        $schdulePet = new SchedulePetEntity();
                        $schdulePet->setSchedule($schedule);
                        $schdulePet->setPet($admissionPet->getPet());
                        $em->persist($schdulePet);
                        $em->flush();
                    }
                }
            }
        }
    }

    /**
     * @Route("/shelter_ajax_list", name="medical_record_shelter_ajax_list")
     */
    public function shelter_ajax_list(Request $request, AuthService $authService) {

        
      $get = $request->query->all();

      $result = array(
          "draw" => intval($get['draw']),
          "recordsTotal" => 0,
          "recordsFiltered" => 0,
          "data" => array()
      );

      if($authService->isLoggedIn()) {
          $result = $this->getDoctrine()->getManager()->getRepository(MedicalRecordEntity::class)->shelter_ajax_list($get, $this->get('session')->get('userData'));
      }

      return new JsonResponse($result);
    }
   
}
