<?php

namespace App\Controller;

use App\Entity\Series;
use App\Repository\SeriesRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SeriesController extends AbstractController
{
    public function __construct(private SeriesRepository $seriesRepository)
    {
    }

    #[Route('/series', name: 'app_series', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $seriesList = $this->seriesRepository->findAll();
        $session = $request->getSession();
        $successMessage = $session->get('success');
        $session->remove('success');
        $seriesList =  $this->seriesRepository->findAll();

        return $this->render('series/index.html.twig', [
            'seriesList' => $seriesList,
            'successMessage' => $successMessage,
        ]);
    }

    #[Route('/series/create', name: 'app_series_form', methods: ['GET'])]
    public function addSeriesForm() : Response {
        return $this->render(
            '/series/form.html.twig');
    }

    #[Route('/series/create', name: 'app_add_series', methods: ['POST'])]
    public function addSeries(Request $request) : Response {
        $seriesName = $request->request->get('name');
        $series = new Series($seriesName);
        $session = $request->getSession();
        $session->set('success', "Série \"$seriesName\" incluída com sucesso.");
        $this->seriesRepository->save($series, true);
        return new RedirectResponse('/series');
    }

    #[Route(
        '/series/delete/{id}', 
        name: 'app_delete_series', 
        methods: ['DELETE'],
        // O Symfony vai varrer a classe entidade até achar a 'id', depois ele recupera a entidade.
        requirements : ['id' => '[0-9]+'], 
    )]
    public function deleteSeries(int $id, Request $request) : Response {
        $this->seriesRepository->removeById($id);
        $session = $request->getSession();
        $session->set('success', 'Série removida com sucesso.');
        return new RedirectResponse('/series');
    }

    #[Route('/series/edit/{series}', name: 'app_edit_series_form', methods: ['GET'])]
    public function editSeriesForm(Series $series): Response {
        return $this->render('series/form.html.twig', compact('series'));
    }
}
