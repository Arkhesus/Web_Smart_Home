<?php

namespace App\Entity;

use App\Repository\SensorsRepository;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\Category;
use App\Entity\Room;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=SensorsRepository::class)
 */
class Sensors
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Assert\NotBlank(groups = {"put"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=30)
     * @Assert\NotBlank(groups = {"post", "delete","put"})
     
     */
    private $Name;

        /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Room", cascade="remove")
     * @ORM\JoinColumn(name="room_id", referencedColumnName="id")
     * @Assert\NotBlank(groups = {"post", "delete","put"})
     */
    private $Room;

        /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Category", cascade="remove")
     * @ORM\JoinColumn(name="category_id", referencedColumnName="id")
     * @Assert\NotBlank(groups = {"post", "delete","put"})
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

    public function getRoom()
    {
        return $this->Room;
    }

    public function setRoom(Room $Room): self
    {
        $this->Room = $Room;

        return $this;
    }

    public function getCategory()
    {
        return $this->Category;
    }

    public function setCategory(Category $Category): self
    {
        $this->Category = $Category;

        return $this;
    }

}
