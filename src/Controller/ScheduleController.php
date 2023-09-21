<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use PhpOffice\PhpSpreadsheet\Reader\Csv;

use App\Entity\ScheduleEntity;
use App\Form\ScheduleForm;

use App\Entity\ScheduleAccessEntity;
use App\Entity\ServiceEntity;
use App\Entity\UserEntity;
use App\Entity\MedicalRecordEntity;
use App\Entity\MedicalRecordServiceEntity;
use App\Entity\SchedulePetEntity;
use App\Entity\MedicalRecordItemEntity;
use App\Entity\MedicalRecordLaboratoryEntity;
use App\Entity\InventoryItemEntity; 
use App\Entity\ScheduleTypeEntity;
use App\Entity\BillingEntity;
use App\Entity\AdmissionEntity;
use App\Entity\AdmissionPetEntity;
use App\Entity\AdmissionTypeEntity;
use App\Entity\ClientEntity;
use App\Entity\ClientPetEntity;

use App\Entity\BreedEntity;
use App\Entity\PetEntity;



use App\Service\AuthService;
use App\Service\InventoryService;



/**
 * @Route("/schedule")
 */
class ScheduleController extends AbstractController
{
    /**
     * @Route("/", name="schedule_index")
     */
    public function index(Request $request, AuthService $authService)
    {
        if(!$authService->isLoggedIn()) return $authService->redirectToLogin();
       // if(!$authService->isUserHasAccesses(array('Schedule'))) return $authService->redirectToHome();

        $em = $this->getDoctrine()->getManager();        
        $page_title = ' Schedule'; 
        return $this->render('Schedule/index.html.twig', [ 
                'page_title' => $page_title, 
                'javascripts' => array('/plugins/datatables/jquery.dataTables.js','/js/schedule/index.js')
            ]
        );
    }

        /**
     * @Route("/ajax_list", name="schedule_ajax_list")
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
            $result = $this->getDoctrine()->getManager()->getRepository(ScheduleEntity::class)->ajax_list($get, $this->get('session')->get('userData'));
        }

        return new JsonResponse($result);
    }




    /**
     * @Route("/ajax_data", name="schedule_ajax_data")
     */
    public function ajax_data(Request $request, AuthService $authService) {

        $get = $request->query->all();
        $result = [];

        if($authService->isLoggedIn()) {
            $result = $this->getDoctrine()->getManager()->getRepository(ScheduleEntity::class)->branchSchedules($get, $this->get('session')->get('userData'));
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
     *      name = "schedule_form"
     * )
     */
    public function formAction($action, $id, Request $request, AuthService $authService, InventoryService $inventoryService) {

        if(!$authService->isLoggedIn()) return $authService->redirectToLogin();
        if(!$authService->getUser()->getType() == 'Super Admin') return $authService->redirectToHome();

        $em = $this->getDoctrine()->getManager();
        $services = $em->getRepository(ServiceEntity::class)->findBy(array('isDeleted' => false));


        $schedule = $em->getRepository(ScheduleEntity::class)->find(base64_decode($id));
        if(!$schedule) {
            $schedule = new ScheduleEntity();
        }

        $formOptions = array('action' => $action, 'branchId' => $authService->getUser()->getBranch()->getId(), 'statuses' => $authService->getScheduleStatus());
        $form = $this->createForm(ScheduleForm::class, $schedule, $formOptions);
        
        $dateToSched = $request->query->get('date') ? date('m/d/Y', strtotime($request->query->get('date'))) : '';
       
        if($request->getMethod() === 'POST') {

            $schedule_form = $request->get($form->getName());

            
            $result = $this->processForm($schedule_form, $schedule, $form, $request, $inventoryService);
            if($result['success']) {
                if($result['redirect'] === 'index') {
                    return $this->redirect($this->generateUrl('schedule_index'), 302);
                } else if($result['redirect'] === 'form') {
                    return $this->redirect($this->generateUrl('schedule_form', array(
                        'action' => 'u',
                        'id' => base64_encode($result['id'])
                    )), 302);
                } else if($result['redirect'] === 'form with error') {
                    return $this->redirect($this->generateUrl('schedule_form'), 302);
                }
            } else {
                $form->submit($schedule_form, false);
            }
        }

        $title = ($action === 'n' ? 'New' : 'Update') . ' Schedule';

        return $this->render('Schedule/form.html.twig', array(
            'title' => $title,
            'page_title' => $title,
            'form' => $form->createView(),
            'action' => $action,
            'id' => $id,
            'schedule' => $schedule,
            'isUserVet' => $authService->isUserVet(),
            'javascripts' => array('/js/schedule/form.js'),
            'services' => $services,
            'dateToSched' => $dateToSched
        ));
    }

    private function processForm($schedule_form, $schedule ,$form, Request $request, $inventoryService) {

        $em = $this->getDoctrine()->getManager();

        $errors = $em->getRepository(ScheduleEntity::class)->validate($schedule_form);
        if(!count($errors)) {
            switch($schedule_form['action']) {
                case 'n': // new

                    $form->handleRequest($request);

                    if ($form->isValid()) {
                        
                        $schedule->setStatus('New');
                        $schedule->setSmsStatus('Unsent');
                        $schedule->setScheduleDate(new \DateTime($schedule_form['schedule_date']));
                        $em->persist($schedule);
                        $em->flush();

                        $this->get('session')->getFlashBag()->set('success_messages', 'Record Successfully Saved.');

                        $result['redirect'] = 'index';

                    } else {
                        
                        $this->get('session')->getFlashBag()->set('error_messages', 'Something went wrong. Please try again.');
                        $result['redirect'] = 'form with error';
                    }

                    break; 
                case 'u':

                    $schedule->setAdmissionType($em->getReference(AdmissionTypeEntity::class, $schedule_form['admission_type']));
                    $schedule->setAttendingVet($em->getReference(UserEntity::class, $schedule_form['attending_vet']));
                    $schedule->setClient($em->getReference(ClientEntity::class, $schedule_form['client']));
                    $schedule->setRemarks($schedule_form['remarks']);
                    $schedule->setStatus($schedule_form['status']);
                    $schedule->setSmsStatus('Unsent');
                    $schedule->setScheduleDate(new \DateTime($schedule_form['schedule_date']));
                    $em->persist($schedule);
                    $em->flush();

                    $this->get('session')->getFlashBag()->set('success_messages', 'Record Successfully Updated.');

                    $result['redirect'] = 'index';
                    break; 
                case 'd': // delete
                    $form->handleRequest($request);


                    if ($form->isValid()) {

                        $schedule->setIsDeleted(true);
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
     * @Route("/admit/{id}",name = "schedule_admit")
     */
    public function admitAction($id, Request $request, AuthService $authService) {

        if(!$authService->isLoggedIn()) return $authService->redirectToLogin();
        if(!$authService->isUserHasAccesses(array('Schedule Admit'))) return $authService->redirectToHome();

        $em = $this->getDoctrine()->getManager();
        $schedule = $em->getRepository(ScheduleEntity::class)->find(base64_decode($id));

        if(!$schedule){

            $this->get('session')->getFlashBag()->set('error_messages', 'Something went wrong. Please try again.');

            return $this->redirect($this->generateUrl('schedule_index'), 302);
        } else {

                $admission = new AdmissionEntity();
                $admission->setClient($schedule->getClient());
                $admission->setAttendingVet($schedule->getAttendingVet());
                $admission->setAdmissionType($schedule->getAdmissionType());
                $admission->setBranch($schedule->getBranch());
                $admission->setStatus('New');
                $em->persist($admission);

                if(count($schedule->getSchedulePets())){

                    foreach($schedule->getSchedulePets() as $pet){

                        $newAdmissionPet = new AdmissionPetEntity();
                        $newAdmissionPet->setAdmission($admission);
                        $newAdmissionPet->setPet($pet->getPet());
                        $em->persist($newAdmissionPet);
                        $em->flush();
                    }
                }

                $schedule->setStatus('Admitted');
                $em->flush();

                $this->get('session')->getFlashBag()->set('success_messages', 'Admission successfully created.');
                return $this->redirect($this->generateUrl('admission_form', array('action' => 'u' , 'id' => $admission->getIdEncoded())), 302);
        }
    }

       /**
     * @Route("/reschedule", name="schedule_reschedule")
     */
    public function reschedule(Request $request, AuthService $authService)
    {
        if(!$authService->isLoggedIn()) return $authService->redirectToLogin();
        if(!$authService->isUserHasAccesses(array('Schedule Reschedule'))) return $authService->redirectToHome();

        if($request->getMethod() == 'POST'){
            
            $formDates = $request->request->get('schedule');
            $em = $this->getDoctrine()->getManager();
            $schdules = $em->getRepository(ScheduleEntity::class)->getAllScheduleIds($formDates, $this->get('session')->get('userData'));

           if(count($schdules)){

                foreach($schdules as $schedule){

                    $scheduleEnt = $em->getRepository(ScheduleEntity::class)->find($schedule['id']);
                    $scheduleEnt->setScheduleDate( new \Datetime($formDates['end_date']));
                    $em->flush();
                }

                $this->get('session')->getFlashBag()->set('success_messages', count($schdules) . ' record  has been re-schesduled.');
                return $this->redirect($this->generateUrl('schedule_index'), 302);
           } else {

                $this->get('session')->getFlashBag()->set('error_messages', 'There are no schedule with the from date.');
                return $this->redirect($this->generateUrl('schedule_reschedule'), 302);
           }
            
        }

        $em = $this->getDoctrine()->getManager();        
        $page_title = ' Schedule Reschedule'; 
        return $this->render('Schedule/reschedule.html.twig', [ 
                'page_title' => $page_title
            ]
        );
    }

     /**
     * @Route("/import", name="schedule_import")
     */
    public function import(Request $request, AuthService $authService)
    {
        if(!$authService->isLoggedIn()) return $authService->redirectToLogin();
        if(!$authService->isUserHasAccesses(array('Schedule Import'))) return $authService->redirectToHome();
        
        if($request->getMethod() == 'POST'){
            $file_mimes = array('text/x-comma-separated-values', 'text/comma-separated-values', 'application/octet-stream', 'application/vnd.ms-excel', 'application/x-csv', 'text/x-csv', 'text/csv', 'application/csv', 'application/excel', 'application/vnd.msexcel', 'text/plain', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            
            if(isset($_FILES['items']['name']) && in_array($_FILES['items']['type'], $file_mimes)) {

                $reader = new Csv();
                $spreadsheet = $reader->load($_FILES['items']['tmp_name']);
 
                $sheetData = $spreadsheet->getActiveSheet()->toArray();
                array_shift($sheetData);

                $em = $this->getDoctrine()->getManager();
                $branch = $authService->getUser()->getBranch();

                foreach($sheetData as $data){
                    $f = $data[0];
                    $l = $data[1];
                    $pn = $data[13];
                    $sd = $data[14];


                    if(!empty($f) && !empty($l)){
                        $breed = !empty($data[10])  ? $data[10] : (!empty($data[11]) ? $data[11] : '');
                  
                        if(!empty($breed)){
                
                            $breedEnt = $em->getRepository(BreedEntity::class)->findOneBy(['description' => $breed, 'isDeleted' => false]);
                            
                            if(is_null($breedEnt)){
                                $breedEnt =  new BreedEntity();
                                $breedEnt->setCode($breed);
                                $breedEnt->setDescription($breed);
                                $em->persist($breedEnt);
                                $em->flush();
                            }

                    
                        }

                        $client = $em->getRepository(ClientEntity::class)->findOneBy(['firstName' => $f, 'lastName' => $l, 'branch' => $branch, 'isDeleted' => false]);

                        if(is_null($client)){

                            $client = new ClientEntity();
                            $client->setFirstName($f);
                            $client->setLastName($l);
                            $client->setBranch($branch);
                            $em->persist($client);
                            $em->flush();

                            $pet = new PetEntity();
                            $pet->setName($pn);
                            $pet->setBreed($breedEnt);
                            $em->persist($pet);
                            $em->flush();

                            $clientPet =  new ClientPetEntity();
                            $clientPet->setClient($client);
                            $clientPet->setPet($pet);
                            $em->persist($clientPet);
                            $em->flush();
                        } else {


                            $pet = $em->getRepository(PetEntity::class)->findOneBy(['name' => $pn]);
                            if(is_null($pet)){
                          
                                $pet = new PetEntity();
                                $pet->setName($pn);
                                $pet->setBreed($breedEnt);
                                $em->persist($pet);
                                $em->flush();

                            }
                            

                            $clientPet = $em->getRepository(ClientPetEntity::class)->findOneBy(['client' => $client, 'pet' => $pet, 'isDeleted' => false]);
                        
                            if(is_null($clientPet)){
                            
                                $clientPet =  new ClientPetEntity();
                                $clientPet->setClient($client);
                                $clientPet->setPet($pet);
                                $em->persist($clientPet);
                                $em->flush();
                            }
                        }

                        //Start Scheduling 
                        $admissionType = $em->getRepository(AdmissionTypeEntity::class)->findOneBy(['description' => 'Spay/Neuter Surgery']);
                        $schedule = $em->getRepository(ScheduleEntity::class)->getClientScheduleByDate($client, $admissionType, date('Y-m-d' , strtotime($sd)));

                        if(!count($schedule)){
                            
                            $schedule = new ScheduleEntity();
                            $schedule->setClient($client);
                            $schedule->setBranch($branch);
                            $schedule->setAdmissionType($admissionType);
                            $schedule->setStatus('New');
                            $schedule->setScheduleDate(new \Datetime(date('m/d/Y' , strtotime($sd))));
                            $em->persist($schedule);
                            $em->flush();
                        } else {

                            $schedule = $em->getRepository(ScheduleEntity::class)->find($schedule[0]['id']);

                        }

                        $schedulePet = $em->getRepository(SchedulePetEntity::class)->findOneBy(['pet' => $pet, 'schedule' => $schedule]);

                        if(is_null($schedulePet)){

                            $schedulePet = new SchedulePetEntity();
                            $schedulePet->setPet($pet);
                            $schedulePet->setSchedule($schedule);
                            $em->persist($schedulePet);
                            $em->flush();
                        }
                    }  
                }

                $this->get('session')->getFlashBag()->set('success_messages', 'Schedule successfully import.');

            } else {

                $this->get('session')->getFlashBag()->set('error_messages', 'Please put a valid CSV file.');

            }

        } else {

            $this->get('session')->getFlashBag()->set('error_messages', 'Unauthorized request please call a system administrator.');

        }


       return $this->redirect($this->generateUrl('schedule_index'),302);
    }

    

    

}
