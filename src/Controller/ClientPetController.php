<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;

use App\Service\AuthService;

use App\Entity\BranchEntity;
use App\Entity\BreedEntity;
use App\Entity\PetEntity;
use App\Entity\ClientEntity;
use App\Entity\AuditTrailEntity;
use App\Entity\UserEntity;
use App\Entity\ClientPetEntity;

/**
 * @Route("/client_pet")
 */
class ClientPetController extends AbstractController
{

    /**
     * @Route("/ajax_transfer_form", name="client_pet_ajax_transfer_form")
     */
    public function ajax_transferForm(Request $request, AuthService $authService): JsonResponse
    {
       
       $result = [ 'success' =>  true, 'msg' => ''];

       if(!$authService->isLoggedIn()) { $result['success'] = false; $result['msg'] = 'Unauthorized access please call a system administrator.';}
       
       $clientId = $request->request->get('clientId');
       $clientPetId = $request->request->get('clientPetId');

       
       $result['html'] = $this->renderView('ClientPet/ajax_transfer_form.html.twig', [
            'page_title' => 'Transfer Pet',
            'clientId' => $clientId,
            'clientPetId' => $clientPetId
        ]);

       return new JsonResponse($result);
    }

    /**
     * @Route("/ajax_transfer_form_process", name="pet_ajax_transfer_form_process")
     */
    public function ajax_transfer_formProcess(Request $request, AuthService $authService): JsonResponse
    {
       
       $result = [ 'success' =>  true, 'msg' => ''];

       if(!$authService->isLoggedIn()) { $result['success'] = false; $result['msg'] = 'Unauthorized access please call a system administrator.';}

       if($request->getMethod() == "POST"){
         
         $client_pet_form = $request->request->get('client_pet_form');
         $em = $this->getDoctrine()->getManager();
         $clientPetToTransfer = $em->getRepository(ClientPetEntity::class)->find($client_pet_form['client_pet']); 
         $errors = $em->getRepository(ClientPetEntity::class)->validateTransfer($client_pet_form, $clientPetToTransfer);

         if(!count($errors)){

            $clientPetToTransfer->setIsDeleted(1);
            $em->flush();

             $clientPet = new ClientPetEntity();
             $clientPet->setClient($em->getReference(ClientEntity::class, $client_pet_form['client']));
             $clientPet->setPet($clientPetToTransfer->getPet());
             $em->persist($clientPet);
             $em->flush();

             $result['msg'] = 'Pet successfully added to transfer.';
        
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
     * @Route("/ajax_remove", name="client_pet_ajax_remove")
     */
    public function ajaxRemove(Request $request, AuthService $authService): JsonResponse
    {
       
        $result = [ 'success' =>  true, 'msg' => ''];

        if(!$authService->isLoggedIn()) { $result['success'] = false; $result['msg'] = 'Unauthorized access please call a system administrator.';}
        
        $em = $this->getDoctrine()->getManager();
        $clientPet = $em->getRepository(ClientPetEntity::class)->find($request->request->get('id'));
        $errors = $em->getRepository(ClientPetEntity::class)->validateRemove($clientPet);
        
        if(count($errors)){
           
            $result['success'] = false; 
            $result['msg'] = $errors[0];
        } else {

            if(!$clientPet){
 
                $result['success'] = false; 
                $result['msg'] = 'Invalid request please try again later.';
              } else {
       
                $clientPet->setIsDeleted(true);
                $em->flush();
       
                $result['msg'] = 'Pet successfully removed from this owner.';
              }
        }
     
 
        return new JsonResponse($result);
    }

}
