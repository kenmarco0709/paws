<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;

use App\Service\AuthService;





/**
 * @Route("/messenger")
 */
class MessengerController extends AbstractController
{
    
    /**
     * @Route("/", name="messenger_list")
     */
    public function messenger_list(Request $request, AuthService $authService) {
        
        if(!$authService->isLoggedIn()) return $authService->redirectToLogin();
        if(!$authService->isUserHasAccesses(array('Messenger'))) return $authService->redirectToHome();
        
       $page_title = ' Messenger'; 
       return $this->render('Messenger/list.html.twig', [ 
            'page_title' => $page_title, 
            'stylesheets' => array('/css/messenger.css'),
            'javascripts' => array('/js/mesenger/list.js') ]
       );

    }


}
