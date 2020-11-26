<?php

namespace App\Controller;

use App\Entity\Question;
use App\Repository\QuestionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class QuestionController extends AbstractController
{
    private $logger;
    private $isDebug;
    private $entityManager;
    private $questionRepository;

    public function __construct(LoggerInterface $logger, bool $isDebug, EntityManagerInterface $entityManager, QuestionRepository $questionRepository)
    {
        $this->logger = $logger;
        $this->isDebug = $isDebug;
        $this->entityManager = $entityManager;
        $this->questionRepository = $questionRepository;
    }


    /**
     * @Route("/", name="app_homepage")
     */
    public function homepage()
    {
        $questions = $this->questionRepository->findAllAskedQuestionsOrderedByNewest();

        return $this->render('question/homepage.html.twig', [
            'questions' => $questions
        ]);
    }

    /**
     * @Route("/questions/new")
     */
    public function new()
    {
        return new Response("{$question->getId()} - {$question->getSlug()}");
    }

    /**
     * @Route("/questions/{slug}", name="app_question_show")
     * @param Question $question
     * @return Response
     */
    public function show(Question $question): Response
    {
        if ($this->isDebug) {
            $this->logger->info('We are in debug mode!');
        }

        $answers = [
            'Make sure your cat is sitting `purrrfectly` still 🤣',
            'Honestly, I like furry shoes better than MY cat',
            'Maybe... try saying the spell backwards?',
        ];

        return $this->render('question/show.html.twig', [
            'question' => $question,
            'answers' => $answers
        ]);
    }

    /**
     * @Route("/question/{slug}/vote", name="app_question_vote", methods={"POST"})
     */
    public function questionVote(Question $question, Request $request)
    {
        $direction = $request->request->get('direction');

        if($direction === 'up'){
            $question->upVotes();
        }

        if ($direction === 'down'){
            $question->downVotes();
        }

        $this->entityManager->flush();

        return $this->redirectToRoute('app_question_show', [
            'slug' => $question->getSlug()
        ]);
    }
}
