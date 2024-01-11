<?php

namespace App\Controller;

use App\Repository\MovieRepository;
use App\Entity\Movie;
use App\Form\MovieFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MoviesController extends AbstractController
{

    // private $em;
    // public function __construct(EntityManagerInterface $em)
    // {
    //     $this->em = $em;
    // }

    private $em;
    private $movieRepository;
    public function __construct(MovieRepository $movieRepository, EntityManagerInterface $em)
    {
         $this->movieRepository = $movieRepository;
         $this->em = $em;
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

    #[Route('/movies/create', name: 'create_movie')]
    public function create(Request $request): Response
    {
        $movie = new Movie;
        $form = $this->createForm(MovieFormType::class, $movie);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) 
        {
            $newMovie = $form->getData();
            
            $imagePath = $form->get('imagePath')->getData();
            if ($imagePath)
            {
                $newFileName = uniqid() . '.' . $imagePath->guessExtension();

                try 
                {
                    $imagePath->move(
                        $this->getParameter('kernel.project_dir') . '/public/uploads',
                        $newFileName
                    );
                } catch (FileException $e)
                {
                    return new Response($e->getMessage());
                }

                $newMovie->setImagePath('/uploads/' . $newFileName);
            }

            $this->em->persist($newMovie);
            $this->em->flush();

            return $this->redirectToRoute('movies');
        }

        return $this->render('movies/create.html.twig', [
            'form' => $form->createView()
        ]);
    }

    

    #[Route('/movies/edit/{id}', name: 'edit_movie')]
    public function edit($id, Request $request): Response
    {
        $movie = $this->movieRepository->find($id);
        $form = $this->createForm(MovieFormType::class, $movie);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $imagePath = $form->get('imagePath')->getData();

            if ($imagePath) {
                // Handle new image upload
                $newFileName = uniqid() . '.' . $imagePath->guessExtension();

                try {
                    $imagePath->move(
                        $this->getParameter('kernel.project_dir') . '/public/uploads',
                        $newFileName
                    );
                } catch (FileException $e) {
                    return new Response($e->getMessage());
                }

                // Remove the old image if it exists
                if ($movie->getImagePath() !== null) {
                    $oldImagePath = $this->getParameter('kernel.project_dir') . '/public' . $movie->getImagePath();
                    if (file_exists($oldImagePath)) {
                        unlink($oldImagePath);
                    }
                }

                $movie->setImagePath('/uploads/' . $newFileName);
            }

            // Update other fields
            $movie->setTitle($form->get('title')->getData());
            $movie->setReleaseYear($form->get('releaseYear')->getData());
            $movie->setDescription($form->get('description')->getData());

            $this->em->flush();

            return $this->redirectToRoute('movies');
        }

        return $this->render('movies/edit.html.twig', [
            'movie' => $movie,
            'form' => $form->createView(),
        ]);
    }


    #[Route('/movies/delete/{id}', methods: ['GET', 'DELETE'], name: 'delete_movie')]
    public function delete($id): Response
    {
        $movie = $this->movieRepository->find($id);
        $this->em->remove($movie);
        $this->em->flush();

        return $this->redirectToRoute('movies');
    }



    #[Route('/movies/{id}', methods: ["GET"], name: 'show_movie')]
    public function show($id): Response
    {
        $movie = $this->movieRepository->find($id);

        return $this->render('movies/show.html.twig', [
            'movie' => $movie
        ]);
    }
}
