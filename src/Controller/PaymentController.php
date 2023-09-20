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
use App\Entity\BranchEntity;
use App\Entity\BreedEntity;
use App\Entity\PaymentEntity;
use App\Entity\PaymentTypeEntity;

use App\Entity\ClientEntity;
use App\Entity\InvoiceEntity;

use App\Form\PaymentForm;

/**
 * @Route("/payment")
 */
class PaymentController extends AbstractController
{

    /**
     * @Route("", name="payment_index")
     */
    public function index(Request $request, AuthService $authService)
    {
        if(!$authService->isLoggedIn()) return $authService->redirectToLogin();
        if(!$authService->isUserHasAccesses(array('Payment'))) return $authService->redirectToHome();
        
       $page_title = ' Payment'; 
       return $this->render('Payment/index.html.twig', [ 
            'page_title' => $page_title, 
            'javascripts' => array('plugins/datatables/jquery.dataTables.js','/js/payment/index.js') ]
       );
    }

        /**
     * @Route("/ajax_list", name="payment_ajax_list")
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

            $data = $this->getDoctrine()->getManager()->getRepository(PaymentEntity::class)->ajax_list($get, $this->get('session')->get('userData'));

            foreach($data['results'] as $row) {

                if(isset($get['clientId'])){
                    $values = array(
                        $row['paymentDate'],
                        "<a href='/invoice/form/u/" . base64_encode($row['invoiceNo']) . "'>".$row['invoiceNo']."</a>",
                        $row['paymentType'],
                        $row['amount'],
                        $row['isDeposit'] ? 'Yes' : 'No'
                    );
                } else {
                    $values = array(
                        $row['paymentDate'],
                        "<a href='/invoice/form/u/" . base64_encode($row['invoiceNo']) . "'>".$row['invoiceNo']."</a>",
                        $row['client'],
                        $row['paymentType'],
                        $row['amount'],
                        $row['isDeposit'] ? 'Yes' : 'No'
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
     * @Route("/invoice_ajax_form", name="payment_invoice_ajax_form")
     */
    public function invoice_ajaxForm(Request $request, AuthService $authService): JsonResponse
    {
       
       $result = [ 'success' =>  true, 'msg' => ''];

       if(!$authService->isLoggedIn()) { $result['success'] = false; $result['msg'] = 'Unauthorized access please call a system administrator.';}
       
       $invoiceId = $request->request->get('invoiceId');

       $em = $this->getDoctrine()->getManager();

       $invoice = $em->getRepository(InvoiceEntity::class)->find(base64_decode($invoiceId));
       $formOptions = array('action' => 'n' , 'invoiceId' => $invoiceId);
       $form = $this->createForm(PaymentForm::class, new PaymentEntity(), $formOptions);
    
       $result['html'] = $this->renderView('Payment/invoice_payment_ajax_form.html.twig', [
            'page_title' => 'New Payment',
            'form' => $form->createView(),
            'invoice' => $invoice,
            'javascripts' => array('/js/payment/invoice_payment_form.js')
        ]);

       return new JsonResponse($result);
    }

    /**
     * @Route("/ajax_form_process", name="payment_invoice_ajax_form_process")
     */
    public function ajaxFormProcess(Request $request, AuthService $authService): JsonResponse
    {
       
       $result = [ 'success' =>  true, 'msg' => ''];

       if(!$authService->isLoggedIn()) { $result['success'] = false; $result['msg'] = 'Unauthorized access please call a system administrator.';}

       if($request->getMethod() == "POST"){
         $paymentForm = $request->request->get('payment_form');
         
         $em = $this->getDoctrine()->getManager();
         $errors = $em->getRepository(PaymentEntity::class)->validate($paymentForm);

         if(!count($errors)){

            $invoice = $em->getRepository(InvoiceEntity::class)->find(base64_decode($paymentForm['invoice']));

             $newPayment = new PaymentEntity();
             $newPayment->setInvoice($invoice);
             $newPayment->setPaymentType($em->getReference(PaymentTypeEntity::class, $paymentForm['payment_type']));
             $newPayment->setReferenceNo($paymentForm['reference_no']);
             $newPayment->setAmount($paymentForm['amount']);

             if(isset($paymentForm['is_deposit'])){
                $newPayment->setIsDeposit(true);
             }
             $newPayment->setPaymentDate(new \DateTime($paymentForm['payment_date']));


             $em->persist($newPayment);
             $em->flush();

             $invoice->setAmountDue($invoice->getAmountDue() - $newPayment->getAmount());
             $em->flush();

             if($invoice->getAmountDue() <= 0 ){
                
                if($invoice->getAdmission()){
                    
                    $admission = $invoice->getAdmission();

                    if($admission->getAdmissionType()->getDescription() == 'Confinement' && $admission->getDischargedDate() == null){
                       
                        $admission->setStatus('Ongoing Confinement');
                        $invoice->setStatus('Paid Partial Payment');

                    } else {

                        $admission->setStatus('Paid');
                    }
                } else {
                    $invoice->setStatus('Paid Payment');

                }

             } else {

                $invoice->setStatus('Pending Payment');
             }

             $em->flush();

             $result['msg'] = 'Payment successfully added to record.';
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

    /**
     * @Route("/report", name = "payment_report")
     */
    public function reportAction(AuthService $authService){

        if(!$authService->isLoggedIn()) return $authService->redirectToLogin();
        if(!$authService->isUserHasAccesses(array('Report Payment'))) return $authService->redirectToHome();
        
       $page_title = ' Report Payment'; 
       return $this->render('Payment/report.html.twig', [ 
            'page_title' => $page_title
       ]);
    }

       /**
     * @Route("/report/pdf", name = "payment_report_pdf")
     */
    public function reportPdf(Request $request, AuthService $authService, Pdf $pdf){

        ini_set('memory_limit', '2048M');
        $dateRange = $request->query->all();
        $startDate = isset($dateRange['start_date']) ? $dateRange['start_date'] : NULL; 
        $endDate = isset($dateRange['end_date']) ? $dateRange['end_date'] : NULL; 


        $paymentReportTransactions  = $this->getDoctrine()->getManager()->getRepository(PaymentEntity::class)->report($startDate, $endDate, $this->get('session')->get('userData'));
        $options = [
            'orientation' => 'Landscape',
            'print-media-type' =>  True,
            'zoom' => .7
        ];


         $newContent = $this->renderView('Payment/report.wkpdf.twig', array(
            'startDate' => $startDate,
            'endDate' => $endDate,
            'paymentReportTransactions' => $paymentReportTransactions
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
            'Content-Disposition'   => 'attachment; filename="'.  'payment-report' .'-' . date('m/d/Y') . '.pdf"'
        ));
    }

       /**
     * @Route("/report/csv", name = "payment_report_csv")
     */
    public function reportCsv(Request $request, AuthService $authService){

        ini_set('memory_limit', '2048M');
        set_time_limit(0);
        $dateRange = $request->query->all();
        $startDate = isset($dateRange['start_date']) ? $dateRange['start_date'] : NULL; 
        $endDate = isset($dateRange['end_date']) ? $dateRange['end_date'] : NULL; 

        $em = $this->getDoctrine()->getManager();
        $paymentReportTransactions  = $this->getDoctrine()->getManager()->getRepository(PaymentEntity::class)->report($startDate, $endDate, $this->get('session')->get('userData'));

        $columnRange = range('A', 'Z');
        $cellsData = array(
            array('cell' => 'A1', 'data' => 'Invoice #'),
            array('cell' => 'B1', 'data' => 'Client'),
            array('cell' => 'C1', 'data' => 'Date'),
            array('cell' => 'D1', 'data' => 'Payment Type'),
            array('cell' => 'E1', 'data' => 'Amount')
        );
        $rowCtr = 1;
        $totalCount = 0;

        $totalAmount = 0;
        $paymentTypes = [];
        foreach($paymentReportTransactions as $paymentReportTransaction) {
            $totalAmount = $totalAmount + $paymentReportTransaction['amount'];
            $rowCtr++;
            $totalCount++;
            if(!isset($paymentTypes[$paymentReportTransaction['paymentType']])){
                $paymentTypes[$paymentReportTransaction['paymentType']] = array('amount' =>  $paymentReportTransaction['amount']);
            } else {
                $paymentTypes[$paymentReportTransaction['paymentType']]['amount'] = $paymentReportTransaction['amount'] + $paymentTypes[$paymentReportTransaction['paymentType']]['amount'];
            }

            $cellsData[] = array('cell' => "A$rowCtr", 'data' => $paymentReportTransaction['invoiceId']);
            $cellsData[] = array('cell' => "B$rowCtr", 'data' => $paymentReportTransaction['client']);
            $cellsData[] = array('cell' => "C$rowCtr", 'data' => $paymentReportTransaction['paymentDate']);
            $cellsData[] = array('cell' => "D$rowCtr", 'data' => $paymentReportTransaction['paymentType']);
            $cellsData[] = array('cell' => "E$rowCtr", 'data' => $paymentReportTransaction['amount']);
        }
        $rowCtr++;
        $cellsData[] = array('cell' => "D$rowCtr", 'data' => 'Total: ');
        $cellsData[] = array('cell' => "E$rowCtr", 'data' => $totalAmount);
        $rowCtr++;
        $rowCtr++;
        foreach($paymentTypes as $k => $paymentType){
            $rowCtr++;

            $cellsData[] = array('cell' => "A$rowCtr", 'data' => $k);
            $cellsData[] = array('cell' => "C$rowCtr", 'data' => $paymentType['amount']);
        }
        $rowCtr++;
        $cellsData[] = array('cell' => "B$rowCtr", 'data' => 'Total: ');
        $cellsData[] = array('cell' => "C$rowCtr", 'data' => $totalAmount);

        $rowCtr++;
        $cellsData[] = array('cell' => "A$rowCtr", 'data' => 'Total Records: ' . $totalCount);


        $page_title = 'Payment Report';
        return $this->export_to_excel($columnRange, $cellsData, $page_title);
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

       /**
     * @Route("/ajax_get_ctr", name="payment_ajax_get_ctr")
     */
    public function ajaxGetCtr(Request $request, AuthService $authService): JsonResponse
    {
       
       $result = [ 'success' =>  true, 'msg' => ''];

       if(!$authService->isLoggedIn()) { $result['success'] = false; $result['msg'] = 'Unauthorized access please call a system administrator.';}
       
       $formOptions = array('action' => 'n' , 'branchId' => $authService->getUser()->getBranch()->getIdEncoded());
       $result['ctr'] = $this->getDoctrine()->getManager()->getRepository(PaymentEntity::class)->getCtr($request->request->get('type'), $this->get('session')->get('userData'));
       
       return new JsonResponse($result);
    }

    /**
     * @Route("/ajax_get_yearly_analytics", name="payment_ajax_get_yearly_analytics")
     */
    public function ajaxGetYearlyAnalytics(Request $request, AuthService $authService): JsonResponse
    {
       
       $result = [ 'success' =>  true, 'msg' => ''];

       if(!$authService->isLoggedIn()) { $result['success'] = false; $result['msg'] = 'Unauthorized access please call a system administrator.';}
       
       $formOptions = array('action' => 'n' , 'branchId' => $authService->getUser()->getBranch()->getIdEncoded());
       $results  = $this->getDoctrine()->getManager()->getRepository(PaymentEntity::class)->getYearlyAnalytics($this->get('session')->get('userData'));

       
       $days = [];
       $stats = [];

       foreach($results as $stat){
        
           array_push($days, $stat['yearMonth']);
           array_push($stats, $stat['amount']);
       }

       $result['days'] = $days;
       $result['stats'] = $stats;
       $result['success'] = true;
       return new JsonResponse(json_encode($result));
    }

    /**
     * @Route("/ajax_get_monthly_analytics", name="payment_ajax_get_monthly_analytics")
     */
    public function ajaxGetMonthlyAnalytics(Request $request, AuthService $authService): JsonResponse
    {
       
       $result = [ 'success' =>  true, 'msg' => ''];

       if(!$authService->isLoggedIn()) { $result['success'] = false; $result['msg'] = 'Unauthorized access please call a system administrator.';}
       
       $formOptions = array('action' => 'n' , 'branchId' => $authService->getUser()->getBranch()->getIdEncoded());
       $results  = $this->getDoctrine()->getManager()->getRepository(PaymentEntity::class)->getMonthlyAnalytics($this->get('session')->get('userData'));

       
       $days = [];
       $stats = [];

       foreach($results as $stat){
        
           array_push($days, $stat['dayMonth']);
           array_push($stats, $stat['amount']);
       }

       $result['days'] = $days;
       $result['stats'] = $stats;
       $result['success'] = true;

       return new JsonResponse(json_encode($result));
    }

}
