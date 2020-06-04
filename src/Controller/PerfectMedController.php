<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface; 
use Doctrine\ORM\Query;
use Symfony\Component\HttpFoundation\Cookie;

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
                if (isset($_COOKIE["user_code"])) {
                    if($_COOKIE["user_code"] === $user->getCode()){
                        return $this->redirect("/" . $user->getUsername());
                    }
                } elseif($this->session->get("user_code") === $user->getCode()){
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
                            setcookie("user_code", $user->getCode(), time() + (86400 * 365), "/");
                            $this->session->set("user_code", $user->getCode());
                        } else {
                            $this->session->set("user_code", $user->getCode());
                        }
                        return $this->redirect("/{$user->getUsername()}");
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

        if (isset($_POST['submit'])) {
            
            $errors = [];
            $isFormValid = true;

            if ($_POST["username"] == "" || $_POST["email"] == "" || $_POST["password"] == "" || $_POST["repeat_password"] == "") { 
                $isFormValid = false;
                $errors[] = "Fill empty inputs";
            } else {
                $users = $this->getDoctrine()
                            ->getRepository(Users::class)
                            ->findAll();
                
                foreach ($users as $user) {
                    if($_POST["username"] === $user->getUsername()){
                        $isFormValid = false;
                        $errors[] = "This username is already taken";
                    } 
                    if($_POST["email"] === $user->getEmail()){
                        $isFormValid = false;
                        $errors[] = "This email is already taken";
                    }
                }

                if (strlen($_POST["password"]) < 6) {
                    $isFormValid = false;
                    $errors[] = "Password must contain at least 6 characters";
                } elseif ($_POST["password"] !== $_POST["repeat_password"]) {
                    $isFormValid = false;
                    $errors[] = "Passwords are different";
                }
            }
            
            if($isFormValid){
                $entityManager = $this->getDoctrine()->getManager();

                $user = new Users();
                $user->setUsername($_POST['username']);
                $user->setPassword($_POST['password']);
                $user->setEmail($_POST["email"]);
                $user->setCode(uniqid("") . uniqid("") . uniqid(""));
                $user->setActivated(false);

                $entityManager->persist($user);
                $entityManager->flush();
        
            } else {
                return $this->render("perfect_med/sign_up.html.twig", [
                    "errors" => $errors,
                    "username" => $_POST["username"],
                    "email" => $_POST["email"],
                    "password" => $_POST["password"],
                    "repeat_password" => $_POST["repeat_password"]
                ]);
            }
        }

        return $this->render("perfect_med/sign_up.html.twig");
    }

    /**
     * @Route("/{username}", name="home_page_logged")
     */
    public function home_page_logged($username) {

        if(isset($_POST['logged_out'])){
            setcookie("user_code", "", time()-1, "/");
            if ($this->session->get("user_code")) {
                $this->session->remove("user_code");
            }
            return $this->redirectToRoute("home_page");
        }
        
        if(isset($_COOKIE['user_code']) || $this->session->get("user_code")){
                 $users = $this->getDoctrine()
                            ->getRepository(Users::class)
                            ->findAll();

                foreach($users as $user){
                    if (isset($_COOKIE["user_code"])) {
                        if($_COOKIE["user_code"] === $user->getCode()){
                            return $this->render("perfect_med/index.html.twig", ["logged" => true]);
                        }
                    } elseif($this->session->get("user_code") === $user->getCode()){
                        return $this->render("perfect_med/index.html.twig", ["logged" => true]);
                    }
                }
                
            return $this->redirectToRoute("sign_in");

        } else {
            return $this->redirectToRoute("home_page");
        }   
        
    }
}