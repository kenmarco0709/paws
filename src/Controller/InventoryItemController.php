<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;

use App\Entity\InventoryItemEntity;
use App\Form\InventoryItemForm;
use App\Entity\MedicalRecordItemEntity;

use App\Form\InventoryItemAdjustmentForm;
use App\Form\InventoryItemCompletedOrderForm;

use App\Entity\InventoryItemAdjustmentEntity;
use App\Entity\InventoryItemCompletedOrderEntity;

use App\Service\AuthService;
use App\Service\InventoryService;
use App\Entity\InvoiceInventoryItemEntity;
use App\Entity\ItemEntity;
use App\Entity\InvoiceVoidInventoryItemEntity;

use PhpOffice\PhpSpreadsheet\Reader\Csv;
 
/**
 * @Route("/inventory_item")
 */
class InventoryItemController extends AbstractController
{
    /**
     * @Route("", name="inventory_item_index")
     */
    public function index(Request $request, AuthService $authService)
    {
        if(!$authService->isLoggedIn()) return $authService->redirectToLogin();
        if(!$authService->isUserHasAccesses(array('Inventory Item'))) return $authService->redirectToHome();
        
       $page_title = ' Inventory Item'; 
       return $this->render('InventoryItem/index.html.twig', [ 
            'page_title' => $page_title, 
            'javascripts' => array('plugins/datatables/jquery.dataTables.js','/js/inventory_item/index.js') ]
       );
    }

    /**
     * @Route("/details/{id}", name="inventory_item_details")
     */
    public function details($id, Request $request, AuthService $authService)
    {
        if(!$authService->isLoggedIn()) return $authService->redirectToLogin();
        if(!$authService->isUserHasAccesses(array('Inventory Item Details'))) return $authService->redirectToHome();
        
        $inventoryItem = $this->getDoctrine()->getManager()->getRepository(InventoryItemEntity::class)->find(base64_decode($id));
        $page_title = ' Inventory Item Details'; 
       return $this->render('InventoryItem/details.html.twig', [ 
            'inventoryItem' => $inventoryItem,
            'page_title' => $page_title, 
            'javascripts' => array('/plugins/datatables/jquery.dataTables.js','/js/inventory_item/details.js') ]
       );
    }



    /**
     * @Route("/ajax_list", name="inventory_item_ajax_list")
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

            $userData = $this->get('session')->get('userData');
            $hasDetails = $authService->getUser()->getType() == 'Super Admin' ||  in_array('Inventory Item Details', $userData['accesses']) ? true : false;
            $data = $this->getDoctrine()->getManager()->getRepository(InventoryItemEntity::class)->ajax_list($get, $userData );

            foreach($data['results'] as $row) {

                $id = base64_encode($row['id']);
                $action = '';

                if($hasDetails) {
                    $url = $this->generateUrl('inventory_item_details', array(
                        'id' => $id
                    ));
                   
                    $action = "<a class='action-button-style btn btn-primary' href='$url'>Details</a>";
                }

                $values = array(
                    $row['item'],
                    $row['quantity'],
                    $row['buying_price'],
                    $row['selling_price'],
                    $action,

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
     *      name = "inventory_item_form"
     * )
     */
    public function formAction($action, $id, Request $request, AuthService $authService) {

        if(!$authService->isLoggedIn()) return $authService->redirectToLogin();
        if(!$authService->isUserHasAccesses(array('Inventory Item New'))) return $authService->redirectToHome();

        $em = $this->getDoctrine()->getManager();

        $inventory_item = $em->getRepository(InventoryItemEntity::class)->find(base64_decode($id));
        if(!$inventory_item) {
            $inventory_item = new InventoryItemEntity();
        }

        $formOptions = array('action' => $action, 'branchId' => $authService->getUser()->getBranch()->getIdEncoded());
        $form = $this->createForm(InventoryItemForm::class, $inventory_item, $formOptions);

        if($request->getMethod() === 'POST') {

            $inventory_item_form = $request->get($form->getName());
            $result = $this->processForm($inventory_item_form, $inventory_item, $form, $request);

            if($result['success']) {
                if($result['redirect'] === 'index') {
                    return $this->redirect($this->generateUrl('inventory_item_index'), 302);
                } else if($result['redirect'] === 'form') {
                    return $this->redirect($this->generateUrl('inventory_item_form', array(
                        'action' => 'u',
                        'id' => base64_encode($result['id'])
                    )), 302);
                } else if($result['redirect'] === 'form with error') {
                    return $this->redirect($this->generateUrl('inventory_item_form'), 302);
                }
            } else {
                $form->submit($inventory_item_form, false);
            }
        }

        $title = ($action === 'n' ? 'New' : 'Update') . ' Inventory Item';

        return $this->render('InventoryItem/form.html.twig', array(
            'title' => $title,
            'page_title' => $title,
            'form' => $form->createView(),
            'action' => $action,
            'id' => $id,
            'javascripts' => ['/js/inventory_item/form.js'] 
        ));
    }

    private function processForm($inventory_item_form, $inventory_item ,$form, Request $request) {

        $em = $this->getDoctrine()->getManager();

        $errors = $em->getRepository(InventoryItemEntity::class)->validate($inventory_item_form);

        if(!count($errors)) {

            switch($inventory_item_form['action']) {
                case 'n': // new

                    $form->handleRequest($request);

                    if ($form->isValid()) {
                        
                        $inventory_item->setQuantity($inventory_item_form['beginning_quantity']);
                        $em->persist($inventory_item);
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

                        $em->persist($inventory_item);

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

                        $inventory_item->setIsDeleted(true);
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
     * @Route("/autocomplete", name="inventory_item_autocomplete")
     */
    public function autocompleteAction(Request $request) {

        return new JsonResponse(array(
            'query' => 'inventory_items',
            'suggestions' => $this->getDoctrine()->getManager()->getRepository(InventoryItemEntity::class)->autocomplete_suggestions($request->query->all(), $this->get('session')->get('userData'))
        ));
    }

        /**
     * @Route(
     *      "/adjusment_form/{inventory_item_id}/{action}/{id}",
     *      defaults = {
     *          "action":  "n",
     *          "id": 0
     *      },
     *      requirements = {
     *          "action": "n|u"
     *      },
     *      name = "inventory_item_adjustment_form"
     * )
     */
    public function itemAdjustmentFormAction($inventory_item_id, $action, $id, Request $request, AuthService $authService, InventoryService $inventoryService) {

        if(!$authService->isLoggedIn()) return $authService->redirectToLogin();
        if(!$authService->isUserHasAccesses(array('Inventory Item Details Adjustment New'))) return $authService->redirectToHome();


        $em = $this->getDoctrine()->getManager();

        $inventory_item_adjustment = $em->getRepository(InventoryItemAdjustmentEntity::class)->find(base64_decode($id));
        if(!$inventory_item_adjustment) {
            $inventory_item_adjustment = new InventoryItemAdjustmentEntity();
        }

        $formOptions = array('action' => $action, 'inventoryItemId' => $inventory_item_id);
        $form = $this->createForm(InventoryItemAdjustmentForm::class, $inventory_item_adjustment, $formOptions);

        if($request->getMethod() === 'POST') {

            $inventory_item_adjustment_form = $request->get($form->getName());
            $result = $this->itemAdjustmentProcessForm($inventory_item_adjustment_form, $inventory_item_adjustment, $form, $request, $inventoryService);

            if($result['success']) {
                if($result['redirect'] === 'index') {
                    return $this->redirect($this->generateUrl('inventory_item_details', array('id' => $inventory_item_id)), 302);
                } else if($result['redirect'] === 'form') {
                    return $this->redirect($this->generateUrl('inventory_item_adjustment_form', array(
                        'inventory_item_id' => $inventory_item_id,
                        'action' => 'u',
                        'id' => base64_encode($result['id'])
                    )), 302);
                } else if($result['redirect'] === 'form with error') {
                    return $this->redirect($this->generateUrl('inventory_item_adjustment_form', array('inventory_item_id' => $inventory_item_id)), 302);
                }
            } else {
                $form->submit($inventory_item_adjustment_form, false);
            }
        }

        $title = ($action === 'n' ? 'New' : 'Update') . ' Inventory Item Adjustment';

        return $this->render('InventoryItem/adjustment_form.html.twig', array(
            'title' => $title,
            'page_title' => $title,
            'form' => $form->createView(),
            'action' => $action,
            'id' => $id,
            'inventory_item_id' => $inventory_item_id
        ));
    }

    private function itemAdjustmentProcessForm($inventory_item_adjustment_form, $inventory_item_adjustment ,$form, Request $request, $processInventory) {

        $em = $this->getDoctrine()->getManager();

        $errors = $em->getRepository(InventoryItemAdjustmentEntity::class)->validate($inventory_item_adjustment_form);

        if(!count($errors)) {

            switch($inventory_item_adjustment_form['action']) {
                case 'n': // new

                    $form->handleRequest($request);

                    if ($form->isValid()) {
                        
                        $em->persist($inventory_item_adjustment);
                        $em->flush();
                        $item = $em->getRepository(ItemEntity::class)->findOneBy(array('isDeleted' => false, 'description' => $data[0]));

                        $processInventory->processInventory($inventory_item_adjustment);

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

                        $em->persist($inventory_item_adjustment);
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

                        $inventory_item_adjustment->setIsDeleted(true);
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
     *      "/completed_order_form/{inventory_item_id}/{action}/{id}",
     *      defaults = {
     *          "action":  "n",
     *          "id": 0
     *      },
     *      requirements = {
     *          "action": "n|u"
     *      },
     *      name = "inventory_item_completed_order_form"
     * )
     */
    public function completedOrderFormAction($inventory_item_id, $action, $id, Request $request, AuthService $authService, InventoryService $inventoryService) {

        if(!$authService->isLoggedIn()) return $authService->redirectToLogin();
        if(!$authService->isUserHasAccesses(array('Inventory Item Details Completed Order New'))) return $authService->redirectToHome();


        $em = $this->getDoctrine()->getManager();

        $inventory_item_completed_order = $em->getRepository(InventoryItemCompletedOrderEntity::class)->find(base64_decode($id));
        if(!$inventory_item_completed_order) {
            $inventory_item_completed_order = new InventoryItemCompletedOrderEntity();
        }

        $formOptions = array('action' => $action, 'inventoryItemId' => $inventory_item_id);
        $form = $this->createForm(InventoryItemCompletedOrderForm::class, $inventory_item_completed_order, $formOptions);

        if($request->getMethod() === 'POST') {

            $inventory_item_completed_order_form = $request->get($form->getName());
            $result = $this->itemCompletedOrderProcessForm($inventory_item_completed_order_form, $inventory_item_completed_order, $form, $request, $inventoryService);

            if($result['success']) {
                if($result['redirect'] === 'index') {
                    return $this->redirect($this->generateUrl('inventory_item_details', array('id' => $inventory_item_id)), 302);
                } else if($result['redirect'] === 'form') {
                    return $this->redirect($this->generateUrl('inventory_item_completed_order_form', array(
                        'inventory_item_id' => $inventory_item_id,
                        'action' => 'u',
                        'id' => base64_encode($result['id'])
                    )), 302);
                } else if($result['redirect'] === 'form with error') {
                    return $this->redirect($this->generateUrl('inventory_item_completed_order_form', array('inventory_item_id' => $inventory_item_id)), 302);
                }
            } else {
                $form->submit($inventory_item_completed_order_form, false);
            }
        }

        $title = ($action === 'n' ? 'New' : 'Update') . ' Inventory Item Completed Order';

        return $this->render('InventoryItem/completed_order_form.html.twig', array(
            'title' => $title,
            'page_title' => $title,
            'form' => $form->createView(),
            'action' => $action,
            'id' => $id,
            'inventory_item_id' => $inventory_item_id,
            'javascripts' => array('/js/inventory_item/completed_order_form.js') 
        ));
    }

    private function itemCompletedOrderProcessForm($inventory_item_completed_order_form, $inventory_item_completed_order ,$form, Request $request, $processInventory) {

        $em = $this->getDoctrine()->getManager();

        $errors = $em->getRepository(InventoryItemCompletedOrderEntity::class)->validate($inventory_item_completed_order_form);

        if(!count($errors)) {

            switch($inventory_item_completed_order_form['action']) {
                case 'n': // new

                    $form->handleRequest($request);

                    if ($form->isValid()) {
                        
                        $em->persist($inventory_item_completed_order);
                        $em->flush();

                        $inventoryItem = $em->getRepository(InventoryItemEntity::class)->find(base64_decode($inventory_item_completed_order_form['inventory_item']));
                        $inventoryItem->setSupplier($inventory_item_completed_order->getSupplier());
                        $em->flush();

                        $processInventory->processInventory($inventory_item_completed_order);

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

                        $em->persist($inventory_item_completed_order);
                        $em->flush();

                        $inventoryItem = $em->getRepository(InventoryItemEntity::class)->find(base64_decode($inventory_item_completed_order_form['inventory_item']));
                        $inventoryItem->setSupplier($inventory_item_completed_order->getSupplier());
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

                        $inventory_item_completed_order->setIsDeleted(true);
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
     * @Route("/item_completed_order_ajax_list", name="inventory_item_completed_order_ajax_list")
     */
    public function item_completed_order_ajax_list(Request $request, AuthService $authService) {

        $get = $request->query->all();

        $result = array(
            "draw" => intval($get['draw']),
            "recordsTotal" => 0,
            "recordsFiltered" => 0,
            "data" => array()
        );

        if($authService->isLoggedIn()) {

            $userData = $this->get('session')->get('userData');
            $data = $this->getDoctrine()->getManager()->getRepository(InventoryItemCompletedOrderEntity::class)->ajax_list($get, $userData );

            foreach($data['results'] as $row) {

               $values = array(
                    $row['orderDate'],
                    $row['supplier'],
                    $row['quantity'],
                    $row['selling_price'],
                    $row['buying_price'],
                    $row['remarks']

                );

                $result['data'][] = $values;
            }

            $result['recordsTotal'] = $data['total'];
            $result['recordsFiltered'] = $data['total'];
        }

        return new JsonResponse($result);
    }

    /**
     * @Route("/item_adjustement_ajax_list", name="inventory_item_adjustement_ajax_list")
     */
    public function item_adjustement_ajax_list(Request $request, AuthService $authService) {

        $get = $request->query->all();

        $result = array(
            "draw" => intval($get['draw']),
            "recordsTotal" => 0,
            "recordsFiltered" => 0,
            "data" => array()
        );

        if($authService->isLoggedIn()) {

            $userData = $this->get('session')->get('userData');
            $data = $this->getDoctrine()->getManager()->getRepository(InventoryItemAdjustmentEntity::class)->ajax_list($get, $userData );

            foreach($data['results'] as $row) {

               $values = array(
                    $row['adjustmentDate'],
                    $row['adjustment_type'],
                    $row['lowQuantity'],
                    $row['quantity'],
                    $row['selling_price'],
                    $row['buying_price'],
                    $row['remarks']

                );

                $result['data'][] = $values;
            }

            $result['recordsTotal'] = $data['total'];
            $result['recordsFiltered'] = $data['total'];
        }

        return new JsonResponse($result);
    }

    /**
     * @Route("/medical_record_item", name="inventory_item_medical_record_item_ajax_list")
     */
    public function medical_record_item_ajax_list(Request $request, AuthService $authService) {

        $get = $request->query->all();

        $result = array(
            "draw" => intval($get['draw']),
            "recordsTotal" => 0,
            "recordsFiltered" => 0,
            "data" => array()
        );

        if($authService->isLoggedIn()) {

            $userData = $this->get('session')->get('userData');
            $data = $this->getDoctrine()->getManager()->getRepository(MedicalRecordItemEntity::class)->inventory_item_ajax_list($get, $userData );

            foreach($data['results'] as $row) {

               $values = array(
                    $row['date'],
                    "Deduct",
                    $row['quantity']
                );

                $result['data'][] = $values;
            }

            $result['recordsTotal'] = $data['total'];
            $result['recordsFiltered'] = $data['total'];
        }

        return new JsonResponse($result);
    }

        /**
     * @Route("/invoice_inventory_item", name="inventory_item_invoice_inventory_item_ajax_list")
     */
    public function invoice_inventory_item_ajax_list(Request $request, AuthService $authService) {

        $get = $request->query->all();

        $result = array(
            "draw" => intval($get['draw']),
            "recordsTotal" => 0,
            "recordsFiltered" => 0,
            "data" => array()
        );

        if($authService->isLoggedIn()) {

            $userData = $this->get('session')->get('userData');
            $data = $this->getDoctrine()->getManager()->getRepository(InvoiceInventoryItemEntity::class)->inventory_item_ajax_list($get, $userData );

            foreach($data['results'] as $row) {

               $values = array(
                    $row['date'],
                    "Deduct",
                    $row['quantity']
                );

                $result['data'][] = $values;
            }

            $result['recordsTotal'] = $data['total'];
            $result['recordsFiltered'] = $data['total'];
        }

        return new JsonResponse($result);
    }

            /**
     * @Route("/invoice_void_inventory_item", name="inventory_item_invoice_void_inventory_item_ajax_list")
     */
    public function invoice_void_inventory_item_ajax_list(Request $request, AuthService $authService) {

        $get = $request->query->all();

        $result = array(
            "draw" => intval($get['draw']),
            "recordsTotal" => 0,
            "recordsFiltered" => 0,
            "data" => array()
        );

        if($authService->isLoggedIn()) {

            $userData = $this->get('session')->get('userData');
            $data = $this->getDoctrine()->getManager()->getRepository(InvoiceVoidInventoryItemEntity::class)->inventory_item_ajax_list($get, $userData );

            foreach($data['results'] as $row) {

               $values = array(
                    $row['date'],
                    "Add",
                    $row['quantity']
                );

                $result['data'][] = $values;
            }

            $result['recordsTotal'] = $data['total'];
            $result['recordsFiltered'] = $data['total'];
        }

        return new JsonResponse($result);
    }

     /**
     * @Route("/import", name="inventory_item_import")
     */
    public function import(Request $request, AuthService $authService)
    {
        if(!$authService->isLoggedIn()) return $authService->redirectToLogin();
        if(!$authService->isUserHasAccesses(array('Inventory Item Import'))) return $authService->redirectToHome();
        
        if($request->getMethod() == 'POST'){
            $file_mimes = array('text/x-comma-separated-values', 'text/comma-separated-values', 'application/octet-stream', 'application/vnd.ms-excel', 'application/x-csv', 'text/x-csv', 'text/csv', 'application/csv', 'application/excel', 'application/vnd.msexcel', 'text/plain', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            
            if(isset($_FILES['items']['name']) && in_array($_FILES['items']['type'], $file_mimes)) {

                $reader = new Csv();
                $spreadsheet = $reader->load($_FILES['items']['tmp_name']);
 
                $sheetData = $spreadsheet->getActiveSheet()->toArray();
                array_shift($sheetData);

                $em = $this->getDoctrine()->getManager();

                foreach($sheetData as $data){

                    $item = $em->getRepository(ItemEntity::class)->findOneBy(array('isDeleted' => false, 'description' => $data[0]));
                
                    if(!$item){

                        $item = new ItemEntity();
                        $item->setCode($data[0]);
                        $item->setDescription($data[0]);
                        $em->persist($item);
                        $em->flush();
                    }

                    $itemInventory = $em->getRepository(InventoryItemEntity::class)->findOneBy(array('isDeleted' => 0, 'item' => $item->getId(), 'branch' =>  $authService->getUser()->getBranch()));

                    if(!$itemInventory){
                            $itemInventory = new InventoryItemEntity();
                            $itemInventory->setBranch($authService->getUser()->getBranch());
                            $itemInventory->setItem($item);
                         
                    }

                    $itemInventory->setQuantity(!is_null($data[1]) ? $data[1] : 0.00);
                    $itemInventory->setBuyingPrice(!is_null($data[2]) ? $data[2] : 0.00);
                    $itemInventory->setSellingPrice(!is_null($data[3]) ? $data[3] : 0.00);
                    $em->persist($itemInventory);
                    $em->flush();
                }

                $this->get('session')->getFlashBag()->set('success_messages', 'Items successfully import.');

            } else {

                $this->get('session')->getFlashBag()->set('error_messages', 'Please put a valid CSV file.');

            }

        } else {

            $this->get('session')->getFlashBag()->set('error_messages', 'Unauthorized request please call a system administrator.');

        }


       return $this->redirect($this->generateUrl('inventory_item_index'),302);
    }


    

}
