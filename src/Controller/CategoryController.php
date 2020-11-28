<?php
// src/Controller/CategoryController.php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Program;
use App\Entity\Category;
use App\Entity\Season;
use App\Entity\Episode;


/**
* @Route("/categories", name="category_")
*/
class CategoryController extends AbstractController
{
    /**
    * Show all categories from Category entity
    *
    * @Route("/", name="index")
    * @return Response A response instance
    */
    public function index(): Response
    {
        $categories = $this->getDoctrine()
        ->getRepository(Category::class)
        ->findAll();

        if (!$categories) {
            throw $this->createNotFoundException(
            'No category found in category\'s table.'
            );
        }

        return $this->render(
            '/category/index.html.twig',
            ['categories' => $categories]);
    }
    
    /**
    * Show all programs from a category
    * 
    * @param string $categoryName the category name
    * @Route("/{categoryName}", name="show")
    * @return Response
    */
    public function show(string $categoryName): Response
    {
        if (!$categoryName) {
            throw $this
                ->createNotFoundException('No '.$categoryName.' has been sent to find a category in category\'s table.');
        }
        $category = $this->getDoctrine()
        ->getRepository(Category::class)
        ->findBy(['name' => $categoryName]);

        $programs = $this->getDoctrine()
            ->getRepository(Program::class)
            ->findBy(
                ['category' => $category],
                ['id' => 'DESC'],
                3
            );
            
        return $this->render('category/show.html.twig', [
            'categoryName' => $categoryName, 
            'programs' => $programs]);
    }
}