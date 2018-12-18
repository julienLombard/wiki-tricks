<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\CategoryRepository")
 */
class Category
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=100)
     */
    private $name;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Trick", mappedBy="category")
     */
    private $tricks;

    public function __construct()
    {
        $this->tricks = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return Collection|Trick[]
     */
    public function getTricks(): Collection
    {
        return $this->tricks;
    }

    public function addTricks(Trick $tricks): self
    {
        if (!$this->trickss->contains($tricks)) {
            $this->trickss[] = $tricks;
            $tricks->setCategory($this);
        }

        return $this;
    }

    public function removeTricks(Trick $tricks): self
    {
        if ($this->trickss->contains($tricks)) {
            $this->trickss->removeElement($tricks);
            // set the owning side to null (unless already changed)
            if ($tricks->getCategory() === $this) {
                $tricks->setCategory(null);
            }
        }

        return $this;
    }
}
