<?php

namespace App\Controller;

use App\Repository\MovieRepository;
use App\Entity\Movie;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MoviesController extends AbstractController
{

    // private $em;
    // public function __construct(EntityManagerInterface $em)
    // {
    //     $this->em = $em;
    // }

    private $movieRepository;
    public function __construct(MovieRepository $movieRepository)
    {
         $this->movieRepository = $movieRepository;
    }

    #[Route('/movies', name: 'movies')]
    public function index(): Response
    {
        $movies = $this->movieRepository->findAll();
        // dd($movies);


        return $this->render('movies/index.html.twig', [
            'movies' => $movies
        ]);

        // $repository = $this->em->getRepository(Movie::class);

        // findAll() - SELECT * FROM movies;
        // $movies = $repository->findAll();

        // find() - SELECT * FROM movies WHERE id = 5;
        // $movies = $repository->find(5);

        // findBy() - SELECT * FROM movies ORDER BY id DESC;
        // $movies = $repository->findBy([], ['id' => 'DESC']);

        // findBy() - SELECT * FROM movies ORDER BY id DESC;
        // $movies = $repository->findOneBy(['id' => 5, 'title' => 'The Dark Knight'], ['id' => 'DESC']);

        // count() - SELECT COUNT() from movies WHERE id = 5
        // $movies = $repository->count(['id' => 5]);

        // $movies = $repository->getClassName();

        // dd($movies);
 
    }

    #[Route('/movies/{id}', methods: ["GET"], name: 'movie')]
    public function show($id): Response
    {
        $movie = $this->movieRepository->find($id);

        return $this->render('movies/show.html.twig', [
            'movie' => $movie
        ]);
    }
}
