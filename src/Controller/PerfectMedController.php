<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface; 
use Doctrine\ORM\Query;

use App\Entity\Users;


class PerfectMedController extends AbstractController{

    public function __construct(SessionInterface $session){
        $this->session = $session;
    }

    /**
     * @Route("/", name="home_page")
     */
    public function home_page() {

        if (isset($_COOKIE["user_code"]) || $this->session->get("user_code")){

            $users = $this->getDoctrine()
                        ->getRepository(Users::class)
                        ->findAll();

            foreach ($users as $user) {
                if($_COOKIE["user_code"] === $user->getCode() || $this->session->get("user_code") === $user->getCode()){
                    return $this->redirect("/" . $user->getUsername());
                }
            }

        } else {

            return $this->render('perfect_med/index.html.twig');

        }
    }

    /**
     * @Route("/sign_in", name="sign_in")
     */
    public function sign_in() {

        if(isset($_POST['submit'])){

            $users = $this->getDoctrine()
                ->getRepository(Users::class)
                ->findAll();

            if(strlen($_POST['login']) == 0 || strlen($_POST['password']) == 0){
                return $this->render("perfect_med/sign_in.html.twig", [
                    "empty_inputs" => true
                ]);
            }

            $incorrect_data = true;
              
            foreach ($users as $user) {
               if(($_POST['login'] === $user->getUsername() || $_POST['login'] === $user->getEmail()) && $_POST['password'] === $user->getPassword()){
                   $incorrect_data = false;
               }
            }
            
            if($incorrect_data){

                return $this->render("perfect_med/sign_in.html.twig", array(
                   "incorrect_data" => true
                ));

            } else {

                foreach ($users as $user) {
                    if((trim($_POST['login']) === $user->getUsername() || trim($_POST['login']) === $user->getEmail()) && $_POST['password'] === $user->getPassword()){

                        if (isset($_POST["stay_logged"])) {     
                            setcookie("user_code", $user->getCode(), null, "/");
                            $this->session->set("user_code", $user->getCode());
                        } else {
                            $this->session->set("user_code", $user->getCode());
                        }
                        // return new Response (var_dump($_COOKIE['user_code']));
                        return $this->redirect("/$user->getUsername()");
                    }
                }
            }
        }
        
        return $this->render('perfect_med/sign_in.html.twig');
    }

    /**
     * @Route("/sign_up", name="sign_up")
     */
    public function sign_up() {
        return $this->render("perfect_med/sign_up.html.twig");
    }

    /**
     * @Route("/{username}", name="home_page_logged")
     */
    public function home_page_logged($username) {

        $users = $this->getDoctrine()
                    ->getRepository(Users::class)
                    ->findAll();
        
        if(isset($_COOKIE['user_code']) || $this->session->get("user_code")){

                foreach($users as $user){
                    if(($this->session->get("user_code") === $user->getCode() || $_COOKIE["user_code"] === $user->getCode()) && $username === $user->getUsername()){
                        return $this->render("perfect_med/index.html.twig", ["logged" => true]);
                    }
                }
                
            return $this->redirectToRoute("sign_in");

        } else {
            return $this->redirectToRoute("home_page");
        }
    }
}