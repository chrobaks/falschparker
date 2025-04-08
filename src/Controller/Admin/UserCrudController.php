<?php

namespace App\Controller\Admin;

use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class UserCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('firstName', 'Vorname'),
            TextField::new('lastName', 'Familienname'),
            TextField::new('email', 'Emailadresse'),
            BooleanField::new('isVerified')->setLabel('Ist Verifiziert'),
            ChoiceField::new('roles')
                ->setLabel('Benutzerrollen')
                ->setChoices([
                    'Administrator' => 'ROLE_ADMIN',
                    'Editor' => 'ROLE_EDITOR',
                    'Benutzer' => 'ROLE_USER',
                    'Superadmin' => 'ROLE_SUPER_ADMIN',
                ])
                ->allowMultipleChoices(true) // Mehrfachauswahl aktivieren
                ->renderExpanded(true),     // Optional: als Checkbox-Liste anzeigen

        ];
    }
}
