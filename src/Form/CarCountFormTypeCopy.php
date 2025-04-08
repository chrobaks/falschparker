<?php

// src/Form/CarcountType.php
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

class CarCountFormTypeCopy extends AbstractType
{
    // Baut das Formular
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('user_id', HiddenType::class, [
                'data' => $options['user_id'],
                'mapped' => false,
            ])
            ->add('counts', HiddenType::class, [
                'data' => '',
                'mapped' => false,
            ])
            ->add('latitude', NumberType::class, [
                'required' => true,
                'html5' => true,
                'label' => false,
                'attr' => ['step' => 0.000001, 'class' => 'd-none'], // Genauigkeit der Eingabe
            ])
            ->add('longitude', NumberType::class, [
                'required' => true,
                'html5' => true,
                'label' => false,
                'attr' => ['step' => 0.000001, 'class' => 'd-none'],
            ])
            ->add('street_name', TextType::class, [
                'label' => 'Straßen-Name',
                'empty_data' => 'Bitte klicke eine Straße in der OpenStreetMap an!',
                'attr' => ['id' => 'streetName'],
            ])
            ->add('count', IntegerType::class, [
                'label' => 'Anzahl der gezählten Autos',
                'attr' => ['id' => 'count'],
                'mapped' => false, // Keine Bindung zur CarCount-Entity
                'required' => true, // Optional: Macht das Feld nicht zwingend
            ])
            // 'tatbestand_id' als Auswahlfeld, das alle Einträge aus der Tatbestand-Tabelle zeigt
            ->add('tatbestand', EntityType::class, [
                'class' => Tatbestand::class,
                'choice_label' => 'text', // Angenommen, die Tatbestand-Entity hat ein 'name'-Feld
                'label' => 'Tatbestand',
                'placeholder' => 'Bitte wähle einen Tatbestand',
                'mapped' => false, // Keine Bindung zur CarCount-Entity
                'required' => true, // Optional: Macht das Feld nicht zwingend
            ])

            ->add('cache', ButtonType::class, [
                'label' => 'Tatbestand speichern'
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Zählung speichern'
            ]);

            $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) {
                $form = $event->getForm();
                $data = $event->getData();

                // Hole den ausgewählten Tatbestand (falls nötig)
                $selectedTatbestand = $form->get('tatbestand')->getData();

                // Nutze den Wert (z. B. für eine Validierung oder Weiterverarbeitung)
            });
    }

    // Konfiguration des Formulars und der Optionen
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Carcount::class, // Verknüpft das Formular mit der Carcount-Entity
            'csrf_protection' => true,
            'user_id' => null,
        ]);
    }

    // Optionale Methode für das Handling von "user_id"
    public function getBlockPrefix(): string
    {
        return 'carcount_form';
    }
}
