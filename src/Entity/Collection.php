<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiSubresource;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * An order.
 *
 * @ORM\Entity
 * @ApiResource(
 *     collectionOperations={"get"},
 *     itemOperations={"get"}
 * )
 */
class Collection implements CollectionInterface
{
    /**
     * @var int The id of this item.
     *
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    public $id;

    /**
     * @var string The name of this restaurant.
     *
     * @ORM\Column(type="string")
     */
    public $name;

    /**
     * @var string Image relating to this collection.
     *
     * @ORM\Column(type="string")
     */
    public $image;

    /**
     * @ORM\ManyToOne(targetEntity="Member")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    public $guest;

    public function __construct()
    {
        //$this->items = new ArrayCollection();
    }

    public function getId() : ?int
    {
        return $this->id;
    }
}
