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

use App\Entity\InvoiceEntity;
use App\Form\InvoiceForm;

use App\Entity\InvoiceAccessEntity;
use App\Entity\ServiceEntity;
use App\Entity\UserEntity;
use App\Entity\MedicalRecordEntity;
use App\Entity\MedicalRecordServiceEntity;
use App\Entity\InvoicePetEntity;
use App\Entity\MedicalRecordItemEntity;
use App\Entity\MedicalRecordLaboratoryEntity;
use App\Entity\InventoryItemEntity; 
use App\Entity\InvoiceTypeEntity;
use App\Entity\BillingEntity;
use App\Entity\ScheduleEntity;
use App\Entity\SchedulePetEntity;
use App\Entity\InvoiceServiceEntity;
use App\Entity\InvoiceInventoryItemEntity;
use App\Entity\InvoiceAdmissionInventoryItemEntity;
use App\Entity\InvoiceAdmissionServiceEntity;
use App\Entity\InvoiceVoidInventoryItemEntity;


use App\Service\AuthService;
use App\Service\InventoryService;

/**
 * @Route("/invoice")
 */
class InvoiceController extends AbstractController
{
    /**
     * @Route("", name="invoice_index")
     */
    public function index(Request $request, AuthService $authService)
    {
        if(!$authService->isLoggedIn()) return $authService->redirectToLogin();
        if(!$authService->isUserHasAccesses(array('Invoice'))) return $authService->redirectToHome();

        $em = $this->getDoctrine()->getManager();        
       $page_title = ' Invoice'; 
       return $this->render('Invoice/index.html.twig', [ 
            'page_title' => $page_title, 
            'javascripts' => array('plugins/datatables/jquery.dataTables.js','/js/invoice/index.js')
        ]
       );
    }



    /**
     * @Route("/ajax_list", name="invoice_ajax_list")
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
            $result = $this->getDoctrine()->getManager()->getRepository(InvoiceEntity::class)->ajax_list($get, $this->get('session')->get('userData'));
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
     *      name = "invoice_form"
     * )
     */
    public function formAction($action, $id, Request $request, AuthService $authService, InventoryService $inventoryService) {

        if(!$authService->isLoggedIn()) return $authService->redirectToLogin();
        if(!$authService->getUser()->getType() == 'Super Admin') return $authService->redirectToHome();

        $em = $this->getDoctrine()->getManager();
        $services = $em->getRepository(ServiceEntity::class)->findBy(array('isDeleted' => false));


        $invoice = $em->getRepository(InvoiceEntity::class)->find(base64_decode($id));
        if(!$invoice) {
            $invoice = new InvoiceEntity();
        }

        $formOptions = array('action' => $action, 'branchId' => $authService->getUser()->getBranch()->getId());
        $form = $this->createForm(InvoiceForm::class, $invoice, $formOptions);
        
        if($request->getMethod() === 'POST') {

            $invoice_form = $request->get($form->getName());

            
            $result = $this->processForm($invoice_form, $invoice, $form, $request, $inventoryService);
            if($result['success']) {
                if($result['redirect'] === 'index') {
                    return $this->redirect($this->generateUrl('invoice_index'), 302);
                } else if($result['redirect'] === 'form') {
                    return $this->redirect($this->generateUrl('invoice_form', array(
                        'action' => 'u',
                        'id' => base64_encode($result['id'])
                    )), 302);
                } else if($result['redirect'] === 'form with error') {
                    return $this->redirect($this->generateUrl('invoice_form'), 302);
                }
            } else {
                $form->submit($invoice_form, false);
            }
        }

        $title = ($action === 'n' ? 'New' : 'Update') . ' Invoice';

        return $this->render('Invoice/form.html.twig', array(
            'title' => $title,
            'page_title' => $title,
            'form' => $form->createView(),
            'action' => $action,
            'id' => $id,
            'invoice' => $invoice,
            'javascripts' => array('/js/invoice/form.js')
        ));
    }

    private function processForm($invoice_form, $invoice ,$form, Request $request, $inventoryService) {

        $em = $this->getDoctrine()->getManager();
        $invoiceFormData = $request->request->get('invoice');

        $errors = $em->getRepository(InvoiceEntity::class)->validate($invoice_form, $invoiceFormData);
        if(!count($errors)) {
            switch($invoice_form['action']) {
                case 'n': // new

                    $form->handleRequest($request);
                    if ($form->isValid()) {

                        if(isset($invoice_form['invoice_date']) && $invoice_form['invoice_date'] != ''){
                            $invoice->setInvoiceDate(new \DateTime($invoice_form['invoice_date']));
                        } else {

                            $invoice->setInvoiceDate(null);
                        }
                        $invoice->setStatus('New'); 
                        $em->persist($invoice);
                        $em->flush();

                        $this->get('session')->getFlashBag()->set('success_messages', 'Record Successfully Saved.');

                        $result['redirect'] = 'form';
                        $result['id'] = $invoice->getId();

                    } else {

                        $this->get('session')->getFlashBag()->set('error_messages', 'Something went wrong. Please try again.');
                        $result['redirect'] = 'form with error';
                    }

                    break;
               
                case 'u': // update

                    if(isset($invoice_form['invoice_date']) && $invoice_form['invoice_date'] != ''){
                        $invoice->setInvoiceDate(new \DateTime($invoice_form['invoice_date']));
                    } else {

                        $invoice->setInvoiceDate(null);
                    }
                    $invoice->setDiscount($invoiceFormData['percent_discount']);
                    $invoice->setAmountDue($invoiceFormData['grand_total']);
                
               
                    if(isset($invoiceFormData['service']) &&  count($invoiceFormData['service'])){
                       
                        $formServiceIds = [];

                        foreach($invoiceFormData['service'] as $k => $service){
    
                            $formServiceIds[$k] = $service['id'];
                        }

                        foreach($invoice->getInvoiceServices() as $service){
                            if(!in_array($service->getService()->getId(), $formServiceIds)){
                                $em->remove($service);
                                $em->flush();
                            }
                        }

                        foreach($invoiceFormData['service'] as $k => $service){
                            
                            if(!in_array($service['id'], $invoice->serviceIds())){

                                $newInvoiceService = new InvoiceServiceEntity();
                                $newInvoiceService->setInvoice($invoice);
                                $newInvoiceService->setService($em->getReference(ServiceEntity::class,$service['id']));
                           
                            } else {

                                $newInvoiceService = $em->getRepository(InvoiceServiceEntity::class)->findOneBy(array('invoice' => $invoice->getId(), 'service' => $service['id']));                              
                            }

                            $newInvoiceService->setRemarks($service['remarks']);
                            $newInvoiceService->setQuantity($service['quantity']);
                            $newInvoiceService->setDiscount($service['percent_discount']);
                            $newInvoiceService->setAmount($service['total_price']);
                            $em->persist($newInvoiceService);
                            $em->flush();


                        }
                    } else {

                         
                        $formServiceIds = [];

                        foreach($invoice->getInvoiceServices() as $service){
                            if(!in_array($service->getService()->getId(), $formServiceIds)){
                                $em->remove($service);
                                $em->flush();
                            }
                        }
                    }

                    if(isset($invoiceFormData['item']) &&  count($invoiceFormData['item'])){
                       
                        $formItemIds = [];
                        foreach($invoiceFormData['item'] as $k => $item){
    
                            $formItemIds[$k] = $item['id'];
                        }

                        foreach($invoice->getInvoiceInventoryItems() as $inventoryItem){
                        
                            if(!in_array($inventoryItem->getInventoryItem()->getId(), $formItemIds)){
                                $em->remove($inventoryItem);
                                $em->flush();

                                $invoiceVoidInventoryItem =  new InvoiceVoidInventoryItemEntity();
                                $invoiceVoidInventoryItem->setQuantity($inventoryItem->getQuantity());
                                $invoiceVoidInventoryItem->setInventoryItem($inventoryItem->getInventoryItem());
                                $invoiceVoidInventoryItem->setInvoice($inventoryItem->getInvoice());
                                $invoiceVoidInventoryItem->setDiscount($inventoryItem->getDiscount());
                                $invoiceVoidInventoryItem->setAmount($inventoryItem->getAmount());
                                $em->persist($invoiceVoidInventoryItem);
                                $em->flush(); 

                                $inventoryService->processInventory($invoiceVoidInventoryItem);

                            }
    
                        }
                        
                        foreach($invoiceFormData['item'] as $k => $item){

                            $inventoryItem = $em->getRepository(InventoryItemEntity::class)->find($item['id']);
                            
                            if(!in_array($item['id'], $invoice->inventoryItemIds())){
                                $invoiceInventoryItem = new InvoiceInventoryItemEntity();
                                $invoiceInventoryItem->setInvoice($invoice);
                                $invoiceInventoryItem->setInventoryItem($inventoryItem);

                           
                            } else {

                                $invoiceInventoryItem = $em->getRepository(InvoiceInventoryItemEntity::class)->findOneBy(array('invoice' => $invoice->getId(), 'inventoryItem' => $item['id']));                              
                            }

                            $invoiceInventoryItem->setRemarks($item['remarks']);
                            $invoiceInventoryItem->setQuantity($item['quantity']);
                            $invoiceInventoryItem->setDiscount($item['percent_discount']);
                            $invoiceInventoryItem->setAmount($item['total_price']);
                            $invoiceInventoryItem->setBuyingPrice($inventoryItem->getBuyingPrice());
                            $invoiceInventoryItem->setSellingPrice($inventoryItem->getSellingPrice());
                            
                            if($inventoryItem->getSupplier()){
                                $invoiceInventoryItem->setSupplier($inventoryItem->getSupplier());

                            }
                            $em->persist($invoiceInventoryItem);
                            $em->flush();

                            $inventoryService->processInventory($invoiceInventoryItem);
                        }
                    } else {

                        $formItemIds = [];

                        foreach($invoice->getInvoiceInventoryItems() as $inventoryItem){
                        
                            if(!in_array($inventoryItem->getInventoryItem()->getId(), $formItemIds)){
                                $em->remove($inventoryItem);
                                $em->flush();

                                $invoiceVoidInventoryItem =  new InvoiceVoidInventoryItemEntity();
                                $invoiceVoidInventoryItem->setQuantity($inventoryItem->getQuantity());
                                $invoiceVoidInventoryItem->setInventoryItem($inventoryItem->getInventoryItem());
                                $invoiceVoidInventoryItem->setInvoice($inventoryItem->getInvoice());
                                $invoiceVoidInventoryItem->setDiscount($inventoryItem->getDiscount());
                                $invoiceVoidInventoryItem->setAmount($inventoryItem->getAmount());
                                $invoiceVoidInventoryItem->setRemarks($inventoryItem->getRemarks());

                                $em->persist($invoiceVoidInventoryItem);
                                $em->flush(); 

                                $inventoryService->processInventory($invoiceVoidInventoryItem);

                            }
    
                        }
                    }

                
                    if(isset($invoiceFormData['admission_service']) && count($invoiceFormData['admission_service'])){
                        foreach($invoiceFormData['admission_service'] as $k => $service){
                            $newInvoiceService = $em->getRepository(InvoiceAdmissionServiceEntity::class)->findOneBy(array(
                                'invoice' => $invoice->getId(),
                                'service' => $service['id'],
                                'admission' => $invoice->getAdmission()->getId()
                            ));                              
                            
                            $newInvoiceService->setDiscount($service['percent_discount']);
                            $newInvoiceService->setRemarks($service['remarks']);


                            $total = $service['price'] * $service['quantity'] ;
                            if($newInvoiceService->getDiscount() && $newInvoiceService->getDiscount() > 0 ){
                                $total -= (($total * $newInvoiceService->getDiscount()) / 100);
                            }


                            $newInvoiceService->setAmount($total);
                            $em->persist($newInvoiceService);
                            $em->flush();
    
                        }
                    }

                    if(isset($invoiceFormData['admission_item']) && count($invoiceFormData['admission_item'])){
                        foreach($invoiceFormData['admission_item'] as $k => $item){
                            $invoiceAdmissionInventoryItem = $em->getRepository(InvoiceAdmissionInventoryItemEntity::class)->findOneBy(array(
                                'invoice' => $invoice->getId(),
                                'inventoryItem' => $item['id'],
                                'admission' => $invoice->getAdmission()->getId()
                            ));                              
                            
                            $invoiceAdmissionInventoryItem->setDiscount($item['percent_discount']);
                            $invoiceAdmissionInventoryItem->setRemarks($item['remarks']);


                            $total = $item['price'] * $item['quantity'] ;
                            if($invoiceAdmissionInventoryItem->getDiscount() && $invoiceAdmissionInventoryItem->getDiscount() > 0 ){
                                $total -= (($total * $invoiceAdmissionInventoryItem->getDiscount()) / 100);
                            }
                            $invoiceAdmissionInventoryItem->setAmount($total);
                            $em->persist($invoiceAdmissionInventoryItem);
                            $em->flush();
    
                        }
                    }

                    if($invoice->getAmountDue() <= 0 ){
                
                        if($invoice->getAdmission()){
                            
                            $admission = $invoice->getAdmission();

                            if($admission->getAdmissionType()->getDescription() == 'Confinement' && $admission->getDischargedDate() == null){
                               
                                $admission->setStatus('Ongoing Confinement');
                                $invoice->setStatus('Paid Partial Payment');
        
                            } else {
        
                                $admission->setStatus('Paid');
                                $invoice->setStatus('Paid Payment');
                            }
                        } else {
                            $invoice->setStatus('Paid Payment');

                        }
        
                     } else {
        
                        $invoice->setStatus('Pending Payment');
                     }

                     $em->flush();
                     $this->get('session')->getFlashBag()->set('success_messages', 'Record Successfully updated.');

                   
                    $result['redirect'] = 'form';
                    $result['id'] = $invoice->getId();

                    break;    
                case 'd': // delete
                    $form->handleRequest($request);


                    if ($form->isValid()) {

                        $invoice->setIsDeleted(true);
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
     * @Route("/aging_report", name = "invoice_aging_report")
     */
    public function aging_reportAction(AuthService $authService){

        if(!$authService->isLoggedIn()) return $authService->redirectToLogin();
        if(!$authService->isUserHasAccesses(array('Report Invoice Aging'))) return $authService->redirectToHome();
        
       $page_title = ' Invoice Aging Report'; 
       return $this->render('Invoice/aging_report.html.twig', [ 
            'page_title' => $page_title
       ]);
    } 

    /**
     * @Route("/report", name = "invoice_report")
     */
    public function reportAction(AuthService $authService){

        if(!$authService->isLoggedIn()) return $authService->redirectToLogin();
        if(!$authService->isUserHasAccesses(array('Report Invoice'))) return $authService->redirectToHome();
        
       $page_title = ' Invoice Report'; 
       return $this->render('Invoice/report.html.twig', [ 
            'page_title' => $page_title
       ]);
    } 

    /**
     * @Route("/download/{id}", name = "invoice_download")
     */
    public function download(Request $request, AuthService $authService, Pdf $pdf, $id){

        ini_set('memory_limit', '2048M');
        
        $invoice  = $this->getDoctrine()->getManager()->getRepository(InvoiceEntity::class)->find(base64_decode($id));

        $options = [
            'orientation' => 'Portrait',
            'print-media-type' =>  True,
            'zoom' => .7
        ];

         $newContent = $this->renderView('Invoice/download.wkpdf.twig', array(
            'invoice' => $invoice,
            'userData' => $this->get('session')->get('userData') 
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
            'Content-Disposition'   => 'attachment; filename="'.  'invoice-' .$invoice->getId().'-' . date('m/d/Y') . '.pdf"'
        ));
    }


    /**
     * @Route("/report/pdf", name = "report_sales_income_pdf")
     */
    public function reportPdf(Request $request, AuthService $authService, Pdf $pdf){

        ini_set('memory_limit', '2048M');
        $dateRange = $request->query->all();
        $startDate = isset($dateRange['start_date']) ? $dateRange['start_date'] : NULL; 
        $endDate = isset($dateRange['end_date']) ? $dateRange['end_date'] : NULL; 


        $invoiceTransactions  = $this->getDoctrine()->getManager()->getRepository(InvoiceEntity::class)->report($startDate, $endDate, $this->get('session')->get('userData'));

        $options = [
            'orientation' => 'Landscape',
            'print-media-type' =>  True,
            'zoom' => .7
        ];


         $newContent = $this->renderView('Invoice/report.wkpdf.twig', array(
            'startDate' => $startDate,
            'endDate' => $endDate,
            'invoiceTransactions' => $invoiceTransactions
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
            'Content-Disposition'   => 'attachment; filename="'.  'invoice-report' .'-' . date('m/d/Y') . '.pdf"'
        ));
    }

       /**
     * @Route("/report/csv", name = "invoice_report_csv")
     */
    public function reportCsv(Request $request, AuthService $authService){

        ini_set('memory_limit', '2048M');
        set_time_limit(0);
        $dateRange = $request->query->all();
        $startDate = isset($dateRange['start_date']) ? $dateRange['start_date'] : NULL; 
        $endDate = isset($dateRange['end_date']) ? $dateRange['end_date'] : NULL; 

        $em = $this->getDoctrine()->getManager();
        $invoiceTransactions  = $this->getDoctrine()->getManager()->getRepository(InvoiceEntity::class)->report($startDate, $endDate, $this->get('session')->get('userData'));

        $columnRange = range('A', 'Z');
        $cellsData = array(
            array('cell' => 'A1', 'data' => 'Invoice #'),
            array('cell' => 'B1', 'data' => 'Client'),
            array('cell' => 'C1', 'data' => 'Date'),
            array('cell' => 'D1', 'data' => 'Amount Due'),
            array('cell' => 'E1', 'data' => 'Discount'),
            array('cell' => 'F1', 'data' => 'Paid Amount'),
            array('cell' => 'G1', 'data' => 'Reimbursed Amount'),
            array('cell' => 'H1', 'data' => 'Balance Amount'),

        );
        $rowCtr = 1;
        $totalCount = 0;

        $paymentAmount = 0;
        $totalRemainingBalance = 0;
        $totalReimbursedAmount = 0;
        foreach($invoiceTransactions as $invoiceTransaction) {
            $paymentAmount = $paymentAmount + $invoiceTransaction['paymentAmount'];
            $totalRemainingBalance = $totalRemainingBalance + $invoiceTransaction['remainingBalance'];
            $totalReimbursedAmount = $totalReimbursedAmount + $invoiceTransaction['reimbursedAmount'];
            $rowCtr++;
            $totalCount++;
            $cellsData[] = array('cell' => "A$rowCtr", 'data' => $invoiceTransaction['invoiceId']);
            $cellsData[] = array('cell' => "B$rowCtr", 'data' => $invoiceTransaction['client']);
            $cellsData[] = array('cell' => "C$rowCtr", 'data' => $invoiceTransaction['invoiceDate']);
            $cellsData[] = array('cell' => "D$rowCtr", 'data' => $invoiceTransaction['totalPrice']);
            $cellsData[] = array('cell' => "E$rowCtr", 'data' => $invoiceTransaction['grandDiscount']);
            $cellsData[] = array('cell' => "F$rowCtr", 'data' => $invoiceTransaction['paymentAmount']);
            $cellsData[] = array('cell' => "G$rowCtr", 'data' => $invoiceTransaction['reimbursedAmount']);
            $cellsData[] = array('cell' => "H$rowCtr", 'data' => $invoiceTransaction['remainingBalance']);
        }
        $rowCtr++;
        $cellsData[] = array('cell' => "E$rowCtr", 'data' => 'Total: ');
        $cellsData[] = array('cell' => "F$rowCtr", 'data' => $paymentAmount);
        $cellsData[] = array('cell' => "G$rowCtr", 'data' => $totalReimbursedAmount);
        $cellsData[] = array('cell' => "H$rowCtr", 'data' => $totalRemainingBalance);


        $rowCtr++;

        $rowCtr++;
        $cellsData[] = array('cell' => "A$rowCtr", 'data' => 'Total Records: ' . $totalCount);


        $page_title = 'Invoice Report';
        return $this->export_to_excel($columnRange, $cellsData, $page_title);
    }

     /**
     * @Route("/aging_report/pdf", name = "invoice_aging_report_pdf")
     */
    public function aging_reportPdf(Request $request, AuthService $authService, Pdf $pdf){

        ini_set('memory_limit', '2048M');
        $dateRange = $request->query->all();

        $invoiceTransactions  = $this->getDoctrine()->getManager()->getRepository(InvoiceEntity::class)->agingReport($this->get('session')->get('userData'));

        $options = [
            'orientation' => 'Landscape',
            'print-media-type' =>  True,
            'zoom' => .7
        ];


         $newContent = $this->renderView('Invoice/aging_report.wkpdf.twig', array(
            'invoiceTransactions' => $invoiceTransactions
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
            'Content-Disposition'   => 'attachment; filename="'.  'invoice-aging-report' .'-' . date('m/d/Y') . '.pdf"'
        ));
    }

       /**
     * @Route("/aging_report/csv", name = "invoice_aging_report_csv")
     */
    public function aging_reportCsv(Request $request, AuthService $authService){

        ini_set('memory_limit', '2048M');
        set_time_limit(0);
        $dateRange = $request->query->all();

        $em = $this->getDoctrine()->getManager();
        $invoiceTransactions  = $this->getDoctrine()->getManager()->getRepository(InvoiceEntity::class)->agingReport($this->get('session')->get('userData'));

        $columnRange = range('A', 'Z');
        $cellsData = array(
            array('cell' => 'A1', 'data' => 'Invoice #'),
            array('cell' => 'B1', 'data' => 'Client'),
            array('cell' => 'C1', 'data' => 'Date'),
            array('cell' => 'D1', 'data' => 'Amount Due'),
            array('cell' => 'E1', 'data' => 'Discount'),
            array('cell' => 'F1', 'data' => 'Paid Amount'),
            array('cell' => 'G1', 'data' => 'Reimbursed Amount'),
            array('cell' => 'H1', 'data' => 'Balance Amount'),
            array('cell' => 'I1', 'data' => ''),


        );
        $rowCtr = 1;
        $totalCount = 0;

        $paymentAmount = 0;
        $totalRemainingBalance = 0;
        $totalReimbursedAmount = 0;
        foreach($invoiceTransactions as $invoiceTransaction) {
            $paymentAmount = $paymentAmount + $invoiceTransaction['paymentAmount'];
            $totalRemainingBalance = $totalRemainingBalance + $invoiceTransaction['remainingBalance'];
            $totalReimbursedAmount = $totalReimbursedAmount + $invoiceTransaction['reimbursedAmount'];
            $rowCtr++;
            $totalCount++;
            $cellsData[] = array('cell' => "A$rowCtr", 'data' => $invoiceTransaction['invoiceId']);
            $cellsData[] = array('cell' => "B$rowCtr", 'data' => $invoiceTransaction['client']);
            $cellsData[] = array('cell' => "C$rowCtr", 'data' => $invoiceTransaction['invoiceDate']);
            $cellsData[] = array('cell' => "D$rowCtr", 'data' => $invoiceTransaction['totalPrice']);
            $cellsData[] = array('cell' => "E$rowCtr", 'data' => $invoiceTransaction['grandDiscount']);
            $cellsData[] = array('cell' => "F$rowCtr", 'data' => $invoiceTransaction['paymentAmount']);
            $cellsData[] = array('cell' => "G$rowCtr", 'data' => $invoiceTransaction['reimbursedAmount']);
            $cellsData[] = array('cell' => "H$rowCtr", 'data' => $invoiceTransaction['remainingBalance']);
            $cellsData[] = array('cell' => "I$rowCtr", 'data' => $authService->timeAgo($invoiceTransaction['invoiceDate'] . '23:59:59'));

        }
        $rowCtr++;
        $cellsData[] = array('cell' => "E$rowCtr", 'data' => 'Total: ');
        $cellsData[] = array('cell' => "F$rowCtr", 'data' => $paymentAmount);
        $cellsData[] = array('cell' => "G$rowCtr", 'data' => $totalReimbursedAmount);
        $cellsData[] = array('cell' => "H$rowCtr", 'data' => $totalRemainingBalance);


        $rowCtr++;

        $rowCtr++;
        $cellsData[] = array('cell' => "A$rowCtr", 'data' => 'Total Records: ' . $totalCount);


        $page_title = 'Invoice Report';
        return $this->export_to_excel($columnRange, $cellsData, $page_title);
    }

    /**
     * @Route("/print/{id}", name="invoice_print")
     */
    public function print(Request $request, AuthService $authService, $id)
    {
       
       $result = [ 'success' =>  true, 'msg' => ''];

       if(!$authService->isLoggedIn()) { $result['success'] = false; $result['msg'] = 'Unauthorized access please call a system administrator.';}

       
       $em = $this->getDoctrine()->getManager();

       $invoice = $em->getRepository(InvoiceEntity::class)->find(base64_decode($id));     
       $page_title = ' Invoice Report'; 
       
       return $this->render('Invoice/print.html.twig', [
            'invoice' => $invoice,
            'userData' => $this->get('session')->get('userData') 
        ]);
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
