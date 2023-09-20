<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Knp\Snappy\Pdf;
use Symfony\Component\HttpFoundation\Response;
use PhpOffice\PhpSpreadsheet\Reader\Csv;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

use App\Entity\ShelterAdmissionEntity;
use App\Form\ShelterAdmissionForm;
use App\Form\ShelterAdmissionAdoptForm;
use App\Form\ShelterAdmissionFosteredForm;

use App\Entity\ServiceEntity;
use App\Entity\UserEntity;
use App\Entity\PetEntity;
use App\Entity\SpeciesEntity;
use App\Entity\FacilityEntity;
use App\Entity\BranchEntity;


use App\Service\AuthService;


/**
 * @Route("/shelter_admission")
 */
class ShelterAdmissionController extends AbstractController
{
    /**
     * @Route("", name="shelter_admission_index")
     */
    public function index(Request $request, AuthService $authService)
    {
        if(!$authService->isLoggedIn()) return $authService->redirectToLogin();
        if(!$authService->isUserHasAccesses(array('Shelter Admission'))) return $authService->redirectToHome();

        $em = $this->getDoctrine()->getManager();        
       $page_title = ' Shelter Admission'; 
       return $this->render('ShelterAdmission/index.html.twig', [ 
            'page_title' => $page_title, 
            'javascripts' => array('/plugins/datatables/jquery.dataTables.js','/js/shelter_admission/index.js')
        ]
       );
    }
    

    /**
     * @Route("/select_admission_type", name="shelter_admission_select_admission_type")
     */
    public function selectAdmissionType(Request $request, AuthService $authService)
    {
       
       if(!$authService->isLoggedIn()) return $authService->redirectToLogin();
       if(!$authService->isUserHasAccesses(array('Shelter Admission Select Admission Type'))) return $authService->redirectToHome();

       $em = $this->getDoctrine()->getManager();        
       $page_title = ' Select Treatment Type'; 
       return $this->render('ShelterAdmission/select_admission_type.html.twig', [ 
            'page_title' => $page_title,
            'admissionTypes' => $authService->getShelterAdmissionTypes()
        ]
       );
    }

    /**
     * @Route("/ajax_list", name="shelter_admission_ajax_list")
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
            $result = $this->getDoctrine()->getManager()->getRepository(ShelterAdmissionEntity::class)->ajax_list($get, $this->get('session')->get('userData'));
        }

        return new JsonResponse($result);
    }

    /**
     * @Route("/ajax_history_list", name="shelter_admission_ajax_history_list")
     */
    public function ajax_history_list(Request $request, AuthService $authService) {

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
            $result = $this->getDoctrine()->getManager()->getRepository(ShelterAdmissionEntity::class)->ajax_history_list($get, $this->get('session')->get('userData'));
        }

        return new JsonResponse($result);
    }

          /**
     * @Route(
     *      "/form/{admission_type}/{action}/{id}",
     *      defaults = {
     *          "action":  "n",
     *          "id": 0
     *      },
     *      requirements = {
     *          "action": "n|u",
     *          "admission_type": "0|1"
     *      },
     *      name = "shelter_admission_form"
     * )
     */
    public function formAction($admission_type,$action, $id, Request $request, AuthService $authService) {

        if(!$authService->isLoggedIn()) return $authService->redirectToLogin();
        if(!$authService->getUser()->getType() == 'Super Admin') return $authService->redirectToHome();
        
        $em = $this->getDoctrine()->getManager();
        $shelterAdmission = $em->getRepository(ShelterAdmissionEntity::class)->find(base64_decode($id));
        if(!$shelterAdmission) {
            $shelterAdmission = new ShelterAdmissionEntity();
        }

        $formOptions = array('admissionType' => $admission_type, 'action' => $action, 'branchId' => $authService->getUser()->getBranch()->getId());
        $form = $this->createForm(ShelterAdmissionForm::class, $shelterAdmission, $formOptions);
        
        if($request->getMethod() === 'POST') {

            $shelterAdmission_form = $request->get($form->getName());
            $result = $this->processForm($shelterAdmission_form, $shelterAdmission, $form, $request);
            if($result['success']) {
                if($result['redirect'] === 'index') {
                    return $this->redirect($this->generateUrl('shelter_admission_index'), 302);
                } else if($result['redirect'] === 'form') {
                    return $this->redirect($this->generateUrl('shelter_admission_form', array(
                        'action' => 'u',
                        'id' => base64_encode($result['id'])
                    )), 302);
                } else if($result['redirect'] === 'details') {
                    return $this->redirect($this->generateUrl('shelter_admission_details', array(
                        'id' => base64_encode($result['id'])
                    )), 302);
                } else if($result['redirect'] === 'form with error') {
                    return $this->redirect($this->generateUrl('shelter_admission_form'), 302);
                }
            } else {
                $form->submit($shelterAdmission_form, false);
            }
        }

        $title = ($action === 'n' ? 'New' : 'Update') . ' Admission';

        return $this->render('ShelterAdmission/form.html.twig', array(
            'title' => $title,
            'page_title' => $title,
            'form' => $form->createView(),
            'action' => $action,
            'id' => $id,
            'shelterAdmission' => $shelterAdmission,
            'admission_type' => $admission_type,
            'javascripts' => array('/js/shelter_admission/form.js'),
        ));
    }

    private function processForm($shelterAdmission_form, $shelterAdmission ,$form, Request $request) {

        $em = $this->getDoctrine()->getManager();
        $errors = $em->getRepository(ShelterAdmissionEntity::class)->validate($shelterAdmission_form);

        if(!count($errors)) {

            switch($shelterAdmission_form['action']) {
                case 'n': // new

                    $form->handleRequest($request);

                    if ($form->isValid()) {
                        
                        $shelterAdmission->setStatus('Admitted');
                        $em->persist($shelterAdmission);
                        $em->flush();

                        $this->get('session')->getFlashBag()->set('success_messages', 'Record Successfully Saved.');

                        $result['redirect'] = 'details';
                        $result['id'] = $shelterAdmission->getId();

                    } else {

                        $this->get('session')->getFlashBag()->set('error_messages', 'Something went wrong. Please try again.');
                        $result['redirect'] = 'form with error';
                    }

                    break;

                case 'u': // update

                    $form->handleRequest($request);

                    if ($form->isValid()) {

                        $em->persist($shelterAdmission);

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

                        $shelterAdmission->setIsDeleted(true);
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
     * @Route("/ajax_form", name="shelter_admission_ajax_form")
     */
    public function ajaxForm(Request $request, AuthService $authService): JsonResponse
    {
       
       $result = [ 'success' =>  true, 'msg' => ''];
       if(!$authService->isLoggedIn()) { $result['success'] = false; $result['msg'] = 'Unauthorized access please call a system administrator.';}
       
       $em = $this->getDoctrine()->getManager();
       $formData = $request->request->all();
       $shelterAdmission = $em->getRepository(ShelterAdmissionEntity::class)->find(base64_decode($formData['id']));
       $formOptions = array('admissionType' => $shelterAdmission->getAdmissionType(), 'action' => $formData['action'], 'branchId' => $authService->getUser()->getBranch()->getId());
       $form = $this->createForm(ShelterAdmissionForm::class, $shelterAdmission, $formOptions);

       $result['html'] = $this->renderView('ShelterAdmission/ajax_form.html.twig', [
            'page_title' =>  'Update Admission',
            'form' => $form->createView(),
            'action' => $formData['action'],
            'admission_type' => $shelterAdmission->getAdmissionType(),
            'id' => $formData['id']
        ]);

       return new JsonResponse($result);
    }

        /**
     * @Route("/ajax_form_process", name="shelter_admission_ajax_form_process")
     */
    public function ajaxFormProcess(Request $request, AuthService $authService): JsonResponse
    {
       
       $result = [ 'success' =>  true, 'msg' => ''];

       if(!$authService->isLoggedIn()) { $result['success'] = false; $result['msg'] = 'Unauthorized access please call a system administrator.';}

       if($request->getMethod() == "POST"){

         $admissionForm = $request->request->get('shelter_admission_form');         
         $em = $this->getDoctrine()->getManager();
         $errors = $em->getRepository(ShelterAdmissionEntity::class)->validate($admissionForm);

         if(!count($errors)){
            
            $shelterAdmission = $em->getRepository(ShelterAdmissionEntity::class)->find($admissionForm['id']);

            $formOptions = array('admissionType' => $shelterAdmission->getAdmissionType(), 'action' => $admissionForm['action'], 'branchId' => $authService->getUser()->getBranch()->getId());
            $form = $this->createForm(ShelterAdmissionForm::class, $shelterAdmission, $formOptions);


            switch($admissionForm['action']) {
               case 'n': // new

                     $form->handleRequest($request);
      
                     if ($form->isValid()) {
                        
                        $em->persist($shelterAdmission);
                        $em->flush();
   
                        $result['msg'] = 'Admission successfully added to record.';

                     } else {
      
                        $result['error'] = 'Ooops something went wrong please try again later.';
                     }
      
                     break;
      
               case 'u': // update

                     $form->handleRequest($request);
                     if ($form->isValid()) {
                        
                        $em->persist($shelterAdmission);
                        $em->flush();
                        $result['msg'] = 'Admission successfully updated.';
                     } else {
                       
                        $result['error'] = 'Ooops something went wrong please try again later.';
                     }
      
                     break;
      
               case 'd': // delete
                     $form->handleRequest($request);
                     if ($form->isValid()) {
                          
                        $shelterAdmission->setIsDeleted(true);
                        $em->flush();
      
                        $result['msg'] = 'Admission successfully deleted.';
      
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
    * @Route("/details/{id}", name="shelter_admission_details")
    */
    public function details($id,Request $request, AuthService $authService)
    {
       
       if(!$authService->isLoggedIn()) return $authService->redirectToLogin();
       if(!$authService->isUserHasAccesses(array('Shelter Admission Details'))) return $authService->redirectToHome();
      

       $shelterAdmission  = $this->getDoctrine()->getManager()->getRepository(ShelterAdmissionEntity::class)->find(base64_decode($id)); 
       $page_title = ' Admission Details'; 

       return $this->render('ShelterAdmission/details.html.twig', [ 
          'page_title' => $page_title,
          'shelterAdmission' => $shelterAdmission, 
          'javascripts' => array('/plugins/datatables/jquery.dataTables.js','/js/shelter_admission/details.js') 
         ]);
    }

        /**
     * @Route("/ajax_details_part", name="shelter_admission_ajax_details_part")
     */
    public function ajaxDetailsPart(Request $request, AuthService $authService): JsonResponse
    {
       
       $result = [ 'success' =>  true, 'msg' => ''];
       if(!$authService->isLoggedIn()) { $result['success'] = false; $result['msg'] = 'Unauthorized access please call a system administrator.';}
       
       $em = $this->getDoctrine()->getManager();
       $data = $request->request->all();
       $shelterAdmission = $em->getRepository(ShelterAdmissionEntity::class)->find(base64_decode($data['id']));
           
       $result['html'] = $this->renderView('ShelterAdmission/details_part.html.twig', [
            'shelterAdmission' => $shelterAdmission,
            'part' => $data['part'],
            'admissionTypes' => $authService->getShelterAdmissionTypes()
        ]);

       return new JsonResponse($result);
    }

       /**
     * @Route("/adopt_ajax_form", name="shelter_admission_adopt_ajax_form")
     */
    public function adoptAjaxForm(Request $request, AuthService $authService): JsonResponse
    {
       
       $result = [ 'success' =>  true, 'msg' => ''];
       if(!$authService->isLoggedIn()) { $result['success'] = false; $result['msg'] = 'Unauthorized access please call a system administrator.';}
       
       $em = $this->getDoctrine()->getManager();
       $formData = $request->request->all();
       $shelterAdmission = $em->getRepository(ShelterAdmissionEntity::class)->find(base64_decode($formData['id']));
       $formOptions = array('action' => $formData['action']);
       $form = $this->createForm(ShelterAdmissionAdoptForm::class, $shelterAdmission, $formOptions);

       $result['html'] = $this->renderView('ShelterAdmission/adopt_ajax_form.html.twig', [
            'page_title' =>  'Adopt Pet',
            'form' => $form->createView(),
            'action' => $formData['action']
        ]);

       return new JsonResponse($result);
    }


        /**
     * @Route("/adopt_ajax_form_process", name="shelter_admission_adopt_ajax_form_process")
     */
    public function adoptajaxFormProcess(Request $request, AuthService $authService): JsonResponse
    {
       
       $result = [ 'success' =>  true, 'msg' => ''];

       if(!$authService->isLoggedIn()) { $result['success'] = false; $result['msg'] = 'Unauthorized access please call a system administrator.';}

       if($request->getMethod() == "POST"){

         $shelterAdmissionAdoptForm = $request->request->get('shelter_admission_adopt_form');         
         $em = $this->getDoctrine()->getManager();
         $errors = $em->getRepository(ShelterAdmissionEntity::class)->validateAdoptionForm($shelterAdmissionAdoptForm);

         if(!count($errors)){
            
            $shelterAdmission = $em->getRepository(ShelterAdmissionEntity::class)->find($shelterAdmissionAdoptForm['id']);

            $formOptions = array('action' => $shelterAdmissionAdoptForm['action']);
            $form = $this->createForm(ShelterAdmissionAdoptForm::class, $shelterAdmission, $formOptions);


            switch($shelterAdmissionAdoptForm['action']) {
               case 'n': // new

                     $form->handleRequest($request);
      
                     if ($form->isValid()) {
                        
                        $em->persist($shelterAdmission);
                        $em->flush();
   
                        $result['msg'] = 'Admission successfully added to record.';

                     } else {
      
                        $result['error'] = 'Ooops something went wrong please try again later.';
                     }
      
                     break;
      
               case 'u': // update

                     $form->handleRequest($request);
                     if ($form->isValid()) {
                        
                        $shelterAdmission->setStatus('Adopted');
                        $em->persist($shelterAdmission);
                        $em->flush();
                        $result['msg'] = 'Admission successfully updated.';
                     } else {
                       
                        $result['error'] = 'Ooops something went wrong please try again later.';
                     }
      
                     break;
      
               case 'd': // delete
                     $form->handleRequest($request);
                     if ($form->isValid()) {
                          
                        $shelterAdmission->setIsDeleted(true);
                        $em->flush();
      
                        $result['msg'] = 'Admission successfully deleted.';
      
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
     * @Route("/fostered_ajax_form", name="shelter_admission_fostered_ajax_form")
     */
    public function fosteredAjaxForm(Request $request, AuthService $authService): JsonResponse
    {
       
       $result = [ 'success' =>  true, 'msg' => ''];
       if(!$authService->isLoggedIn()) { $result['success'] = false; $result['msg'] = 'Unauthorized access please call a system administrator.';}
       
       $em = $this->getDoctrine()->getManager();
       $formData = $request->request->all();
       $shelterAdmission = $em->getRepository(ShelterAdmissionEntity::class)->find(base64_decode($formData['id']));
       $formOptions = array('action' => $formData['action']);
       $form = $this->createForm(ShelterAdmissionFosteredForm::class, $shelterAdmission, $formOptions);

       $result['html'] = $this->renderView('ShelterAdmission/fostered_ajax_form.html.twig', [
            'page_title' =>  'Fostered Pet',
            'form' => $form->createView(),
            'action' => $formData['action']
        ]);

       return new JsonResponse($result);
    }

        /**
     * @Route("/fostered_ajax_form_process", name="shelter_admission_fostered_ajax_form_process")
     */
    public function fosteredAjaxFormProcess(Request $request, AuthService $authService): JsonResponse
    {
       
       $result = [ 'success' =>  true, 'msg' => ''];

       if(!$authService->isLoggedIn()) { $result['success'] = false; $result['msg'] = 'Unauthorized access please call a system administrator.';}

       if($request->getMethod() == "POST"){

         $shelterAdmissionFosteredForm = $request->request->get('shelter_admission_fostered_form');         
         $em = $this->getDoctrine()->getManager();
         $errors = $em->getRepository(ShelterAdmissionEntity::class)->validateFosteredForm($shelterAdmissionFosteredForm);

         if(!count($errors)){
            
            $shelterAdmission = $em->getRepository(ShelterAdmissionEntity::class)->find($shelterAdmissionFosteredForm['id']);

            $formOptions = array('action' => $shelterAdmissionFosteredForm['action']);
            $form = $this->createForm(ShelterAdmissionFosteredForm::class, $shelterAdmission, $formOptions);


            switch($shelterAdmissionFosteredForm['action']) {
               case 'n': // new

                     $form->handleRequest($request);
      
                     if ($form->isValid()) {
                        
                        $em->persist($shelterAdmission);
                        $em->flush();
   
                        $result['msg'] = 'Admission successfully added to record.';

                     } else {
      
                        $result['error'] = 'Ooops something went wrong please try again later.';
                     }
      
                     break;
      
               case 'u': // update

                     $form->handleRequest($request);
                     if ($form->isValid()) {
                        
                        $shelterAdmission->setStatus('Fostered');
                        $em->persist($shelterAdmission);
                        $em->flush();
                        $result['msg'] = 'Admission successfully updated.';
                     } else {
                       
                        $result['error'] = 'Ooops something went wrong please try again later.';
                     }
      
                     break;
      
               case 'd': // delete
                     $form->handleRequest($request);
                     if ($form->isValid()) {
                          
                        $shelterAdmission->setIsDeleted(true);
                        $em->flush();
      
                        $result['msg'] = 'Admission successfully deleted.';
      
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
     * @Route("/adoption_report", name = "shelter_admission_adoption_report")
     */
    public function adoption_reportAction(AuthService $authService){

        if(!$authService->isLoggedIn()) return $authService->redirectToLogin();
        if(!$authService->isUserHasAccesses(array('Report Adoption'))) return $authService->redirectToHome();
        
       $page_title = ' Adoption Report'; 
       return $this->render('ShelterAdmission/adoption_report.html.twig', [ 
            'page_title' => $page_title
       ]);
    } 

        /**
     * @Route("/adoption_report_csv", name = "shelter_admission_adoption_report_csv")
     */
    public function adoptionReportCsv(Request $request, AuthService $authService){

        ini_set('memory_limit', '2048M');
        set_time_limit(0);
        $dateRange = $request->query->all();
        $startDate = isset($dateRange['start_date']) ? $dateRange['start_date'] : NULL; 
        $endDate = isset($dateRange['end_date']) ? $dateRange['end_date'] : NULL; 

        $em = $this->getDoctrine()->getManager();
        $results  = $this->getDoctrine()->getManager()->getRepository(ShelterAdmissionEntity::class)->adoption_report($startDate, $endDate, $this->get('session')->get('userData'));
        $columnRange = range('A', 'G');
        $cellsData = array( );

        $rowCtr = 0;
        $totalCount = 0;

        $rowCtr++;

        $cellsData[] = array('cell' => "A$rowCtr", 'data' => 'Pet');
        $cellsData[] = array('cell' => "B$rowCtr", 'data' => 'Adopter Name');
        $cellsData[] = array('cell' => "C$rowCtr", 'data' => 'Adopter Contact #');
        $cellsData[] = array('cell' => "D$rowCtr", 'data' => 'Adopter Address');
        $cellsData[] = array('cell' => "E$rowCtr", 'data' => 'Adopter Email Address');
        $cellsData[] = array('cell' => "F$rowCtr", 'data' => 'Adoption Date');
        $cellsData[] = array('cell' => "G$rowCtr", 'data' => 'Remarks');
          
        foreach($results as $result) {

            $rowCtr++;
            $cellsData[] = array('cell' => "A$rowCtr", 'data' => $result['pet']);
            $cellsData[] = array('cell' => "B$rowCtr", 'data' => $result['adopter']);
            $cellsData[] = array('cell' => "C$rowCtr", 'data' => $result['adopterContact']);
            $cellsData[] = array('cell' => "D$rowCtr", 'data' => $result['adopterAddress']);
            $cellsData[] = array('cell' => "E$rowCtr", 'data' => $result['adopterEmail']);
            $cellsData[] = array('cell' => "F$rowCtr", 'data' => $result['adoptionDate']);
            $cellsData[] = array('cell' => "G$rowCtr", 'data' => $result['remarks']);

        }

        $page_title = 'Adoption Report';
        return $this->export_to_excel($columnRange, $cellsData, $page_title);
    }

        /**
     * @Route("/adoption_report/pdf", name = "adoption_report_pdf")
     */
    public function adoptionReportPdf(Request $request, AuthService $authService, Pdf $pdf){

        ini_set('memory_limit', '2048M');
        $dateRange = $request->query->all();
        $startDate = isset($dateRange['start_date']) ? $dateRange['start_date'] : NULL; 
        $endDate = isset($dateRange['end_date']) ? $dateRange['end_date'] : NULL; 


        $results  = $this->getDoctrine()->getManager()->getRepository(ShelterAdmissionEntity::class)->adoption_report($startDate, $endDate, $this->get('session')->get('userData'));
        $options = [
            'orientation' => 'Portrait',
            'print-media-type' =>  True,
            'zoom' => .5
        ];


         $newContent = $this->renderView('ShelterAdmission/adoption_report.wkpdf.twig', array(
            'startDate' => $startDate,
            'endDate' => $endDate,
            'results' => $results
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
            'Content-Disposition'   => 'attachment; filename="'.  'adoption-report' .'-' . date('m/d/Y') . '.pdf"'
        ));
    }


    /**
     * @Route("/import", name="shelter_admission_import")
     */
    public function import(Request $request, AuthService $authService)
    {
        if(!$authService->isLoggedIn()) return $authService->redirectToLogin();
        
        if($request->getMethod() == 'POST'){
            $file_mimes = array('text/x-comma-separated-values', 'text/comma-separated-values', 'application/octet-stream', 'application/vnd.ms-excel', 'application/x-csv', 'text/x-csv', 'text/csv', 'application/csv', 'application/excel', 'application/vnd.msexcel', 'text/plain', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            
            if(isset($_FILES['shelter_admissions']['name']) && in_array($_FILES['shelter_admissions']['type'], $file_mimes)) {

                $reader = new Csv();
                $spreadsheet = $reader->load($_FILES['shelter_admissions']['tmp_name']);
 
                $sheetData = $spreadsheet->getActiveSheet()->toArray();
                array_shift($sheetData);

                $em = $this->getDoctrine()->getManager();
                $userData =  $this->get('session')->get('userData');
                $branch = $em->getReference(BranchEntity::class, $userData['branchId']);
                foreach($sheetData as $data){

                    $species = $em->getRepository(SpeciesEntity::class)->findOneBy(array('isDeleted' => false, 'description' => $data[3], 'branch' => $branch)); 

                    if(!$species){

                        $species = new SpeciesEntity();
                        $species->setCode($data[3]);
                        $species->setDescription($data[3]);
                        $species->setBranch($branch);
                        $em->persist($species);
                        $em->flush();
                    }

                    $facility = $em->getRepository(FacilityEntity::class)->findOneBy(array('isDeleted' => false, 'description' => $data[0], 'branch' => $branch));
                    
                    if(!$facility){

                        $facility = new FacilityEntity();
                        $facility->setCode($data[0]);
                        $facility->setDescription($data[0]);
                        $facility->setBranch($branch);
                        $facility->setSpecies($species);
                        $em->persist($facility);
                        $em->flush();
                    } 

                    $pet = $em->getRepository(PetEntity::class)->findOneBy(array('isDeleted' => false, 'name' => $data[1], 'branch' => $branch));
                    
                    if(!$pet){
                        
                        $pet = new PetEntity();
                        $pet->setName($data[1]);
                        $pet->setSpecies($species);
                        $pet->setBranch($branch);
                      
                        $em->persist($pet);
                        $em->flush();
                    }

                    $pet->setGender($data[2]);
                    $em->flush();
                    
                    $shelterAdmission = new ShelterAdmissionEntity();
                    $shelterAdmission->setPet($pet);
                    $shelterAdmission->setFacility($facility);
                    $shelterAdmission->setAdmissionDate(new \DateTime('now'));
                    $shelterAdmission->setRescuerName('N/A');
                    $shelterAdmission->setRescuerContact('N/A');
                    $shelterAdmission->setRescueDate(new \DateTime('now'));
                    $shelterAdmission->setRescuePlace('N/A');
                    $shelterAdmission->setRescueStory('N/A');
                    $shelterAdmission->setStatus('Admitted');
                    $shelterAdmission->setAdmissionType(0);
                    $shelterAdmission->setBranch($branch);
                    $em->persist($shelterAdmission);
                    $em->flush();


                
                }

                $this->get('session')->getFlashBag()->set('success_messages', 'Admission successfully import.');

            } else {

                $this->get('session')->getFlashBag()->set('error_messages', 'Please put a valid CSV file.');

            }

        } else {

            $this->get('session')->getFlashBag()->set('error_messages', 'Unauthorized request please call a system administrator.');

        }


       return $this->redirect($this->generateUrl('service_index'),302);
    }
    
    /* ===================================================================================== */
    /* =====================P R I V A T E   F U N C T I O N S=============================== */
    /* ===================================================================================== */


    private function export_to_excel($columnRange, $cellsData, $page_title, $customStyle=array()) {

        $spreadSheet = new SpreadSheet();
        $activeSheet = $spreadSheet->getActiveSheet(0);

        foreach($cellsData as $cellData) {
            $activeSheet->getCell($cellData['cell'])->setValue($cellData['data']);
         
        }

        $writer = new Xlsx($spreadSheet);
        $response =  new StreamedResponse(
            function () use ($writer) {
                $writer->save('php://output');
            }
        );

        $response->headers->set('Content-Type', 'text/vnd.ms-excel; charset=utf-8');
        $response->headers->set('Content-Disposition', 'attachment;filename="' . $page_title . '.xlsx"');
        $response->headers->set('Pragma', 'public');
        $response->headers->set('Cache-Control', 'maxage=1');

        return $response;
    }

}
