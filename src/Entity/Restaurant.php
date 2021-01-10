<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiSubresource;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * A restaurant.
 *
 * @ORM\Entity
 * @ApiResource(
 *     collectionOperations={"get"},
 *     itemOperations={"get"}
 * )
 */
class Restaurant implements RestaurantInterface
{
    /**
     * @var int The id of this restaurant.
     *
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var string The primary cuisine of the restaurant.
     *
     * @ORM\Column(type="smallint", nullable=false)
     */
    public $cuisine;

    /**
     * @var string The URL slug for the page.
     *
     * @ORM\Column(type="string", unique=true)
     */
    public $slug;

    /**
     * @var string The name of this restaurant.
     *
     * @ORM\Column(type="string")
     */
    public $name;

    /**
     * @var string URL slug
     *
     * @ORM\Column(type="string", unique=true)
     */
    public $address;

    /**
     * @var string The owner of this restaurant.
     *
     * @ORM\Column(type="string")
     */
    public $phoneNumber;

    /**
     * @var string The postal address of this restraurant.
     *
     * @ORM\Column(type="string")
     */
    public $website;

    /**
     * @var string The postal address of this restraurant.
     *
     * @ORM\Column(type="string")
     */
    public $email;

    /**
     * @var Review[] The reviews for this restaurant.
     *
     * @ORM\OneToMany(targetEntity="Review", mappedBy="restaurant", cascade={"persist", "remove"})
     * @ORM\OrderBy({"id"="ASC"})
     */
    public $reviews;

    public function __construct()
    {
        $this->reviews = new ArrayCollection();
    }

    public function getId() : ?int
    {
        return $this->id;
    }
}
