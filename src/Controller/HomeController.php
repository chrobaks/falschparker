<?php

namespace App\Controller;

use App\Entity\CarCount;
use App\Entity\TatbestandCount;
use App\Form\CarCountFilterFormType;
use App\Form\CarCountFormType;
use App\Repository\CarCountRepository;
use App\Repository\TatbestandRepository;
use App\Service\CarCountFormHandler;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class HomeController extends AbstractController
{
    public function __construct(EntityManagerInterface $entityManager)
    {

    }

    #[Route('/', name: 'app_landingpage')]
    public function landingpage(
        Request $request,
        CarCountRepository $carCountRepository,
        CarCountFormHandler $carCountFormHandler
    ): Response
    {
        $filterForm = $this->createForm(CarCountFilterFormType::class, null, [
            'street_choices' => $carCountRepository->findDistinctStreets(),
        ]);
        $filterForm->handleRequest($request);

        if ($filterForm->isSubmitted() && $filterForm->isValid()) {
            $carCounts = $carCountRepository->findAllWithFilters(
                $filterForm->get('street_name')->getData(),
                $filterForm->get('dateStart')->getData(),
                $filterForm->get('dateEnd')->getData(),
                $filterForm->get('tatbestand')->getData()
            );
        } else {
            $carCounts = $carCountRepository->findAllWithFilters();
        }

        return $this->render('home/landing_page.html.twig', [
            'carCounts' => $carCountFormHandler->formateCarCount($carCounts),
            'filterForm' => $filterForm->createView(),
        ]);
    }

    #[Route('/home', name: 'app_home')]
    public function index(
        Security $security,
        Request $request,
        TatbestandRepository $tatbestandRepository,
        CarCountRepository $carCountRepository,
        CarCountFormHandler $carCountFormHandler
    ): Response
    {
        $streetNameOptions = $carCountFormHandler->formateStreetOptions($carCountRepository->findAllStreetData());

        $form = $this->createForm(CarCountFormType::class, null, [
            'street_name_data' => $streetNameOptions,
        ]);

        if ($carCountFormHandler->processForm($request, $security)) {
            $this->addFlash('success', 'Die Daten wurden erfolgreich gespeichert.');
        }

        $formStatus = $carCountFormHandler->getStatus();

        if ($formStatus === 'isValid' || $formStatus === 'error') {
            if ($formStatus === 'error') {
                $this->addFlash('error', 'Die Daten konnten nicht gespeichert werden.');
                $this->addFlash('error', $carCountFormHandler->getErrorJson());
            }

            return $this->redirectToRoute('app_home');

        } else {
            return $this->render('home/index.html.twig', [
                'tatbestand' => $tatbestandRepository->findAll(),
                'form' => $form->createView(),
            ]);
        }
    }
}
