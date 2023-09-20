<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;

use App\Service\AuthService;

use App\Entity\BranchEntity;

use App\Entity\ClientEntity;
use App\Form\ClientForm;
use App\Entity\AuditTrailEntity;
use App\Entity\UserEntity;



/**
 * @Route("/client")
 */
class ClientController extends AbstractController
{
    /**
     * @Route("/ajax_form", name="client_ajax_form")
     */
    public function ajaxForm(Request $request, AuthService $authService): JsonResponse
    {
       
       $result = [ 'success' =>  true, 'msg' => ''];

       if(!$authService->isLoggedIn()) { $result['success'] = false; $result['msg'] = 'Unauthorized access please call a system administrator.';}
       
       $formOptions = array('action' => 'n' , 'branchId' => $authService->getUser()->getBranch()->getIdEncoded());
       $form = $this->createForm(ClientForm::class, new ClientEntity(), $formOptions);
    
       $result['html'] = $this->renderView('Client/ajax_form.html.twig', [
            'page_title' => 'New Client',
            'form' => $form->createView()
        ]);

       return new JsonResponse($result);
    }

    /**
     * @Route("/ajax_form_process", name="client_ajax_form_process")
     */
    public function ajaxFormProcess(Request $request, AuthService $authService): JsonResponse
    {
       
       $result = [ 'success' =>  true, 'msg' => ''];

       if(!$authService->isLoggedIn()) { $result['success'] = false; $result['msg'] = 'Unauthorized access please call a system administrator.';}

       if($request->getMethod() == "POST"){
         $clientForm = $request->request->get('client_form');
         
         $em = $this->getDoctrine()->getManager();
         $errors = $em->getRepository(ClientEntity::class)->validate($clientForm);

         if(!count($errors)){


             $newClient = new ClientEntity();
             $newClient->setFirstName($clientForm['first_name']);
             $newClient->setLastName($clientForm['last_name']);
             $newClient->setContactNo($clientForm['contact_no']);
             $newClient->setAddress($clientForm['address']);
             $newClient->setEmail($clientForm['email']);

             $newClient->setBranch($em->getReference(BranchEntity::class, base64_decode($clientForm['branch'])));
             $em->persist($newClient);
             $em->flush();

              


             $result['msg'] = 'Client successfully added to record.';
             $result['client_name'] = $newClient->getFullName();
             $result['client_id'] = $newClient->getId();
        
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
    * @Route("", name="client_index")
    */
   public function index(Request $request, AuthService $authService)
   {
      
      if(!$authService->isLoggedIn()) return $authService->redirectToLogin();
      if(!$authService->isUserHasAccesses(array('Client'))) return $authService->redirectToHome();
      
      $page_title = ' Client'; 
      return $this->render('Client/index.html.twig', [ 
         'page_title' => $page_title, 
         'javascripts' => array('plugins/datatables/jquery.dataTables.js','/js/client/index.js') ]
      );
   }

   /**
    * @Route("/details/{id}", name="client_details")
    */
    public function details($id,Request $request, AuthService $authService)
    {
       
       if(!$authService->isLoggedIn()) return $authService->redirectToLogin();
       if(!$authService->isUserHasAccesses(array('Client Details'))) return $authService->redirectToHome();
      

       $client  = $this->getDoctrine()->getManager()->getRepository(ClientEntity::class)->find(base64_decode($id)); 
       $page_title = ' Client Details'; 
       return $this->render('Client/details.html.twig', [ 
          'page_title' => $page_title,
          'client' => $client, 
          'javascripts' => array('/plugins/datatables/jquery.dataTables.js','/js/client/details.js') 
         ]);
    }


   /**
    * @Route("/autocomplete", name="client_autocomplete")
    */
   public function autocompleteAction(Request $request) {

      return new JsonResponse(array(
         'query' => 'clients',
         'suggestions' => $this->getDoctrine()->getManager()->getRepository(ClientEntity::class)->autocompleteSuggestions($request->query->all(), $this->get('session')->get('userData'))
      ));
   }

   /**
    * @Route("/ajax_list", name="client_ajax_list")
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
          $result = $this->getDoctrine()->getManager()->getRepository(ClientEntity::class)->ajax_list($get, $this->get('session')->get('userData'));
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
   *      name = "client_form"
   * )
   */
   public function formAction($action, $id, Request $request, AuthService $authService) {

   if(!$authService->isLoggedIn()) return $authService->redirectToLogin();
   if(!$authService->getUser()->getType() == 'Super Admin') return $authService->redirectToHome();

   $em = $this->getDoctrine()->getManager();

   $client = $em->getRepository(ClientEntity::class)->find(base64_decode($id));
   if(!$client) {
      $client = new ClientEntity();
   }

   $formOptions = array('action' => 'n' , 'branchId' => $authService->getUser()->getBranch()->getIdEncoded());
   $form = $this->createForm(ClientForm::class, $client, $formOptions);

   if($request->getMethod() === 'POST') {

      $client_form = $request->get($form->getName());
      $result = $this->processForm($client_form, $client, $form, $request);

      if($result['success']) {
         if($result['redirect'] === 'index') {
               return $this->redirect($this->generateUrl('client_index'), 302);
         } else if($result['redirect'] === 'form') {
               return $this->redirect($this->generateUrl('client_form', array(
                  'action' => 'u',
                  'id' => base64_encode($result['id'])
               )), 302);
         } else if($result['redirect'] === 'form with error') {
               return $this->redirect($this->generateUrl('client_form'), 302);
         }
      } else {
         $form->submit($client_form, false);
      }
   }

   $title = ($action === 'n' ? 'New' : 'Update') . ' Client';

   return $this->render('Client/form.html.twig', array(
      'title' => $title,
      'page_title' => $title,
      'form' => $form->createView(),
      'action' => $action,
      'id' => $id
   ));
   }

   private function processForm($client_form, $client ,$form, Request $request) {

   $em = $this->getDoctrine()->getManager();

   $errors = $em->getRepository(ClientEntity::class)->validate($client_form);

   if(!count($errors)) {

      switch($client_form['action']) {
         case 'n': // new

               $form->handleRequest($request);

               if ($form->isValid()) {
                  
                  $em->persist($client);
                  $em->flush();

                  $newAT = new AuditTrailEntity();
                  $newAT->setUser($em->getReference(UserEntity::class, base64_decode($_COOKIE['userId'])));
                  $newAT->entity = $client;
                  $newAT->setRefTable('client');
                  $newAT->parseInformation('New');
                  $em->persist($newAT);
                  $em->flush();

                  $this->get('session')->getFlashBag()->set('success_messages', 'Record Successfully Saved.');

                  $result['redirect'] = 'index';

               } else {

                  $this->get('session')->getFlashBag()->set('error_messages', 'Something went wrong. Please try again.');
                  $result['redirect'] = 'form with error';
               }

               break;

         case 'u': // update

               $newAT = new AuditTrailEntity();
               $newAT->setUser($em->getReference(UserEntity::class, base64_decode($_COOKIE['userId'])));
               $newAT->entity = $client;
               $newAT->setRefTable('client');
               $newAT->parseOriginalDetails();

               $form->handleRequest($request);

               if ($form->isValid()) {

                  $em->persist($client);
                  $em->flush();

                   

                  $this->get('session')->getFlashBag()->set('success_messages', 'Record Successfully Updated.');

                  $result['redirect'] = 'index';

               } else {

                  $this->get('session')->getFlashBag()->set('error_messages', 'Something went wrong. Please try again.');
                  $result['redirect'] = 'form with error';
               }

               break;

         case 'd': // delete
               $form->handleRequest($request);


               if ($form->isValid()) {
                  
                  $newAT = new AuditTrailEntity();
                  $newAT->setUser($em->getReference(UserEntity::class, base64_decode($_COOKIE['userId'])));
                  $newAT->entity = $client;
                  $newAT->setRefTable('client');
                  $newAT->parseInformation('Delete');
                  $em->persist($newAT);

                  $client->setIsDeleted(true);
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

     /**
     * @Route("/pet_list", name="client_pet_list")
     */
    public function petList(Request $request, AuthService $authService): JsonResponse
    {
       
         $result = [ 'success' =>  true, 'msg' => ''];
         if(!$authService->isLoggedIn()) { $result['success'] = false; $result['msg'] = 'Unauthorized access please call a system administrator.';}
         
         $clientId = $request->request->get('clientId');
         $client = $this->getDoctrine()->getManager()->getRepository(ClientEntity::class)->find($clientId); 
         $petList = array();

         $pets = $client->getClientPets();
         if($pets){
            foreach($pets as $key => $pet){
            
               if(!$pet->getPet()->getIsDeleted() ){
                  $petList[$key]['id'] = $pet->getPet()->getId();
                  $petList[$key]['name'] = $pet->getPet()->getName();
               }
            
            }
         }

         $result['list'] = json_encode($petList);
         return new JsonResponse($result);
    }

      /**
     * @Route("/ajax_get_client_ctr", name="client_ajax_get_client")
     */
    public function ajaxGetClientCtr(Request $request, AuthService $authService): JsonResponse
    {
       
       $result = [ 'success' =>  true, 'msg' => ''];

       if(!$authService->isLoggedIn()) { $result['success'] = false; $result['msg'] = 'Unauthorized access please call a system administrator.';}
       
       $formOptions = array('action' => 'n' , 'branchId' => $authService->getUser()->getBranch()->getIdEncoded());
       $result['ctr'] = $this->getDoctrine()->getManager()->getRepository(ClientEntity::class)->getCtr($request->request->get('type'), $this->get('session')->get('userData'));
       
       return new JsonResponse($result);
    }

    
    /**
     * @Route("/ajax_get_monthly_analytics", name="client_ajax_get_monthly_analytics")
     */
    public function ajaxGetMonthlyAnalytics(Request $request, AuthService $authService): JsonResponse
    {
       
       $result = [ 'success' =>  true, 'msg' => ''];

       if(!$authService->isLoggedIn()) { $result['success'] = false; $result['msg'] = 'Unauthorized access please call a system administrator.';}
       
       $formOptions = array('action' => 'n' , 'branchId' => $authService->getUser()->getBranch()->getIdEncoded());
       $results  = $this->getDoctrine()->getManager()->getRepository(ClientEntity::class)->getMontlyAnalytics($this->get('session')->get('userData'));

       
       $days = [];
       $stats = [];

       foreach($results as $stat){
        
           array_push($days, $stat['dayMonth']);
           array_push($stats, $stat['clientCtr']);
       }

       $result['days'] = $days;
       $result['stats'] = $stats;
       $result['success'] = true;
       
       return new JsonResponse(json_encode($result));
    }

    /**
     * @Route("/ajax_get_yearly_analytics", name="client_ajax_get_yearly_analytics")
     */
    public function ajaxGetYearlyAnalytics(Request $request, AuthService $authService): JsonResponse
    {
       
       $result = [ 'success' =>  true, 'msg' => ''];

       if(!$authService->isLoggedIn()) { $result['success'] = false; $result['msg'] = 'Unauthorized access please call a system administrator.';}
       
       $formOptions = array('action' => 'n' , 'branchId' => $authService->getUser()->getBranch()->getIdEncoded());
       $results  = $this->getDoctrine()->getManager()->getRepository(ClientEntity::class)->getYearlyAnalytics($this->get('session')->get('userData'));

       
       $days = [];
       $stats = [];

       foreach($results as $stat){
        
           array_push($days, $stat['yearMonth']);
           array_push($stats, $stat['clientCtr']);
       }

       $result['days'] = $days;
       $result['stats'] = $stats;
       $result['success'] = true;
       
       return new JsonResponse(json_encode($result));
    }
}
