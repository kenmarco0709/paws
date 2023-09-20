<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;


use App\Service\AuthService;

/**
 * @Route("/data_analytic")
 */
class DataAnalyticController extends AbstractController
{
    /**
     * @Route("", name="data_analytic_index")
     */
    public function index(Request $request, AuthService $authService)
    {

        if(!$authService->isLoggedIn()) return $authService->redirectToLogin();

       return $this->render('DataAnalytics/index.html.twig', ['javascripts' => array('/js/dashboard/index.js')] );

    }
    
}
