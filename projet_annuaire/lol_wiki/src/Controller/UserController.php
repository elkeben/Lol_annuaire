<?php


namespace App\Controller;

use App\Entity\User;
use App\Form\RegisterType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{



    /**
     * @Route("/profile", name="profile")
     * @return Response
     */

    public function profile(){

        $userObject = $this->getUser();
        $view = $this->renderView("pages/profile.html.twig",['user'=>$userObject]);
        return new Response($view);
    }


    /**
     * @Route("/editProfile", name="editProfile")
     * @return Response
     */

    public function editProfile(){


        $view = $this->renderView("pages/editProfile.html.twig");
        return new Response($view);
    }
}
