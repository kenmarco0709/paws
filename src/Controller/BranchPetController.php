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
use App\Form\BranchPetForm;
use App\Entity\AuditTrailEntity;
use App\Entity\PetEntity;



/**
 * @Route("/branch_pet")
 */
class BranchPetController extends AbstractController
{

   /**
     * @Route("/ajax_form", name="branch_pet_ajax_form")
     */
    public function ajaxForm(Request $request, AuthService $authService): JsonResponse
    {
       
       $result = [ 'success' =>  true, 'msg' => ''];
       if(!$authService->isLoggedIn()) { $result['success'] = false; $result['msg'] = 'Unauthorized access please call a system administrator.';}
       
       $em = $this->getDoctrine()->getManager();
       $formData = $request->request->all();
       $pet = $em->getRepository(PetEntity::class)->find(base64_decode($formData['id']));
       
       if(!$pet) {
          $pet = new PetEntity();
       }

       $formOptions = array('action' => $formData['action'] , 'branchId' => $authService->getUser()->getBranch()->getIdEncoded());
       $form = $this->createForm(BranchPetForm::class, $pet, $formOptions);
    
       $result['html'] = $this->renderView('BranchPet/ajax_form.html.twig', [
            'page_title' => ($formData['action'] == 'n' ? 'New ' : ' Update') . ' Pet',
            'form' => $form->createView(),
            'action' => $formData['action']
        ]);

       return new JsonResponse($result);
    }

    /**
     * @Route("/ajax_form_process", name="branch_pet_ajax_form_process")
     */
    public function ajaxFormProcess(Request $request, AuthService $authService): JsonResponse
    {
       
       $result = [ 'success' =>  true, 'msg' => ''];

       if(!$authService->isLoggedIn()) { $result['success'] = false; $result['msg'] = 'Unauthorized access please call a system administrator.';}

       if($request->getMethod() == "POST"){

         $branchPetForm = $request->request->get('branch_pet_form');         
         $em = $this->getDoctrine()->getManager();
         $errors = $em->getRepository(PetEntity::class)->validate_branch_pet($branchPetForm);

         if(!count($errors)){
            
            $pet = $em->getRepository(PetEntity::class)->find($branchPetForm['id']);
            
            if(!$pet) {
               $pet = new PetEntity();
            }
     
            $formOptions = array('action' => $branchPetForm['action'] , 'branchId' => $authService->getUser()->getBranch()->getIdEncoded());
            $form = $this->createForm(BranchPetForm::class, $pet, $formOptions);


            switch($branchPetForm['action']) {
               case 'n': // new

                     $form->handleRequest($request);
      
                     if ($form->isValid()) {
                        
                        $em->persist($pet);
                        $em->flush();
   
                        $result['msg'] = 'Pet successfully added to record.';
                        $result['pet_name'] = $pet->getName();
                        $result['pet_id'] = $pet->getId();
                     } else {
      
                        $result['error'] = 'Ooops something went wrong please try again later.';
                     }
      
                     break;
      
               case 'u': // update

                     $form->handleRequest($request);
                     if ($form->isValid()) {
      
                        $em->persist($pet);
                        $em->flush();


                        if(isset($_FILES)){
                           $this->processFile($_FILES, $pet, $em);
                        }

                        $result['msg'] = 'Pet successfully updated.';
                     } else {
                       
                        $result['error'] = 'Ooops something went wrong please try again later.';
                     }
      
                     break;
      
               case 'd': // delete
                     $form->handleRequest($request);
                     if ($form->isValid()) {
                          
                        $pet->setIsDeleted(true);
                        $em->flush();
      
                        $result['msg'] = 'Pet successfully deleted.';
      
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

   private function processFile($files, $pet, $em){

      if(isset($files['branch_pet_form']) && !empty($files['branch_pet_form']['tmp_name']['beforeFile'])) {
         $baseName = $pet->getId() . '-' . time() . '.' . pathinfo($files['branch_pet_form']['name']['beforeFile'], PATHINFO_EXTENSION);
         $uploadFile = $pet->getUploadRootDir() . '/' . $baseName;

         if(move_uploaded_file($files['branch_pet_form']['tmp_name']['beforeFile'], $uploadFile)) {
            $pet->removeBeforeFilePhoto();
            $pet->setBeforeFilePhotoDescription($files['branch_pet_form']['name']['beforeFile']);
            $pet->setParsedBeforeFilePhotoDescription($baseName);
         }

         $em->persist($pet);
         $em->flush();
      }
   
      if(isset($files['branch_pet_form']) && !empty($files['branch_pet_form']['tmp_name']['afterFile'])) {
         $baseName = $pet->getId() . '-' . time() . '.' . pathinfo($files['branch_pet_form']['name']['afterFile'], PATHINFO_EXTENSION);
         $uploadFile = $pet->getUploadRootDir() . '/' . $baseName;

         if(move_uploaded_file($files['branch_pet_form']['tmp_name']['afterFile'], $uploadFile)) {
            $pet->removeAfterFilePhoto();
            $pet->setAfterFilePhotoDescription($files['branch_pet_form']['name']['afterFile']);
            $pet->setParsedAfterFilePhotoDescription($baseName);
         }

         $em->persist($pet);
         $em->flush();
      }      
   }

}