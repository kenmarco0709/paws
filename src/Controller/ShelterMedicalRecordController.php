<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;

use App\Entity\ServiceEntity;
use App\Entity\MedicalRecordServiceEntity;
use App\Entity\AdmissionTypeEntity;
use App\Entity\MedicalRecordEntity;
use App\Entity\MedicalRecordLaboratoryEntity;
use App\Entity\MedicalRecordItemEntity;
use App\Entity\MedicalRecordPrescriptionInventoryItemEntity;
use App\Entity\InventoryItemEntity;

use App\Service\AuthService;
use App\Form\ShelterMedicalRecordForm;

/**
 * @Route("/shelter_medical_record")
 */
class ShelterMedicalRecordController extends AbstractController
{
    

     /**
     * @Route("/ajax_select_admission_type_form", name="shelter_medical_record_ajax_select_admission_type_form")
     */
    public function select_admission_type_ajaxForm(Request $request, AuthService $authService): JsonResponse
    {
       
       $result = [ 'success' =>  true, 'msg' => ''];
       if(!$authService->isLoggedIn()) { $result['success'] = false; $result['msg'] = 'Unauthorized access please call a system administrator.';}
       $formData = $request->request->all();
       $em = $this->getDoctrine()->getManager();
       $admissionTypes = $em->getRepository(AdmissionTypeEntity::class)->findAll();
       $result['html'] = $this->renderView('ShelterMedicalRecord/ajax_select_admission_type_form.html.twig', [
            'page_title' => ' Select Treatment Type',
            'admissionTypes' => $admissionTypes,
            'id' => $formData['id'],
            'action' => $formData['action'],
            'admissionId' => $formData['admissionId'] 
        ]);

       return new JsonResponse($result);
    }

       /**
     * @Route("/ajax_form", name="shelter_medical_record_ajax_form")
     */
    public function ajaxForm(Request $request, AuthService $authService): JsonResponse
    {
       
       $result = [ 'success' =>  true, 'msg' => ''];
       if(!$authService->isLoggedIn()) { $result['success'] = false; $result['msg'] = 'Unauthorized access please call a system administrator.';}
       
       $formData = $request->request->all();
       $em = $this->getDoctrine()->getManager();

       $admissionType = $em->getRepository(AdmissionTypeEntity::class)->find(base64_decode($formData['admissionType']));
       $medicalRecord = $em->getRepository(MedicalRecordEntity::class)->find(base64_decode($formData['id']));
       
       if(!$medicalRecord) {
          $medicalRecord = new MedicalRecordEntity();
       }

       $vaccinnes = $em->getRepository(ServiceEntity::class)->getServiceByServiceTypeDesc('Vaccination', $this->get('session')->get('userData'));
       $formOptions = array('action' => $formData['action'] , 'admissionId' => $formData['admissionId'], 'admissionType' => $formData['admissionType']);
       $form = $this->createForm(ShelterMedicalRecordForm::class, $medicalRecord, $formOptions);
    
       $result['html'] = $this->renderView('ShelterMedicalRecord/ajax_form.html.twig', [
            'page_title' => ($formData['action'] == 'n' ? 'New ' : ' Update') . ' Medical Record - ' . $admissionType->getDescription(),
            'form' => $form->createView(),
            'action' => $formData['action'],
            'admissionType' => $admissionType,
            'javascripts' => array('/js/shelter_medical_record/ajax_form.js'),
            'vaccinnes' => $vaccinnes,
            'medicalRecord' => $medicalRecord

        ]);

       return new JsonResponse($result);
    }

        /**
     * @Route("/ajax_form_process", name="shelter_medical_record_ajax_form_process")
     */
    public function ajaxFormProcess(Request $request, AuthService $authService): JsonResponse
    {
       
       $result = [ 'success' =>  true, 'msg' => ''];

       if(!$authService->isLoggedIn()) { $result['success'] = false; $result['msg'] = 'Unauthorized access please call a system administrator.';}

       if($request->getMethod() == "POST"){

         $shelterMedicalRecordForm = $request->request->get('shelter_medical_record_form');         
         $em = $this->getDoctrine()->getManager();
         //$errors = $em->getRepository(BehaviorRecordEntity::class)->validate($behaviorRecordForm);
         $errors = [];
         if(!count($errors)){
            
            $shelterMedicalrecord = $em->getRepository(MedicalRecordEntity::class)->find($shelterMedicalRecordForm['id']);
            
            if(!$shelterMedicalrecord) {
               $shelterMedicalrecord = new MedicalRecordEntity();
            }
     
            $formOptions = array('action' => $shelterMedicalRecordForm['action'] , 'admissionId' => $shelterMedicalRecordForm['shelter_admission'], 'admissionType' => $shelterMedicalRecordForm['admission_type']);
            $form = $this->createForm(ShelterMedicalRecordForm::class, $shelterMedicalrecord, $formOptions);


            switch($shelterMedicalRecordForm['action']) {
               case 'n': // new

                     $form->handleRequest($request);
      
                     if ($form->isValid()) {
                        
                        $em->persist($shelterMedicalrecord);
                        $em->flush();

                        $this->processLaboratory($_FILES, $shelterMedicalrecord, $em);
                        $this->processPrescription($shelterMedicalRecordForm, $shelterMedicalrecord, $em);
                        $this->processItem($shelterMedicalRecordForm, $shelterMedicalrecord, $em);
                        $this->processService($shelterMedicalRecordForm, $shelterMedicalrecord, $em);
                        $this->processVaccine($shelterMedicalRecordForm, $shelterMedicalrecord, $em);

                        $this->processLaboratory($_FILES, $shelterMedicalrecord, $em);
                        $result['msg'] = 'Medical Record successfully added to record.';
                     } else {
      
                        $result['error'] = 'Ooops something went wrong please try again later.';
                     }
      
                     break;
      
               case 'u': // update

                     $form->handleRequest($request);
                     if ($form->isValid()) {
      
                        $em->persist($shelterMedicalrecord);
                        $em->flush();
                        
                        $this->processRemove($shelterMedicalRecordForm, $shelterMedicalrecord, $em);
                        $this->processLaboratory($_FILES, $shelterMedicalrecord, $em);
                        $this->processPrescription($shelterMedicalRecordForm, $shelterMedicalrecord, $em);
                        $this->processItem($shelterMedicalRecordForm, $shelterMedicalrecord, $em);
                        $this->processService($shelterMedicalRecordForm, $shelterMedicalrecord, $em);
                        $this->processVaccine($shelterMedicalRecordForm, $shelterMedicalrecord, $em);

                        $result['msg'] = 'Medical Record Record successfully updated.';
                     } else {
                       
                        $result['error'] = 'Ooops something went wrong please try again later.';
                     }
      
                     break;
      
               case 'd': // delete
                     $form->handleRequest($request);
                     if ($form->isValid()) {
                          
                        $shelterMedicalrecord->setIsDeleted(true);
                        $em->flush();
      
                        $result['msg'] = 'Medical Record Record successfully deleted.';
      
                     } else {
      
                        $result['error'] = 'Ooops 3something went wrong please try again later.';
                     }
      
                     break;
            }
        
         } else {

             $result['success'] = false;
             $result['msg'] = '';
             foreach ($errors as $error) {
                 
                 $result['msg'] .= $error;
             }
         }
     } else {

         $result['error'] = 'Ooops something went wrong please try again later.';
     }
    
       return new JsonResponse($result);
    }

    private function processRemove($form, $medicalRecord, $em){

         $laboratoryIds = isset($form['laboratory_id']) ? $form['laboratory_id'] : []; 
         $prescriptionItemIds = isset($form['prescription_item_id']) ? $form['prescription_item_id'] : []; 
         $serviceItemIds = isset($form['service_item_id']) ? $form['service_item_id'] : []; 
         $treatmentIds = isset($form['treatment_id']) ? $form['treatment_id'] : []; 

        
         if(isset($form['service'])){
            $treatmentIds = array_merge($treatmentIds,$form['service']);
         } 

         //delete laboratory
         foreach($medicalRecord->getMedicalRecordLaboratories() as $laboratory){
            if(!in_array($laboratory->getIdEncoded(), $laboratoryIds)){
               $laboratory->setIsDeleted(true);
               $em->flush();
            }
         } 

         //delete prescription
         foreach($medicalRecord->getMedicalRecordPrescriptionInventoryItems() as $item){
            if(!in_array($item->getId(),  $prescriptionItemIds)){
               $item->setIsDeleted(true);
               $em->flush();
            }
         } 
         //delete items
         foreach($medicalRecord->getMedicalRecordItems() as $item){
            if(!in_array($item->getId(),   $serviceItemIds)){
               $item->setIsDeleted(true);
               $em->flush();
            }
         }
         //delete services
         foreach($medicalRecord->getMedicalRecordServices() as $service){
            if(!in_array($service->getId(), $treatmentIds)){
               $service->setIsDeleted(true);
               $em->flush();
            }
         }
    }
    
    private function processLaboratory($files, $medicalRecord,$em){

      if(isset($files) && isset($files['shelter_medical_record'])){

         if(isset($files['shelter_medical_record']) && !empty($files['shelter_medical_record']['tmp_name']['laboratory'])) {
            
            foreach($files['shelter_medical_record']['tmp_name']['laboratory'] as $k => $file){
               
               if(!empty($file)){
                
                  $medicalRecordlaboratory = new MedicalRecordlaboratoryEntity();
                  $medicalRecordlaboratory->setMedicalRecord($medicalRecord);
                  $baseName = $medicalRecordlaboratory->getId() . '-' . time() . '.' . pathinfo($files['shelter_medical_record']['name']['laboratory'][$k], PATHINFO_EXTENSION);
                  $uploadFile = $medicalRecordlaboratory->getUploadRootDir() . '/' . $baseName;

                  if(move_uploaded_file($files['shelter_medical_record']['tmp_name']['laboratory'][$k], $uploadFile)) {

                     $medicalRecordlaboratory->setFileDesc($files['shelter_medical_record']['name']['laboratory'][$k]);
                     $medicalRecordlaboratory->setParsedFiledDesc($baseName);
                     $em->persist($medicalRecordlaboratory);
                     $em->flush();

                  }
               }
            }
         }  
      }    
   }

   private function processPrescription($form, $medicalRecord, $em){

      if(isset($form['prescription_item'])){
         
         foreach ($form['prescription_item'] as $key => $i) {

            if(isset($i['prescription_item_id'])){
               $prescriptionItem = $em->getRepository(MedicalRecordPrescriptionInventoryItemEntity::class)->find($i['prescription_item_id']);
            } else {

               $prescriptionItem =  new MedicalRecordPrescriptionInventoryItemEntity();
            }   

            $prescriptionItem->setRemarks($i['remarks']);
            $prescriptionItem->setQuantity($i['quantity']);
            $prescriptionItem->setMedicalRecord($medicalRecord);
            $prescriptionItem->setInventoryItem($em->getReference(InventoryItemEntity::class, $i['id']));
            $em->persist($prescriptionItem);
            $em->flush();
         }
         
      }
   }

   private function processItem($form, $medicalRecord, $em){

      if(isset($form['service_item'])){
         
         foreach ($form['service_item'] as $key => $i) {

            if(isset($i['service_item_id'])){
               $item = $em->getRepository(MedicalRecordItemEntity::class)->find($i['service_item_id']);
            } else {

               $item =  new MedicalRecordItemEntity();
            }   

            $item->setRemarks($i['remarks']);
            $item->setQuantity($i['quantity']);
            $item->setMedicalRecord($medicalRecord);
            $item->setInventoryItem($em->getReference(InventoryItemEntity::class, $i['id']));
            $em->persist($item);
            $em->flush();
         }
         
      }
   }

   private function processService($form, $medicalRecord, $em){

      if(isset($form['treatment'])){

         foreach ($form['treatment'] as $key => $i) {

            if(isset($i['treatment_id'])){
               $item = $em->getRepository(MedicalRecordServiceEntity::class)->find($i['treatment_id']);
            } else {

               $item =  new MedicalRecordServiceEntity();
            }   

            $item->setRemarks($i['remarks']);
            $item->setMedicalRecord($medicalRecord);
            $item->setService($em->getReference(ServiceEntity::class, $i['id']));
            $em->persist($item);
            $em->flush();
         }
         
      }
   }

   private function processVaccine($form, $medicalRecord, $em){
     
      if(isset($form['service'])){

         foreach ($form['service'] as $key => $i) {       
                        
            $mService = $em->getRepository(MedicalRecordServiceEntity::class)->findOneBy(array(
               'medicalRecord' => $medicalRecord->getId(),
               'service' => $i,
               'isDeleted' => 0
            ));
         
            if(!$mService){

               $item =  new MedicalRecordServiceEntity();
               $item->setMedicalRecord($medicalRecord);
               $item->setService($em->getReference(ServiceEntity::class, $i));
               $em->persist($item);
               $em->flush();
            }
         }
      }
   }

   
}
