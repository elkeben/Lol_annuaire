<?php


namespace App\Controller;

use App\Entity\Contact;
use App\Entity\User;
use App\Form\ContactType;
use App\Form\RegisterType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{


    // fonction qui sert à faire le rendu du profile
    /**
     * @Route("/profile", name="profile")
     * @return Response
     */

    public function profile(){

        $userObject = $this->getUser();
        $view = $this->renderView("pages/profile.html.twig",['user'=>$userObject]);
        return new Response($view);
    }

    // route qui permet de créer un formulaire et d'envoyer un mailer hardcodé
    /**
     * @Route("/contact", name="contact")
     *
     * @param Request $request
     * @param EntityManagerInterface $em
     * @param MailerInterface $mailer
     * @return Response
     * @throws TransportExceptionInterface
     */
    public function contact(Request $request, EntityManagerInterface $em, MailerInterface $mailer)
    {
        $contact = new Contact();
        $form = $this->createForm(ContactType::class, $contact);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($contact);
            $em->flush();
            // email hardcodé
            $email= new Email();
            $email
                ->from('cacaglouglou@gmail.com')
                ->to('benjaminbleuwart@gmail.com')
                ->subject('Merci pour votre mail !')
                ->text('Sending mail is fun !')
                ->html('<p>Merci pour votre mail vous recevrez une réponse le plus rapidement possible !</p>');

            $mailer->send($email);

            return $this->redirectToRoute('contactMessage');
        }
        return $this->render('pages/contact.html.twig', ['contactForm' => $form->createView()]);
    }



    //route qui renvoit sur une nouvelle page quand la personne à envoyer le formulaire de contact
    /**
     * @Route("/contactMessage", name="contactMessage")
     * @return Response
     */

    public function ContactMessage()
    {


        $view = $this->renderView("pages/ContactMessage.html.twig");
        return new Response($view);
    }
}
