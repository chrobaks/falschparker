<?php

namespace App\Entity;

use App\Repository\CarCountRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use phpDocumentor\Reflection\Types\Integer;

#[ORM\Entity(repositoryClass: CarCountRepository::class)]
#[ORM\Index(name: "idx_lat_lon", columns: ["latitude", "longitude"])]
class CarCount
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: false)]
    private ?string $street_name = null;

    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $street_details = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createAt = null;

    /**
     * @var string
     */
    #[ORM\Column(type: "decimal", precision: 9, scale: 6, nullable: false)]
    private string $latitude;

    /**
     * @var string
     */
    #[ORM\Column(type: "decimal", precision: 9, scale: 6, nullable: false)]
    private string $longitude;

    /**
     * @var Collection<int, TatbestandCount>
     */
    #[ORM\OneToMany(targetEntity: TatbestandCount::class, mappedBy: 'carCount', cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $tatbestandCounts;

    public function __construct()
    {
        $this->tatbestandCounts = new ArrayCollection();
        $this->createAt = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLatitude(): float
    {
        return $this->latitude;
    }

    public function setLatitude(string $latitude): void
    {
        $this->latitude = $latitude;
    }

    public function getLongitude(): float
    {
        return $this->longitude;
    }

    public function setLongitude(string $longitude): void
    {
        $this->longitude = $longitude;
    }

    public function getStreetName(): ?string
    {
        return $this->street_name;
    }

    public function setStreetName(string $street_name): static
    {
        $this->street_name = $street_name;

        return $this;
    }

    public function getStreetDetails(): ?array
    {
        return $this->street_details;
    }

    public function setStreetDetails(?array $streetDetails): self
    {
        $this->street_details = $streetDetails;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getCreateAt(): ?\DateTimeInterface
    {
        return $this->createAt;
    }

    public function setCreateAt(\DateTimeInterface $createAt): static
    {
        $this->createAt = $createAt;

        return $this;
    }

    /**
     * @return Collection
     */
    public function getTatbestandCounts(): Collection
    {
        return $this->tatbestandCounts;
    }

    public function addTatbestandCount(TatbestandCount $tatbestandCount): self
    {
        if (!$this->tatbestandCounts->contains($tatbestandCount)) {
            $this->tatbestandCounts[] = $tatbestandCount;
            $tatbestandCount->setCarCount($this);
        }

        return $this;
    }

    public function removeTatbestandCount(TatbestandCount $tatbestandCount): self
    {
        if ($this->tatbestandCounts->removeElement($tatbestandCount)) {
            // set the owning side to null (unless already changed)
            if ($tatbestandCount->getCarCount() === $this) {
                $tatbestandCount->setCarCount(null);
            }
        }

        return $this;
    }

    public function __toString(): string
    {
        return $this->street_name ?? '';
    }
}
