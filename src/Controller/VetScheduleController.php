<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;

use App\Entity\VetScheduleEntity;
use App\Form\VetScheduleForm;
use App\Service\AuthService;

/**
 * @Route("/vet_schedule")
 */
class VetScheduleController extends AbstractController
{
    /**
     * @Route("", name="vet_schedule_index")
     */
    public function index(Request $request, AuthService $authService)
    {
        if(!$authService->isLoggedIn()) return $authService->redirectToLogin();
        if(!$authService->isUserHasAccesses(array('Vet Schedule'))) return $authService->redirectToHome();

        $em = $this->getDoctrine()->getManager();        
        $page_title = ' Veterinary Schedule'; 
        return $this->render('VetSchedule/index.html.twig', [ 
                'page_title' => $page_title, 
                'javascripts' => array('plugins/datatables/jquery.dataTables.js','/js/vet_schedule/index.js')
            ]
        );
    }

        /**
     * @Route("/ajax_list", name="vet_schedule_ajax_list")
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
            $result = $this->getDoctrine()->getManager()->getRepository(VetScheduleEntity::class)->ajax_list($get, $this->get('session')->get('userData'));
        }

        return new JsonResponse($result);
    }




    /**
     * @Route("/ajax_data", name="vet_schedule_ajax_data")
     */
    public function ajax_data(Request $request, AuthService $authService) {

        $get = $request->query->all();
        $result = [];

        if($authService->isLoggedIn()) {
            $result = $this->getDoctrine()->getManager()->getRepository(VetScheduleEntity::class)->branchVetSchedules($get, $this->get('session')->get('userData'));
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
     *      name = "vet_schedule_form"
     * )
     */
    public function formAction($action, $id, Request $request, AuthService $authService) {

        if(!$authService->isLoggedIn()) return $authService->redirectToLogin();
        if(!$authService->getUser()->getType() == 'Super Admin') return $authService->redirectToHome();

        $em = $this->getDoctrine()->getManager();

        $vetSchedule = $em->getRepository(VetScheduleEntity::class)->find(base64_decode($id));
        if(!$vetSchedule) {
            $vetSchedule = new VetScheduleEntity();
        }

        $formOptions = array('action' => $action, 'branchId' => $authService->getUser()->getBranch()->getId(), 'scheduleTypes' => $authService->getScheduleTypes());
        $form = $this->createForm(VetScheduleForm::class, $vetSchedule, $formOptions);
        
        $dateToSched = $request->query->get('date') ? date('m/d/Y', strtotime($request->query->get('date'))) : '';
       
        if($request->getMethod() === 'POST') {

            $vet_schedule_form = $request->get($form->getName());

            
            $result = $this->processForm($vet_schedule_form, $vetSchedule, $form, $request);
            if($result['success']) {
                if($result['redirect'] === 'index') {
                    return $this->redirect($this->generateUrl('vet_schedule_index'), 302);
                } else if($result['redirect'] === 'form') {
                    return $this->redirect($this->generateUrl('vet_schedule_form', array(
                        'action' => 'u',
                        'id' => base64_encode($result['id'])
                    )), 302);
                } else if($result['redirect'] === 'form with error') {
                    return $this->redirect($this->generateUrl('vet_schedule_form'), 302);
                }
            } else {
                $form->submit($vet_schedule_form, false);
            }
        }

        $title = ($action === 'n' ? 'New' : 'Update') . ' Veterinary Schedule';

        return $this->render('VetSchedule/form.html.twig', array(
            'title' => $title,
            'page_title' => $title,
            'form' => $form->createView(),
            'action' => $action,
            'id' => $id,
            'vetSchedule' => $vetSchedule,
            'isUserVet' => $authService->isUserVet(),
            'javascripts' => array('/js/vet_schedule/form.js'),
            'dateToSched' => $dateToSched
        ));
    }

    private function processForm($vet_schedule_form, $vet_schedule ,$form, Request $request) {

        $em = $this->getDoctrine()->getManager();

        $errors = $em->getRepository(VetScheduleEntity::class)->validate($vet_schedule_form);
        if(!count($errors)) {
            switch($vet_schedule_form['action']) {
                case 'n': // new

                    $form->handleRequest($request);

                    if ($form->isValid()) {
                        
                        $vet_schedule->setScheduleDateFrom(new \DateTime($vet_schedule_form['schedule_date_from']));
                        $vet_schedule->setScheduleDateTo(new \DateTime($vet_schedule_form['schedule_date_to']));
                        $em->persist($vet_schedule);
                        $em->flush();

                        $this->get('session')->getFlashBag()->set('success_messages', 'Record Successfully Saved.');

                        $result['redirect'] = 'index';

                    } else {
                        
                        $this->get('session')->getFlashBag()->set('error_messages', 'Something went wrong. Please try again.');
                        $result['redirect'] = 'form with error';
                    }

                    break; 
                case 'u':
                    $form->handleRequest($request);

                    $vet_schedule->setScheduleDateFrom(new \DateTime($vet_schedule_form['schedule_date_from']));
                    $vet_schedule->setScheduleDateTo(new \DateTime($vet_schedule_form['schedule_date_to']));
                    $em->persist($vet_schedule);
                    $em->flush();

                    $this->get('session')->getFlashBag()->set('success_messages', 'Record Successfully Updated.');

                    $result['redirect'] = 'index';
                    break; 
                case 'd': // delete
                    $form->handleRequest($request);


                    if ($form->isValid()) {

                        $vet_schedule->setIsDeleted(true);
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
  
}
