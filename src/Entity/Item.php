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
 * A jewellery item.
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
class Item implements ItemInterface
{
    /**
     * @var int The id of this review.
     *
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    public $id;

    /**
     * @var string The written review.
     *
     * @ORM\Column(type="text", nullable=false)
     * @Assert\NotBlank
     */
    public $name;

    /**
     * @var string How the value for money was perceived.
     *
     * @ORM\Column(type="smallint", nullable=false)
     */
    public $price;

    /**
     * @var string Status of this order.
     *
     * @ORM\Column(type="smallint", nullable=false)
     */
    public $status = 1;

    /**
     * @ORM\ManyToOne(targetEntity="Collection")
     * @ORM\JoinColumn(onDelete="CASCADE")
     * @Assert\NotNull
     */
    public $collection;

    public function __construct()
    {
        //
    }

    public function getId() : ?int
    {
        return $this->id;
    }
}
