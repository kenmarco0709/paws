<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;

use App\Entity\BillingEntity;
use App\Form\PaymentForm;

use App\Entity\BillingAccessEntity;
use App\Entity\ServiceEntity;
use App\Entity\UserEntity;
use App\Entity\MedicalRecordEntity;
use App\Entity\MedicalRecordServiceEntity;
use App\Entity\BillingPetEntity;
use App\Entity\MedicalRecordItemEntity;
use App\Entity\MedicalRecordLaboratoryEntity;
use App\Entity\InventoryItemEntity; 
use App\Entity\BillingTypeEntity;
use App\Entity\PaymentEntity;


use App\Service\AuthService;


/**
 * @Route("/billing")
 */
class BillingController extends AbstractController
{
    /**
     * @Route("", name="billing_index")
     */
    public function index(Request $request, AuthService $authService)
    {
        if(!$authService->isLoggedIn()) return $authService->redirectToLogin();
        if(!$authService->isUserHasAccesses(array('Billing'))) return $authService->redirectToHome();

            $em = $this->getDoctrine()->getManager();        
            $page_title = ' Billing'; 
      
             return $this->render('Billing/index.html.twig', [ 
                'page_title' => $page_title, 
                'javascripts' => array('plugins/datatables/jquery.dataTables.js','/js/billing/index.js')
            ]
       );
    }

     /**
     * @Route("/details/{id}", name="billing_details")
     */
    public function billingDetails(Request $request, AuthService $authService, $id)
    {
        if(!$authService->isLoggedIn()) return $authService->redirectToLogin();
        if(!$authService->isUserHasAccesses(array('Billing Details'))) return $authService->redirectToHome();

        $em = $this->getDoctrine()->getManager();        
        $page_title = ' Billing Details';
        
        $confimentService = $em->getRepository(ServiceEntity::class)->findOneBy(array('description' => 'Confinement Fee'));
        $consultationService = $em->getRepository(ServiceEntity::class)->findOneBy(array('description' => 'Consultation Fee'));

        return $this->render('Billing/details.html.twig', [ 
            'page_title' => $page_title, 
            'billing' => $em->getRepository(BillingEntity::class)->find(base64_decode($id)),
            'confimentService' =>  $confimentService,
            'consultationService' =>  $consultationService,
            'javascripts' => array('/plugins/datatables/jquery.dataTables.js','/js/billing/details.js')

        ]
       );
    }



    /**
     * @Route("/ajax_list", name="billing_ajax_list")
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
            $result = $this->getDoctrine()->getManager()->getRepository(BillingEntity::class)->ajax_list($get, $this->get('session')->get('userData'));
        }

        return new JsonResponse($result);
    }

      /**
     * @Route("/payment_ajax_list", name="billing_payment_ajax_list")
     */
    public function payment_ajax_list(Request $request, AuthService $authService) {

    
        $get = $request->query->all();

        $result = array(
            "draw" => intval($get['draw']),
            "recordsTotal" => 0,
            "recordsFiltered" => 0,
            "data" => array()
        );

        if($authService->isLoggedIn()) {
            $result = $this->getDoctrine()->getManager()->getRepository(PaymentEntity::class)->payment_ajax_list($get, $this->get('session')->get('userData'));
        }

        return new JsonResponse($result);
    }

/**
 * @Route(
 *      "/{billingId}/payment/form/{action}/{id}",
 *      defaults = {
 *          "action":  "n",
 *          "id": 0
 *      },
 *      requirements = {
 *          "action": "n|u"
 *      },
 *      name = "billing_payment_form"
 * )
 */
public function paymentFormAction($billingId, $action, $id, Request $request, AuthService $authService) {

    if(!$authService->isLoggedIn()) return $authService->redirectToLogin();
    if(!$authService->getUser()->getType() == 'Super Admin') return $authService->redirectToHome();

    $em = $this->getDoctrine()->getManager();

    $payment = $em->getRepository(PaymentEntity::class)->find(base64_decode($id));
    if(!$payment) {
        $payment = new PaymentEntity();
    }

    $formOptions = array('action' => $action , 'billingId' => $billingId);
    $form = $this->createForm(PaymentForm::class, $payment, $formOptions);

    if($request->getMethod() === 'POST') {

        $payment_form = $request->get($form->getName());
        $result = $this->processForm($payment_form, $payment, $form, $request);

        if($result['success']) {
            if($result['redirect'] === 'index') {
                return $this->redirect($this->generateUrl('billing_details', array('id' => $billingId)), 302);
            } else if($result['redirect'] === 'form') {
                return $this->redirect($this->generateUrl('payment_form', array(
                    'billingId'=> $billingId,
                    'action' => 'u',
                    'id' => base64_encode($result['id'])
                )), 302);
            } else if($result['redirect'] === 'form with error') {
                return $this->redirect($this->generateUrl('payment_form'), 302);
            }
        } else {
            $form->submit($payment_form, false);
        }
    }
    $title = ($action === 'n' ? 'New' : 'Update') . ' Payment';

    return $this->render('Billing/payment_form.html.twig', array(
        'title' => $title,
        'page_title' => $title,
        'form' => $form->createView(),
        'action' => $action,
        'id' => $id,
        'billingId' => $billingId,       
        'javascripts' => array('/js/billing/payment_form.js')


    ));
}

private function processForm($payment_form, $payment ,$form, Request $request) {

    $em = $this->getDoctrine()->getManager();

   
    $errors = $em->getRepository(PaymentEntity::class)->validate($payment_form);

    if(!count($errors)) {
       
        $billing = $em->getRepository(BillingEntity::class)->find(base64_decode($payment_form['billing']));
       
        switch($payment_form['action']) {
            case 'n': // new

                $form->handleRequest($request);

                if ($form->isValid()) {
                    
                    $em->persist($payment);
                    $em->flush();
                    

                    if($billing->totalPayment() >= $billing->getAmountDue()){

                        $billing->setStatus('Paid');
                        $billing->getAdmission()->setStatus('Paid');
                    }


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

                    $em->persist($payment);
                    $em->flush();

                    if($billing->totalPayment() >= $billing->getAmountDue()){

                        $billing->setStatus('Paid');
                        $billing->getAdmission()->setStatus('Paid');
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

                    $payment->setIsDeleted(true);
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
