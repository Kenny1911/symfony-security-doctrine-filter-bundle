<?php

declare(strict_types=1);

namespace Kenny1911\SymfonySecurityDoctrineFilterBundle\Tests\Bootstrap\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ReadableCollection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class Shop
{
    #[ORM\Id]
    #[ORM\Column(type: 'integer', nullable: false)]
    private int $id;

    #[ORM\ManyToOne]
    private User $owner;

    /** @var Collection<array-key, User> */
    #[ORM\ManyToMany(targetEntity: User::class)]
    private Collection $employees;

    public function __construct(int $id, User $owner)
    {
        $this->id = $id;
        $this->owner = $owner;
        $this->employees = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getOwner(): User
    {
        return $this->owner;
    }

    /**
     * @return ReadableCollection<array-key, User>
     */
    public function getEmployees(): ReadableCollection
    {
        return $this->employees;
    }

    public function addEmployee(User $user): self
    {
        if (!$this->employees->contains($user)) {
            $this->employees->add($user);
        }

        return $this;
    }

    public function removeEmployee(User $user): self
    {
        $this->employees->removeElement($user);

        return $this;
    }
}
