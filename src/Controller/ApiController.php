<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

use App\Entity\UserEntity;


/**
 * @Route("/api", name="app_login")
 */
class ApiController extends AbstractController
{
    /**
     * @Route("/login", name="api_login")
     */
    public function index(Request $request): JsonResponse
    {

        header('Access-Control-Allow-Origin: *');
        header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method, Cache-Control, Pragma, Authorization");
        header("Allow: GET, POST, OPTIONS, PUT, DELETE");
        header('Access-Control-Allow-Methods', 'GET,PUT,POST,DELETE,PATCH,OPTIONS');

       $result = array(
            'Success' => false,
            'Msg' => ''
       );

       if($request->getMethod() == 'POST'){

            $login = json_decode($request->getContent(), true);

            if(isset($login)){

                
                $em = $this->getDoctrine()->getManager();
                $user = $em->getRepository(UserEntity::class)->findOneBy(array('isActive' => 1, 'username' => $login['username']));

                if($user){

                    if($user->getPassword() == crypt(md5($login['password']), $user->getPassword())){
                        $now = strtotime("now");
                        $payload =[
                                    "jti" => base64_encode(random_bytes(16)),
                                    "iss" => $this->getParameter('app.jwt_issuer'),
                                    "aud" => $this->getParameter('app.jwt_aud'),
                                    "data" => [
                                        "_id" => $user->getIdEncoded(),
                                        "fullname" => $user->getFullName(),
                                        "type" => $user->getType()
                                    ]
                                ];

                        $jwt = JWT::encode($payload, $this->getParameter('app.jwt_secret'), $this->getParameter('app.jwt_algo'));
                        
                        $result['Success'] = true;
                        $result['Token'] = $jwt;
                    } else {
                    
                        $result['Msg'] = 'Password is not correct.';
                    }

                } else {

                    $result['Msg'] = 'Invalid User';
                }

            } else {
                
                $result['Msg'] = 'Opps something went wrong please try again';
            }

       } else {

            $result['Msg'] = 'Unauthorized access';
       }

 
       
       return new JsonResponse($result);
    }

    /**
     * @Route("/users", name="api_users")
     */
    public function users(Request $request): JsonResponse
    {
        header('Access-Control-Allow-Origin: *');
        header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method, Cache-Control, Pragma, Authorization");
        header("Allow: GET, POST, OPTIONS, PUT, DELETE");
        header('Access-Control-Allow-Methods', 'GET,PUT,POST,DELETE,PATCH,OPTIONS');


        $users = array(
            'items' => array(
                0 => array(
                    'id' => 1,
                    "name" => "kenneth",
                    "username" => "username",
                    "email" => "email@test.com",
                    "type" => "superadmin"
                )
            ) 
        );
        
        return new JsonResponse($users);
    } 
}
