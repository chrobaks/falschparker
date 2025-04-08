<?php

namespace App\Entity;

use App\Repository\TatbestandCountRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TatbestandCountRepository::class)]
class TatbestandCount
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'tatbestandCounts')]
    #[ORM\JoinColumn(nullable: false)]
    private ?CarCount $carCount = null;

    #[ORM\ManyToOne(targetEntity: Tatbestand::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Tatbestand $tatbestand = null;

    #[ORM\Column]
    private ?int $count = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCarCount(): ?CarCount
    {
        return $this->carCount;
    }

    public function setCarCount(?CarCount $carCount): static
    {
        $this->carCount = $carCount;

        return $this;
    }

    public function getTatbestand(): ?Tatbestand
    {
        return $this->tatbestand;
    }

    public function setTatbestand(?Tatbestand $tatbestand): static
    {
        $this->tatbestand = $tatbestand;

        return $this;
    }

    public function getCount(): ?int
    {
        return $this->count;
    }

    public function setCount(int $count): static
    {
        $this->count = $count;

        return $this;
    }
}
