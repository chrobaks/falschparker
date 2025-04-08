<?php

namespace App\Form;

use App\Entity\Tatbestand;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CarCountFilterFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('street_name', ChoiceType::class, [
                'required' => false,
                'label' => 'Straßenname',
                'choices' => $options['street_choices'],
                'placeholder' => 'Alle Straßen',
                'attr' => ['class' => 'form-select form-select-sm'],
            ])
            // Filterfeld für Datum (Zeitraum auswählen)
            ->add('dateStart', DateType::class, [
                'required' => false,
                'widget' => 'single_text', // Datepicker
                'label' => 'Von Datum',
                'attr' => ['class' => 'form-select form-select-sm'],
            ])
            // Filterfeld: Enddatum
            ->add('dateEnd', DateType::class, [
                'required' => false,
                'widget' => 'single_text', // Datepicker
                'label' => 'Bis Datum',
                'attr' => ['class' => 'form-select form-select-sm'],
            ])
            // Filterfeld für Tatbestand (Dropdown mit Auswahl)
            ->add('tatbestand', EntityType::class, [
                'class' => Tatbestand::class,
                'choice_label' => 'text', // Attribut aus deiner Tatbestand-Entity (z. B. "name")
                'required' => false,
                'label' => 'Tatbestand',
                'placeholder' => 'Alle',
                'attr' => ['class' => 'form-select form-select-sm'],
            ])
            ->add('submit', SubmitType::class, [
                'label_html' => true,
                'label' => '<i class="bi bi-search"></i>',
                'attr' => [
                    'class' => 'btn btn-primary btn-sm',
                    'data-bs-toggle' => 'tooltip',
                    'data-bs-placement' => 'top',
                    'title' => 'Suche starten.'
                ],
            ])
            ->add('btnReset', ButtonType::class, [
                'label_html' => true,
                'label' => '<i class="bi bi-arrow-clockwise"></i>',
                'attr' => [
                    'class' => 'btn btn-danger btn-sm btn-refresh',
                    'data-bs-toggle' => 'tooltip',
                    'data-bs-placement' => 'top',
                    'title' => 'Das Formular leeren.'
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['street_choices' => [],]);
    }
}