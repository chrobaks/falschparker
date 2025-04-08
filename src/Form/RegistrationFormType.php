<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class RegistrationFormType extends AbstractType
{
    private RouterInterface $router;

    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $agbUrl = $this->router->generate('app_agb');

        $builder
            ->add('firstName', TextType::class, [
                'label' => 'Vorname',
                'constraints' => [
                    new NotBlank([
                        'message' => 'Bitte gib deinen Vornamen ein!',
                    ]),
                ],
            ])
            ->add('lastName', TextType::class,
                ['label' => 'Nachname',
                    'constraints' => [
                        new NotBlank([
                            'message' => 'Bitte gib deinen Nachnamen ein!',
                        ]),
                    ],
                ])
            ->add('email', EmailType::class, ['constraints' => [
                        new NotBlank([
                            'message' => 'Bitte gib deine Emailadresse ein!',
                        ]),
                    ],])
            ->add('plainPassword', PasswordType::class, [
                // instead of being set onto the object directly,
                // this is read and encoded in the controller
                'label' => 'Passwort (min 12 Zeichen)',
                'mapped' => false,
                'attr' => ['autocomplete' => 'new-password'],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Ein Passwort eingeben',
                    ]),
                    new Length([
                        'min' => 12,
                        'minMessage' => 'Dein Passwort sollte min. 12 Zeichen lang sein',
                        // max length allowed by Symfony for security reasons
                        'max' => 4096,
                    ]),
                ],
            ])
            ->add('agreeTerms', CheckboxType::class, [
                'mapped' => false,
                'label' => 'Ich akzeptiere die <a href="'.$agbUrl.'" target="_blank">Allgemeinen Benutzerbedingungen</a>',
                'label_html' => true,
                'constraints' => [
                    new IsTrue([
                        'message' => 'Bitte akzeptiere die AGB',
                    ]),
                ],
            ])
            ->add('save', SubmitType::class, ['label' => 'Benutzer speichern'])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'csrf_protection' => true,
        ]);
    }
}
