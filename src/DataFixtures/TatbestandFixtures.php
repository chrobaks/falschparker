<?php
// src/DataFixtures/TatbestandFixtures.php
namespace App\DataFixtures;

use App\Entity\Tatbestand;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class TatbestandFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // Deine Anfangsdaten
        $tatbestände = [
        'Parken auf dem Gehweg (mit mind. 1 Rad oben auf dem Bordstein)',
        'Parken auf dem Radweg',
        'Parken im Grünstreifen',
        'Parken zu nah an Kreuzungen'
        ];

        // Für jedes Tatbestand-Element ein Objekt anlegen und speichern
        foreach ($tatbestände as $text) {
        $tatbestand = new Tatbestand();
        $tatbestand->setText($text);
        $manager->persist($tatbestand);
    }

    // Alle Objekte in die Datenbank persistieren
    $manager->flush();
    }
}
