<?php
// src/Controller/ProgramController.php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use App\Entity\Program;
use App\Entity\Category;
use App\Entity\Season;
use App\Entity\Episode;

/**
* @Route("/programs", name="programs_")
*/

class ProgramController extends AbstractController
{
    /**
     * @Route("/", name="index")
     */
    public function index(): Response
    {
        $programs = $this->getDoctrine()
        ->getRepository(Program::class)
        ->findAll();

        if (!$programs) {
            throw $this->createNotFoundException(
            'No program found in program\'s table.'
            );
        }
        return $this->render('program/index.html.twig', [
            'programs' => $programs]);
    }

    /**
    *   Getting a program with a formatted slug for title
    *
    * @Route("/{programId}", name="show")
    * @ParamConverter("program", class="App\Entity\Program", options={"mapping": {"programId": "id"}})
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
}