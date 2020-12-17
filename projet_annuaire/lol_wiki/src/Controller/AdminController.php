<?php


namespace App\Controller;



use App\Entity\Champion;
use App\Entity\Competence;
use App\Form\ChampionType;
use App\Repository\ChampionRepository;
use App\Service\PhotoUploader;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


class AdminController extends AbstractController
{


    // route uniquement accessible en étant admin ou + qui permet de créer un champion en créant également ses 4 compétences en même temps

    /**
     * @Route("/createChampion", name="createChampion")
     * @param Request $request
     * @param EntityManagerInterface $em
     * @param PhotoUploader $photoUploader
     * @return Response
     */

    public function CreateChampion( Request $request,  EntityManagerInterface $em, PhotoUploader $photoUploader){

        $champion = new Champion();

            // création des 4 compétences
            $competence1 = new Competence();
            $competence1->setNom('');
            $champion->addCompetence($competence1);
            $competence2 = new Competence();
            $competence2->setNom('');
            $champion->addCompetence($competence2);
            $competence3 = new Competence();
            $competence3->setNom('');
            $champion->addCompetence($competence3);
            $competence4 = new Competence();
            $competence4->setNom('');
            $champion->addCompetence($competence4);
            // foreach pour que chaque compétence soit persist
            foreach($champion->getCompetences() as $competence){
                $em->persist($competence);
            }
        // création normale d'un form
        $form = $this->createForm(ChampionType::class, $champion);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // service d'upload d'image
            $photoUploader->uploadFilesFromForm($form);

            $em->persist($champion);
            $em->flush();
            return $this->redirectToRoute('listChampion');
        }
        return $this->render('admin/add-champion.html.twig', ['championForm' => $form->createView()]);
    }


    // fonction pour la suppression d'un champion (+ event dans l'entité champion  pour supprimer l'image

    /**
     * @Route("/listChampion/delete/{id<\d+>}", name="deleteChampion")
     * @param Champion $champion
     * @param EntityManagerInterface $em
     * @return RedirectResponse| Response
     */

    public function deleteChampion(Champion $champion, EntityManagerInterface $em){

        $em->remove($champion);
        $em->flush();

        return $this->redirectToRoute('listChampion');

    }

    // fonction pour l'édit d'un champion

    /**
     * @Route("/listChampion/edit/{id<\d+>}", name="editChampion")
     * @param Champion $champion
     * @param EntityManagerInterface $em
     * @param Request $request
     * @param PhotoUploader $photoUploader
     * @return RedirectResponse|Response
     */

    public function editChampion(Champion $champion, EntityManagerInterface $em, Request $request, PhotoUploader $photoUploader){
        // en ne créant pas d'instance, symfony va récuperer les données du champions grace à son id
        $form= $this->createForm(ChampionType::class, $champion);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // service d'upload d'image
            $photoUploader->uploadFilesFromForm($form);

            $em->persist($champion);
            $em->flush();
            return $this->redirectToRoute('listChampion', ['slug'=> $champion->getSlug()]);
        }
        return $this->render('admin/add-champion.html.twig', ['championForm' => $form->createView()]);

    }

    // fonction également accessible qu'en étant admin, elle permet d'afficher tousles champions
    // et de donner la possibilité de les éditer ou de les supprimer

    /**
     * @Route("/listChampion", name="listChampion")
     * @param ChampionRepository $championRepository
     * @return Response
     */
    public function ListChampions(ChampionRepository $championRepository){

        // cette fonction sert dans un premier temps à afficher tous les champions l'un à la suite
        // de l'autre mais également de les trier par ordre alphabetique
        $champions = $championRepository->findAllChampions();

        return $this->render("admin/list-champion.html.twig", ['champions' => $champions]);
    }
}




