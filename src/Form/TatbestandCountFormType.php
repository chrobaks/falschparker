<?php

namespace App\Form;

use App\Entity\Tatbestand;
use App\Entity\TatbestandCount;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Validator\Constraints\NotBlank;

class TatbestandCountFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('tatbestand', EntityType::class, [
                'class' => Tatbestand::class,
                'label' => false,
                'placeholder' => 'Bitte Tatbestand wählen...',
                'attr' => [
                    'class' => 'tatbestand-text',
                ],
            'constraints' => [
                new NotBlank([
                    'message' => 'Es wurde kein Tatbestand gefunden!'
                    ]),
                ],
            ])
            ->add('count', IntegerType::class, [
                'label' => false,
                'attr' => [
                    'class' => 'tatbestand-count no-spin  rounded-0',
                    'min' => 0,// Add CSS class here
                    'onInPut' => 'validity.valid || (value="")'
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => TatbestandCount::class,
        ]);
    }
}