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
use App\Entity\InvoiceEntity;
use App\Entity\ScheduleEntity;
use App\Entity\AdmissionTypeEntity;
use App\Entity\MedicalRecordEntity;




/**
 * @Route("/report")
 */
class ReportController extends AbstractController
{

    /**
     * @Route("/sales_income", name="report_sales_income")
     */
    public function salesIncomeReport(Request $request, AuthService $authService)
    {
        if(!$authService->isLoggedIn()) return $authService->redirectToLogin();
        if(!$authService->isUserHasAccesses(array('Report Sales Income'))) return $authService->redirectToHome();
        
       $page_title = ' Sales Income Report'; 
       return $this->render('Report/sales_income.html.twig', [ 
            'page_title' => $page_title, 
            'javascripts' => array('/plugins/datatables/jquery.dataTables.js','/js/report/sales_income.js') ]
       );
    }

     /**
     * @Route("/sales_income_ajax_list", name="report_sales_income_ajax_list")
     */
    public function sales_income_ajax_list(Request $request, AuthService $authService) {

        $get = $request->query->all();

        $result = array(
            "draw" => intval($get['draw']),
            "recordsTotal" => 0,
            "recordsFiltered" => 0,
            "data" => array()
        );

        if($authService->isLoggedIn()) {
            $result = $this->getDoctrine()->getManager()->getRepository(InvoiceEntity::class)->sales_income_ajax_list($get, $this->get('session')->get('userData'));
        }

        return new JsonResponse($result);
    }

     /**
     * @Route("/sales_income_breakdown", name="report_sales_income_breakdown")
     */
    public function sales_income_breakdown(Request $request, AuthService $authService) {
        
        $result = ['success' => true, 'msg' => ''];

        $invoice = $this->getDoctrine()->getManager()->getRepository(InvoiceEntity::class)->find($request->request->get('id'));
        if(!$invoice){
            $result['success'] = false;
            $result['msg'] = 'Something went wrong please try again later.';
        } else {

            $result['html'] = $this->renderView('Report/sales_income_breakdown.html.twig', [
                'invoice' => $invoice
            ]);
        }

        return new JsonResponse($result);
    }

    /**
     * @Route("/sales_income/pdf", name = "report_sales_income_pdf")
     */
    public function salesIncomePdf(Request $request, AuthService $authService, Pdf $pdf){

        ini_set('memory_limit', '2048M');
        $dateRange = $request->query->all();
        $startDate = isset($dateRange['start_date']) ? $dateRange['start_date'] : NULL; 
        $endDate = isset($dateRange['end_date']) ? $dateRange['end_date'] : NULL; 


        $invoiceTransactions  = $this->getDoctrine()->getManager()->getRepository(InvoiceEntity::class)->sales_income_report($startDate, $endDate, $this->get('session')->get('userData'));
        $options = [
            'orientation' => 'Portrait',
            'print-media-type' =>  True,
            'zoom' => .5
        ];


         $newContent = $this->renderView('Report/sales_income.wkpdf.twig', array(
            'startDate' => $startDate,
            'endDate' => $endDate,
            'results' => $invoiceTransactions
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
            'Content-Disposition'   => 'attachment; filename="'.  'sales-income-report' .'-' . date('m/d/Y') . '.pdf"'
        ));
    }

    /**
     * @Route("/sales_income_csv", name = "report_report_sales_income_csv")
     */
    public function salesIncomeCsv(Request $request, AuthService $authService){

        ini_set('memory_limit', '2048M');
        set_time_limit(0);
        $dateRange = $request->query->all();
        $startDate = isset($dateRange['start_date']) ? $dateRange['start_date'] : NULL; 
        $endDate = isset($dateRange['end_date']) ? $dateRange['end_date'] : NULL; 

        $em = $this->getDoctrine()->getManager();
        $results  = $this->getDoctrine()->getManager()->getRepository(InvoiceEntity::class)->sales_income_report($startDate, $endDate, $this->get('session')->get('userData'));
        $columnRange = range('A', 'J');
        $cellsData = array(
            array('cell' => 'A1', 'data' => 'Sales Income Report'),
            array('cell' => 'A2', 'data' => 'Start Date: ' .$startDate),
            array('cell' => 'A3', 'data' => 'End Date: ' .$endDate)

        );

        $rowCtr = 3;
        $totalCount = 0;
        $discount = 0;
        $gross = 0;
        $net = 0;
        $paymentAmt = 0;
        $dueAmt = 0;

        $rowCtr++;
        $cellsData[] = array('cell' => "B$rowCtr", 'data' => 'Description');
        $cellsData[] = array('cell' => "C$rowCtr", 'data' => 'Quantity');
        $cellsData[] = array('cell' => "D$rowCtr", 'data' => 'Buying Price');
        $cellsData[] = array('cell' => "E$rowCtr", 'data' => 'Selling Price');
        $cellsData[] = array('cell' => "F$rowCtr", 'data' => 'Discount');
        $cellsData[] = array('cell' => "G$rowCtr", 'data' => 'Gross');
        $cellsData[] = array('cell' => "H$rowCtr", 'data' => 'Net');
        $cellsData[] = array('cell' => "I$rowCtr", 'data' => 'Payment Amount');
        $cellsData[] = array('cell' => "J$rowCtr", 'data' => 'Receivable Amount');
          
        foreach($results as $result) {

            $rowCtr++;
            $totalCount++;
            $paymentAmt+= $result['payment_amt'];
            $dueAmt+= $result['due_amt'];

            $cellsData[] = array('cell' => "A$rowCtr", 'data' => 'Invoice #: ' .$result['invoice_id']);
            $rowCtr++;
            $cellsData[] = array('cell' => "A$rowCtr", 'data' => 'Client: ' .$result['client']);

            foreach($result['products'] as $product){

                $rowCtr++;
                $discount+= $product['discount'];
                $gross+= $product['gross'];
                $net+= $product['net'];

                $cellsData[] = array('cell' => "B$rowCtr", 'data' => $product['description']);
                $cellsData[] = array('cell' => "C$rowCtr", 'data' => $product['quantity']);
                $cellsData[] = array('cell' => "D$rowCtr", 'data' => $product['buying_price']);
                $cellsData[] = array('cell' => "E$rowCtr", 'data' => $product['selling_price']);
                $cellsData[] = array('cell' => "F$rowCtr", 'data' => $product['discount']);
                $cellsData[] = array('cell' => "G$rowCtr", 'data' => $product['gross']);
                $cellsData[] = array('cell' => "H$rowCtr", 'data' => $product['gross']);

            }

            $rowCtr++;
            $cellsData[] = array('cell' => "I$rowCtr", 'data' => $result['payment_amt']);
            $cellsData[] = array('cell' => "J$rowCtr", 'data' => $result['due_amt']);
        }

        $rowCtr++;
        $cellsData[] = array('cell' => "E$rowCtr", 'data' => 'Total: ');
        $cellsData[] = array('cell' => "F$rowCtr", 'data' => $discount);
        $cellsData[] = array('cell' => "G$rowCtr", 'data' => $gross);
        $cellsData[] = array('cell' => "H$rowCtr", 'data' => $net);
        $cellsData[] = array('cell' => "I$rowCtr", 'data' => $paymentAmt);
        $cellsData[] = array('cell' => "J$rowCtr", 'data' => $dueAmt);


        $page_title = 'sales_income_report';
        return $this->export_to_excel($columnRange, $cellsData, $page_title);
    }

    /**
     * @Route("/medical_record", name = "report_medical_record")
     */
    public function medical_recordAction(AuthService $authService){

       if(!$authService->isLoggedIn()) return $authService->redirectToLogin();
       
       $em = $this->getDoctrine()->getManager();
       $admissionTypes = $em->getRepository(AdmissionTypeEntity::class)->findBy([ 'isDeleted' => 0]);
       $page_title = ' Medical Record Report'; 
       return $this->render('Report/medical_record.html.twig', [ 
            'page_title' => $page_title,
            'admissionTypes' => $admissionTypes,
            'javascripts' => array('/js/report/medical_record.js')
       ]);
    }

       /**
     * @Route("/medical_record_csv", name = "report_medical_record_csv")
     */
    public function medicalRecordCsv(Request $request, AuthService $authService){

        ini_set('memory_limit', '2048M');
        set_time_limit(0);
        $params = $request->query->all();
        $startDate = isset($params['start_date']) ? $params['start_date'] : NULL; 
        $endDate = isset($params['end_date']) ? $params['end_date'] : NULL; 
        $admissionType = isset($params['admission_type']) ? $params['admission_type'] : NULL; 

        $rowCtr = 0;
        $columnRange = range('A', 'M');

        $em = $this->getDoctrine()->getManager();
        $results  = $this->getDoctrine()->getManager()->getRepository(MedicalRecordEntity::class)->report($startDate, $endDate, $admissionType, $this->get('session')->get('userData'));


        $rowCtr++;
        $cellsData[] = array('cell' => "A$rowCtr", 'data' => 'Admission Date');
        $cellsData[] = array('cell' => "B$rowCtr", 'data' => 'Client');
        $cellsData[] = array('cell' => "C$rowCtr", 'data' => 'Pet');
        $cellsData[] = array('cell' => "D$rowCtr", 'data' => 'Weight');
        $cellsData[] = array('cell' => "E$rowCtr", 'data' => 'Temperature');
        $cellsData[] = array('cell' => "F$rowCtr", 'data' => 'Primary Complain');
        $cellsData[] = array('cell' => "G$rowCtr", 'data' => 'Medical Interpretation');
        $cellsData[] = array('cell' => "H$rowCtr", 'data' => 'Diagnosis');
        $cellsData[] = array('cell' => "I$rowCtr", 'data' => 'Vaccine Due Date');
        $cellsData[] = array('cell' => "J$rowCtr", 'data' => 'Returned Date');
        $cellsData[] = array('cell' => "K$rowCtr", 'data' => 'Vaccine Lot No.');
        $cellsData[] = array('cell' => "L$rowCtr", 'data' => 'Vaccine Batch  No.');
        $cellsData[] = array('cell' => "M$rowCtr", 'data' => 'Vaccine Expiration Date');
          
        foreach($results as $result) {

            $rowCtr++;

            $cellsData[] = array('cell' => "A$rowCtr", 'data' => $result['admissionDate']);
            $cellsData[] = array('cell' => "B$rowCtr", 'data' => $result['client']);
            $cellsData[] = array('cell' => "C$rowCtr", 'data' => $result['pet']);
            $cellsData[] = array('cell' => "D$rowCtr", 'data' => $result['weight']);
            $cellsData[] = array('cell' => "E$rowCtr", 'data' => $result['temperature']);
            $cellsData[] = array('cell' => "F$rowCtr", 'data' => $result['primary_complain']);
            $cellsData[] = array('cell' => "G$rowCtr", 'data' => $result['medical_interpretation']);
            $cellsData[] = array('cell' => "H$rowCtr", 'data' => $result['diagnosis']);
            $cellsData[] = array('cell' => "I$rowCtr", 'data' => $result['vaccineDueDate']);
            $cellsData[] = array('cell' => "J$rowCtr", 'data' => $result['returnedDate']);
            $cellsData[] = array('cell' => "K$rowCtr", 'data' => $result['vaccine_lot_no']);
            $cellsData[] = array('cell' => "L$rowCtr", 'data' => $result['vaccine_lot_no']);
            $cellsData[] = array('cell' => "M$rowCtr", 'data' => $result['vaccineExpirationDate']);

        }

        $page_title = 'medical_record';
        return $this->export_to_excel($columnRange, $cellsData, $page_title);
    }

    /**
     * @Route("/schedule", name = "report_schedule")
     */
    public function scheduleAction(AuthService $authService){

        if(!$authService->isLoggedIn()) return $authService->redirectToLogin();
        if(!$authService->isUserHasAccesses(array('Report Schedule'))) return $authService->redirectToHome();
        
       $page_title = ' Schedule Report'; 
       return $this->render('Report/schedule.html.twig', [ 
            'page_title' => $page_title,
            'statuses' => $authService->getScheduleStatus(),
            'javascripts' => array('/js/report/schedule.js')
       ]);
    }
    
    /**
     * @Route("/schedule_csv", name = "report_schedule_csv")
     */
    public function scheduleCsv(Request $request, AuthService $authService){

        ini_set('memory_limit', '2048M');
        set_time_limit(0);
        $params = $request->query->all(); 

        $em = $this->getDoctrine()->getManager();
        $results  = $this->getDoctrine()->getManager()->getRepository(ScheduleEntity::class)->report($params['start_date'], $params['end_date'], $params['schedule_type'], $params['status'], $this->get('session')->get('userData'));

        $columnRange = range('A', 'D');

        $rowCtr = 0;
        $rowCtr++;
        $cellsData[] = array('cell' => "A$rowCtr", 'data' => 'Client');
        $cellsData[] = array('cell' => "B$rowCtr", 'data' => 'Pet');
        $cellsData[] = array('cell' => "C$rowCtr", 'data' => 'Schedule Date');
        $cellsData[] = array('cell' => "D$rowCtr", 'data' => 'Status');
          
        foreach($results as $result){

            $rowCtr++;

            $cellsData[] = array('cell' => "A$rowCtr", 'data' => $result['client']);
            $cellsData[] = array('cell' => "B$rowCtr", 'data' => $result['schedulePets']);
            $cellsData[] = array('cell' => "C$rowCtr", 'data' => $result['scheduleDate']);
            $cellsData[] = array('cell' => "D$rowCtr", 'data' => $result['status']);

        }

        $rowCtr++;
        $page_title = 'schedule_report';
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

}
