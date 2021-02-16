<?php

namespace App\Entity;

use App\Repository\SensorsRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=SensorsRepository::class)
 */
class Sensors
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=30)
     */
    private $Name;

    /**
     * @ORM\Column(type="string", length=30, nullable=true)
     */
    private $Room;

    /**
     * @ORM\Column(type="string", length=30, nullable=true)
     */
    private $Category;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->Name;
    }

    public function setName(string $Name): self
    {
        $this->Name = $Name;

        return $this;
    }

    public function getRoom(): ?string
    {
        return $this->Room;
    }

    public function setRoom(?string $Room): self
    {
        $this->Room = $Room;

        return $this;
    }

    public function getCategory(): ?string
    {
        return $this->Category;
    }

    public function setCategory(?string $Category): self
    {
        $this->Category = $Category;

        return $this;
    }
}
