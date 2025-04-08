<?php

namespace App\Service;

use App\Entity\CarCount;
use App\Entity\User;
use App\Form\CarCountFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;


class CarCountFormHandler
{
    private EntityManagerInterface $entityManager;
    private FormFactoryInterface $formFactory;
    private Security $security;
    private string $status;
    private $error;

    public function __construct(EntityManagerInterface $entityManager, FormFactoryInterface $formFactory, Security $security)
    {
        $this->entityManager = $entityManager;
        $this->formFactory = $formFactory;
        $this->security = $security;
        $this->status = 'notSubmitted';
        $this->error = [];
    }

    /**
     * Verarbeitet das CarCount-Formular und speichert die Daten, wenn es gültig ist.
     */
    public function processForm(Request $request): ?CarCount
    {
        $user = $this->security->getUser();
        $carCount = new CarCount();
        $carCount->setUser($user);

        $form = $this->formFactory->create(CarCountFormType::class, $carCount);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if (!$form->isValid()) {
                $this->error = $this->getFormErrors($form);
                $this->setStatus('error');
            } else {
                $formData = $request->request->all();
                $streetDetailsString = $formData['carcount_form']['street_details'] ?? null;
                if ($streetDetailsString !== null) {
                    $streetDetails = json_decode($streetDetailsString, true);

                    if (json_last_error() !== JSON_ERROR_NONE) {
                        $this->error = 'Ungültiges JSON in street_details';
                        $this->setStatus('error');
                        return null;
                    }

                    $carCount->setStreetDetails($streetDetails);
                } else {
                    throw new \InvalidArgumentException('Ungültiges JSON übermittelt.');
                }

                foreach ($carCount->getTatbestandCounts() as $tatbestandCount) {
                    $tatbestandCount->setCarCount($carCount);
                }

                $this->setStatus('isValid');
                $this->entityManager->persist($carCount);
                $this->entityManager->flush();

                return $carCount;
            }
        }

        return null;
    }

    /**
     * @return false|string
     */
    public function getErrorJson(): false|string
    {
        return json_encode($this->error, JSON_PRETTY_PRINT);
    }

    /**
     * @param FormInterface $form
     * @return array
     */
    private function getFormErrors(FormInterface $form): array
    {
        $errors = [];

        // Formular-Fehler auf oberster Ebene (globale Fehler) hinzufügen
        foreach ($form->getErrors() as $error) {
            $errors[] = $error->getMessage();
        }

        // Fehler der einzelnen Felder rekursiv sammeln
        foreach ($form->all() as $child) {
            if (!$child->isValid()) {
                foreach ($child->getErrors() as $error) {
                    $errors[$child->getName()][] = $error->getMessage();
                }
            }
        }

        return $errors;
    }

    /**
     * @param array $streetOptions
     * @return array
     */
    public function formateStreetOptions(array $streetOptions): array
    {
        $options = [];

        foreach ($streetOptions as $streetNameOption) {
            $id = $streetNameOption['id'] .'|' . $streetNameOption['longitude'] . '|' . $streetNameOption['latitude'] ;
            $options[$streetNameOption['street_name']] = $id;
        }

        return $options;
    }

    /**
     * @param array $carCounts
     * @return array
     */
    public function formateCarCount(array $carCounts): array
    {
        $data = [];
        foreach ($carCounts as $row) {
            $streetName = $row['streetName'];
            $streetDetails = $row['streetDetails'];
            $latitude = $row['latitude'];
            $longitude = $row['longitude'];
            $createAt = $row['createAt']->format('Y-m-d H:i:s');

            $tatbestandData = [
                'tatbestand' => $row['tatbestandText'],
                'count' => $row['count'],
            ];

            // Suche nach vorhandenen Einträgen für dieselbe Straße und Koordinaten
            $existingStreetKey = null;
            foreach ($data as $index => $entry) {
                if (
                    $entry['streetName'] === $streetName &&
                    $entry['streetDetails'] === $streetDetails &&
                    $entry['latitude'] == $latitude &&
                    $entry['longitude'] == $longitude
                ) {
                    $existingStreetKey = $index;
                    break;
                }
            }

            if ($existingStreetKey !== null) {
                // Details zur vorhandenen Straße hinzufügen
                $existingDetailsIndex = null;
                foreach ($data[$existingStreetKey]['details'] as $index => $detail) {
                    if ($detail['createAt'] === $createAt) {
                        $existingDetailsIndex = $index;
                        break;
                    }
                }

                if ($existingDetailsIndex !== null) {
                    // Tatbestände zu den vorhandenen Details hinzufügen
                    $data[$existingStreetKey]['details'][$existingDetailsIndex]['details'][] = $tatbestandData;
                } else {
                    // Neues Datum hinzufügen
                    $data[$existingStreetKey]['details'][] = [
                        'createAt' => $createAt,
                        'details' => [$tatbestandData],
                    ];
                }
            } else {
                // Neue Straße + neue Koordinaten hinzufügen
                $data[] = [
                    'streetName' => $streetName,
                    'streetDetails' => $streetDetails,
                    'longitude' => $longitude,
                    'latitude' => $latitude,
                    'createAt' => $createAt,
                    'details' => [
                        [
                            'createAt' => $createAt,
                            'details' => [$tatbestandData],
                        ],
                    ],
                ];
            }
        }

        return $data;
    }

    /**
     * @param string $status
     */
    private function setStatus(string $status): void
    {
        $this->status = $status;
    }

    public function getStatus(): string
    {
        return $this->status;
    }
}