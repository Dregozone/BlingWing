<?php

namespace App\Entity;

/**
 * Nothing special, only a place to keep the rating constants.
 */
interface ItemInterface
{
    const SERVICE = [
        5 => "outstanding",
        4 => "remarkable",
        3 => "sufficient",
        2 => "underwhelming",
        1 => "disappointing"
    ];

    const FOOD = [
        5 => "fabulous",
        4 => "delicious",
        3 => "palatable",
        2 => "mediocre",
        1 => "poor"
    ];

    const CLEANLINESS = [
        5 => "royal",
        4 => "posh",
        3 => "gentle",
        2 => "rustic",
        1 => "disgusting"
    ];

    const VALUE = [
        5 => "generous",
        4 => "fair",
        3 => "tolerable",
        2 => "inappropriate",
        1 => "rip-off"
    ];

    const OCCASION = [
        1 => "nothing special",
        2 => "business",
        3 => "birthday",
        4 => "promotion",
        5 => "date",
        6 => "engagement",
        7 => "wedding",
        8 => "anniversary",
        9 => "child’s birth",
        10 => "funeral",
        11 => "other"
    ];

    const STATUS_DRAFT = 1;
    const STATUS_PENDING = 2;
    const STATUS_PUBLISHED = 3;
    const STATUS_ARCHIVED = 4;
    const STATUS_DELETED = 5;

    const STATUS = [
        self::STATUS_DRAFT => "draft",
        self::STATUS_PENDING  => "pending",
        self::STATUS_PUBLISHED  => "published",
        self::STATUS_ARCHIVED  => "archived",
        self::STATUS_DELETED => "deleted"
    ];
}
