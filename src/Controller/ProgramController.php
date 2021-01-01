<?php
// src/Controller/ProgramController.php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Program;
use App\Entity\User;
use App\Entity\Category;
use App\Entity\Season;
use App\Entity\Episode;
use App\Form\ProgramType;
use App\Repository\ProgramRepository;
use App\Service\Slugify;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use App\Form\SearchProgramFormType;
use Symfony\Component\HttpFoundation\Session\SessionInterface;


/**
* @Route("/programs", name="programs_")
*/

class ProgramController extends AbstractController
{
    /**
     * @Route("/", name="index")
     */
    public function index(Request $request, ProgramRepository $programRepository): Response
    {
        $form = $this->createForm(SearchProgramFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $search = $form->getData()['search'];
            $programs = $programRepository->findLikeName($search);

        } else {
            $programs = $programRepository->findAll();
        }

        if (!$programs) {
            throw $this->createNotFoundException(
            'No program found in program\'s table.'
            );
        }
        return $this->render('program/index.html.twig', [
            'programs' => $programs,
            'form' => $form->createView(),
            ]);
    }

    /**
     * The controller for the category add form
     *
     * @Route("/new", name="new")
     * @param Slugify $slugify
     * @param Request $request
     * @return Response
     */
    public function new(Request $request, Slugify $slugify, MailerInterface $mailer) : Response
    {
        $program = new Program();

        $form = $this->createForm(ProgramType::class, $program);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();

            $slug = $slugify->generate($program->getTitle());        
            $program->setSlug($slug);
            
            $entityManager->persist($program);

            $program->setOwner($this->getUser());

            $entityManager->flush();

            $this->addFlash('success', 'La série a été créée');

            $email = (new Email())
                ->from($this->getParameter('mailer_from'))
                ->to('your_email@example.com')
                ->subject('Une nouvelle série vient d\'être publiée !')
                ->html($this->renderView('program/newProgramEmail.html.twig', ['program' => $program, $email='program']));

            $mailer->send($email);

            return $this->redirectToRoute('programs_index');
        }
        return $this->render('program/new.html.twig', [
            "form" => $form->createView(),
        ]);
    }

    /**
     * @Route("/{slug}/edit", name="edit", methods={"GET", "POST"})
     * @ParamConverter("program", class="App\Entity\Program", options={"mapping": {"slug": "slug"}})
     * @return Response
     */
    public function edit(Request $request, Program $program): Response
    {
        $form = $this->createForm(ProgramType::class, $program);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            $this->addFlash('success', 'La série a été modifié');

            return $this->redirectToRoute('programs_index');
        }

        if (!($this->getUser() == $program->getOwner())) {
            // If not the owner, throws a 403 Access Denied exception
            throw new AccessDeniedException('Only the owner can edit the program!');
        }
        
        return $this->render('program/edit.html.twig', [
            'program' => $program,
            'form' => $form->createView(),
        ]);
    }



    /**
    *
    * @Route("/{slug}", name="show")
    * @ParamConverter("program", class="App\Entity\Program", options={"mapping": {"slug": "slug"}})
    * @return Response
    */
    public function show(Program $program):Response
    {
        $seasons = $program->getSeason();

        return $this->render('program/show.html.twig', [
            'program' => $program,
            'seasons' => $seasons
        ]);
    }

    /**
    *   Show all details from a season
    *
    * @Route("/{programId}/seasons/{seasonId}", name="season_show")
    * @ParamConverter("program", class="App\Entity\Program", options={"mapping": {"programId": "id"}})
    * @ParamConverter("season", class="App\Entity\Season", options={"mapping": {"seasonId": "id"}})
    * @return Response
    */
    public function showSeason(Program $program, Season $season): Response
    {
        $episode = $season->getEpisode();

        return $this->render('program/showseason.html.twig', [
            'season' => $season,
            'program' => $program,
            'episode' => $episode,
        ]);
    }

    /**
    *   Show all details from an episode
    *
    * @Route("{programId}/season/{seasonId}/episode/{episodeId}", name="episode_show")
    * @ParamConverter("program", class="App\Entity\Program", options={"mapping": {"programId": "id"}})
    * @ParamConverter("season", class="App\Entity\Season", options={"mapping": {"seasonId": "id"}})
    * @ParamConverter("episode", class="App\Entity\Episode", options={"mapping": {"episodeId": "id"}})
    * @return Response
    */
    public function showEpisode(Program $program, Season $season, Episode $episode): Response
    {
        return $this->render('program/showepisode.html.twig', [
            'season' => $season,
            'program' => $program,
            'episode' => $episode, 
        ]);
    }

    /**
     * @Route("/{id}", name="delete", methods={"DELETE"})
    * @ParamConverter("program", class="App\Entity\Program", options={"mapping": {"id": "id"}})
     */
    public function delete(Request $request, Program $program): Response
    {
        if ($this->isCsrfTokenValid('delete'.$program->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($program);
            $entityManager->flush();

            $this->addFlash('danger', 'La série a été supprimée');
        }

        return $this->redirectToRoute('program_index');
    }

    /**
     * @Route("/{id}/watchlist", name="watchlist", methods={"GET","POST"})
     * @ParamConverter("program", class="App\Entity\Program", options={"mapping": {"id": "id"}})
     * @param Program $program
     * @param EntityManagerInterface $entityManager
     * @return Response
     */
    public function addToWatchlist(Request $Request, Program $program, EntityManagerInterface $entityManager): Response
        {
            if ($this->getUser()->getWatchlist()->contains($program)) {
                $this->getUser()->removeWatchlist($program);
            }
            else {
                $this->getUser()->addWatchlist($program);
            }
    
            $entityManager->flush();
    
            return $this->json([
                'isInWatchlist' => $this->getUser()->isInWatchlist($program)
            ]);
        }
}