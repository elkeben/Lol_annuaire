<?php


namespace App\Controller;

use App\Entity\Answer;
use App\Entity\Champion;
use App\Entity\Contact;
use App\Entity\Message;
use App\Entity\User;
use App\Form\AnswerType;
use App\Form\ContactType;
use App\Form\MessageType;
use App\Form\SearchFormType;
use App\Repository\ChampionRepository;
use App\search\Search;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;

class DefaultController extends AbstractController
{

    /**
     * @Route("/", name="home")
     * @param Request $request
     * @param ChampionRepository $championRepository
     * @return Response
     */
    public function home( Request $request, ChampionRepository $championRepository)
    {

        $search= new Search();
        $searchForm = $this->createForm(SearchFormType::class,$search);
        $searchForm->handleRequest($request);

        if($searchForm->isSubmitted() && $searchForm->isValid()){

            $result = $championRepository->search($search);

            if($result == null){

                $this->addFlash('erreur', 'Aucun champion ne correspond à ce nom');

            }
            return  $this->render('pages/champions.html.twig',['champions'=> $result,

                'searchForm' => $searchForm->createView()
            ]);
        }
        $lastThree = $championRepository->findWithPhotos(3);
        return $this->render('pages/home.html.twig', ['champions' => $lastThree,'searchForm' => $searchForm->createView()]);

    }


    /**
     * @Route("/champions", name="champions")
     * @param Request $request
     * @param ChampionRepository $championRepository
     * @param PaginatorInterface $paginator
     * @return Response
     */
    public function champions(Request $request, ChampionRepository $championRepository, PaginatorInterface $paginator)
    {
        $search= new Search();
        $searchForm = $this->createForm(SearchFormType::class,$search);
        $searchForm->handleRequest($request);

        if($searchForm->isSubmitted() && $searchForm->isValid()){

            $result = $championRepository->search($search);

            $result = $paginator->paginate(
                $result,
                $request->query->getInt('page',1),
                9
            );
            return  $this->render('pages/champions.html.twig',['champions'=> $result,

                'searchForm' => $searchForm->createView()]);

        }
        $champions = $championRepository->findAllChampions();
        $champions = $paginator->paginate(
            $champions,
            $request->query->getInt('page',1),
            9
        );
        return $this->render('pages/champions.html.twig', ['champions' => $champions,'searchForm' => $searchForm->createView()]);
    }



    /**
     * @Route("/role/{name}", name="showByRole")
     * @param Request $request
     * @param $name
     * @param ChampionRepository $championRepository
     * @param PaginatorInterface $paginator
     * @return Response
     */
    public function rolePage( Request $request, $name, ChampionRepository $championRepository, PaginatorInterface $paginator)
    {
        $search= new Search();
        $searchForm = $this->createForm(SearchFormType::class,$search);
        $searchForm->handleRequest($request);

        if($searchForm->isSubmitted() && $searchForm->isValid()){

            $result = $championRepository->search($search);

            $result = $paginator->paginate(
                $result,
                $request->query->getInt('page',1),
                9
            );

            return  $this->render('pages/champions.html.twig',['champions'=> $result,

                'searchForm' => $searchForm->createView()]);
        }
        $champions = $championRepository->findBy(array('role' => $name));
        $champions = $paginator->paginate(
            $champions,
            $request->query->getInt('page',1),
            9
        );
        return $this->render('pages/champions.html.twig', ['champions' => $champions,'searchForm' => $searchForm->createView()]);

    }


    /**
     * @Route("/letter/{letter}", name="showByLetter")
     * @param Request $request
     * @param $letter
     * @param ChampionRepository $championRepository
     * @param PaginatorInterface $paginator
     * @return Response
     */
    public function letterPage(Request $request, $letter, ChampionRepository $championRepository, PaginatorInterface $paginator)
    {
        $search= new Search();
        $searchForm = $this->createForm(SearchFormType::class,$search);
        $searchForm->handleRequest($request);

        if($searchForm->isSubmitted() && $searchForm->isValid()){

            $result = $championRepository->search($search);

            $result = $paginator->paginate(
                $result,
                $request->query->getInt('page',1),
                10
            );

            return  $this->render('pages/champions.html.twig',['champions'=> $result,

                'searchForm' => $searchForm->createView()]);
        }
        $champions = $championRepository->findByLetter($letter);
        $champions = $paginator->paginate(
            $champions,
            $request->query->getInt('page',1),
            9
        );

    return $this->render('pages/champions.html.twig', ['champions' => $champions,'searchForm'=>$searchForm->createView()]);

    }


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

    /**
     * @Route("/contactMessage", name="contactMessage")
     * @return Response
     */

    public function ContactMessage()
    {


        $view = $this->renderView("pages/ContactMessage.html.twig");
        return new Response($view);
    }


    /**
     * @Route("/singleChampion/{slug}", name="singleChampion")
     * @param Champion $champion
     * @param Request $request
     * @param EntityManagerInterface $em
     * @return Response
     */
    public function singleChampion(Champion $champion, Request $request, EntityManagerInterface $em)
    {
        $user= $this->getUser();
        $message = new Message();
        $message->setChampion($champion);
        $message->setUser($user);
        $messageForm = $this->createForm(MessageType::class, $message);
        $messageForm->handleRequest($request);

        if ($messageForm->isSubmitted() && $messageForm->isValid()) {
            $em->persist($message);
            $em->flush();
            return $this->redirectToRoute('singleChampion', [
                'slug' => $champion->getSlug(),
                'messageForm' => $messageForm->createView()]);
        }

        $answerForm = $this->createForm(AnswerType::class, null, ['action' => $this->generateUrl('postAnswer')]);

        return $this->render('pages/singleChampion.html.twig', [
            'champion' => $champion,
            'user' =>$user,
            'messageForm' => $messageForm->createView(),
            'answerForm' => $answerForm->createView()
        ]);

    }


    /**
     * @Route("/post/answer", name="postAnswer", condition="request.isXmlHttpRequest()", methods={"POST"})
     * @param Request $request
     * @param EntityManagerInterface $em
     * @return AccessDeniedException|Response
     */
    public function postAnswer(Request $request, EntityManagerInterface $em){

        $answer = new Answer();
        $answerForm = $this->createForm(AnswerType::class, $answer);
        $answerForm->handleRequest($request);
        if ($this->getUser() !== $answer->getMessage()->getChampion()) {
            return new AccessDeniedException("you're not the OP");
        }

        if ($answerForm->isSubmitted() && $answerForm->isValid()) {
            $em->persist($answer);
            $em->flush();
        }

        return $this->render('pages/element/answer.html.twig', ['answer' => $answer]);

    }



}


