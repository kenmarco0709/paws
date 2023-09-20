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
use App\Entity\PetPhotoEntity;
use App\Form\PetPhotoForm;


/**
 * @Route("/pet_photo")
 */
class PetPhotoController extends AbstractController
{

   /**
     * @Route("/ajax_form", name="pet_photo_ajax_form")
     */
    public function ajaxForm(Request $request, AuthService $authService): JsonResponse
    {
       
       $result = [ 'success' =>  true, 'msg' => ''];
       if(!$authService->isLoggedIn()) { $result['success'] = false; $result['msg'] = 'Unauthorized access please call a system administrator.';}
       
       $em = $this->getDoctrine()->getManager();
       $formData = $request->request->all();
       $PetPhoto = $em->getRepository(PetPhotoEntity::class)->find(base64_decode($formData['id']));
       
       if(!$PetPhoto) {
          $PetPhoto = new PetPhotoEntity();
       }

       $formOptions = array('action' => $formData['action'] , 'petId' => $formData['petId']);
       $form = $this->createForm(PetPhotoForm::class, $PetPhoto, $formOptions);
    
       $result['html'] = $this->renderView('PetPhoto/ajax_form.html.twig', [
            'page_title' => ($formData['action'] == 'n' ? 'New ' : ' Update') . ' Pet Photo',
            'form' => $form->createView(),
            'action' => $formData['action']
        ]);

       return new JsonResponse($result);
    }

    /**
     * @Route("/ajax_form_process", name="pet_photo_ajax_form_process")
     */
    public function ajaxFormProcess(Request $request, AuthService $authService): JsonResponse
    {
       
       $result = [ 'success' =>  true, 'msg' => ''];

       if(!$authService->isLoggedIn()) { $result['success'] = false; $result['msg'] = 'Unauthorized access please call a system administrator.';}

       if($request->getMethod() == "POST"){

         $petPhotoForm = $request->request->get('pet_photo_form');       

         $em = $this->getDoctrine()->getManager();
         $errors = [];

         if( $petPhotoForm['action'] == 'n' && !isset($_FILES['pet_photo_form'])){
            $errors[] = ' Please put a valid photo.';
         }

         if(!count($errors)){
            
            $petPhoto = $em->getRepository(PetPhotoEntity::class)->find($petPhotoForm['id']);
            
            if(!$petPhoto) {
               $petPhoto = new PetPhotoEntity();
            }
     
            $formOptions = array('action' => $petPhotoForm['action'] , 'petId' => $petPhotoForm['pet']);
            $form = $this->createForm(PetPhotoForm::class, $petPhoto, $formOptions);


            switch($petPhotoForm['action']) {
               case 'n': // new

                     $form->handleRequest($request);
      
                     if ($form->isValid()) {

                        if(isset($_FILES['pet_photo_form']) && !empty($_FILES['pet_photo_form']['tmp_name']['before_photo'])) {
                           $baseName = $petPhoto->getId() . '-' . time() . '.' . pathinfo($_FILES['pet_photo_form']['name']['before_photo'], PATHINFO_EXTENSION);
                           $uploadPhoto = $petPhoto->getUploadRootDir() . '/' . $baseName;
         
                           if(move_uploaded_file($_FILES['pet_photo_form']['tmp_name']['before_photo'], $uploadPhoto)) {
                              $petPhoto->setBeforeDescription($_FILES['pet_photo_form']['name']['before_photo']);
                              $petPhoto->setParsedBeforeDescription($baseName);
                           }

                        }

                        if(isset($_FILES['pet_photo_form']) && !empty($_FILES['pet_photo_form']['tmp_name']['after_photo'])) {
                           $baseName = $petPhoto->getId() . '-' . time() . '.' . pathinfo($_FILES['pet_photo_form']['name']['after_photo'], PATHINFO_EXTENSION);
                           $uploadPhoto = $petPhoto->getUploadRootDir() . '/' . $baseName;
         
                           if(move_uploaded_file($_FILES['pet_photo_form']['tmp_name']['after_photo'], $uploadPhoto)) {
                              $petPhoto->setAfterDescription($_FILES['pet_photo_form']['name']['after_photo']);
                              $petPhoto->setParsedAfterDescription($baseName);
                           }

                        }
                        
                        
                        $em->persist($petPhoto);
                        $em->flush();
                        
   
                        $result['msg'] = 'Pet Behavior Record successfully added to record.';
                     } else {
      
                        $result['error'] = 'Ooops something went wrong please try again later.';
                     }
      
                     break;
      
               case 'u': // update

                     $form->handleRequest($request);
                     if ($form->isValid()) {
      
                        if(isset($_FILES['pet_photo_form']) && !empty($_FILES['pet_photo_form']['tmp_name']['photo'])) {
                           $baseName = $petPhoto->getId() . '-' . time() . '.' . pathinfo($_FILES['pet_photo_form']['name']['photo'], PATHINFO_EXTENSION);
                           $uploadPhoto = $petPhoto->getUploadRootDir() . '/' . $baseName;
         
                           if(move_uploaded_photo($_FILES['pet_photo_form']['tmp_name']['photo'], $uploadPhoto)) {

                              $petPhoto->removePhoto();
                              $petPhoto->setDescription($_FILES['pet_photo_form']['name']['photo']);
                              $petPhoto->setParsedDescription($baseName);
                           }

                           $em->persist($petPhoto);
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
                          
                        $petPhoto->setIsDeleted(true);
                        $petPhoto->removePhoto();
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
    * @Route("/ajax_list", name="pet_photo_ajax_list")
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
          $result = $this->getDoctrine()->getManager()->getRepository(PetPhotoEntity::class)->ajax_list($get, $this->get('session')->get('userData'));
      }

      return new JsonResponse($result);
   }

}