<?php

// src/Form/CarCountFormType.php
namespace App\Form;

use App\Entity\Carcount;
use App\Entity\User;
use App\Entity\Tatbestand;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

class CarCountFormType extends AbstractType
{
    // Baut das Formular
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('latitude', NumberType::class, [
                'required' => true,
                'html5' => true,
                'label' => false,
                'attr' => ['step' => 0.000001], // Genauigkeit der Eingabe
            ])
            ->add('longitude', NumberType::class, [
                'required' => true,
                'html5' => true,
                'label' => false,
                'attr' => ['step' => 0.000001],
            ])
            ->add('street_name', TextType::class, [
                'label_html' => true,
                'label' => 'Straßen-Name <i class="bi bi-info-circle fw-bold" data-bs-toggle="tooltip" title="Der Straßenname kann nicht manuell eingegeben werde, benutze die OpenStreetMap! Such dir in der Map eine Straße aus und klick darauf! Oder wähle einen Namen aus den gespeicherten Straßennamen"></i>',
                'empty_data' => 'Bitte klicke eine Straße in der OpenStreetMap an!',
                'attr' => ['id' => 'streetName', 'readonly' => 'readonly'],
            ])
            ->add('street_details', HiddenType::class, [
                'mapped' => false,
            ])
            ->add('street_name_options', ChoiceType::class, [
                'label_html' => true,
                'label' => 'Gespeicherten Straßenname auswählen <i class="bi bi-info-circle fw-bold" data-bs-toggle="tooltip" title="Wähle einen Strassennamen mit seinen Koordinaten, wenn er schon existiert, oder klicke in der OpenStreetMap auf eine Straße!"></i>',
                'choices' => $options['street_name_data'],
                'mapped' => false,
                'required' => false,
                'placeholder' => 'Auswahl Straße',
                'empty_data' => '',
                'constraints' => [],
            ])
            ->add('tatbestandCounts', CollectionType::class, [
                    'entry_type' => TatbestandCountFormType::class,
                    'entry_options' => ['label' => false,],
                    'allow_add' => true,      // dynamisches Hinzufügen erlauben
                    'allow_delete' => true,   // Löschen erlauben
                    'by_reference' => false,  // wichtig, damit die add-Methode (addTatbestandCount) genutzt wird
                    'prototype' => true,      // ein Prototype wird generiert
                    'prototype_name' => '__name__',
                    'label' => false,
//                    'data' => [],
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Zählung speichern'
            ]);

            $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) {
                $form = $event->getForm();
                $data = $event->getData();
            });
    }

    // Konfiguration des Formulars und der Optionen
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Carcount::class,
            'csrf_protection' => true,
            'street_name_data' => [],
        ]);
    }

    // Optionale Methode für das Handling von "user_id"
    public function getBlockPrefix(): string
    {
        return 'carcount_form';
    }
}
