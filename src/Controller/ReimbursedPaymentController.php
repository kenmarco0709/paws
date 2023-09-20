<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Knp\Snappy\Pdf;
use Symfony\Component\HttpFoundation\Response;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

use App\Service\AuthService;

use App\Entity\ReimbursedPaymentEntity;
use App\Entity\ReimbursedPaymentTypeEntity;

use App\Entity\ClientEntity;
use App\Entity\InvoiceEntity;

use App\Form\ReimbursedPaymentForm;

/**
 * @Route("/reimbursed_payment")
 */
class ReimbursedPaymentController extends AbstractController
{

    /**
     * @Route("", name="reimbursed_payment_index")
     */
    public function index(Request $request, AuthService $authService)
    {
        if(!$authService->isLoggedIn()) return $authService->redirectToLogin();
        if(!$authService->isUserHasAccesses(array('Reimbursed Payment'))) return $authService->redirectToHome();
        
       $page_title = ' Reimbursed Payment'; 
       return $this->render('ReimbursedPayment/index.html.twig', [ 
            'page_title' => $page_title, 
            'javascripts' => array('plugins/datatables/jquery.dataTables.js','/js/reimbursed_payment/index.js') ]
       );
    }

        /**
     * @Route("/ajax_list", name="reimbursed_payment_ajax_list")
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

            $data = $this->getDoctrine()->getManager()->getRepository(ReimbursedPaymentEntity::class)->ajax_list($get, $this->get('session')->get('userData'));

            foreach($data['results'] as $row) {

                if(isset($get['clientId'])){
                    $values = array(
                        $row['reimbursed_paymentDate'],
                        "<a href='/invoice/form/u/" . base64_encode($row['invoiceNo']) . "'>".$row['invoiceNo']."</a>",
                        $row['amount'],
                    );
                } else {
                    $values = array(
                        $row['reimbursed_paymentDate'],
                        "<a href='/invoice/form/u/" . base64_encode($row['invoiceNo']) . "'>".$row['invoiceNo']."</a>",
                        $row['client'],
                        $row['amount'],
                    );
                }

              

                $result['data'][] = $values;
            }

            $result['recordsTotal'] = $data['total'];
            $result['recordsFiltered'] = $data['total'];
        }

        return new JsonResponse($result);
    }

    /**
     * @Route("/invoice_ajax_form", name="reimbursed_payment_invoice_ajax_form")
     */
    public function invoice_ajaxForm(Request $request, AuthService $authService): JsonResponse
    {
       
       $result = [ 'success' =>  true, 'msg' => ''];

       if(!$authService->isLoggedIn()) { $result['success'] = false; $result['msg'] = 'Unauthorized access please call a system administrator.';}
       
       $invoiceId = $request->request->get('invoiceId');

       $em = $this->getDoctrine()->getManager();

       $invoice = $em->getRepository(InvoiceEntity::class)->find(base64_decode($invoiceId));
       $formOptions = array('action' => 'n' , 'invoiceId' => $invoiceId);
       $form = $this->createForm(ReimbursedPaymentForm::class, new ReimbursedPaymentEntity(), $formOptions);
    
       $result['html'] = $this->renderView('ReimbursedPayment/invoice_reimbursed_payment_ajax_form.html.twig', [
            'page_title' => 'New Reimbursed Payment',
            'form' => $form->createView(),
            'invoice' => $invoice,
            'javascripts' => array('/js/reimbursed_payment/invoice_reimbursed_payment_form.js')
        ]);

       return new JsonResponse($result);
    }

    /**
     * @Route("/ajax_form_process", name="reimbursed_payment_invoice_ajax_form_process")
     */
    public function ajaxFormProcess(Request $request, AuthService $authService): JsonResponse
    {
       
       $result = [ 'success' =>  true, 'msg' => ''];

       if(!$authService->isLoggedIn()) { $result['success'] = false; $result['msg'] = 'Unauthorized access please call a system administrator.';}

       if($request->getMethod() == "POST"){

         $reimbursed_paymentForm = $request->request->get('reimbursed_payment_form');
         $em = $this->getDoctrine()->getManager();
         $invoice = $em->getRepository(InvoiceEntity::class)->find(base64_decode($reimbursed_paymentForm['invoice']));
         $errors = $em->getRepository(ReimbursedPaymentEntity::class)->validate($reimbursed_paymentForm, $invoice);

          if(!count($errors)){

            $invoice = $em->getRepository(InvoiceEntity::class)->find(base64_decode($reimbursed_paymentForm['invoice']));

             $newReimbursedPayment = new ReimbursedPaymentEntity();
             $newReimbursedPayment->setInvoice($invoice);
             $newReimbursedPayment->setAmount($reimbursed_paymentForm['amount']);
             $em->persist($newReimbursedPayment);
             $em->flush();

             $result['msg'] = 'Reimbursed Payment successfully added to record.';
             $result['totalPayment'] =  $invoice->grandTotal();
             
        
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

}
