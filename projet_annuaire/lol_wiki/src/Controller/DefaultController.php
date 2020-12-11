<?php


namespace App\Controller;

use App\Entity\Champion;
use App\Entity\Contact;
use App\Form\ContactType;
use App\Repository\ChampionRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DefaultController extends AbstractController
{

    /**
     * @Route("/", name="home")
     * @param ChampionRepository $championRepository
     * @return Response
     */
    public function home(ChampionRepository $championRepository){
        $lastThree = $championRepository->findWithPhotos(3);

        $view = $this->renderView('pages/home.html.twig', ['champions' => $lastThree]);

        return new Response($view);
    }


    /**
     * @Route("/champions", name="champions")
     * @param ChampionRepository $championRepository
     * @return Response
     */
    public function champions(ChampionRepository $championRepository){
        $champions = $championRepository->findAllChampions();

        return $this->render("pages/champions.html.twig", ['champions' => $champions]);

    }
    /**
     * @Route("/singleChampion/{slug}", name="singleChampion")
     * @param Champion $champion
     * @return Response
     */

    public function singleChampion( Champion $champion){


        $view = $this->renderView("pages/singleChampion.html.twig",['champion'=>$champion]);
        return new Response($view);
    }

    /**
     * @Route("/role/{name}", name="showByRole")
     * @param $name
     * @param ChampionRepository $championRepository
     * @return Response
     */
    public function rolePage($name,ChampionRepository $championRepository ) {

        $champions = $championRepository->findBy(array('role' => $name));

        return $this->render('pages/champions.html.twig', ['champions' => $champions]);

    }


    /**
     * @Route("/letter/{letter}", name="showByLetter")
     * @param $letter
     * @param ChampionRepository $championRepository
     * @return Response
     */
    public function letterPage($letter,ChampionRepository $championRepository ) {

        $champions = $championRepository->findByLetter($letter);

        return $this->render('pages/champions.html.twig', ['champions' => $champions]);

    }


    /**
     * @Route("/contact", name="contact")
     *
     * @param Request $request
     * @param EntityManagerInterface $em
     * @return Response
     */
    public function contact(Request $request, EntityManagerInterface $em){
        $contact = new Contact();
        $form = $this->createForm(ContactType::class, $contact);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($contact);
            $em->flush();
            return $this->redirectToRoute('contactMessage');
        }
        return $this->render('pages/contact.html.twig', ['contactForm' => $form->createView()]);
    }

    /**
     * @Route("/contactMessage", name="contactMessage")
     * @return Response
     */

    public function ContactMessage(){


        $view = $this->renderView("pages/ContactMessage.html.twig");
        return new Response($view);
    }




}
