<?php

namespace App\Controller\Admin;

use App\Entity\CarCount;
use App\Form\TatbestandCountFormType;
use Doctrine\ORM\EntityManagerInterface;
use Dompdf\Dompdf;
use Dompdf\Options;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Form\Filter\Type\DateTimeFilterType;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use Symfony\Component\Routing\Annotation\Route;

use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Filter\DateTimeFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\TextFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\NumericFilter;

class CarCountCrudController extends AbstractCrudController
{
    private EntityManagerInterface $entityManager;
    private array $filters = [];

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public static function getEntityFqcn(): string
    {
        return CarCount::class;
    }

    public function configureFields(string $pageName): iterable
    {
        $this->entityManager->getRepository(CarCount::class)
            ->createQueryBuilder('c')
            ->leftJoin('c.tatbestandCounts', 'tc')
            ->addSelect('tc')
            ->leftJoin('tc.tatbestand', 't')
            ->addSelect('t')
            ->getQuery()
            ->getResult();

        return [
            NumberField::new('id', 'ID'),
            TextField::new('streetName'),
            NumberField::new('latitude', 'Latitude')->setCustomOption('step', 0.000001),
            NumberField::new('longitude', 'Longitude')->setCustomOption('step', 0.000001),
//            NumberField::new('count', 'Zählung')->onlyOnIndex(),
            DateTimeField::new('createAt')->setFormat('dd.MM.yyyy HH:mm'),
            TextField::new('user.email', 'Benutzer')
                ->formatValue(fn($value, $entity) => $entity->getUser()->getFirstname().' '.$entity->getUser()->getLastname().' ('.$value.')'),
            CollectionField::new('tatbestandCounts', 'Tatbestand-Zählungen') // Beziehung zu tatbestand_count
            ->setTemplatePath('admin/tatbestand_counts.html.twig') // Optional: eigenes Template für Anzeige
            // ->onlyOnDetail()// Nur im Detail-View sichtbar
            ->setEntryType(TatbestandCountFormType::class) // Dein bestehendes Formular für TatbestandCounts
            ->allowAdd(true) // Erlaube Hinzufügen neuer TatbestandCounts
            ->allowDelete(true)// Erlaube Löschen von TatbestandCounts
        ];
    }

    public function configureActions(Actions $actions): Actions
    {
        $request = $this->container->get('request_stack')->getCurrentRequest();
        $queryParams = $request->query->all(); // Alle Query-Parameter abrufen

        return $actions
            ->add(Crud::PAGE_INDEX, Action::new('pdfExport')
                ->linkToRoute('admin_carcount_pdf', $queryParams)
                ->setLabel('Export PDF')
                ->setIcon('bi bi-filetype-pdf')
            );
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(DateTimeFilter::new('createAt', 'Datum von/bis')) // Datum filtern
            ->add(TextFilter::new('street_name', 'Straßenname')) // Nach Straßenname filtern
            ->add(EntityFilter::new('user', 'Benutzer')); // Nach Benutzer filtern
            // ->add(NumericFilter::new('count', 'Anzahl der Autos')) // Nach Anzahl filtern
            // ->add(EntityFilter::new('tatbestandCounts.tatbestand', 'Tatbestand')); // Tatbestand-Filter
    }

    #[Route('/admin/carcount/pdf', name: 'admin_carcount_pdf')]
    public function exportPdf(Request $request): Response
    {
        // Initialisiere DomPDF
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isPhpEnabled', true);

        $dompdf = new Dompdf($options);

        $repository = $this->entityManager->getRepository(CarCount::class);

        $filters = $request->query->all();
        $filters = $filters['routeParams']['filters'] ?? [];

        $queryBuilder = $repository->createQueryBuilder('c');

        if (!empty($filters['street_name'])) {
            $value = $filters['street_name']['value'] ?? [];
            $queryBuilder->andWhere('c.street_name LIKE :street_name')
                ->setParameter('street_name', '%' . $value . '%');
        }

        if (!empty($filters['user'])) {
            $value = $filters['user']['value'] ?? [];
            $queryBuilder->andWhere('c.user = :user')
                ->setParameter('user', $value);
        }

        if (!empty($filters['count'])) {
            $value = $filters['count']['value'] ?? [];
            $filters = $filters['routeParams']['filters'] ?? [];
            $queryBuilder->andWhere('c.count = :count')
                ->setParameter('count', $value);
        }

        if (!empty($filters['createAt']['start'])) {
            $value = $filters['createAt']['start']['value'] ?? [];
            $queryBuilder->andWhere('c.createAt >= :start')
                ->setParameter('start', new \DateTime($value));
        }

        if (!empty($filters['createAt']['end'])) {
            $value = $filters['createAt']['end']['value'] ?? [];
            $queryBuilder->andWhere('c.createAt <= :end')
                ->setParameter('end', new \DateTime($value));
        }

        //$carCounts = $queryBuilder->getQuery()->getResult();
        $carCounts = $queryBuilder
            ->select('c', 't', 'tc') // TatbestandCounts und Tatbestand laden
            ->leftJoin('c.tatbestandCounts', 'tc')
            ->leftJoin('tc.tatbestand', 't')
            ->getQuery()
            ->getResult();

        // HTML für das PDF erstellen
        $html = $this->renderView('admin/carcount_pdf.html.twig', [
            'carCounts' => $carCounts,
        ]);

        // PDF generieren
        $dompdf->loadHtml($html);
        $dompdf->render();

        // PDF als Response ausgeben
        return new Response(
            $dompdf->output(),
            200,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="carcount_report.pdf"'
            ]
        );
    }
}
