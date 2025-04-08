<?php

namespace App\Entity;

use App\Repository\TatbestandRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TatbestandRepository::class)]
class Tatbestand
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $text = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getText(): ?string
    {
        return $this->text;
    }

    public function setText(string $text): static
    {
        $this->text = $text;

        return $this;
    }

    public function __toString(): string
    {
        return $this->text ?? '';
    }
}
