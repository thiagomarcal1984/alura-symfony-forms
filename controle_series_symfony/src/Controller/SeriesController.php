<?php

namespace App\Controller;

use App\Entity\Series;
use App\Repository\SeriesRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SeriesController extends AbstractController
{
    public function __construct(
        private SeriesRepository $seriesRepository,
        // EntityManager foi injetado para RECUPERAR o objeto.
        private EntityManagerInterface $entityManager, 
    )
    {
    }

    #[Route('/series', name: 'app_series', methods: ['GET'])]
    public function index(): Response
    {
        $seriesList =  $this->seriesRepository->findAll();

        return $this->render('series/index.html.twig', [
            'seriesList' => $seriesList,
        ]);
    }

    #[Route('/series/create', name: 'app_series_form', methods: ['GET'])]
    public function addSeriesForm() : Response {
        return $this->render('/series/form.html.twig');
    }

    #[Route('/series/create', name: 'app_add_series', methods: ['POST'])]
    public function addSeries(Request $request) : Response {
        $seriesName = $request->request->get('name');
        $series = new Series($seriesName);
        $this->seriesRepository->save($series, true);
        return new RedirectResponse('/series');
    }

    #[Route('/series/delete', methods: ['POST'])]
    public function deleteSeries(Request $request) : Response {
        $id = $request->query->get('id');
        // getPartialReference recupera o objeto que conterá apenas o ID.
        $series = $this->entityManager->getPartialReference(Series::class, $id);
        $this->seriesRepository->remove($series, flush: true);
        return new RedirectResponse('/series');
    }
}
