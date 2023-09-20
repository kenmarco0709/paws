<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;

use App\Entity\BreedEntity;
use App\Form\BreedForm;

use App\Entity\BreedAccessEntity;
use App\Service\AuthService;

/**
 * @Route("/breed")
 */
class BreedController extends AbstractController
{
    /**
     * @Route("", name="breed_index")
     */
    public function index(Request $request, AuthService $authService)
    {
        if(!$authService->isLoggedIn()) return $authService->redirectToLogin();
        if(!$authService->isUserHasAccesses(array('Breed'))) return $authService->redirectToHome();
        
       $page_title = ' Breed'; 
       return $this->render('Breed/index.html.twig', [ 
            'page_title' => $page_title, 
            'javascripts' => array('plugins/datatables/jquery.dataTables.js','/js/breed/index.js') ]
       );
    }

    /**
     * @Route("/ajax_list", name="breed_ajax_list")
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

            $hasUpdate = $authService->getUser()->getType() == 'Super Admin' ? true : false;
            $data = $this->getDoctrine()->getManager()->getRepository(BreedEntity::class)->ajax_list($get, $this->get('session')->get('userData'));

            foreach($data['results'] as $row) {

                $id = base64_encode($row['id']);
                $action = '';

                if($hasUpdate) {
                    $url = $this->generateUrl('breed_form', array(
                        'action' => 'u',
                        'id' => $id
                    ));
                   
                    $action = "<a class='action-button-style btn btn-primary' href='$url'>Update</a>";
                }

                $values = array(
                    $row['code'],
                    $row['description'],
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
     *      name = "breed_form"
     * )
     */
    public function formAction($action, $id, Request $request, AuthService $authService) {

        if(!$authService->isLoggedIn()) return $authService->redirectToLogin();
        if(!$authService->getUser()->getType() == 'Super Admin') return $authService->redirectToHome();

        $em = $this->getDoctrine()->getManager();

        $breed = $em->getRepository(BreedEntity::class)->find(base64_decode($id));
        if(!$breed) {
            $breed = new BreedEntity();
        }

        $formOptions = array('action' => $action);
        $form = $this->createForm(BreedForm::class, $breed, $formOptions);

        if($request->getMethod() === 'POST') {

            $breed_form = $request->get($form->getName());
            $result = $this->processForm($breed_form, $breed, $form, $request);

            if($result['success']) {
                if($result['redirect'] === 'index') {
                    return $this->redirect($this->generateUrl('breed_index'), 302);
                } else if($result['redirect'] === 'form') {
                    return $this->redirect($this->generateUrl('breed_form', array(
                        'action' => 'u',
                        'id' => base64_encode($result['id'])
                    )), 302);
                } else if($result['redirect'] === 'form with error') {
                    return $this->redirect($this->generateUrl('breed_form'), 302);
                }
            } else {
                $form->submit($breed_form, false);
            }
        }

        $title = ($action === 'n' ? 'New' : 'Update') . ' Breed';

        return $this->render('Breed/form.html.twig', array(
            'title' => $title,
            'page_title' => $title,
            'form' => $form->createView(),
            'action' => $action,
            'id' => $id
        ));
    }

    private function processForm($breed_form, $breed ,$form, Request $request) {

        $em = $this->getDoctrine()->getManager();

        $errors = $em->getRepository(BreedEntity::class)->validate($breed_form);

        if(!count($errors)) {

            switch($breed_form['action']) {
                case 'n': // new

                    $form->handleRequest($request);

                    if ($form->isValid()) {
                        
                        $em->persist($breed);
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

                        $em->persist($breed);

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

                        $breed->setIsDeleted(true);
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
     * @Route("/autocomplete", name="breed_autocomplete")
     */
    public function autocompleteAction(Request $request) {

        return new JsonResponse(array(
            'query' => 'breeds',
            'suggestions' => $this->getDoctrine()->getManager()->getRepository(BreedEntity::class)->autocomplete_suggestions($request->query->all(), $this->get('session')->get('userData'))
        ));
    }


    

}
