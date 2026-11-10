<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use DateTimeImmutable;

/**
 * A jewellery item.
 */
#[ORM\Entity]
#[ApiResource(operations: [
    new GetCollection(),
    new Post(),
    new Get(),
    new Put(security: "is_granted('ROLE_MODERATOR')"),
])]
class Item implements ItemInterface
{
    /**
     * @var int The id of this review.
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    public $id;

    /**
     * @var string The written review.
     */
    #[ORM\Column(type: 'text', nullable: false)]
    #[Assert\NotBlank]
    public $name;

    /**
     * @var string How the value for money was perceived.
     */
    #[ORM\Column(type: 'smallint', nullable: false)]
    public $price;

    /**
     * @var string Status of this order.
     */
    #[ORM\Column(type: 'smallint', nullable: false)]
    public $status = 1;

    #[ORM\ManyToOne(targetEntity: Collection::class)]
    #[ORM\JoinColumn(onDelete: 'CASCADE')]
    #[Assert\NotNull]
    public $collection;

    /**
     * @var string Date when the rating was submitted.
     */
    #[ORM\Column(type: 'datetime_immutable', nullable: false)]
    #[Assert\NotNull]
    public $dateAdded;

    public function __construct()
    {
        $this->dateAdded = new DateTimeImmutable();
    }

    public function getId() : ?int
    {
        return $this->id;
    }
}
