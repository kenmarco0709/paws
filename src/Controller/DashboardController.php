<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;


use App\Service\AuthService;

/**
 * @Route("/dashboard")
 */
class DashboardController extends AbstractController
{
    /**
     * @Route("", name="dashboard_index")
     */
    public function index(Request $request, AuthService $authService)
    {

        if(!$authService->isLoggedIn()) return $authService->redirectToLogin();

       return $this->render('Dashboard/index.html.twig', ['javascripts' => array('/js/dashboard/index.js')] );

    }
    
}
