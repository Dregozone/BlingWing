<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use DateTimeImmutable;

/**
 * An order.
 */
#[ORM\Entity]
#[ApiResource(operations: [new GetCollection(), new Get()])]
class Collection implements CollectionInterface
{
    /**
     * @var int The id of this item.
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    public $id;

    /**
     * @var string The name of this restaurant.
     */
    #[ORM\Column(type: 'string')]
    public $name;

    /**
     * @var string Image relating to this collection.
     */
    #[ORM\Column(type: 'string')]
    public $image;

    /**
     * @var string Date when the rating was submitted.
     */
    #[ORM\Column(type: 'datetime_immutable', nullable: false)]
    #[Assert\NotNull]
    public $dateAdded;

    #[ORM\ManyToOne(targetEntity: Member::class)]
    #[ORM\JoinColumn(onDelete: 'CASCADE')]
    public $guest;

    public function __construct()
    {
        //$this->items = new ArrayCollection();
        $this->dateAdded = new DateTimeImmutable();
    }

    public function getId() : ?int
    {
        return $this->id;
    }
}
