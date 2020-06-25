<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\AppointmentRepository")
 */
class Appointment
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $title;

    /**
     * @ORM\Column(type="datetime")
     */
    private $beginAt;

    /**
     * @ORM\Column(type="datetime")
     */
    private $endAt;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $color;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Treatment")
     */
    private $treatment;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Patient", inversedBy="appointment")
     * @ORM\JoinColumn(name="patient_id", referencedColumnName="id")
     */
    private $patient;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Company", inversedBy="appointment")
     * @ORM\JoinColumn(name="company_id", referencedColumnName="id")
     */
    private $company;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Staff", inversedBy="appointments")
     */
    private $staff;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Treatment", inversedBy="appointments")
     */
    private $treatments;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2, nullable=true)
     */
    private $TotalCost;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2, nullable=true)
     */
    private $Discount;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2, nullable=true)
     */
    private $Cost;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $status;

    public function __construct()
    {
        $this->treatments = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getBeginAt(): ?\DateTime
    {
        return $this->beginAt;
    }

    public function setBeginAt(\DateTime $beginAt): self
    {
        $this->beginAt = $beginAt;

        return $this;
    }

    public function getEndAt(): ?\DateTimeInterface
    {
        return $this->endAt;
    }

    public function setEndAt(\DateTimeInterface $endAt): self
    {
        $this->endAt = $endAt;

        return $this;
    }

    public function getTreatment(): ?Treatment
    {
        return $this->treatment;
    }

    public function setTreatment(?Treatment $treatment ): self
    {
        $this->treatment = $treatment;

        return $this;
    }

    public function getPatient(): ?Patient
    {
        return $this->patient;
    }

    public function setPatient(?Patient $patient = null): self
    {
        $this->patient = $patient;

        return $this;
    }

    public function __toString() {
        return $this->title;
    }

    public function getColor(): ?string
    {
        return $this->color;
    }

    public function setColor(string $color): self
    {
        $this->color = $color;

        return $this;
    }

    public function getCompany(): ?Company
    {
        return $this->company;
    }

    public function setCompany(?Company $company): self
    {
        $this->company = $company;

        return $this;
    }

    public function getStaff(): ?Staff
    {
        return $this->staff;
    }

    public function setStaff(?Staff $staff): self
    {
        $this->staff = $staff;

        return $this;
    }

    /**
     * @return Collection|Treatment[]
     */
    public function getTreatments(): Collection
    {
        return $this->treatments;
    }

    public function addTreatment(Treatment $treatment): self
    {
        if (!$this->treatments->contains($treatment)) {
            $this->treatments[] = $treatment;
        }

        return $this;
    }

    public function removeTreatment(Treatment $treatment): self
    {
        if ($this->treatments->contains($treatment)) {
            $this->treatments->removeElement($treatment);
        }

        return $this;
    }

    public function getTotalCost(): ?float
    {
        return $this->TotalCost;
    }

    public function setTotalCost(?float $TotalCost): self
    {
        $this->TotalCost = $TotalCost;

        return $this;
    }

    public function getDiscount(): ?float
    {
        return $this->Discount;
    }

    public function setDiscount(?float $Discount): self
    {
        $this->Discount = $Discount;

        return $this;
    }

    public function getCost(): ?float
    {
        return $this->Cost;
    }

    public function setCost(?float $Cost): self
    {
        $this->Cost = $Cost;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(?string $status): self
    {
        $this->status = $status;

        return $this;
    }


}
