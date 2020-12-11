<?php


namespace App\Controller;



use App\Entity\Champion;
use App\Entity\Competence;
use App\Form\ChampionType;
use App\Repository\ChampionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


class AdminController extends AbstractController
{

    /**
     * @Route("/createChampion", name="createChampion")
     * @param Request $request
     * @param EntityManagerInterface $em
     * @return Response
     */

    public function CreateChampion( Request $request,  EntityManagerInterface $em){

        $champion = new Champion();

        $competence1 = new Competence();
        $champion->addCompetence($competence1);
        $em->persist($competence1);
        $competence2 = new Competence();
        $champion->addCompetence($competence2);
        $em->persist($competence2);
        $competence3 = new Competence();
        $champion->addCompetence($competence3);
        $em->persist($competence3);
        $competence4 = new Competence();
        $champion->addCompetence($competence4);
        $em->persist($competence4);

        $form = $this->createForm(ChampionType::class, $champion);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $imageFile = $form->get('image')->getData();
            if ($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                // this is needed to safely include the file name as part of the URL
                $safeFilename = transliterator_transliterate('Any-Latin; Latin-ASCII; [^A-Za-z0-9_] remove; Lower()', $originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $imageFile->guessExtension();

                // Move the file to the directory where brochures are stored
                try {
                    $imageFile->move(
                        $this->getParameter('images_path'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    // ... handle exception if something happens during file upload
                }
                $form->getData()->setImage($newFilename);

            }
            $em->persist($champion);
            $em->flush();
            return $this->redirectToRoute('listChampion');
        }
        return $this->render('admin/add-champion.html.twig', ['championForm' => $form->createView()]);
    }



    /**
     * @Route("/listChampion", name="listChampion")
     * @param ChampionRepository $championRepository
     * @return Response
     */
    public function ListChampions(ChampionRepository $championRepository){
        $champions = $championRepository->findAllChampions();

        return $this->render("admin/list-champion.html.twig", ['champions' => $champions]);

    }
}




