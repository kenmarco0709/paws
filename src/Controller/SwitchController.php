<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use App\Service\AuthService;


/**
 * @Route("/switch")
 */
class SwitchController extends AbstractController
{
    /**
     * @Route("", name="switch_index")
     */
    public function index(Request $request, AuthService $authService)
    { 

        if(!$authService->isLoggedIn()) return $authService->redirectToLogin();

        $session = $this->get('session');
        $userData = $session->get('userData');

        $userData['currentModule'] = $userData['currentModule'] == 'Shelter' ? 'Clinic' : 'Shelter';


        $session->set('userData', $userData);
        return $this->redirect($this->generateUrl('dashboard_index'), 302);
    }



}
