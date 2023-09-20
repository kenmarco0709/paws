<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;

use App\Service\AuthService;
use App\Service\TransactionService;

use App\Entity\BranchEntity;
use App\Entity\AuditTrailEntity;
use App\Entity\BehaviorRecordEntity;
use App\Form\BehaviorRecordForm;


/**
 * @Route("/behavior_record")
 */
class BehaviorRecordController extends AbstractController
{

   /**
     * @Route("/ajax_form", name="behavior_record_ajax_form")
     */
    public function ajaxForm(Request $request, AuthService $authService): JsonResponse
    {
       
       $result = [ 'success' =>  true, 'msg' => ''];
       if(!$authService->isLoggedIn()) { $result['success'] = false; $result['msg'] = 'Unauthorized access please call a system administrator.';}
       
       $em = $this->getDoctrine()->getManager();
       $formData = $request->request->all();
       $behaviorRecord = $em->getRepository(BehaviorRecordEntity::class)->find(base64_decode($formData['id']));
       
       if(!$behaviorRecord) {
          $behaviorRecord = new BehaviorRecordEntity();
       }

       $formOptions = array('action' => $formData['action'] , 'petId' => $formData['petId']);
       $form = $this->createForm(BehaviorRecordForm::class, $behaviorRecord, $formOptions);
    
       $result['html'] = $this->renderView('BehaviorRecord/ajax_form.html.twig', [
            'page_title' => ($formData['action'] == 'n' ? 'New ' : ' Update') . ' Pet Behavior',
            'form' => $form->createView(),
            'action' => $formData['action']
        ]);

       return new JsonResponse($result);
    }

    /**
     * @Route("/ajax_form_process", name="behavior_record_ajax_form_process")
     */
    public function ajaxFormProcess(Request $request, AuthService $authService): JsonResponse
    {
       
       $result = [ 'success' =>  true, 'msg' => ''];

       if(!$authService->isLoggedIn()) { $result['success'] = false; $result['msg'] = 'Unauthorized access please call a system administrator.';}

       if($request->getMethod() == "POST"){

         $behaviorRecordForm = $request->request->get('behavior_record_form');         
         $em = $this->getDoctrine()->getManager();
         $errors = $em->getRepository(BehaviorRecordEntity::class)->validate($behaviorRecordForm);

         if(!count($errors)){
            
            $behaviorRecord = $em->getRepository(BehaviorRecordEntity::class)->find($behaviorRecordForm['id']);
            
            if(!$behaviorRecord) {
               $behaviorRecord = new BehaviorRecordEntity();
            }
     
            $formOptions = array('action' => $behaviorRecordForm['action'] , 'petId' => $behaviorRecordForm['pet']);
            $form = $this->createForm(BehaviorRecordForm::class, $behaviorRecord, $formOptions);


            switch($behaviorRecordForm['action']) {
               case 'n': // new

                     $form->handleRequest($request);
      
                     if ($form->isValid()) {
                        
                        $em->persist($behaviorRecord);
                        $em->flush();
   
                        $result['msg'] = 'Pet Behavior Record successfully added to record.';
                     } else {
      
                        $result['error'] = 'Ooops something went wrong please try again later.';
                     }
      
                     break;
      
               case 'u': // update

                     $form->handleRequest($request);
                     if ($form->isValid()) {
      
                        $em->persist($behaviorRecord);
                        $em->flush();
                        $result['msg'] = 'Pet Behavior Record successfully updated.';
                     } else {
                       
                        $result['error'] = 'Ooops something went wrong please try again later.';
                     }
      
                     break;
      
               case 'd': // delete
                     $form->handleRequest($request);
                     if ($form->isValid()) {
                          
                        $behaviorRecord->setIsDeleted(true);
                        $em->flush();
      
                        $result['msg'] = 'Pet Behavior Record successfully deleted.';
      
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

    
   /**
    * @Route("/ajax_list", name="behavior_record_ajax_list")
    */
    public function ajax_listAction(Request $request, AuthService $authService) {

      $get = $request->query->all();

      $result = array(
          "draw" => intval($get['draw']),
          "recordsTotal" => 0,
          "recordsFiltered" => 0,
          "data" => array()
      );

      if($authService->isLoggedIn()) {
          $result = $this->getDoctrine()->getManager()->getRepository(BehaviorRecordEntity::class)->ajax_list($get, $this->get('session')->get('userData'));
      }

      return new JsonResponse($result);
   }

}