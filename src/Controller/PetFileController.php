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
use App\Entity\PetFileEntity;
use App\Form\PetFileForm;


/**
 * @Route("/pet_file")
 */
class PetFileController extends AbstractController
{

   /**
     * @Route("/ajax_form", name="pet_file_ajax_form")
     */
    public function ajaxForm(Request $request, AuthService $authService): JsonResponse
    {
       
       $result = [ 'success' =>  true, 'msg' => ''];
       if(!$authService->isLoggedIn()) { $result['success'] = false; $result['msg'] = 'Unauthorized access please call a system administrator.';}
       
       $em = $this->getDoctrine()->getManager();
       $formData = $request->request->all();
       $PetFile = $em->getRepository(PetFileEntity::class)->find(base64_decode($formData['id']));
       
       if(!$PetFile) {
          $PetFile = new PetFileEntity();
       }

       $formOptions = array('action' => $formData['action'] , 'petId' => $formData['petId']);
       $form = $this->createForm(PetFileForm::class, $PetFile, $formOptions);
    
       $result['html'] = $this->renderView('PetFile/ajax_form.html.twig', [
            'page_title' => ($formData['action'] == 'n' ? 'New ' : ' Update') . ' Pet File',
            'form' => $form->createView(),
            'action' => $formData['action']
        ]);

       return new JsonResponse($result);
    }

    /**
     * @Route("/ajax_form_process", name="pet_file_ajax_form_process")
     */
    public function ajaxFormProcess(Request $request, AuthService $authService): JsonResponse
    {
       
       $result = [ 'success' =>  true, 'msg' => ''];

       if(!$authService->isLoggedIn()) { $result['success'] = false; $result['msg'] = 'Unauthorized access please call a system administrator.';}

       if($request->getMethod() == "POST"){

         $petFileForm = $request->request->get('pet_file_form');       

         $em = $this->getDoctrine()->getManager();
         // $errors = $em->getRepository(PetFileEntity::class)->validate($petFileForm);
         $errors = [];

         if( $petFileForm['action'] == 'n' && !isset($_FILES['pet_file_form'])){
            $errors[] = ' Please put a valid file.';
         }

         if(!count($errors)){
            
            $petFile = $em->getRepository(PetFileEntity::class)->find($petFileForm['id']);
            
            if(!$petFile) {
               $petFile = new PetFileEntity();
            }
     
            $formOptions = array('action' => $petFileForm['action'] , 'petId' => $petFileForm['pet']);
            $form = $this->createForm(PetFileForm::class, $petFile, $formOptions);


            switch($petFileForm['action']) {
               case 'n': // new

                     $form->handleRequest($request);
      
                     if ($form->isValid()) {

                        if(isset($_FILES['pet_file_form']) && !empty($_FILES['pet_file_form']['tmp_name']['file'])) {
                           $baseName = $petFile->getId() . '-' . time() . '.' . pathinfo($_FILES['pet_file_form']['name']['file'], PATHINFO_EXTENSION);
                           $uploadFile = $petFile->getUploadRootDir() . '/' . $baseName;
         
                           if(move_uploaded_file($_FILES['pet_file_form']['tmp_name']['file'], $uploadFile)) {
                              $petFile->setDescription($_FILES['pet_file_form']['name']['file']);
                              $petFile->setParsedDescription($baseName);
                           }

                           $em->persist($petFile);
                           $em->flush();
                        }         
                        
   
                        $result['msg'] = 'Pet Behavior Record successfully added to record.';
                     } else {
      
                        $result['error'] = 'Ooops something went wrong please try again later.';
                     }
      
                     break;
      
               case 'u': // update

                     $form->handleRequest($request);
                     if ($form->isValid()) {
      
                        if(isset($_FILES['pet_file_form']) && !empty($_FILES['pet_file_form']['tmp_name']['file'])) {
                           $baseName = $petFile->getId() . '-' . time() . '.' . pathinfo($_FILES['pet_file_form']['name']['file'], PATHINFO_EXTENSION);
                           $uploadFile = $petFile->getUploadRootDir() . '/' . $baseName;
         
                           if(move_uploaded_file($_FILES['pet_file_form']['tmp_name']['file'], $uploadFile)) {

                              $petFile->removeFile();
                              $petFile->setDescription($_FILES['pet_file_form']['name']['file']);
                              $petFile->setParsedDescription($baseName);
                           }

                           $em->persist($petFile);
                           $em->flush();
                        }  

                        $result['msg'] = 'Pet Behavior Record successfully updated.';
                     } else {
                       
                        $result['error'] = 'Ooops something went wrong please try again later.';
                     }
      
                     break;
      
               case 'd': // delete
                     $form->handleRequest($request);
                     if ($form->isValid()) {
                          
                        $petFile->setIsDeleted(true);
                        $petFile->removeFile();
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
    * @Route("/ajax_list", name="pet_file_ajax_list")
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
          $result = $this->getDoctrine()->getManager()->getRepository(PetFileEntity::class)->ajax_list($get, $this->get('session')->get('userData'));
      }

      return new JsonResponse($result);
   }

}