<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;

use App\Entity\ItemCategoryEntity;

use App\Form\ItemCategoryForm;


use App\Service\AuthService;

use PhpOffice\PhpSpreadsheet\Reader\Csv;


/**
 * @Route("/item_category")
 */
class ItemCategoryController extends AbstractController
{
    /**
     * @Route("", name="item_category_index")
     */
    public function index(Request $request, AuthService $authService)
    {
        if(!$authService->isLoggedIn()) return $authService->redirectToLogin();
        if(!$authService->isUserHasAccesses(array('CMS Item Category'))) return $authService->redirectToHome();
        
        
       $page_title = ' Item Category'; 
       return $this->render('ItemCategory/index.html.twig', [ 
            'page_title' => $page_title, 
            'javascripts' => array('plugins/datatables/jquery.dataTables.js','/js/item_category/index.js') ]
       );
    }


    /**
     * @Route("/ajax_list", name="item_category_ajax_list")
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

            $hasUpdate = $authService->getUser()->getType() == 'Super Admin'  || $authService->isUserHasAccesses(array('CMS Item Category')) ? true : false;
            $data = $this->getDoctrine()->getManager()->getRepository(ItemCategoryEntity::class)->ajax_list($get, $this->get('session')->get('userData'));

            foreach($data['results'] as $row) {

                $id = base64_encode($row['id']);
                $action = '';

                if($hasUpdate) {
                    $url = $this->generateUrl('item_category_form', array(
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
     *      name = "item_category_form"
     * )
     */
    public function formAction($action, $id, Request $request, AuthService $authService) {

        if(!$authService->isLoggedIn()) return $authService->redirectToLogin();
        if(!$authService->isUserHasAccesses(array('Item Category New','Item Category Update'))) return $authService->redirectToHome();

        $em = $this->getDoctrine()->getManager();

        $item_category = $em->getRepository(ItemCategoryEntity::class)->find(base64_decode($id));
        if(!$item_category) {
            $item_category = new ItemCategoryEntity();
        }

        $formOptions = array('action' => $action , 'branchId' => $authService->getUser()->getBranch()->getIdEncoded());
        $form = $this->createForm(ItemCategoryForm::class, $item_category, $formOptions);

        if($request->getMethod() === 'POST') {

            $item_category_form = $request->get($form->getName());
            $result = $this->processForm($item_category_form, $item_category, $form, $request);

            if($result['success']) {
                if($result['redirect'] === 'index') {
                    return $this->redirect($this->generateUrl('item_category_index'), 302);
                } else if($result['redirect'] === 'form') {
                    return $this->redirect($this->generateUrl('item_category_form', array(
                        'action' => 'u',
                        'id' => base64_encode($result['id'])
                    )), 302);
                } else if($result['redirect'] === 'form with error') {
                    return $this->redirect($this->generateUrl('item_category_form'), 302);
                }
            } else {
                $form->submit($item_category_form, false);
            }
        }
        $title = ($action === 'n' ? 'New' : 'Update') . ' ItemCategory';

        return $this->render('ItemCategory/form.html.twig', array(
            'javascripts' => array('/js/item_category/form.js'),

            'title' => $title,
            'page_title' => $title,
            'form' => $form->createView(),
            'action' => $action,
            'id' => $id

        ));
    }

    private function processForm($item_category_form, $item_category ,$form, Request $request) {

        $em = $this->getDoctrine()->getManager();
        $errors = $em->getRepository(ItemCategoryEntity::class)->validate($item_category_form);

        if(!count($errors)) {

            switch($item_category_form['action']) {
                case 'n': // new

                    $form->handleRequest($request);

                    if ($form->isValid()) {
                        
                        $em->persist($item_category);
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

                        $em->persist($item_category);

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

                        $item_category->setIsDeleted(true);
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
     * @Route("/autocomplete", name="item_category_autocomplete")
     */
    public function autocompleteAction(Request $request) {

        return new JsonResponse(array(
            'query' => 'item_categorys',
            'suggestions' => $this->getDoctrine()->getManager()->getRepository(ItemCategoryEntity::class)->autocomplete_suggestions($request->query->all(), $this->get('session')->get('userData'))
        ));
    }
}
