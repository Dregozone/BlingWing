<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Security\Core\User\UserInterface;
use ApiPlatform\Core\Annotation\ApiResource;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * A user.
 * @ORM\Entity
 * @UniqueEntity("email", message="This e-mail address is already registered.")
 *
 * @ApiResource(
 *     normalizationContext={"groups"={"public"}},
 *     denormalizationContext={"groups"={"public"}},
 *     collectionOperations={"post"},
 *     itemOperations={"get"}
 * )
 */
class User implements UserInterface
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
     *
     * @ORM\Id
     * @ORM\Column(type="string", unique=true)
     * @Groups({"public"})
     */
    public $id;

    /**
     * @var string The name of this restaurant.
     *
     * @ORM\Column(type="string")
     * @Groups("public")
     */
    public $name;

    /**
     * @var string URL slug
     *
     * @ORM\Column(type="string", unique=true)
     * @Groups("public")
     * @Assert\Email
     */
    public $email;

    /**
     * @var string The user type, see self::TYPE_*.
     *
     * @ORM\Column(type="smallint", nullable=false)
     */
    public $type = self::TYPE_ANONYMOUS;

    /**
     * @var string the random salt
     *
     * @ORM\Column(type="string", length=40)
     * @Assert\Length(min=20)
     */
    public $salt;

    /**
     * @var string the hashed/salted password
     *
     * @ORM\Column(type="string")
     * @Assert\Length(min=30)
     */
    public $password;

    /**
     * @var Review[]
     *
     * @ORM\OneToMany(targetEntity="Review", mappedBy="guest", cascade={"persist", "remove"})
     * @ORM\OrderBy({"id"="ASC"})
     */
    public $reviews;

    public function __construct()
    {
        $this->reviews = new ArrayCollection();
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

    public function getUsername()
    {
        return $this->id;
    }

    public function getPassword()
    {
        return $this->password;
    }

    public function getSalt()
    {
        return $this->salt;
    }

    /**
     * Returns the type and also "account" if the type is not "anonymous". Used for authorization.
     */
    public function getRoles()
    {
        $roles = ["ROLE_" . \strtoupper(static::ROLE[$this->type])];

        if ($this->type > 1)
            $roles[] = "ROLE_USER";

        return $roles;
    }

    public function eraseCredentials()
    {
    }
}
