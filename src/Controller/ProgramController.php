<?php
// src/Controller/ProgramController.php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
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
    * @param string $slug The slugger
    * @Route("/{id}", requirements={"id" = "\d+"}, name="show")
    * @return Response
    */
    public function show(int $id):Response
    {
        $id = preg_replace(
            '/-/',
            ' ', ucwords(trim(strip_tags($id)), "-")
        );
        $program = $this->getDoctrine()
            ->getRepository(Program::class)
            ->findOneBy(['id' => $id]);
        $seasons = $program->getSeason();

        return $this->render('program/show.html.twig', [
            'program' => $program,
            'seasons' => $seasons
        ]);
    }

    /**
    *   Show all details from a season
    *
    * @param int $id of the season
    * @Route("/{programId}/season/{seasonId}", name="season_show")
    * @return Response
    */
    public function showSeason(int $programId, int $seasonId): Response
    {
        $season = $this->getDoctrine()
            ->getRepository(Season::class)
            ->findOneBy(
                ['id' => $seasonId]
            );

        $program = $this->getDoctrine()
            ->getRepository(Program::class)
            ->findOneBy(
                ['id' => $programId]
            );    

        $episodes = $season->getEpisode();

        return $this->render('program/showseason.html.twig', [
            'season' => $season,
            'program' => $program,
            'episodes' => $episodes,
        ]);
    }

    /**
    *   Show all details from an episode
    *
    * @param Episode $episode
    * @Route("/program/{programId}/season/{seasonId}/episode/{episodeId}", name="season_episode_show")
    * @return Response
    */
    public function showEpisode(int $programId, int $seasonId, int $episodeId): Response
    {
        $program = $this->getDoctrine()
        ->getRepository(Program::class)
        ->findOneBy(
            ['id' => $programId]
        );
        $season = $this->getDoctrine()
        ->getRepository(Season::class)
        ->findOneBy(
            ['id' => $seasonId]
        );
        $episode = $this->getDoctrine()
        ->getRepository(Episode::class)
        ->findOneBy(
            ['id' => $episodeId]
        );
        return $this->render('program/showepisode.html.twig', [
            'season' => $season,
            'program' => $program,
            'episode' => $episode, 
        ]);
    }
}