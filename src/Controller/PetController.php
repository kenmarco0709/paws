<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Knp\Snappy\Pdf;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;


use App\Service\AuthService;

use App\Entity\BranchEntity;
use App\Entity\BreedEntity;
use App\Entity\PetEntity;
use App\Entity\ClientEntity;
use App\Entity\AuditTrailEntity;
use App\Entity\UserEntity;
use App\Entity\ClientPetEntity;
use App\Entity\MedicalRecordEntity;
use App\Entity\CabinetFormEntity;
use App\Form\CabinetFormForm;


use App\Form\PetForm;

/**
 * @Route("/pet")
 */
class PetController extends AbstractController
{

     /**
     * @Route("/ajax_send_medical_record_form", name="pet_ajax_send_medical_record_form")
     */
    public function ajax_send_medical_recordForm(Request $request, AuthService $authService): JsonResponse
    {
       
         
      $result = [ 'success' =>  true, 'msg' => ''];

      if(!$authService->isLoggedIn()) { $result['success'] = false; $result['msg'] = 'Unauthorized access please call a system administrator.';}
      
      $id = $request->request->get('id');
      $result['html'] = $this->renderView('Pet/ajax_send_medical_record_form.html.twig', [
           'page_title' => 'Send Pet Medical Record',
           'id' => $id
       ]);

      return new JsonResponse($result);
    }

      /**
     * @Route("/ajax_send_medical_record_form_process", name="pet_ajax_send_medical_record_form_process")
     */
    public function ajax_send_medical_record_formProcess(Request $request, AuthService $authService, Pdf $pdf): JsonResponse
    {
       
       $result = [ 'success' =>  true, 'msg' => ''];

       if(!$authService->isLoggedIn()) { $result['success'] = false; $result['msg'] = 'Unauthorized access please call a system administrator.';}
       
         $em = $this->getDoctrine()->getManager();
         $params = $request->request->get('pet_form');

         $medicalRecord = $em->getRepository(MedicalRecordEntity::class)->find(base64_decode($params['id']));
         if(!$medicalRecord){

            $result['success'] = false; 
            $result['msg'] = 'Invalid request please try again later.';
          } else {

            ini_set('memory_limit', '2048M');

            $companyLogo = "";
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
            $file = 'temp/medical_record-'.$medicalRecord->getId().'-'.date('h-i-s').'.pdf';
            file_put_contents($file, base64_decode($pdfContent));

            $mail = new PHPMailer(true);

            try {
               //Server settings
               $mail->SMTPDebug = false;                      //Enable verbose debug output
               $mail->isSMTP();                                            //Send using SMTP
               $mail->Host       = 'vetcloudsystem.one';                     //Set the SMTP server to send through
               $mail->SMTPAuth   = true;                                   //Enable SMTP authentication
               $mail->Username   = 'support@vetcloudsystem.one';                     //SMTP username
               $mail->Password   = 'zUB)9Dl?D8!}';                               //SMTP password
               $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;            //Enable implicit TLS encryption
               $mail->Port       = 465;                                    //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`

               //Recipients
               $mail->setFrom('support@vetcloudsystem.one');
               $mail->addAddress('kenneth.marco09@gmail.com');

               //Attachments
               $mail->addAttachment($file); 

               //Content
               $mail->isHTML(true);                                  //Set email format to HTML
               $mail->Subject = 'Medical Record';
               $mail->Body    = 'See attached pdf file to see medical records.';

               $mail->send();

               unlink($file);
               $result['msg'] = 'Pet Medical Record Successfully sent.';


            } catch (Exception $e) {
               $result['msg'] = 'Pet successfully removed.';
            }


          }

       return new JsonResponse($result);
    }


    /**
     * @Route("/ajax_remove", name="pet_ajax_remove")
     */
    public function ajaxRemmove(Request $request, AuthService $authService): JsonResponse
    {
       
       $result = [ 'success' =>  true, 'msg' => ''];

       if(!$authService->isLoggedIn()) { $result['success'] = false; $result['msg'] = 'Unauthorized access please call a system administrator.';}
       
       $em = $this->getDoctrine()->getManager();
       $pet = $em->getRepository(PetEntity::class)->find($request->request->get('id'));

       if(!$pet){

         $result['success'] = false; 
         $result['msg'] = 'Invalid request please try again later.';
       } else {

         $pet->setIsDeleted(true);
         $em->flush();

         $result['msg'] = 'Pet successfully removed.';
       }

       return new JsonResponse($result);
    }


    /**
     * @Route("/ajax_form", name="pet_ajax_form")
     */
    public function ajaxForm(Request $request, AuthService $authService): JsonResponse
    {
       
       $result = [ 'success' =>  true, 'msg' => ''];

       if(!$authService->isLoggedIn()) { $result['success'] = false; $result['msg'] = 'Unauthorized access please call a system administrator.';}
       
       $clientId = $request->request->get('clientId');
       $formOptions = array('action' => 'n' , 'clientId' => base64_encode($clientId));
       $form = $this->createForm(PetForm::class, new PetEntity(), $formOptions);
    
       $result['html'] = $this->renderView('Pet/ajax_form.html.twig', [
            'page_title' => 'New Pet',
            'form' => $form->createView()
        ]);

       return new JsonResponse($result);
    }

    /**
     * @Route("/ajax_form_process", name="pet_ajax_form_process")
     */
    public function ajaxFormProcess(Request $request, AuthService $authService): JsonResponse
    {
       
       $result = [ 'success' =>  true, 'msg' => ''];

       if(!$authService->isLoggedIn()) { $result['success'] = false; $result['msg'] = 'Unauthorized access please call a system administrator.';}

       if($request->getMethod() == "POST"){
         $petForm = $request->request->get('pet_form');
         
         $em = $this->getDoctrine()->getManager();
         $errors = $em->getRepository(PetEntity::class)->validate($petForm);

         if(!count($errors)){

             $newPet = new PetEntity();
             $newPet->setName($petForm['name']);
             $newPet->setGender($petForm['gender']);
             $newPet->setColorMarkings($petForm['color_markings']);

             if(!empty($petForm['birth_date'])){
               $newPet->setBirthDate(new \DateTime($petForm['birth_date']));
             }
             if(!empty($petForm['breed'])){
               $newPet->setBreed($em->getReference(BreedEntity::class, $petForm['breed']));
             }
             //$newPet->setClient($em->getReference(ClientEntity::class, base64_decode($petForm['client'])));
             
             $em->persist($newPet);
             $em->flush();

             $clientPet = new ClientPetEntity();
             $clientPet->setClient($em->getReference(ClientEntity::class, base64_decode($petForm['client_id'])));
             $clientPet->setPet($newPet);

             $em->persist($clientPet);
             $em->flush();

             $result['msg'] = 'Pet successfully added to record.';
             $result['pet_name'] = $clientPet->getPet()->getName();
             $result['pet_id'] = $clientPet->getPet()->getId();
        
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
     * @Route("/ajax_add_existing_form", name="pet_ajax_add_existing_form")
     */
    public function ajax_add_existingForm(Request $request, AuthService $authService): JsonResponse
    {
       
       $result = [ 'success' =>  true, 'msg' => ''];

       if(!$authService->isLoggedIn()) { $result['success'] = false; $result['msg'] = 'Unauthorized access please call a system administrator.';}
       
       $clientId = $request->request->get('clientId');
       $result['html'] = $this->renderView('Pet/ajax_add_existing_form.html.twig', [
            'page_title' => 'Add Pet',
            'clientId' => $clientId
        ]);

       return new JsonResponse($result);
    }

    

        /**
     * @Route("/ajax_add_existing_pet_form_process", name="pet_ajax_add_existing_pet_form_process")
     */
    public function ajax_add_existing_pet_formProcess(Request $request, AuthService $authService): JsonResponse
    {
       
       $result = [ 'success' =>  true, 'msg' => ''];

       if(!$authService->isLoggedIn()) { $result['success'] = false; $result['msg'] = 'Unauthorized access please call a system administrator.';}

       if($request->getMethod() == "POST"){
         $petForm = $request->request->get('pet_form');
         
         $em = $this->getDoctrine()->getManager();
         $errors = $em->getRepository(ClientPetEntity::class)->validate($petForm);

         if(!count($errors)){

             $clientPet = new ClientPetEntity();
             $clientPet->setClient($em->getReference(ClientEntity::class, $petForm['client']));
             $clientPet->setPet($em->getReference(PetEntity::class,$petForm['pet']));
             $em->persist($clientPet);
             $em->flush();

             $result['msg'] = 'Pet successfully added to client.';
        
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
     * @Route("/ajax_merge_form", name="pet_ajax_merge_form")
     */
    public function pet_ajax_mergeForm(Request $request, AuthService $authService): JsonResponse
    {
       
       $result = [ 'success' =>  true, 'msg' => ''];

       if(!$authService->isLoggedIn()) { $result['success'] = false; $result['msg'] = 'Unauthorized access please call a system administrator.';}
       
       $petId = $request->request->get('petId');
       $result['html'] = $this->renderView('Pet/ajax_merge_form.html.twig', [
            'page_title' => 'Merge Pet',
            'petId' => $petId
        ]);

       return new JsonResponse($result);
    }

      /**
     * @Route("/ajax_merge_form_process", name="pet_ajax_merge_form_process")
     */
    public function ajax_merge_formProcess(Request $request, AuthService $authService): JsonResponse
    {
       
       $result = [ 'success' =>  true, 'msg' => ''];

       if(!$authService->isLoggedIn()) { $result['success'] = false; $result['msg'] = 'Unauthorized access please call a system administrator.';}

       if($request->getMethod() == "POST"){
         $petForm = $request->request->get('pet_form');
         
         $em = $this->getDoctrine()->getManager();
         $errors = $em->getRepository(PetEntity::class)->validateMerge($petForm);

         if(!count($errors)){

            $petToMerge = $em->getRepository(PetEntity::class)->find($petForm['merge_pet']);
            $mergeToPet = $em->getRepository(PetEntity::class)->find($petForm['pet']);

            $petAdmissions = $petToMerge->getAdmissionPets();

            if(count($petAdmissions)){

               foreach($petAdmissions as $petAdmission){
                  $petAdmission->setPet($mergeToPet);
                  $em->flush();
               }
            }

            $petToMerge->setIsDeleted(1);
            $em->flush();
            $result['msg'] = 'Pet successfully merge.';
        
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
    * @Route("", name="pet_index")
    */
   public function index(Request $request, AuthService $authService)
   {
      
      if(!$authService->isLoggedIn()) return $authService->redirectToLogin();
      if(!$authService->isUserHasAccesses(array('Pet'))) return $authService->redirectToHome();
      
      $page_title = ' Pet'; 
      return $this->render('Pet/index.html.twig', [ 
         'page_title' => $page_title, 
         'javascripts' => array('plugins/datatables/jquery.dataTables.js','/js/pet/index.js') ]
      );
   }

   /**
    * @Route("/details/{id}", name="pet_details")
    */
    public function details($id,Request $request, AuthService $authService)
    {
       
       if(!$authService->isLoggedIn()) return $authService->redirectToLogin();
       if(!$authService->isUserHasAccesses(array('Pet Details'))) return $authService->redirectToHome();
      

       $pet  = $this->getDoctrine()->getManager()->getRepository(PetEntity::class)->find(base64_decode($id)); 
       $page_title = ' Pet Details'; 
       return $this->render('Pet/details.html.twig', [ 
          'page_title' => $page_title,
          'pet' => $pet, 
          'javascripts' => array('/plugins/datatables/jquery.dataTables.js','/js/pet/details.js') 
         ]);
    }


   /**
    * @Route("/autocomplete", name="pet_autocomplete")
    */
   public function autocompleteAction(Request $request) {

      return new JsonResponse(array(
         'query' => 'pets',
         'suggestions' => $this->getDoctrine()->getManager()->getRepository(PetEntity::class)->autocompleteSuggestions($request->query->all(), $this->get('session')->get('userData'))
      ));
   }

     /**
    * @Route("/autocomplete_with_adopter", name="pet_autocomplete_with_adopter")
    */
    public function autocompletewithAdopterAction(Request $request) {

      return new JsonResponse(array(
         'query' => 'pets',
         'suggestions' => $this->getDoctrine()->getManager()->getRepository(PetEntity::class)->withAdopterAutocompleteSuggestions($request->query->all(), $this->get('session')->get('userData'))
      ));
   }

     /**
    * @Route("/autocomplete_with_client", name="pet_autocomplete_with_client")
    */
    public function autocompletewithClientAction(Request $request) {

      return new JsonResponse(array(
         'query' => 'pets',
         'suggestions' => $this->getDoctrine()->getManager()->getRepository(PetEntity::class)->withClientAutocompleteSuggestions($request->query->all(), $this->get('session')->get('userData'))
      ));
   }

   /**
    * @Route("/ajax_list", name="pet_ajax_list")
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
          $result = $this->getDoctrine()->getManager()->getRepository(PetEntity::class)->ajax_list($get, $this->get('session')->get('userData'));
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
   *      name = "pet_form"
   * )
   */
   public function formAction($action, $id, Request $request, AuthService $authService) {

      if(!$authService->isLoggedIn()) return $authService->redirectToLogin();
      if(!$authService->getUser()->getType() == 'Super Admin') return $authService->redirectToHome();

      $em = $this->getDoctrine()->getManager();

      $pet = $em->getRepository(PetEntity::class)->find(base64_decode($id));
      if(!$pet) {
         $pet = new PetEntity();
      }

      $formOptions = array('action' => 'n' , 'clientId' => null);
      $form = $this->createForm(PetForm::class, $pet, $formOptions);

      if($request->getMethod() === 'POST') {

         $pet_form = $request->get($form->getName());
         $result = $this->processForm($pet_form, $pet, $form, $request);

         if($result['success']) {
            if($result['redirect'] === 'index') {
                  return $this->redirect($this->generateUrl('pet_index'), 302);
            } else if($result['redirect'] === 'form') {
                  return $this->redirect($this->generateUrl('pet_form', array(
                     'action' => 'u',
                     'id' => base64_encode($result['id'])
                  )), 302);
            } else if($result['redirect'] === 'form with error') {
                  return $this->redirect($this->generateUrl('pet_form'), 302);
            }
         } else {
            $form->submit($pet_form, false);
         }
      }

      $title = ($action === 'n' ? 'New' : 'Update') . ' Pet';

      return $this->render('Pet/form.html.twig', array(
         'title' => $title,
         'page_title' => $title,
         'form' => $form->createView(),
         'action' => $action,
         'id' => $id,
         'pet' => $pet
      ));
   }

   private function processForm($pet_form, $pet ,$form, Request $request) {

      $em = $this->getDoctrine()->getManager();

      $errors = $em->getRepository(PetEntity::class)->validate($pet_form);

      if(!count($errors)) {

         switch($pet_form['action']) {
            case 'n': // new

                  $form->handleRequest($request);

                  if ($form->isValid()) {
                     
                     if(isset($pet_form['birth_date']) && !empty($pet_form['birth_date'])){
                        $pet->setBirthDate(new \DateTime($pet_form['birth_date']));
                     }

                     $em->persist($pet);
                     $em->flush();

                     

                     $this->get('session')->getFlashBag()->set('success_messages', 'Record Successfully Saved.');

                     $result['redirect'] = 'index';

                  } else {

                     $this->get('session')->getFlashBag()->set('error_messages', 'Something went wrong. Please try again.');
                     $result['redirect'] = 'form with error';
                  }

                  break;

            case 'u': // update

                  

                  $form->handleRequest($request);

                  if ($form->isValid()) {

                     if(isset($pet_form['birth_date']) && !empty($pet_form['birth_date'])){
                        $pet->setBirthDate(new \DateTime($pet_form['birth_date']));

                     }

                  $em->persist($pet);
                  

                     if(isset($_FILES['pet_form']) && !empty($_FILES['pet_form']['tmp_name']['profile_pic'])) {

                           $baseName = $pet->getId() . '-' . time() . '.' . pathinfo($_FILES['pet_form']['name']['profile_pic'], PATHINFO_EXTENSION);
                           $uploadFile = $pet->getUploadRootDir() . '/' . $baseName;
         
                           if(move_uploaded_file($_FILES['pet_form']['tmp_name']['profile_pic'], $uploadFile)) {
                              $pet->setProfilePicDesc($_FILES['pet_form']['name']['profile_pic']);
                              $pet->setParsedProfilePicDesc($baseName);
                           }
                     } 

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

                     $pet->setIsDeleted(true);
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
   * @Route(
   *      "/form/{pet}/{action}/{id}",
   *      defaults = {
   *          "action":  "n",
   *          "id": 0
   *      },
   *      requirements = {
   *          "action": "n|u"
   *      },
   *      name = "pet_cabinet_form"
   * )
   */
  public function cabinet_formAction($action, $id, $pet, Request $request, AuthService $authService) {

      if(!$authService->isLoggedIn()) return $authService->redirectToLogin();
      if(!$authService->getUser()->getType() == 'Super Admin') return $authService->redirectToHome();

      $em = $this->getDoctrine()->getManager();

      $cabinetForm = $em->getRepository(CabinetFormEntity::class)->find(base64_decode($id));
      if(!$cabinetForm) {
         $cabinetForm = new CabinetFormEntity();
      }

      $formOptions = array('action' => 'n' , 'petId' => $pet,'formTypes' => $authService->getCabinetFormTypes());
      $form = $this->createForm(CabinetFormForm::class, $cabinetForm, $formOptions);

      if($request->getMethod() === 'POST') {

         $cabinetForm_form = $request->get($form->getName());
         $result = $this->process_cabinetForm($cabinetForm_form, $cabinetForm, $form, $request);

         if($result['success']) {
            if($result['redirect'] === 'index') {
                  return $this->redirect($this->generateUrl('pet_index'), 302);
            } else if($result['redirect'] === 'details') {
               return $this->redirect($this->generateUrl('pet_details', array(
                  'id' => base64_encode($result['id'])
               )), 302);
          } else if($result['redirect'] === 'form with error') {
                  return $this->redirect($this->generateUrl('pet_form'), 302);
            }
         } else {
            $form->submit($pet_form, false);
         }
      }

      $title = ($action === 'n' ? 'New' : 'Update') . ' Cabinet Form';

      return $this->render('Pet/cabinet_form_form.html.twig', array(
         'title' => $title,
         'page_title' => $title,
         'form' => $form->createView(),
         'action' => $action,
         'id' => $id,
         'cabinetForm' => $cabinetForm,
         'petId' => $pet
      ));
   }

   private function process_cabinetForm($cabinetForm_form, $cabinetForm ,$form, Request $request) {

      $em = $this->getDoctrine()->getManager();

      $errors = $em->getRepository(CabinetFormEntity::class)->validate($cabinetForm_form);

      if(!count($errors)) {

         switch($cabinetForm_form['action']) {
            case 'n': // new

                  $form->handleRequest($request);

                  if ($form->isValid()) {
                     
                     $em->persist($cabinetForm);
                     $em->flush();

                     if(isset($_FILES['cabinet_form_form']) && !empty($_FILES['cabinet_form_form']['tmp_name']['cabinet_file'])) {

                        
                        $baseName = $cabinetForm->getId() . '-' . time() . '.' . pathinfo($_FILES['cabinet_form_form']['name']['cabinet_file'], PATHINFO_EXTENSION);
                        $uploadFile = $cabinetForm->getUploadRootDir() . '/' . $baseName;
      
                        if(move_uploaded_file($_FILES['cabinet_form_form']['tmp_name']['cabinet_file'], $uploadFile)) {
                           $cabinetForm->setFileDesc($_FILES['cabinet_form_form']['name']['cabinet_file']);
                           $cabinetForm->setParsedFiledDesc($baseName);
                        }

                        $em->flush();
                     } 

                     

                     $this->get('session')->getFlashBag()->set('success_messages', 'Record Successfully Saved.');

                     $result['redirect'] = 'details';
                     $result['id'] = $cabinetForm->getPet()->getId();

                  } else {

                     $this->get('session')->getFlashBag()->set('error_messages', 'Something went wrong. Please try again.');
                     $result['redirect'] = 'form with error';
                  }

                  break;

            case 'u': // update

                  $form->handleRequest($request);

                  if ($form->isValid()) {

                     if(isset($_FILES['cabinet_form_form']) && !empty($_FILES['cabinet_form_form']['tmp_name']['cabinet_file'])) {

                        
                        $baseName = $cabinetForm->getId() . '-' . time() . '.' . pathinfo($_FILES['cabinet_form_form']['name']['cabinet_file'], PATHINFO_EXTENSION);
                        $uploadFile = $cabinetForm->getUploadRootDir() . '/' . $baseName;
      
                        if(move_uploaded_file($_FILES['cabinet_form_form']['tmp_name']['cabinet_file'], $uploadFile)) {
                           $cabinetForm->setFileDesc($_FILES['cabinet_form_form']['name']['cabinet_file']);
                           $cabinetForm->setParsedFiledDesc($baseName);
                        }

                        $em->flush();
                     } 

                  } else {

                     $this->get('session')->getFlashBag()->set('error_messages', 'Something went wrong. Please try again.');
                     $result['redirect'] = 'form with error';
                  }

                  break;

            case 'd': // delete
                  $form->handleRequest($request);


                  if ($form->isValid()) {

                     $cabinetForm->setIsDeleted(true);
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
    * @Route("/ajax_list_cabinet_form", name="pet_ajax_list_cabinet_form")
    */
    public function ajax_list_cabinet_formAction(Request $request, AuthService $authService) {

      $get = $request->query->all();

      $result = array(
          "draw" => intval($get['draw']),
          "recordsTotal" => 0,
          "recordsFiltered" => 0,
          "data" => array()
      );

      if($authService->isLoggedIn()) {
          $result = $this->getDoctrine()->getManager()->getRepository(CabinetFormEntity::class)->ajax_list($get, $this->get('session')->get('userData'));
      }

      return new JsonResponse($result);
   }

   /**
    * @Route("/medical_history", name="pet_medical_history_ajax")
    */
    public function pet_medical_history_ajax(Request $request, AuthService $authService) {

      $result = [];
      $petId = $request->request->get('id');

      $result['success'] = true;
      $result['html'] = $this->renderView('Pet/medical_history.html.twig',array('page_title' => 'Medical History','pet' => $this->getDoctrine()->getManager()->getRepository(PetEntity::class)->find($petId)));
      return new JsonResponse($result);
   }

   

}
