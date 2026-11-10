<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Post;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\LegacyPasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use DateTimeImmutable;

/**
 * A user.
 */
#[ORM\Entity]
#[UniqueEntity('email', message: 'This e-mail address is already registered.')]
#[ApiResource(
    operations: [new Post(), new Get()],
    normalizationContext: ['groups' => ['public']],
    denormalizationContext: ['groups' => ['public']],
)]
class Member implements UserInterface, LegacyPasswordAuthenticatedUserInterface
{
    const TYPE_ANONYMOUS = 1;
    const TYPE_SUBSCRIBER = 2;
    const TYPE_MODERATOR = 3;
    const TYPE_ANALYST = 4;

    const ROLE = [
        self::TYPE_ANONYMOUS => "anonymous",
        self::TYPE_SUBSCRIBER => "subscriber",
        self::TYPE_MODERATOR => "moderator",
        self::TYPE_ANALYST => "analyst"
    ];

    /**
     * @var string unique login ID
     */
    #[ORM\Id]
    #[ORM\Column(type: 'string', unique: true)]
    #[Groups(['public'])]
    public $id;

    /**
     * @var string The name of this restaurant.
     */
    #[ORM\Column(type: 'string')]
    #[Groups(['public'])]
    public $title;

    /**
     * @var string The name of this restaurant.
     */
    #[ORM\Column(type: 'string')]
    #[Groups(['public'])]
    public $firstName;

    /**
     * @var string The name of this restaurant.
     */
    #[ORM\Column(type: 'string')]
    #[Groups(['public'])]
    public $lastName;

    /**
     * @var string URL slug
     */
    #[ORM\Column(type: 'string', unique: true)]
    #[Groups(['public'])]
    #[Assert\Email]
    public $email;

    /**
     * @var string The user type, see self::TYPE_*.
     */
    #[ORM\Column(type: 'smallint', nullable: false)]
    public $type = self::TYPE_ANONYMOUS;

    /**
     * @var string the random salt
     */
    #[ORM\Column(type: 'string', length: 40)]
    #[Assert\Length(min: 20)]
    public $salt;

    /**
     * @var string the hashed/salted password
     */
    #[ORM\Column(type: 'string')]
    #[Assert\Length(min: 30)]
    public $password;

    /**
     * @var string Date when the rating was submitted.
     */
    #[ORM\Column(type: 'datetime_immutable', nullable: false)]
    #[Assert\NotNull]
    public $dateAdded;

    /**
     * @var Collection[]
     */
    #[ORM\OneToMany(targetEntity: Collection::class, mappedBy: 'guest', cascade: ['persist', 'remove'])]
    #[ORM\OrderBy(['id' => 'ASC'])]
    public $collections;

    public function __construct()
    {
        $this->collections = new ArrayCollection();
        $this->dateAdded = new DateTimeImmutable();
    }

    public function getId() : ?string
    {
        return $this->id;
    }

    // methods required by UserInterface

    public function equals(UserInterface $user)
    {
        return $user->getId() === $this->getId() && get_class($user) === get_class($this);
    }

    public function getUserIdentifier(): string
    {
        return (string) $this->id;
    }

    public function getUsername(): ?string
    {
        return $this->id;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function getSalt(): ?string
    {
        return $this->salt;
    }

    /**
     * Returns the type and also "account" if the type is not "anonymous". Used for authorization.
     */
    public function getRoles(): array
    {
        $roles = ["ROLE_" . \strtoupper(static::ROLE[$this->type])];

        if ($this->type > 1)
            $roles[] = "ROLE_USER";

        return $roles;
    }

    public function eraseCredentials(): void
    {
    }
}
