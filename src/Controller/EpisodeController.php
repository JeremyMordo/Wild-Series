<?php

namespace App\Controller;

use App\Entity\Episode;
use App\Entity\Comment;
use App\Form\EpisodeType;
use App\Form\CommentType;
use App\Repository\EpisodeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\Slugify;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

/**
 * @Route("/episode", name="episode_")
 */
class EpisodeController extends AbstractController
{
    /**
     * @Route("/", name="index", methods={"GET"})
     */
    public function index(EpisodeRepository $episodeRepository): Response
    {
        $episode = new Episode();
        $season = $episode->getSeason();

        return $this->render('episode/index.html.twig', [
        'episodes' => $episodeRepository->findAll(), 'season' => $season]);
    }

    /**
     * @Route("/new", name="new", methods={"GET","POST"})
     * @param Slugify $slugify
     */
    public function new(Request $request, Slugify $slugify, MailerInterface $mailer): Response
    {
        $episode = new Episode();
        $form = $this->createForm(EpisodeType::class, $episode);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();

            $slug = $slugify->generate($episode->getTitle());        
            $episode->setSlug($slug);

            $season=$episode->getSeason();
            $entityManager->persist($episode);
            $entityManager->flush();

            $email = (new Email())
                ->from($this->getParameter('mailer_from'))
                ->to('your_email@example.com')
                ->subject('Un nouvel épisode vient d\'être publié !')
                ->html($this->renderView('episode/newEpisodeEmail.html.twig', ['episode' => $episode, "season" => $season]));

            $mailer->send($email);
            return $this->redirectToRoute('episode_index');
        }

        return $this->render('episode/new.html.twig', [
            'episode' => $episode,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{slug}", name="show", methods={"GET","POST"})
     * @ParamConverter("episode", class="App\Entity\Episode", options={"mapping": {"slug": "slug"}})
     */
    public function show(Episode $episode, Request $request): Response
    {

        $comment = new Comment();
        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($comment);
            
            $user = $this->getUser();
            $comment->setAuthor($user);
            $comment->setEpisode($episode);

            $comment=$episode->getComments();

            $entityManager->flush();

            return $this->redirectToRoute("episode_show", ['slug' => $episode->getSlug()]);
        }
        $comments = $this->getDoctrine()
        ->getRepository(Comment::class)
        ->findBy(
            ['episode' => $episode],
        );

        return $this->render('episode/show.html.twig', [
            'episode' => $episode, 'comments' => $comments, "form" => $form->createView()]);
    }

    /**
    * @Route("/{slug}/{id}", name="deleteComment", methods={"DELETE"})
    * @ParamConverter("comment", class="App\Entity\Comment", options={"mapping": {"id": "id"}})
    * @ParamConverter("episode", class="App\Entity\Episode", options={"mapping": {"slug": "slug"}})
     */
    public function deleteComment(Request $request, Comment $comment, Episode $episode) 
    {
        if ($this->isCsrfTokenValid('delete'.$comment->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($comment);
            $entityManager->flush();
        }

        return $this->redirectToRoute("episode_show", ['slug' => $episode->getSlug()]);
    }

    /**
     * @Route("/{slug}/edit", name="edit", methods={"GET","POST"})
    * @ParamConverter("episode", class="App\Entity\Episode", options={"mapping": {"slug": "slug"}})
    * @param Slugify $slugify
     */
    public function edit(Request $request, Episode $episode, Slugify $slugify): Response
    {
        $form = $this->createForm(EpisodeType::class, $episode);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $form->getData();

            $slug = $slugify->generate($episode->getTitle());        
            $episode->setSlug($slug);
            
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('episode_index');
        }

            
        return $this->render('episode/edit.html.twig', [
            'episode' => $episode,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="delete", methods={"DELETE"})
    * @ParamConverter("episode", class="App\Entity\Episode", options={"mapping": {"id": "id"}})
     */
    public function delete(Request $request, Episode $episode): Response
    {
        if ($this->isCsrfTokenValid('delete'.$episode->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($episode);
            $entityManager->flush();
        }

        return $this->redirectToRoute('episode_index');
    }
}
