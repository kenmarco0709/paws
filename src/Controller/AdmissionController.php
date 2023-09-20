<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Knp\Snappy\Pdf;
use Symfony\Component\HttpFoundation\Response;

use App\Entity\AdmissionEntity;
use App\Form\AdmissionForm;

use App\Entity\AdmissionAccessEntity;
use App\Entity\ServiceEntity;
use App\Entity\UserEntity;
use App\Entity\MedicalRecordEntity;
use App\Entity\MedicalRecordServiceEntity;
use App\Entity\AdmissionPetEntity;
use App\Entity\MedicalRecordItemEntity;
use App\Entity\MedicalRecordPrescriptionInventoryItemEntity;
use App\Entity\MedicalRecordLaboratoryEntity;
use App\Entity\MedicalRecordPhotoEntity;
use App\Entity\InventoryItemEntity; 
use App\Entity\AdmissionTypeEntity;
use App\Entity\BillingEntity;
use App\Entity\ScheduleEntity;
use App\Entity\SchedulePetEntity;
use App\Entity\InvoiceAdmissionInventoryItemEntity;
use App\Entity\InvoiceAdmissionServiceEntity;
use App\Entity\InvoiceEntity;

use App\Service\AuthService;
use App\Service\InventoryService;

/**
 * @Route("/admission")
 */
class AdmissionController extends AbstractController
{
    /**
     * @Route("", name="admission_index")
     */
    public function index(Request $request, AuthService $authService)
    {
        if(!$authService->isLoggedIn()) return $authService->redirectToLogin();
        if(!$authService->isUserHasAccesses(array('Admission'))) return $authService->redirectToHome();

        $em = $this->getDoctrine()->getManager();        
       $page_title = ' Client'; 
       return $this->render('Admission/index.html.twig', [ 
            'page_title' => $page_title, 
            'javascripts' => array('/plugins/datatables/jquery.dataTables.js','/js/admission/index.js')
        ]
       );
    }

    /**
     * @Route("/confinement", name="admission_confinement")
     */
    public function confinement(Request $request, AuthService $authService)
    {
       
       if(!$authService->isLoggedIn()) return $authService->redirectToLogin();
       if(!$authService->isUserHasAccesses(array('Admission Confinement'))) return $authService->redirectToHome();

       $em = $this->getDoctrine()->getManager();        
       $page_title = ' Confinement'; 
       return $this->render('Admission/confinement.html.twig', [ 
            'page_title' => $page_title, 
            'javascripts' => array('/plugins/datatables/jquery.dataTables.js','/js/admission/index.js')
        ]
       );
    }



    /**
     * @Route("/ajax_list", name="admission_ajax_list")
     */
    public function ajax_list(Request $request, AuthService $authService) {

        $get = $request->query->all();

        $result = array(
            "draw" => intval($get['draw']),
            "recordsTotal" => 0,
            "recordsFiltered" => 0,
            "data" => array()
        );

        $get = $request->query->all();

        $result = array(
            "draw" => intval($get['draw']),
            "recordsTotal" => 0,
            "recordsFiltered" => 0,
            "data" => array()
        );

        if($authService->isLoggedIn()) {
            $result = $this->getDoctrine()->getManager()->getRepository(AdmissionEntity::class)->ajax_list($get, $this->get('session')->get('userData'));
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
     *      name = "admission_form"
     * )
     */
    public function formAction($action, $id, Request $request, AuthService $authService, InventoryService $inventoryService) {

        if(!$authService->isLoggedIn()) return $authService->redirectToLogin();
        if(!$authService->getUser()->getType() == 'Super Admin') return $authService->redirectToHome();
        
        $em = $this->getDoctrine()->getManager();
        $services = $em->getRepository(ServiceEntity::class)->findBy(array('isDeleted' => false, 'branch' => $authService->getUser()->getBranch()->getId()));
        $confinementType = !is_null($request->query->get('admissionType')) ? $request->query->get('admissionType') : '' ; 
        $admission = $em->getRepository(AdmissionEntity::class)->find(base64_decode($id));
        if(!$admission) {
            $admission = new AdmissionEntity();
        }

        $formOptions = array('action' => $action, 'branchId' => $authService->getUser()->getBranch()->getId());
        $form = $this->createForm(AdmissionForm::class, $admission, $formOptions);
        
        if($request->getMethod() === 'POST') {

            $admission_form = $request->get($form->getName());
            $result = $this->processForm($admission_form, $admission, $form, $request, $inventoryService);
            if($result['success']) {
                if($result['redirect'] === 'index') {
                    return $this->redirect($this->generateUrl('admission_index'), 302);
                } else if($result['redirect'] === 'form') {
                    return $this->redirect($this->generateUrl('admission_form', array(
                        'action' => 'u',
                        'id' => base64_encode($result['id'])
                    )), 302);
                } else if($result['redirect'] === 'invoice') {
                    return $this->redirect($this->generateUrl('invoice_form', array(
                        'action' => 'u',
                        'id' => base64_encode($result['invoiceId'])
                    )), 302);
                } else if($result['redirect'] === 'form with error') {
                    return $this->redirect($this->generateUrl('admission_form'), 302);
                }
            } else {
                $form->submit($admission_form, false);
            }
        }

        $title = ($action === 'n' ? 'New' : 'Update') . ' Client';

        $admissionTypeConfinement = $em->getRepository(AdmissionTypeEntity::class)->findOneBy(array('description' => 'Confinement'));
        return $this->render('Admission/form.html.twig', array(
            'title' => $title,
            'page_title' => $title,
            'form' => $form->createView(),
            'action' => $action,
            'id' => $id,
            'admission' => $admission,
            'isUserVet' => $authService->isUserVet(),
            'javascripts' => array('/js/admission/form.js'),
            'services' => $services,
            'confinementType' => $confinementType,
            'admissionTypeConfinement' => $admissionTypeConfinement
        ));
    }

    private function processForm($admission_form, $admission ,$form, Request $request, $inventoryService) {

        $em = $this->getDoctrine()->getManager();

        $errors = $em->getRepository(AdmissionEntity::class)->validate($admission_form);
        if(!count($errors)) {
            switch($admission_form['action']) {
                case 'n': // new

                    $form->handleRequest($request);

                    if ($form->isValid()) {
                        
                        $admission->setStatus('New');
                        $em->persist($admission);
                        $em->flush();

                        $this->get('session')->getFlashBag()->set('success_messages', 'Record Successfully Saved.');

                        $result['redirect'] = 'form';
                        $result['id'] = $admission->getId();

                    } else {

                        $this->get('session')->getFlashBag()->set('error_messages', 'Something went wrong. Please try again.');
                        $result['redirect'] = 'form with error';
                    }

                    break;
                case 'nm': //this create new medical record for time to time medical records
                    $form->handleRequest($request);

                    $admissionFormData = $request->request->get('admission');
                    $result = $this->doUpdate($em, $admission_form, $admissionFormData, $admission);

                    if($result['success']){

                        $admissionPets = $admission->getAdmissionPets();

                        foreach($admissionPets as $admissionPet){
                            $em->refresh($admissionPet);
                            if(count($admissionPet->getMedicalRecords())){  
                                foreach($admissionPet->getMedicalRecords() as $medicalRecord){
                                    if(is_null($medicalRecord->getMedicalRecordHistory())){
                                        $nnMedicalRecord = new MedicalRecordEntity();
                                        $nnMedicalRecord->setAdmissionPet($medicalRecord->getAdmissionPet());
                                        $em->persist($nnMedicalRecord);
                                        $em->flush();
        
                                        $medicalRecord->setMedicalRecordHistory($nnMedicalRecord);
                                        $em->flush();
        
                                        $result['msg'] =  'New Medical Record Successfully Created.';
                                        $result['redirect'] = 'form';
                                        $result['id'] = $admission->getId();
                                    }
                                }
                            }
                        }

                        $this->get('session')->getFlashBag()->add('success_messages', $result['msg']);

                    } else {

                        foreach($result['error_messages'] as $error) {
                            $this->get('session')->getFlashBag()->add('error_messages', $error);
                        }
                    }

                    break;  
                case 'c': //confined
                    $form->handleRequest($request);

                        $admission->setStatus('Is Confined');
                        $newAdmission = new AdmissionEntity();
                        $newAdmission->setAdmissionType($em->getRepository(AdmissionTypeEntity::class)->findOneBy(array('description' => 'Confinement')));
                        $newAdmission->setClient($admission->getClient());
                        $newAdmission->setAdmissionHistory($admission);
                        $newAdmission->setStatus('New');
                        $newAdmission->setBranch($admission->getBranch());
                        $em->flush();

                        if($admission->getAdmissionPets()){
                            foreach($admission->getAdmissionPets() as $pet){

                                $newAdmissionPet =  new AdmissionPetEntity();
                                $newAdmissionPet->setPet($pet->getPet());
                                $newAdmissionPet->setAdmission($newAdmission);
                                $em->persist($newAdmissionPet);
                                $em->flush();
                            }
                        }

                        $this->get('session')->getFlashBag()->set('success_messages', 'Record Successfully Saved.');

                        $result['redirect'] = 'form';
                        $result['id'] = $newAdmission->getId();
                    break;    
                case 'u': // update
                    $form->handleRequest($request);

                    $admissionFormData = $request->request->get('admission');
                    $result = $this->doUpdate($em, $admission_form, $admissionFormData, $admission);

                    if($result['success']){
                        $this->get('session')->getFlashBag()->add('success_messages', $result['msg']);

                    } else {

                        foreach($result['error_messages'] as $error) {
                            $this->get('session')->getFlashBag()->add('error_messages', $error);
                        }
                    }
                
                    break;
                case 'pb':
                    $form->handleRequest($request);

                    $admissionFormData = $request->request->get('admission');
                    $result = $this->doUpdate($em, $admission_form, $admissionFormData, $admission);
                   
                    if($result['success']){
                       
                        $this->get('session')->getFlashBag()->add('success_messages', $result['msg']);

                        $em->refresh($admission);
                        $c_admission = $em->getRepository(AdmissionEntity::class)->findOneBy(array('id' => $admission_form['id']));   
                        $invoice = $em->getRepository(InvoiceEntity::class)->findOneBy(array('admission' => $c_admission));

                        if(!$invoice){
                            $invoice = new InvoiceEntity();
                            $invoice->setAdmission($c_admission);
                            $invoice->setClient($c_admission->getClient());
                            $invoice->setBranch($c_admission->getBranch());
                            $invoice->setStatus('New');
                            $invoice->setInvoiceDate(new \Datetime(date('m/d/Y')));
                            $em->persist($invoice);
                            $em->flush();
                        }

                        $admissionPets = $c_admission->getAdmissionPets();    

                        $medicalRecordItems = [];
                        foreach($admissionPets as $k => $admissionPet){
                            $em->refresh($admissionPet);

                            if(count($admissionPet->getMedicalRecords())){  
                               
                                foreach($admissionPet->getMedicalRecords() as $k2 =>  $medicalRecord){
                                    $em->refresh($medicalRecord);

                                    foreach($medicalRecord->getMedicalRecordServices() as $k3 =>  $medicalRecordService){
                                        $invoiceAdmisssionService = $em->getRepository(InvoiceAdmissionServiceEntity::class)->findOneBy(array(
                                            'admission' => $c_admission->getId(), 
                                            'invoice' => $invoice->getId(),
                                            'service' => $medicalRecordService->getService()->getId()
                                        ));

                                        if(!$invoiceAdmisssionService){

                                            $invoiceAdmisssionService =  new InvoiceAdmissionServiceEntity();
                                            $invoiceAdmisssionService->setInvoice($invoice);
                                            $invoiceAdmisssionService->setAdmission($c_admission);
                                            $invoiceAdmisssionService->setService($medicalRecordService->getService());
                                            $invoiceAdmisssionService->setRemarks($medicalRecordService->getRemarks());
                                            $em->persist($invoiceAdmisssionService);
                                            $em->flush();
                                        }

                                        if($k == 0 && $k2 == 0 && $k3 == 0){
                                            $invoiceAdmisssionService->setQuantity(0);
                                        }

                                       $invoiceAdmisssionService->setQuantity( 1 +  $invoiceAdmisssionService->getQuantity());
                                        $em->flush();
                                    }
                                    
                                    foreach($medicalRecord->getMedicalRecordItems() as $k3 =>  $medicalInventoryItem){
                                
                                      if(isset($medicalRecordItems[$medicalInventoryItem->getInventoryItem()->getId()])){
                                        $medicalRecordItems[$medicalInventoryItem->getInventoryItem()->getId()]['quantity'] = $medicalRecordItems[$medicalInventoryItem->getInventoryItem()->getId()]['quantity'] + $medicalInventoryItem->getQuantity();
                                      } else {
                                        $medicalRecordItems[$medicalInventoryItem->getInventoryItem()->getId()] = [
                                            'quantity' => $medicalInventoryItem->getQuantity(),
                                            'id' => $medicalInventoryItem->getInventoryItem()->getId()
                                          ];
                                      }
                                    }
                                }
                            }
                        }

                        //do invoice medical record items
                        foreach($medicalRecordItems as $k => $medicalRecordItem){
  
                            $invoiceAdmissionInventoryItem =  $em->getRepository(InvoiceAdmissionInventoryItemEntity::class)->findOneBy(array(
                                'admission' => $c_admission->getId(), 
                                'invoice' => $invoice->getId(),
                                'inventoryItem' => $k
                            ));

                            $medicalRecordInventoryItem = $em->getRepository(InventoryItemEntity::class)->find($k);
                            $medicalRecordIi =  $em->getRepository(MedicalRecordItemEntity::class)->findOneBy(array('inventoryItem' => $k));                           
                            if(!$invoiceAdmissionInventoryItem){

                                $invoiceAdmissionInventoryItem =  new InvoiceAdmissionInventoryItemEntity();
                                $invoiceAdmissionInventoryItem->setInvoice($invoice);
                                $invoiceAdmissionInventoryItem->setAdmission($c_admission);
                                $invoiceAdmissionInventoryItem->setInventoryItem($medicalRecordInventoryItem);
                                $invoiceAdmissionInventoryItem->setBuyingPrice($medicalRecordInventoryItem->getBuyingPrice());
                                $invoiceAdmissionInventoryItem->setSellingPrice($medicalRecordInventoryItem->getSellingPrice());
                                if($medicalRecordInventoryItem->getSupplier()){
                                    $invoiceAdmissionInventoryItem->setSupplier($medicalRecordInventoryItem->getSupplier());

                                }

                                $invoiceAdmissionInventoryItem->setRemarks($medicalRecordIi->getRemarks());
                                $em->persist($invoiceAdmissionInventoryItem);
                                $em->flush();
                            }

                            $invoiceAdmissionInventoryItem->setQuantity($medicalRecordItem['quantity']);
                            $em->flush();

                            $inventoryService->processInventory($invoiceAdmissionInventoryItem);
                        }

                        $this->updateBillingInvoice($admission,$em);

                        $this->get('session')->getFlashBag()->set('success_messages', 'Admission successfully billed.');


                        $result['redirect'] = 'invoice';
                        $result['invoiceId'] = $invoice->getId();
                        
                    } else {

                        foreach($result['error_messages'] as $error) {
                            $this->get('session')->getFlashBag()->set('error_messages', $error);
                        }
                    }
                
                    break;    
                case 'd': // delete
                    $form->handleRequest($request);


                    if ($form->isValid()) {

                        $admission->setIsDeleted(true);
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

    private function doUpdate($em, $admission_form, $admissionFormData, $admission){

        $result['success'] = false;
        $em->flush();

        $errors = $this->validateFormData($admissionFormData, $admission);

        if(!count($errors)){

            $admissionPets = $admission->getAdmissionPets();

            if($admissionPets){

                foreach($admissionPets as $k =>  $admissionPet){

                    $newMedicalRecord = null;
                    if(count($admissionPet->getMedicalRecords())){

                        foreach($admissionPet->getMedicalRecords() as $medicalRecord){
                            if(is_null($medicalRecord->getMedicalRecordHistory())){
                                $newMedicalRecord = $medicalRecord;
                            }
                        }

                    }
                    if(is_null($newMedicalRecord)){
                        $newMedicalRecord = new MedicalRecordEntity();
                    }

                    if(isset($admissionFormData['medical_record'][$k]['interpretations'])){
                        $newMedicalRecord->setMedicalInterpretation($admissionFormData['medical_record'][$k]['interpretations']);
                    }

                    if(isset($admissionFormData['medical_record'][$k]['diagnosis'])){
                        $newMedicalRecord->setDiagnosis($admissionFormData['medical_record'][$k]['diagnosis']);
                    }
                    
                    if(isset($admissionFormData['medical_record'][$k]['primary_complain'])){
                        $newMedicalRecord->setPrimaryComplain($admissionFormData['medical_record'][$k]['primary_complain']);
                    }

                    if(isset($admissionFormData['medical_record'][$k]['vaccine_due_date']) && !empty($admissionFormData['medical_record'][$k]['vaccine_due_date'])){

                        $newMedicalRecord->setVaccineDueDate(new \DateTime($admissionFormData['medical_record'][$k]['vaccine_due_date']));
                    } else {
                        $newMedicalRecord->setVaccineDueDate(NULL);

                    }
                    if(isset($admissionFormData['medical_record'][$k]['discharged_date']) && !empty($admissionFormData['medical_record'][$k]['discharged_date'])){

                        $admission->setDischargedDate(new \DateTime($admissionFormData['medical_record'][$k]['discharged_date']));
                        $admission->setStatus('Discharged With Pending Payment');
                    } else {
                        $admission->setDischargedDate(NULL);

                    }

                

                    $newMedicalRecord->setWeight($admissionFormData['medical_record'][$k]['weight']);
                    $newMedicalRecord->setTemperature($admissionFormData['medical_record'][$k]['temperature']);
                    
                    if(isset($admissionFormData['medical_record'][$k]['next_due_date']) && !empty($admissionFormData['medical_record'][$k]['next_due_date']) ){
                       $newMedicalRecord->setVaccineDueDate(new \DateTime($admissionFormData['medical_record'][$k]['next_due_date']));

                    }
                    
                    if(isset($admissionFormData['medical_record'][$k]['prescription'])){
                        $newMedicalRecord->setPrescription($admissionFormData['medical_record'][$k]['prescription']);
                    }
                    

                    if(isset($_FILES['admission']) && !empty($_FILES['admission']['tmp_name']['medical_record'][$k]['vaccine_card'])) {

                        $baseName = $newMedicalRecord->getId() . '-' . time() . '.' . pathinfo($_FILES['admission']['name']['medical_record'][$k]['vaccine_card'], PATHINFO_EXTENSION);
                        $uploadFile = $newMedicalRecord->getUploadRootDir() . '/' . $baseName;
        
                        if(move_uploaded_file($_FILES['admission']['tmp_name']['medical_record'][$k]['vaccine_card'], $uploadFile)) {
                            $newMedicalRecord->setVaxCardDesc($_FILES['admission']['name']['medical_record'][$k]['vaccine_card']);
                            $newMedicalRecord->setParsedVaxCardDesc($baseName);
                        }
                    } 

                    if(isset($_FILES['admission']) && !empty($_FILES['admission']['name']['medical_record']) && count($_FILES['admission']['name']['medical_record']) && isset($_FILES['admission']['name']['medical_record'][$k]['laboratory'])){
                        
                        foreach($_FILES['admission']['name']['medical_record'][$k]['laboratory'] as $lk => $record){

                            $baseName = $newMedicalRecord->getId() . '-' . time() . '.' . pathinfo($_FILES['admission']['name']['medical_record'][$k]['laboratory'][$lk], PATHINFO_EXTENSION);
                            $uploadFile = $newMedicalRecord->getUploadRootDir() . '/' . $baseName;
            
                            if(move_uploaded_file($_FILES['admission']['tmp_name']['medical_record'][$k]['laboratory'][$lk], $uploadFile)) {
                            
                                $medicalRecordLaboratory = new MedicalRecordLaboratoryEntity();
                                $medicalRecordLaboratory->setFileDesc($_FILES['admission']['name']['medical_record'][$k]['laboratory'][$lk]);
                                $medicalRecordLaboratory->setParsedFiledDesc($baseName);
                                $medicalRecordLaboratory->setMedicalRecord($newMedicalRecord);
                                $em->persist($medicalRecordLaboratory);
                                $em->flush();
                            
                            }
                        }
                    }


                    if(isset($_FILES['admission']) && !empty($_FILES['admission']['name']['medical_record']) && count($_FILES['admission']['name']['medical_record']) && isset($_FILES['admission']['name']['medical_record'][$k]['photo'])){
                            foreach($_FILES['admission']['name']['medical_record'][$k]['photo'] as $lk => $record){
                                $medicalRecordPhoto = new MedicalRecordPhotoEntity();
                                $baseName = $medicalRecordPhoto->getId() . '-' . time() . '.' . pathinfo($_FILES['admission']['name']['medical_record'][$k]['photo'][$lk], PATHINFO_EXTENSION);
                                $uploadFile = $medicalRecordPhoto->getUploadRootDir() . '/' . $baseName;

                            if(move_uploaded_file($_FILES['admission']['tmp_name']['medical_record'][$k]['photo'][$lk], $uploadFile)) {
                            
                                $medicalRecordPhoto->setFileDesc($_FILES['admission']['name']['medical_record'][$k]['photo'][$lk]);
                                $medicalRecordPhoto->setParsedFileDesc($baseName);
                                $medicalRecordPhoto->setMedicalRecord($newMedicalRecord);
                                $em->persist($medicalRecordPhoto);
                                $em->flush();
                            
                            }
                        }
                    }
                        

                    $newMedicalRecord->setAdmissionPet($admissionPet);
                    $em->persist($newMedicalRecord);
                    $em->flush();
                    
                    //process schedule
                    $this->processNextSchedule($em, $k, $newMedicalRecord, $admissionFormData);
                    $admissionType = $admissionPet->getAdmission()->getAdmissionType()->getDescription();

                    if(isset($admissionFormData['medical_record'][$k]['service'])){
                        if($admissionType == 'Vaccination'){
                        
                            if(count($newMedicalRecord->getMedicalRecordServices())){

                                //this check is for medical record having the same service type
                                //this check require if admission type is vaccination

                                foreach($newMedicalRecord->getMedicalRecordServices() as $medicalService){
                                    if(!in_array($medicalService->getService()->getId() , $admissionFormData['medical_record'][$k]['service'])){
                                        $em->remove($medicalService);
                                        $em->flush();

                                    }
                                }
                            }

                        }   

                        //Process Service                            

                        foreach($admissionFormData['medical_record'][$k]['service'] as $service){

                            switch ($admissionType){
                                case 'Vaccination':
                                        
                                    if(!in_array($service, $newMedicalRecord->medicalRecordServiceIdsArray())){
                                
                                        $medicalService = new MedicalRecordServiceEntity();
                                        $medicalService->setService($em->getReference(ServiceEntity::class, $service));
                                        $medicalService->setMedicalRecord($newMedicalRecord);
                                        $em->persist($medicalService);
                                        $em->flush();

                                    }
                                    break;
                                
                                case 'Spay/Neuter Surgery':    
                                case 'Other Surgical Procedures':    
                                case 'Confinement':
                                case 'Consultation':
                                case 'Laboratory':    
                                    if(!in_array($service['id'], $newMedicalRecord->medicalRecordServiceIdsArray())){
                                        $medicalService = new MedicalRecordServiceEntity();
                                        $medicalService->setService($em->getReference(ServiceEntity::class, $service['id']));
                                        $medicalService->setMedicalRecord($newMedicalRecord);
                                    
                                    } else {

                                        $medicalService = $em->getRepository(MedicalRecordServiceEntity::class)->findOneBy(array('medicalRecord' => $newMedicalRecord->getId(), 'service' => $service['id']));

                                    }

                                    $medicalService->setRemarks($service['remarks']);
                                    $em->persist($medicalService);
                                    $em->flush();

                                    break;        
                            }
                        }
                    }

                    if(isset($admissionFormData['medical_record'][$k]['inventory_item'] )){
                        //Process Inventory Items 
                        foreach($admissionFormData['medical_record'][$k]['inventory_item'] as $item){
                            
                            switch ($admissionType){
                                case 'Spay/Neuter Surgery':    
                                case 'Other Surgical Procedures': 
                                case 'Confinement':
                                case 'Consultation':
                                case 'Laboratory':    
                                    if(!in_array($item['id'], $newMedicalRecord->medicalRecordItemIdsArray())){
                                    
                                        $medicalItem = new MedicalRecordItemEntity();
                                        $medicalItem->setInventoryItem($em->getReference(InventoryItemEntity::class, $item['id']));
                                        $medicalItem->setMedicalRecord($newMedicalRecord);
                                
                                    } else {

                                        $medicalItem = $em->getRepository(MedicalRecordItemEntity::class)->findOneBy(array('medicalRecord' => $newMedicalRecord->getId(), 'inventoryItem' => $item['id']));
                                    }

                                    $medicalItem->setQuantity($item['quantity']);
                                    $medicalItem->setRemarks($item['remarks']);
                                    $em->persist($medicalItem);
                                    $em->flush();

                                    break;        
                            }
                        }
                    }

                    if(isset($admissionFormData['medical_record'][$k]['prescription_item'] )){
                        //Process Inventory Items 
                        foreach($admissionFormData['medical_record'][$k]['prescription_item'] as $item){
                            if(!in_array($item['id'], $newMedicalRecord->medicalRecordPrescriptionItemIdsArray())){
                            
                                $medicalItem = new MedicalRecordPrescriptionInventoryItemEntity();
                                $medicalItem->setInventoryItem($em->getReference(InventoryItemEntity::class, $item['id']));
                                $medicalItem->setMedicalRecord($newMedicalRecord);

                            } else {

                                $medicalItem = $em->getRepository(MedicalRecordPrescriptionInventoryItemEntity::class)->findOneBy(array('medicalRecord' => $newMedicalRecord->getId(), 'inventoryItem' => $item['id']));
                            }

                            $medicalItem->setQuantity($item['quantity']);
                            $medicalItem->setRemarks($item['remarks']);
                            $em->persist($medicalItem);
                            $em->flush();

                        }
                    }

                    $em->flush();
                    
                }
            }

            $this->updateBillingInvoice($admission,$em);

          
            $result['msg'] = 'Record successfully update.';
            $result['redirect'] = 'form';
            $result['id'] = $admission->getId();
            $result['success'] = true;

        } else {
            
            $result['error_messages'] = $errors;
            $result['id'] = $admission->getId();
            $result['redirect'] = 'form';
            $result['success'] = false;
        }

        return $result;
    }

    private function updateBillingInvoice($admission, $em){

        $totalAmtDue = 0;
        $em->refresh($admission);

        $invoice = $em->getRepository(InvoiceEntity::class)->findOneBy(array('admission' => $admission));

        if($invoice){

            if(count($admission->getInvoiceAdmissionServices())){

                foreach($admission->getInvoiceAdmissionServices() as $invoiceAdmissionService ){

                    $serviceTotalPrice = $invoiceAdmissionService->getQuantity() * $invoiceAdmissionService->getService()->getPrice();

                    if($invoiceAdmissionService->getDiscount() > 0){
                        $serviceTotalPrice  -= (($serviceTotalPrice * $invoiceAdmissionService->getDiscount()) / 100);
                    }

                    $invoiceAdmissionService->setAmount($serviceTotalPrice);
                    $totalAmtDue += $serviceTotalPrice;
                }

            }

            if(count($admission->getInvoiceAdmissionInventoryItems())){

                foreach($admission->getInvoiceAdmissionInventoryItems() as $invoiceAdmissionInventoryItem ){

                    $inventoryItemTotalPrice = $invoiceAdmissionInventoryItem->getQuantity() * $invoiceAdmissionInventoryItem->getInventoryItem()->getSellingPrice();

                    if($invoiceAdmissionInventoryItem->getDiscount() > 0){
                    
                        $inventoryItemTotalPrice  -= (($inventoryItemTotalPrice * $invoiceAdmissionInventoryItem->getDiscount()) / 100);
                    }

                    $invoiceAdmissionInventoryItem->setAmount($inventoryItemTotalPrice);
                    $totalAmtDue += $inventoryItemTotalPrice;
                }

            }

            if($invoice->getDiscount() && $invoice->getDiscount() > 0 ){
                $totalAmtDue -= (($totalAmtDue * $invoice->getDiscount()) / 100);
            }

            if($invoice->totalPayment() > 0){
                
                $totalAmtDue -= $invoice->totalPayment();
            }

            $admission->setStatus('Billed With Pending Payment');
            $invoice->setAmountDue($totalAmtDue);
            $em->flush();
        }
    }

    private function validateFormData($formData, $admission){

        $em = $this->getDoctrine()->getManager();
        $errors = [];

        $ii = []; 
        foreach ($formData['medical_record'] as $k => $data){


            if($admission->getAdmissionType()->getDescription() == 'Vaccination'){
                if(!isset($data['service'])){
                    $errors[] = 'Service not provided.';
                } 
            }
            if(isset($data['inventory_item'])){

                foreach($data['inventory_item'] as  $k2 => $iventoryItem){
                   if(in_array($iventoryItem['id'], array_keys($ii))){

                        $ii[$iventoryItem['id']]['quantity'] +=  $iventoryItem['quantity'];
                   } else {
                        $ii[$iventoryItem['id']] = array(
                            'quantity' => $iventoryItem['quantity']
                        );
                   }
                } 
            }
        }

        foreach($ii as $ki =>  $i){

            $inventoryItem = $em->getRepository(InventoryItemEntity::class)->find($ki);
            if(floatval($i['quantity']) > floatval($inventoryItem->getQuantity())){

                $errors[] = $inventoryItem->getItem()->getDescription() . '  dont have enough stocks.';
            }
        } 

        return $errors;
    }

    private function processNextSchedule($em ,$k, $newMedicalRecord, $admissionFormData){

        if(isset($admissionFormData['medical_record'][$k]['schedule'])){
            
            foreach($admissionFormData['medical_record'][$k]['schedule'] as $s => $scheduleD){
               
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
     * @Route("/print/prescription/{id}", name = "admission_print_prescription")
     */
    public function printPrescriptionPdf(Request $request, AuthService $authService, Pdf $pdf, $id){

        ini_set('memory_limit', '2048M');

        $companyLogo = "";
        $medicalRecord  = $this->getDoctrine()->getManager()->getRepository(MedicalRecordEntity::class)->find(base64_decode($id));
        $rxSymbol = base64_encode(@file_get_contents($this->getParameter('app.global_url').'/dist/img/rx-symbol.jpg'));

        $medicalRecordCompany = $medicalRecord->getAdmissionPet()->getAdmission()->getBranch()->getCompany();
        if($medicalRecordCompany->getLogoDesc() != ''){
            $companyLogo = base64_encode(@file_get_contents($this->getParameter('app.global_url').'/uploads/file/'. $medicalRecordCompany->getParsedLogoDesc()));
        }

        $options = [
            'orientation' => 'portrait',
            'page-height' => '210mm',
            'page-width' => '148.5mm',
            'print-media-type' =>  True,
            'zoom' => .7
        ];


         $newContent = $this->renderView('Admission/print_prescription.wkpdf.twig', array(
            'medicalRecord' => $medicalRecord,
            'rxSymbol' => $rxSymbol,
            'companyLogo' => $companyLogo
        ));

        $xml = $pdf->getOutputFromHtml($newContent,$options);
        $pdfResponse = array(
            'success' => true,
            'msg' => 'PDF was successfully generated.', 
            'pdfBase64' => base64_encode($xml)
        );
       
        $pdfContent = $pdfResponse['pdfBase64'];
         return new Response(base64_decode($pdfContent), 200, array(
            'Content-Type' => 'application/pdf',
            'Content-Disposition'   => 'attachment; filename="'.  'prescription' .'-' . date('m/d/Y') . '.pdf"'
        ));
    }

         /**
     * @Route("/print/medical_record/{id}", name = "admission_print_medical_record")
     */
    public function printMedicalRecordPdf(Request $request, AuthService $authService, Pdf $pdf, $id){

        ini_set('memory_limit', '2048M');

        $companyLogo = "";
        $medicalRecord  = $this->getDoctrine()->getManager()->getRepository(MedicalRecordEntity::class)->find(base64_decode($id));
        $rxSymbol = base64_encode(@file_get_contents($this->getParameter('app.global_url').'/dist/img/rx-symbol.jpg'));

        $medicalRecordCompany = $medicalRecord->getAdmissionPet()->getAdmission()->getBranch()->getCompany();
        if($medicalRecordCompany->getLogoDesc() != ''){
            $companyLogo = base64_encode(@file_get_contents($this->getParameter('app.global_url').'/uploads/file/'. $medicalRecordCompany->getParsedLogoDesc()));
        }

        $options = [
            'orientation' => 'portrait',
            'print-media-type' =>  True,
            'zoom' => .7
        ];


         $newContent = $this->renderView('Admission/print_medical_record.wkpdf.twig', array(
            'medicalRecord' => $medicalRecord,
            'rxSymbol' => $rxSymbol,
            'companyLogo' => $companyLogo
        ));

        $xml = $pdf->getOutputFromHtml($newContent,$options);
        $pdfResponse = array(
            'success' => true,
            'msg' => 'PDF was successfully generated.', 
            'pdfBase64' => base64_encode($xml)
        );
       
        $pdfContent = $pdfResponse['pdfBase64'];
         return new Response(base64_decode($pdfContent), 200, array(
            'Content-Type' => 'application/pdf',
            'Content-Disposition'   => 'attachment; filename="'.  'md' .'-' . date('m/d/Y') . '.pdf"'
        ));
    }

    
      /**
    * @Route("/pet_admission_history", name="pet_admission_history")
    */
    public function pet_admission_historyAjax(Request $request, AuthService $authService) {

        $result = [];
        $petId = $request->request->get('id');
  
        $result['success'] = true;
        $result['html'] = $this->renderView('Admission/pet_history.html.twig',array('page_title' => 'Admission History','admissionPet' => $this->getDoctrine()->getManager()->getRepository(AdmissionPetEntity::class)->find($petId)));
        return new JsonResponse($result);
     }

    

}
