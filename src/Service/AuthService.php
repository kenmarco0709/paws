<?php

namespace App\Service;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use App\Entity\UserEntity;

Class AuthService {

    private $em;
    private $container;

    public function __construct(EntityManagerInterface $em, ContainerInterface $container) {

        $this->em = $em;
        $this->container = $container;
    }



    public function isLoggedIn($requestUri = true) {

        $session = $this->container->get('session');

        if($session->has('userData')) {
            return true;
        } else {
            if($requestUri) {
                $req_uri = $_SERVER['REQUEST_URI'];
                if($req_uri !== $this->container->get('router')->generate('auth_login') &&
                    $req_uri !== $this->container->get('router')->generate('auth_logout') &&
                    strpos($req_uri, 'ajax') === false) $session->set('req_uri', $req_uri);
            }
            return false;
        }
    }

    /**
     * Redirects to login page
     */
    public function redirectToLogin() {

        return new RedirectResponse($this->container->get('router')->generate('auth_login'), 302);
    }

      /**
     * Get user
     */
    public function getUser() {

        $userData = $this->container->get('session')->get('userData');
        return $this->em->getRepository(UserEntity::class)->find($userData['id']);
    }

    // Original PHP code by Chirp Internet: www.chirp.com.au
    public function better_crypt($input, $rounds = 7)
    {
        $salt = "";
        $salt_chars = array_merge(range('A','Z'), range('a','z'), range(0,9));
        for($i=0; $i < 22; $i++) {
            $salt .= $salt_chars[array_rand($salt_chars)];
        }
        return crypt($input, sprintf('$2a$%02d$', $rounds) . $salt);
    }

    /**
     * Get user types
     */
    public function getUserTypes() {

        return array(
            'Manager',
            'Cashier',
            'Doctor',
            'Vet. Nurse',
            'Veterinary Assistant',
            'Groomer',
            'Laboratory Analyst',
            'Secretary',
            'Media Office',
            'Veterinarian',
            'Receptionist',
            'Inventory',
            'Jr. Vet',
            'Vet Tech',
            'Admin',
            'Sr Vet',
            'Human Resource',
            'Supply Officer',
            'Medical Director',
            'Practice Manager',
            'Accountant',
            'Chief Operations Officer',
            'Chief Finance Officer',
            'Procurement Assistant',
            'Assistant Manager',
            'Clinic Manager',
            'Inventory Specialist',
            'Customer Service Representative',
            'Owner',
            'Part Owner',
            'Vet Supervisor',
            'Guard',
            'Marketing Officer',
            'Medical Technician',
            'Intern',
            'Supervisor',
            'Staff'
        );
    }

    /**
     * Get sms types
     */
    public function getSmsTypes() {

        return array(
            'Schedule'
        );
    }

    
    /**
    * Get form types
    */
    public function getCabinetFormTypes() {
   
        return array(
            'Admission Form',
            'Agreement For Confinement',
            'Acknowledgment / Against Medical Advise',
            'Consent To Operate'
        );
    }

      /**
    * Get schedule types
    */
    public function getScheduleTypes() {
   
        return array(
            'Duty Schedule',
            'Leave Schedule'
        );
    }

    /**
    * Get admission types
    */
    public function getShelterAdmissionTypes() {
   
        return array(
            0 => 'Rescue',
            1 => 'Re-Admission'
        );
    }

    /**
    * Get schecdule status
    */
    public function getScheduleStatus() {
   
        return array(
            'Confirmed - Show' => 'Confirmed - Show',
            'Confirmed - No Show' => 'Confirmed - No Show',
            'Cancel' => 'Cancel',
            'Not Confirmed' => 'Not Confirmed',
            'Decline' => 'Decline'
        );
    }

    public function isUserVet(){
        
        $userData = $this->container->get('session')->get('userData');
        
        if(in_array($userData['type'], ['Med Tech','Vet Supervisor ','Procurement Assistant','Vet. Nurse', 'Veterinary Assistant','Lab Assistant', 'Veterinarian', 'Jr. Vet', 'Sr. Vet', 'Vet Tech', 'Medical Director','Practice Manager', 'Chief Operations Officer'])){
            return true;
        }

        return false;
    }

    public function getAccesses() {

        return array(
            array('label' => 'Shelter Admission', 'description' => 'Shelter Admission', 'children' => array(
                array('label' => 'Import', 'description' => 'Shelter Admission Import'),
                array('label' => 'New', 'description' => 'Shelter Admission New'),
                array('label' => 'Update', 'description' => 'Shelter Admission Update'),
                array('label' => 'Delete', 'description' => 'Shelter Admission Delete'),
                array('label' => 'Details', 'description' => 'Shelter Admission Details', 'children' => [
                    array('label' => 'Admission History', 'description' => 'Shelter Admission Details Admission History'),
                    array('label' => 'Medical Record', 'description' => 'Shelter Admission Details Medical Record', 'children' => [
                        array('label' => 'New', 'description' => 'Shelter Admission Details Medical Record New'),
                        array('label' => 'Update', 'description' => 'Shelter Admission Details Medical Record Update'),
                        array('label' => 'Delete', 'description' => 'Shelter Admission Details Medical Record Delete'),
                    ]),
                    array('label' => 'Files', 'description' => 'Shelter Admission Details Files', 'children' => [
                        array('label' => 'New', 'description' => 'Shelter Admission Details Pet Files New'),
                        array('label' => 'Update', 'description' => 'Shelter Admission Details Pet Files Update'),
                        array('label' => 'Delete', 'description' => 'Shelter Admission Details Pet Files Delete'),
                    ]),
                    array('label' => 'Photo', 'description' => 'Shelter Admission Details Photo', 'children' => [
                        array('label' => 'New', 'description' => 'Shelter Admission Details Pet Photo New'),
                        array('label' => 'Update', 'description' => 'Shelter Admission Details Pet Photo Update'),
                        array('label' => 'Delete', 'description' => 'Shelter Admission Details Pet Photo Delete'),
                    ]),
                    array('label' => 'Behavior Record', 'description' => 'Shelter Admission Details Behavior Record', 'children' => [
                        array('label' => 'New', 'description' => 'Shelter Admission Details Behavior Record New'),
                        array('label' => 'Update', 'description' => 'Shelter Admission Details Behavior Record Update'),
                        array('label' => 'Delete', 'description' => 'Shelter Admission Details Behavior Record Delete'),
                    ]),
                ]),
                array('label' => 'Select Admission Type', 'description' => 'Shelter Admission Select Admission Type'),
            )),
            array('label' => 'Admission', 'description' => 'Admission', 'children' => array(
                array('label' => 'Confinement', 'description' => 'Admission Confinement', 'children' => array(
                    array('label' => 'New', 'description' => 'Admission Confinement New'),
                )),
                array('label' => 'New', 'description' => 'Admission New'),
                array('label' => 'Update', 'description' => 'Admission Update'),
                array('label' => 'Delete', 'description' => 'Admission Delete'),
                array('label' => 'Client', 'description' => 'Admission Client', 'children' => array(
                    array('label' => 'New', 'description' => 'Admission Client New'),
                )),
                array('label' => 'Pet', 'description' => 'Admission Pet', 'children' => array(
                    array('label' => 'New', 'description' => 'Admission Pet New'),
                )),
            )),
            array('label' => 'Invoice', 'description' => 'Invoice', 'children' => array(
                array('label' => 'New', 'description' => 'Invoice New'),
                array('label' => 'Update', 'description' => 'Invoice Update'),
                array('label' => 'Delete', 'description' => 'Invoice Delete'),
                array('label' => 'Download', 'description' => 'Invoice Download'),
                array('label' => 'Client', 'description' => 'Invoice Client', 'children' => array(
                    array('label' => 'New', 'description' => 'Invoice Client New'),
                )),
            )),
            array('label' => 'Schedule', 'description' => 'Schedule', 'children' => array(
                array('label' => 'Re-schedule', 'description' => 'Schedule Reschedule'),
                array('label' => 'New', 'description' => 'Schedule New'),
                array('label' => 'Admit', 'description' => 'Schedule Admit'),
                array('label' => 'Delete', 'description' => 'Schedule Delete'),
                array('label' => 'Import', 'description' => 'Schedule Import'),
                array('label' => 'Client', 'description' => 'Schedule Client', 'children' => array(
                    array('label' => 'New', 'description' => 'Schedule Client New'),
                )),
                array('label' => 'Pet', 'description' => 'Schedule Pet', 'children' => array(
                    array('label' => 'New', 'description' => 'Schedule Pet New'),
                )),
            )),
            array('label' => 'Vet Schedule', 'description' => 'Vet Schedule', 'children' => array(
                array('label' => 'New', 'description' => 'Vet Schedule New'),
                array('label' => 'Update', 'description' => 'Vet Schedule Update'),
                array('label' => 'Delete', 'description' => 'Vet  Schedule Delete')
            )),
            array('label' => 'Client', 'description' => 'Client', 'children' => array(
                array('label' => 'New', 'description' => 'Client New'),
                array('label' => 'Update', 'description' => 'Client Update'),
                array('label' => 'Delete', 'description' => 'Client Delete'),
                array('label' => 'Details', 'description' => 'Client Details', 'children' => array(
                        array('label' => 'Pet', 'description' => 'Client Details Pet', 'children' => array(
                            array('label' => 'New', 'description' => 'Client Details Pet New'),
                            array('label' => 'Add Existing Pet', 'description' => 'Client Details Pet Add Existing Pet'),
                            array('label' => 'Transfer', 'description' => 'Client Details Pet Transfer'),
                            array('label' => 'Remove', 'description' => 'Client Details Pet Remove'),
                        )),
                        array('label' => 'Payment', 'description' => 'Client Details Payment')
                    )
                ),
            )),
            array('label' => 'Pet', 'description' => 'Pet', 'children' => array(
                array('label' => 'New', 'description' => 'Pet New'),
                array('label' => 'Update', 'description' => 'Pet Update'),
                array('label' => 'Delete', 'description' => 'Pet Delete'),
                array('label' => 'Merge', 'description' => 'Pet Merge'),
                array('label' => 'Details', 'description' => 'Pet Details', 'children' => array(
                    array('label' => 'Medical Records', 'description' => 'Pet Details Medical Records', 'children' => array(
                        array('label' => 'Download', 'description' => 'Pet Details Medical Records Download'),
                    )),
                    array('label' => 'Cabinet Form', 'description' => 'Pet Details Cabinet Form', 'children' => array(
                        array('label' => 'New', 'description' => 'Pet Details Cabinet Form New'),
                    ))
                )),
            )),
            array('label' => 'Inventory', 'description' => 'Inventory Item', 'children' => array(
                array('label' => 'New', 'description' => 'Inventory Item New'),
                array('label' => 'Import', 'description' => 'Inventory Item Import'),
                array('label' => 'Details', 'description' => 'Inventory Item Details', 'children' => array(
                    array('label' => 'Adjustment', 'description' => 'Inventory Item Details Adjustment', 'children' => array(
                        array('label' => 'New', 'description' => 'Inventory Item Details Adjustment New'),
                    )),
                    array('label' => 'Completed Order', 'description' => 'Inventory Item Details Completed Order', 'children' => array(
                        array('label' => 'New', 'description' => 'Inventory Item Details Completed Order New'),
                    )),
                )),
            )),
            array('label' => 'Payment', 'description' => 'Payment'),
            array('label' => 'Messenger', 'description' => 'Messenger'),

            array('label' => 'Audit Trail', 'description' => 'Audit Trail', 'children' => array(
                array('label' => 'Details', 'description' => 'Audit Trail Details'),
            )),
            array('label' => 'Billing', 'description' => 'Billing', 'children' => array(
                array('label' => 'New', 'description' => 'Billing New'),
                array('label' => 'Details', 'description' => 'Billing Details'),

                array('label' => 'Update', 'description' => 'Billing Update', 'children' => array(
                    
                )),
            )),
            array('label' => 'Report', 'description' => 'Report', 'children' => array(
                array('label' => 'Adoption', 'description' => 'Report Adoption', 'children' => array(
                    array('label' => 'PDF', 'description' => 'Report Adoption Pdf'),
                    array('label' => 'CSV', 'description' => 'Report Adoption Csv')
                )),
                array('label' => 'Sales Income', 'description' => 'Report Sales Income'),
                array('label' => 'Payments', 'description' => 'Report Payment'),
                array('label' => 'Invoice', 'description' => 'Report Invoice', 'children' => array(
                    array('label' => 'Aging', 'description' => 'Report Invoice Aging', 'children' => array(
                        array('label' => 'PDF', 'description' => 'Report Invoice Aging Pdf'),
                        array('label' => 'CSV', 'description' => 'Report Invoice Aging Csv')
                    ))
                )),
                array('label' => 'Schedule', 'description' => 'Report Schedule', 'children' => array(
                    array('label' => 'PDF', 'description' => 'Report Schedule Pdf'),
                    array('label' => 'CSV', 'description' => 'Report Schedule Csv')
                ))
            )),
            array('label' => 'Company', 'description' => 'Company', 'children' => array(
                array('label' => 'Company View', 'description' => 'Company View', 'children' => array(
                    array('label' => 'User', 'description' => 'Company View User', 'children' => array(
                        array('label' => 'New', 'description' => 'Company View User New'),
                        array('label' => 'Update', 'description' => 'Company View User Update'),
                        array('label' => 'Delete', 'description' => 'Company View User Delete'),
                    )),
                    array('label' => 'Branch', 'description' => 'Company View Branch', 'children' => array(
                        array('label' => 'New', 'description' => 'Company View Branch New'),
                        array('label' => 'Update', 'description' => 'Company View Branch Update'),
                        array('label' => 'Delete', 'description' => 'Company View Branch Delete'),
                    )),
                    array('label' => 'Access', 'description' => 'Company View Access', 'children' => array(
                        array('label' => 'Update', 'description' => 'Company View Access'),
                        array('label' => 'Update', 'description' => 'Company View Access Form'),
                    )),
                    array('label' => 'Sms', 'description' => 'Company View Sms', 'children' => array(
                        array('label' => 'New', 'description' => 'Company View Sms New'),
                        array('label' => 'Update', 'description' => 'Company View Sms Update'),
                        array('label' => 'Delete', 'description' => 'Company View Sms Delete'),
                    )),
                ))
            )),
            array('label' => 'CMS', 'description' => 'CMS', 'children' => array(
                array('label' => 'Stage', 'description' => 'CMS Stage', 'children' => array(
                    array('label' => 'New', 'description' => 'Stage New'),
                    array('label' => 'Update', 'description' => 'Stage Update'),
                    array('label' => 'Delete', 'description' => 'Stage Delete')

                )),

                array('label' => 'Facility', 'description' => 'CMS Facility', 'children' => array(
                    array('label' => 'New', 'description' => 'Facility New'),
                    array('label' => 'Update', 'description' => 'Facility Update'),
                    array('label' => 'Delete', 'description' => 'Facility Delete')

                )),

                array('label' => 'Service', 'description' => 'CMS Service', 'children' => array(
                    array('label' => 'New', 'description' => 'CMS Service New'),
                    array('label' => 'Update', 'description' => 'CMS Service Update'),
                    array('label' => 'Delete', 'description' => 'CMS Service Delete'),
                    array('label' => 'Import', 'description' => 'CMS Service Import'),

                )),
                
                array('label' => 'Supplier', 'description' => 'CMS Supplier', 'children' => array(
                    array('label' => 'New', 'description' => 'Supplier New'),
                    array('label' => 'Update', 'description' => 'Supplier Update'),
                    array('label' => 'Delete', 'description' => 'Supplier Delete')

                )),
                array('label' => 'Species', 'description' => 'CMS Species', 'children' => array(
                    array('label' => 'New', 'description' => 'Species New'),
                    array('label' => 'Update', 'description' => 'Species Update'),
                    array('label' => 'Delete', 'description' => 'Species Delete')

                )),
                array('label' => 'Item Category', 'description' => 'CMS Item Category', 'children' => array(
                    array('label' => 'New', 'description' => 'Item Category New'),
                    array('label' => 'Update', 'description' => 'Item Category Update'),
                    array('label' => 'Delete', 'description' => 'Item Category Delete')

                )),
                array('label' => 'Item', 'description' => 'CMS Item', 'children' => array(
                    array('label' => 'New', 'description' => 'Item New'),
                    array('label' => 'Update', 'description' => 'Item Update'),
                    array('label' => 'Delete', 'description' => 'Item Delete'),
                    array('label' => 'Import', 'description' => 'Item Import'),

                )),
                array('label' => 'Payment Type', 'description' => 'CMS Payment Type', 'children' => array(
                    array('label' => 'New', 'description' => 'CMS Payment Type New'),
                    array('label' => 'Update', 'description' => 'CMS Payment Type Update'),
                    array('label' => 'Delete', 'description' => 'CMS Payment Type Delete'),
                ))
            )),
        );
    }

     /**
     * Redirects to home page
     */
    public function redirectToHome() {

        $userData = $this->container->get('session')->get('userData');
        
        if(in_array($userData['type'], ['Med Tech','Vet Supervisor ','Procurement Assistant','Vet. Nurse', 'Veterinary Assistant','Lab Assistant', 'Veterinarian', 'Jr. Vet', 'Sr. Vet', 'Vet Tech', 'Medical Director','Practice Manager', 'Chief Operations Officer'])){
            return new RedirectResponse($this->container->get('router')->generate('schedule_index'), 302);
        }

        return new RedirectResponse($this->container->get('router')->generate('schedule_index'), 302);
    }

     /**
     * Checks if the user has the ess
     */
    public function isUserHasAccesses($accessDescriptions, $hasErrorMsg=true, $matchCtr=false) {
        $session = $this->container->get('session');
        $userData = $session->get('userData');


        if($userData['type'] === 'Super Admin') {
            return true;
        } else {
            if($matchCtr) {
                $accessCtr = 0;
                foreach($accessDescriptions as $accessDescription) if(in_array($accessDescription, $userData['accesses'])) $accessCtr++;
                $hasAccess = count($accessDescriptions) === $accessCtr;
                if(!$hasAccess) {
                    if($hasErrorMsg) {
                        $session->getFlashBag()->set('error_messages', "You don't have the right to access the page. Please contact the administrator.");
                    }
                    return false;
                } else {
                    return true;
                }
            } else {
                foreach($accessDescriptions as $accessDescription) if(in_array($accessDescription, $userData['accesses'])) return true;
                if($hasErrorMsg) $session->getFlashBag()->set('error_messages', "You don't have the right to access the page. Please contact the administrator.");
                return false;
            }
        }
    }
    
    /**
     * getTimeago
     */
    function timeAgo( $time )
    {
        $time_difference = time() - strtotime($time);

        if( $time_difference < 1 ) { return 'less than 1 second ago'; }
        $condition = array( 12 * 30 * 24 * 60 * 60 =>  'year',
                    30 * 24 * 60 * 60       =>  'month',
                    24 * 60 * 60            =>  'day',
                    60 * 60                 =>  'hour',
                    60                      =>  'minute',
                    1                       =>  'second'
        );

        foreach( $condition as $secs => $str )
        {
            $d = $time_difference / $secs;

            if( $d >= 1 )
            {
                $t = round( $d );
                return 'about ' . $t . ' ' . $str . ( $t > 1 ? 's' : '' ) . ' ago';
            }
        }
    }
}