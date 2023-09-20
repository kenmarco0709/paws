<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;

use App\Entity\FacilityEntity;
use App\Entity\SpeciesEntity;

use App\Form\FacilityForm;

use App\Service\AuthService;


/**
 * @Route("/facility")
 */
class FacilityController extends AbstractController
{
    /**
     * @Route("", name="facility_index")
     */
    public function index(Request $request, AuthService $authService)
    {
        if(!$authService->isLoggedIn()) return $authService->redirectToLogin();
        if(!$authService->isUserHasAccesses(array('CMS Facility'))) return $authService->redirectToHome();
        
       $page_title = ' Facility'; 
       return $this->render('Facility/index.html.twig', [ 
            'page_title' => $page_title, 
            'javascripts' => array('plugins/datatables/jquery.dataTables.js','/js/facility/index.js') ]
       );
    }


    /**
     * @Route("/ajax_list", name="facility_ajax_list")
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

            $hasUpdate = $authService->getUser()->getType() == 'Super Admin'  || $authService->isUserHasAccesses(array('CMS Facility')) ? true : false;
            $data = $this->getDoctrine()->getManager()->getRepository(FacilityEntity::class)->ajax_list($get, $this->get('session')->get('userData'));

            foreach($data['results'] as $row) {

                $id = base64_encode($row['id']);
                $action = '';

                if($hasUpdate) {
                    $url = $this->generateUrl('facility_form', array(
                        'action' => 'u',
                        'id' => $id
                    ));
                   
                    $action = "<a class='action-button-style btn btn-primary' href='$url'>Update</a>";
                }

                $values = array(
                    $row['species'],
                    $row['code'],
                    $row['description'],
                    $row['capacity'],
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
     *      name = "facility_form"
     * )
     */
    public function formAction($action, $id, Request $request, AuthService $authService) {

        if(!$authService->isLoggedIn()) return $authService->redirectToLogin();
        if(!$authService->getUser()->getType() == 'Super Admin') return $authService->redirectToHome();

        $em = $this->getDoctrine()->getManager();

        $facility = $em->getRepository(FacilityEntity::class)->find(base64_decode($id));
        if(!$facility) {
            $facility = new FacilityEntity();
        }

        $formOptions = array('action' => $action , 'branchId' => $authService->getUser()->getBranch()->getIdEncoded());
        $form = $this->createForm(FacilityForm::class, $facility, $formOptions);

        if($request->getMethod() === 'POST') {

            $facility_form = $request->get($form->getName());
            $result = $this->processForm($facility_form, $facility, $form, $request);

            if($result['success']) {
                if($result['redirect'] === 'index') {
                    return $this->redirect($this->generateUrl('facility_index'), 302);
                } else if($result['redirect'] === 'form') {
                    return $this->redirect($this->generateUrl('facility_form', array(
                        'action' => 'u',
                        'id' => base64_encode($result['id'])
                    )), 302);
                } else if($result['redirect'] === 'form with error') {
                    return $this->redirect($this->generateUrl('facility_form'), 302);
                }
            } else {
                $form->submit($facility_form, false);
            }
        }
        $title = ($action === 'n' ? 'New' : 'Update') . ' Facility';

        return $this->render('Facility/form.html.twig', array(
            'javascripts' => array('/js/facility/form.js'),

            'title' => $title,
            'page_title' => $title,
            'form' => $form->createView(),
            'action' => $action,
            'id' => $id

        ));
    }

    private function processForm($facility_form, $facility ,$form, Request $request) {

        $em = $this->getDoctrine()->getManager();

        $errors = $em->getRepository(FacilityEntity::class)->validate($facility_form);

        if(!count($errors)) {

            switch($facility_form['action']) {
                case 'n': // new

                    $form->handleRequest($request);

                    if ($form->isValid()) {
                        
                        $em->persist($facility);
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

                        $em->persist($facility);

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

                        $facility->setIsDeleted(true);
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
     * @Route("/autocomplete", name="facility_autocomplete")
     */
    public function autocompleteAction(Request $request) {

        return new JsonResponse(array(
            'query' => 'facilities',
            'suggestions' => $this->getDoctrine()->getManager()->getRepository(FacilityEntity::class)->autocomplete_suggestions($request->query->all(), $this->get('session')->get('userData'))
        ));
    }

    
}
