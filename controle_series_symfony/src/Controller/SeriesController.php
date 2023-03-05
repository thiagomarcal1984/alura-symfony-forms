<?php

namespace App\Controller;

use App\Entity\Series;
use App\Form\SeriesType;
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
        // Praticando a injeção de dependência do EntityManager.
        private EntityManagerInterface $entityManager,
    )
    {
    }

    #[Route('/series', name: 'app_series', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $seriesList =  $this->seriesRepository->findAll();

        return $this->render('series/index.html.twig', [
            'seriesList' => $seriesList,
        ]);
    }

    #[Route('/series/create', name: 'app_series_form', methods: ['GET'])]
    public function addSeriesForm() : Response {
        $seriesForm = $this->createForm(SeriesType::class, new Series(''));
        return $this->renderForm('/series/form.html.twig', compact('seriesForm'));
    }

    #[Route('/series/create', name: 'app_add_series', methods: ['POST'])]
    public function addSeries(Request $request) : Response {
        $series = new Series();
        $this->createForm(SeriesType::class, $series)
            ->handleRequest($request) // Preenche o objeto $series com os dados da requisição.
            // ->isValid() // booleano.
            // ->isSubmitted() // booleano.
            // ->getData() // Retorna cópia do objeto $series preenchido.
        ;
        $this->seriesRepository->save($series, true);
        $this->addFlash(
            'success', 
            "Série \"{$series->getName()}\" incluída com sucesso."
        );
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
        $this->addFlash('success', 'Série removida com sucesso.');
        return new RedirectResponse('/series');
    }

    #[Route('/series/edit/{series}', name: 'app_edit_series_form', methods: ['GET'])]
    public function editSeriesForm(Series $series): Response {
        return $this->render('series/form.html.twig', compact('series'));
    }

    #[Route('/series/edit/{series}', name: 'app_store_series_changes', methods: ['PATCH'])]
    public function storeSeriesChanges(Series $series, Request $request): Response {
        $series->setName($request->request->get('name'));
        $this->entityManager->flush(); // Confirma as alterações no banco.
        $this->addFlash('success', "Série {$series->getName()} editada com sucesso");
        return new RedirectResponse('/series');
    }
}
