<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;

use App\Entity\ServiceEntity;
use App\Entity\ServiceTypeEntity;

use App\Form\ServiceForm;

use App\Entity\ServiceAccessEntity;
use App\Service\AuthService;

use PhpOffice\PhpSpreadsheet\Reader\Csv;


/**
 * @Route("/service")
 */
class ServiceController extends AbstractController
{
    /**
     * @Route("", name="service_index")
     */
    public function index(Request $request, AuthService $authService)
    {
        if(!$authService->isLoggedIn()) return $authService->redirectToLogin();
        if(!$authService->isUserHasAccesses(array('CMS Service'))) return $authService->redirectToHome();
        
       $page_title = ' Service'; 
       return $this->render('Service/index.html.twig', [ 
            'page_title' => $page_title, 
            'javascripts' => array('plugins/datatables/jquery.dataTables.js','/js/service/index.js') ]
       );
    }


    /**
     * @Route("/ajax_list", name="service_ajax_list")
     */
    public function ajax_list(Request $request, AuthService $authService) {

        $get = $request->query->all();

        $result = array(
            "draw" => intval($get['draw']),
            "recordsTotal" => 0,
            "recordsFiltered" => 0,
            "data" => array()
        );

        if($authService->isLoggedIn()) {

            $hasUpdate = $authService->getUser()->getType() == 'Super Admin'  || $authService->isUserHasAccesses(array('CMS Service')) ? true : false;
            $data = $this->getDoctrine()->getManager()->getRepository(ServiceEntity::class)->ajax_list($get, $this->get('session')->get('userData'));

            foreach($data['results'] as $row) {

                $id = base64_encode($row['id']);
                $action = '';

                if($hasUpdate) {
                    $url = $this->generateUrl('service_form', array(
                        'action' => 'u',
                        'id' => $id
                    ));
                   
                    $action = "<a class='action-button-style btn btn-primary' href='$url'>Update</a>";
                }

                $values = array(
                    $row['serviceType'],
                    $row['code'],
                    $row['description'],
                    number_format($row['price'], 2, '.', ','),
                    $action
                );

                $result['data'][] = $values;
            }

            $result['recordsTotal'] = $data['total'];
            $result['recordsFiltered'] = $data['total'];
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
     *      name = "service_form"
     * )
     */
    public function formAction($action, $id, Request $request, AuthService $authService) {

        if(!$authService->isLoggedIn()) return $authService->redirectToLogin();
        if(!$authService->getUser()->getType() == 'Super Admin') return $authService->redirectToHome();

        $em = $this->getDoctrine()->getManager();

        $service = $em->getRepository(ServiceEntity::class)->find(base64_decode($id));
        if(!$service) {
            $service = new ServiceEntity();
        }

        $formOptions = array('action' => $action , 'branchId' => $authService->getUser()->getBranch()->getIdEncoded());
        $form = $this->createForm(ServiceForm::class, $service, $formOptions);

        if($request->getMethod() === 'POST') {

            $service_form = $request->get($form->getName());
            $result = $this->processForm($service_form, $service, $form, $request);

            if($result['success']) {
                if($result['redirect'] === 'index') {
                    return $this->redirect($this->generateUrl('service_index'), 302);
                } else if($result['redirect'] === 'form') {
                    return $this->redirect($this->generateUrl('service_form', array(
                        'action' => 'u',
                        'id' => base64_encode($result['id'])
                    )), 302);
                } else if($result['redirect'] === 'form with error') {
                    return $this->redirect($this->generateUrl('service_form'), 302);
                }
            } else {
                $form->submit($service_form, false);
            }
        }
        $title = ($action === 'n' ? 'New' : 'Update') . ' Service';

        return $this->render('Service/form.html.twig', array(
            'javascripts' => array('/js/service/form.js'),

            'title' => $title,
            'page_title' => $title,
            'form' => $form->createView(),
            'action' => $action,
            'id' => $id

        ));
    }

    private function processForm($service_form, $service ,$form, Request $request) {

        $em = $this->getDoctrine()->getManager();

        $errors = $em->getRepository(ServiceEntity::class)->validate($service_form);

        if(!count($errors)) {

            switch($service_form['action']) {
                case 'n': // new

                    $form->handleRequest($request);

                    if ($form->isValid()) {
                        
                        $em->persist($service);
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

                        $em->persist($service);

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

                        $service->setIsDeleted(true);
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
     * @Route("/autocomplete", name="service_autocomplete")
     */
    public function autocompleteAction(Request $request) {

        return new JsonResponse(array(
            'query' => 'services',
            'suggestions' => $this->getDoctrine()->getManager()->getRepository(ServiceEntity::class)->autocomplete_suggestions($request->query->all(), $this->get('session')->get('userData'))
        ));
    }

         /**
     * @Route("/import", name="service_item_import")
     */
    public function import(Request $request, AuthService $authService)
    {
        if(!$authService->isLoggedIn()) return $authService->redirectToLogin();
        if(!$authService->isUserHasAccesses(array('CMS Service Import'))) return $authService->redirectToHome();
        
        if($request->getMethod() == 'POST'){
            $file_mimes = array('text/x-comma-separated-values', 'text/comma-separated-values', 'application/octet-stream', 'application/vnd.ms-excel', 'application/x-csv', 'text/x-csv', 'text/csv', 'application/csv', 'application/excel', 'application/vnd.msexcel', 'text/plain', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            
            if(isset($_FILES['services']['name']) && in_array($_FILES['services']['type'], $file_mimes)) {

                $reader = new Csv();
                $spreadsheet = $reader->load($_FILES['services']['tmp_name']);
 
                $sheetData = $spreadsheet->getActiveSheet()->toArray();
                array_shift($sheetData);

                $em = $this->getDoctrine()->getManager();
                foreach($sheetData as $data){

                    $serviceType = $em->getRepository(ServiceTypeEntity::class)->findOneBy(array('isDeleted' => false, 'description' => $data[2]));
                    
                    if($serviceType){
                        $service = $em->getRepository(ServiceEntity::class)->findOneBy(array('isDeleted' => 0, 'description' => $data[0], 'branch' =>  $authService->getUser()->getBranch(), 'serviceType' => $serviceType->getId()));

                        if(!$service){
                         
                            $service = new ServiceEntity();
                             $service->setCode($data[0]);
                             $service->setDescription($data[0]);
                             $service->setPrice($data[1]);
                             $service->setBranch($authService->getUser()->getBranch());
                             $service->setServiceType($serviceType);
                             $em->persist($service);
                             $em->flush();
                        }
                    }
                
                }

                $this->get('session')->getFlashBag()->set('success_messages', 'Services successfully import.');

            } else {

                $this->get('session')->getFlashBag()->set('error_messages', 'Please put a valid CSV file.');

            }

        } else {

            $this->get('session')->getFlashBag()->set('error_messages', 'Unauthorized request please call a system administrator.');

        }


       return $this->redirect($this->generateUrl('service_index'),302);
    }

    

}
