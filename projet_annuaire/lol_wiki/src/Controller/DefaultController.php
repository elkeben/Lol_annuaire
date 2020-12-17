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

    // fonction qui sert de point d'entré sur le site

    /**
     * @Route("/", name="home")
     * @param Request $request
     * @param ChampionRepository $championRepository
     * @param PaginatorInterface $paginator
     * @return Response
     */
    public function home( Request $request, ChampionRepository $championRepository, PaginatorInterface $paginator)
    {
        // instance de la barre de recherche suivie du formulaire
        $search= new Search();
        $searchForm = $this->createForm(SearchFormType::class,$search);
        $searchForm->handleRequest($request);

        if($searchForm->isSubmitted() && $searchForm->isValid()){

            $result = $championRepository->search($search);
            // si pas de résultat, on envoit une erreur
            if($result == null){

                $this->addFlash('erreur', 'Aucun champion ne correspond à ce nom');
            }
            $result = $paginator->paginate(
                $result,
                $request->query->getInt('page',1),
                9
            );
            return  $this->render('pages/champions.html.twig',['champions'=> $result,

                'searchForm' => $searchForm->createView()
            ]);
        }
        // on cherche grace à cette fonction les trois derniers champions sortis en fonction de leurs dates
        $lastThree = $championRepository->findWithPhotos(3);
        $lastThree = $paginator->paginate(
            $lastThree,
            $request->query->getInt('page',1),
            9
        );
        return $this->render('pages/home.html.twig', ['champions' => $lastThree,'searchForm' => $searchForm->createView()]);

    }


    // fonction de la page champion

    /**
     * @Route("/champions", name="champions")
     * @param Request $request
     * @param ChampionRepository $championRepository
     * @param PaginatorInterface $paginator
     * @return Response
     */
    public function champions(Request $request, ChampionRepository $championRepository, PaginatorInterface $paginator)
    {
        // on crée une instance de pour la barre de recherche
        $search= new Search();
        // création normale d'un formulaire
        $searchForm = $this->createForm(SearchFormType::class,$search);
        $searchForm->handleRequest($request);

        if($searchForm->isSubmitted() && $searchForm->isValid()){

            $result = $championRepository->search($search);
            // service de pagination pour ne pas avoir trop de résultats sur la même page
            $result = $paginator->paginate(
                $result,
                $request->query->getInt('page',1),
                9
            );
            return  $this->render('pages/champions.html.twig',['champions'=> $result,

                'searchForm' => $searchForm->createView()]);

        }
        // fonction pour afficher tous les champions en les classant par ordre alphabétique
        $champions = $championRepository->findAllChampions();
        // de nouveau une pagination pour éviter que le site crash si la personne ne fait pas de recherche
        $champions = $paginator->paginate(
            $champions,
            $request->query->getInt('page',1),
            9
        );
        return $this->render('pages/champions.html.twig', ['champions' => $champions,'searchForm' => $searchForm->createView()]);
    }

    // fonction qui tri les champions en fonction d'un tag 'role'

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
        // on crée une instance pour la barre de recherche
        $search= new Search();
        //création normale d'un formulaire
        $searchForm = $this->createForm(SearchFormType::class,$search);
        $searchForm->handleRequest($request);

        if($searchForm->isSubmitted() && $searchForm->isValid()){

            $result = $championRepository->search($search);
            // pagination des résultats
            $result = $paginator->paginate(
                $result,
                $request->query->getInt('page',1),
                9
            );

            return  $this->render('pages/champions.html.twig',['champions'=> $result,

                'searchForm' => $searchForm->createView()]);
        }
        // fonction qui va service à trouver tous les champions en fonction du nom de leur role
        $champions = $championRepository->findBy(array('role' => $name));
        // de nouveau une pagination
        $champions = $paginator->paginate(
            $champions,
            $request->query->getInt('page',1),
            9
        );
        return $this->render('pages/champions.html.twig', ['champions' => $champions,'searchForm' => $searchForm->createView()]);

    }

    // fonction qui tri les champions en fonction d'un tag type

    /**
     * @Route("/type/{name}", name="showByType")
     * @param Request $request
     * @param $name
     * @param ChampionRepository $championRepository
     * @param PaginatorInterface $paginator
     * @return Response
     */
    public function typePage( Request $request, $name, ChampionRepository $championRepository, PaginatorInterface $paginator)
    {
        // création d'une instance de la barre de recherche
        $search= new Search();
        // création normale du formulaire
        $searchForm = $this->createForm(SearchFormType::class,$search);
        $searchForm->handleRequest($request);

        if($searchForm->isSubmitted() && $searchForm->isValid()){

            $result = $championRepository->search($search);
            // service de pagination
            $result = $paginator->paginate(
                $result,
                $request->query->getInt('page',1),
                9
            );

            return  $this->render('pages/champions.html.twig',['champions'=> $result,

                'searchForm' => $searchForm->createView()]);
        }
        // fonction qui va trier les champions en fonction du nom de leur type
        $champions = $championRepository->findBy(array('type' => $name));
        $champions = $paginator->paginate(
            $champions,
            $request->query->getInt('page',1),
            9
        );
        return $this->render('pages/champions.html.twig', ['champions' => $champions,'searchForm' => $searchForm->createView()]);

    }

    // fonction pour classe les champions en fonction de leur premiere lettre

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
        // instance de la barre de recherche
        $search= new Search();
        //création normale d'un formulaire
        $searchForm = $this->createForm(SearchFormType::class,$search);
        $searchForm->handleRequest($request);

        if($searchForm->isSubmitted() && $searchForm->isValid()){

            $result = $championRepository->search($search);
            // service de pagination
            $result = $paginator->paginate(
                $result,
                $request->query->getInt('page',1),
                10
            );

            return  $this->render('pages/champions.html.twig',['champions'=> $result,

                'searchForm' => $searchForm->createView()]);
        }
        // fonction qui va trier les champions en fonction  de la variable letter
        $champions = $championRepository->findByLetter($letter);
        // service de pagination
        $champions = $paginator->paginate(
            $champions,
            $request->query->getInt('page',1),
            9
        );

    return $this->render('pages/champions.html.twig', ['champions' => $champions,'searchForm'=>$searchForm->createView()]);

    }

    // fonction qui sert à afficher les données d'un champion et également les messages qui sont liés

    /**
     * @Route("/singleChampion/{slug}", name="singleChampion")
     * @param Champion $champion
     * @param Request $request
     * @param EntityManagerInterface $em
     * @return Response
     */
    public function singleChampion(Champion $champion, Request $request, EntityManagerInterface $em)
    {
        // on récupere l'utilisateur actuelle dans une variable
        $user= $this->getUser();
        // on crée une instance de message
        $message = new Message();
        // on donne la veleur du champion actuelle à la variable $message
        $message->setChampion($champion);
        // on lui donne aussi l'utilisateur en cours
        $message->setUser($user);
        // création normale d'un formulaire
        $messageForm = $this->createForm(MessageType::class, $message);
        $messageForm->handleRequest($request);

        if ($messageForm->isSubmitted() && $messageForm->isValid()) {
            $em->persist($message);
            $em->flush();
            // quand la personne à posté son message, ça le renvoit sur la même page grace au slug du champion
            return $this->redirectToRoute('singleChampion', [
                'slug' => $champion->getSlug(),
                'messageForm' => $messageForm->createView()]);
        }
        // création d'un formulaire de réponse ( en stand By )
        $answerForm = $this->createForm(AnswerType::class, null, ['action' => $this->generateUrl('postAnswer')]);

        return $this->render('pages/singleChampion.html.twig', [
            'champion' => $champion,
            'user' =>$user,
            'messageForm' => $messageForm->createView(),
            'answerForm' => $answerForm->createView()
        ]);

    }

    // fonction pour gérer les réponses à des questions ( en stand by )

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


