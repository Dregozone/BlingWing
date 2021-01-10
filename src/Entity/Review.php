<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Core\Annotation\ApiResource;
use Symfony\Component\Validator\Constraints as Assert;
use DateTimeImmutable;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\Mapping\ClassMetadata;

/**
 * A review of a restaurant visit.
 *
 * @ORM\Entity
 * @ApiResource(
 *     collectionOperations={"get", "post"},
 *     itemOperations={
 *      "get",
 *      "put"={"security"="is_granted('ROLE_MODERATOR')"}
 *     }
 * )
 */
class Review implements ReviewInterface
{
    /**
     * @var int The id of this review.
     *
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var string Date when the rating was submitted.
     *
     * @ORM\Column(type="datetime_immutable", nullable=false)
     * @Assert\NotNull
     */
    public $created;

    /**
     * @var string The written review.
     *
     * @ORM\Column(type="text", nullable=false)
     * @Assert\NotBlank
     */
    public $comment;

    /**
     * @var string How the quality of the food was perceived.
     *
     * @ORM\Column(type="smallint", nullable=false)
     */
    public $food;

    /**
     * @var string How the speed/quality of delivery was perceived.
     *
     * @ORM\Column(type="smallint", nullable=false)
     */
    public $service;

    /**
     * @var string How the cleanliness of the place was perceived.
     *
     * @ORM\Column(type="smallint", nullable=false)
     */
    public $cleanliness;

    /**
     * @var string How the value for money was perceived.
     *
     * @ORM\Column(type="smallint", nullable=false)
     */
    public $value;

    /**
     * @var The number of people in the group.
     *
     * @ORM\Column(type="smallint", nullable=false)
     */
    public $size;

    /**
     * @var string The occasion of the visit to the restaurant.
     *
     * @ORM\Column(type="smallint", nullable=false)
     */
    public $occasion;

    /**
     * @var string Status of this review.
     *
     * @ORM\Column(type="smallint", nullable=false)
     */
    public $status = 1;

    /**
     * @ORM\ManyToOne(targetEntity="Restaurant")
     * @ORM\JoinColumn(onDelete="CASCADE")
     * @Assert\NotNull
     */
    public $restaurant;

    /**
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    public $guest;

    public function __construct()
    {
        $this->created = new DateTimeImmutable();
    }

    public function getId() : ?int
    {
        return $this->id;
    }
}
