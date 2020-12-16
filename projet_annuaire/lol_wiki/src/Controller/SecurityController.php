<?php

namespace App\Controller;

use App\Doctrine\UserCreationListener;
use App\Entity\User;
use App\Form\RegisterType;
use App\Repository\UserRepository;
use App\Service\PhotoUploader;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    /**
     * @Route("/login", name="app_login")
     * @param AuthenticationUtils $authenticationUtils
     * @return Response
     */
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // if ($this->getUser()) {
        //     return $this->redirectToRoute('target_path');
        // }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    /**
     * @Route("/logout", name="app_logout")
     */
    public function logout()
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }


    /**
     * @Route("/register", name="register")
     * @param Request $request
     * @param EntityManagerInterface $em
     * @param UserRepository $userRepository
     * @param UserPasswordEncoderInterface $userPasswordEncoder
     * @param UserCreationListener $userCreationListener
     * @param PhotoUploader $photoUploader
     * @return Response
     */
    public function register(Request $request, EntityManagerInterface $em, UserRepository $userRepository, UserPasswordEncoderInterface $userPasswordEncoder, UserCreationListener $userCreationListener, PhotoUploader $photoUploader){

        $user= new User();
        $user->setRoles(['ROLE_USER']);

        $form = $this->createForm(RegisterType::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $user->setPassword($userPasswordEncoder->encodePassword($user, $user->getPassword()))
                ->eraseCredentials()
            ;
            $photoUploader->uploadFilesFromForm($form);
            $em->persist($user);
            $em->flush();
            return $this->redirectToRoute('app_login',['user'=>$user->getId()]);

        }
        return $this->render('pages/register.html.twig', ['registerForm' => $form->createView() ] );

    }

    /**
     * @Route("/profile/edit/{id<\d+>}", name="editUser")
     * @param User $user
     * @param EntityManagerInterface $em
     * @param Request $request
     * @param PhotoUploader $photoUploader
     * @return RedirectResponse|Response
     */


    public function editUser(User $user, EntityManagerInterface $em, Request $request, PhotoUploader $photoUploader){

        $form= $this->createForm(RegisterType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $photoUploader->uploadFilesFromForm($form);

            $em->persist($user);
            $em->flush();
            return $this->redirectToRoute('profile', ['id'=> $user->getId()]);
        }
        return $this->render('pages/register.html.twig', ['registerForm' => $form->createView()]);

    }


}
