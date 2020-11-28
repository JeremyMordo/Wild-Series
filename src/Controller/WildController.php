<?php
// src/Controller/WildController.php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Program;
use App\Entity\Category;
use App\Entity\Season;
use App\Entity\Episode;


/**
* @Route("/wild", name="wild_")
*/
class WildController extends AbstractController
{
    /**
    *   Show all rows from Program’s entity
    *
    * @Route("/", name="index")
    * @return Response A response instance
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
        return $this->render(
            'wild/index.html.twig',
            ['programs' => $programs]
        );
    }
    
    /**
    *   Getting a program with a formatted slug for title
    *
    * @param string $slug The slugger
    * @Route("/show/{slug}", defaults={"slug" = null}, name="show")
    * @return Response
    */
    public function show(?string $slug):Response
    {
        if (!$slug) {
            throw $this
                ->createNotFoundException('No slug has been sent to find a program in program\'s table.');
        }
        $slug = preg_replace(
            '/-/',
            ' ', ucwords(trim(strip_tags($slug)), "-")
        );
        $program = $this->getDoctrine()
            ->getRepository(Program::class)
            ->findOneBy(['title' => mb_strtolower($slug)]);

        return $this->render('wild/show.html.twig', [
            'program' => $program,
        ]);
    }

    /**
    *   Show all programs from a category
    * 
    * @param string $categoryName the category name
    * @Route("/category/{categoryName}", name="show_category")
    * @return Response
    */
    public function showByCategory(string $categoryName): Response
    {
        if (!$categoryName) {
            throw $this
                ->createNotFoundException('No '.$categoryName.' has been sent to find a category in category\'s table.');
        }
        $category = $this->getDoctrine()
        ->getRepository(Category::class)
        ->findBy(['name' => $categoryName]);

        $programsByCategory = $this->getDoctrine()
            ->getRepository(Program::class)
            ->findBy(
                ['category' => $category],
                ['id' => 'DESC'],
                3
            );

        return $this->render('wild/category.html.twig', [
            'categoryName' => $categoryName, 
            'programsByCategory' => $programsByCategory]);
    }

    /**
    *   Show all seasons from a program
    *
    * @param string $slug The slugger
    * @Route("/program/{slug}", name="program")
    * @return Response
    */
    public function showProgram(string $slug): Response
    {
        if (!$slug) {
            throw $this
                ->createNotFoundException('No slug has been sent to find a program in program\'s table.');
        }
        $slug = preg_replace(
            '/-/',
            ' ', ucwords(trim(strip_tags($slug)), "-")
        );
        $program = $this->getDoctrine()
            ->getRepository(Program::class)
            ->findOneBy(['title' => mb_strtolower($slug)]);
        if (!$program) {
            throw $this->createNotFoundException(
                'No program with '.$slug.' title, found in program\'s table.'
            );
        }
        $seasons = $program->getSeason();

        return $this->render('wild/showprogram.html.twig', [
            'program' => $program,
            'seasons' => $seasons,
        ]);
    }

    /**
    *   Show all details from a season
    *
    * @param int $id of the season
    * @Route("/program/{programId}/season/{seasonId}", name="program_season_show")
    * @return Response
    */
    public function showSeason(int $programId, int $seasonId): Response
    {
        if (!$seasonId) {
            throw $this
                ->createNotFoundException('No id has been sent to find a season in season\'s table.');
        }
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

        return $this->render('wild/showseason.html.twig', [
            'season' => $season,
            'program' => $program,
            'episodes' => $episodes,
        ]);
    }

    /**
    *   Show all details from an episode
    *
    * @param Episode $episode
    * @Route("/program/{programId}/season/{seasonId}/episode/{episodeId}", name="program_season_episode_show")
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

        return $this->render('wild/showepisode.html.twig', [
            'season' => $season,
            'program' => $program,
            'episode' => $episode, 
        ]);
    }
}