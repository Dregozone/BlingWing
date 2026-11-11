<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Collection;
use App\Entity\Restaurant;
use App\Entity\Review;
use App\Entity\User;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityRepository;
use Psr\Log\LoggerInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class FrontendController extends AbstractController
{
    #[Route("/")]
    public function index(): Response
    {
        // Find 10 most recently submit reviews
        $latestItems = $this->entityManager
            ->createQuery('
            SELECT 
                i.name AS ItemName, 
                i.price AS ItemPrice,
                c.name AS CollectionName
            
            FROM App\Entity\Item i
                JOIN i.collection c

            ORDER BY i.dateAdded DESC 
        ')
        ->setFirstResult(0)
        ->setMaxResults(3)
        ->getResult();

        //dump( $latestItems );

        $latestCollections = $this->entityManager
            ->createQuery('
                SELECT 
                    c.name AS CollectionName, 
                    c.image AS CollectionImage
                
                FROM App\Entity\Collection c

                ORDER BY c.dateAdded DESC 
            ')
            ->setFirstResult(0)
            ->setMaxResults(3)
            ->getResult();

        //dump( $latestCollections );

        return $this->render('pages/index.html.twig', [
            "latestItems" => $latestItems,
            "latestCollections" => $latestCollections
        ]);
    }

    /**
     * @Route("/restaurants")
     */
    /*
    public function restaurants(Request $request): Response
    {
        $params = $this->fixRestaurantParams($request->query->all());
        $restaurants = $this->getTopRestaurants($params, 20);

        return $this->render('pages/restaurants.html.twig', [
            "restaurants" => $restaurants,
            "params" => $params,
            "cuisine" => Restaurant::CUISINE,
            "service" => Review::SERVICE,
            "food" => Review::FOOD,
            "occasion" => Review::OCCASION,
            "cleanliness" => Review::CLEANLINESS,
            "value" => Review::VALUE
        ]);
    }
    */

    /**
     * @Route("/review")
     */
    /*
    public function review(): Response
    {
        $restaurants = $this->entityManager->getRepository(Restaurant::class)->findAll();

        return $this->render('pages/review.html.twig', [
            "restaurants" => $restaurants,
            "service" => Review::SERVICE,
            "food" => Review::FOOD,
            "occasion" => Review::OCCASION,
            "cleanliness" => Review::CLEANLINESS,
            "value" => Review::VALUE
        ]);
    }
    */

    /**
     * @Route("/restaurants/{slug}")
     */
    /*
    public function restaurant(string $slug): Response
    {
        $restaurant = $this->createRestaurantBaseQueryBuilder()
            ->addSelect("rv AS review")
            ->addSelect("rs AS details")
            ->andWhere("rs.slug = :slug")
            ->groupBy("rs.id")
            ->setParameter("slug", $slug)
            ->getQuery()
            ->getOneOrNullResult();

        if (!$restaurant) {
            throw $this->createNotFoundException('This restaurant does not exist.');
        }

        // Find all reviews for this restaurant
        $reviews = $this->entityManager
            ->createQuery('
                SELECT
                    review, user

                FROM App\Entity\Review review
                    JOIN review.guest user
                    JOIN review.restaurant rest

                WHERE rest.id = ?1 AND review.status = :status

                ORDER BY review.created DESC
            ')
            ->setParameter(1, $restaurant["details"]->getId())
            ->setParameter("status", Review::STATUS_PUBLISHED)
            ->setFirstResult(0)
            ->setMaxResults(10)
            ->getResult();

        return $this->render('pages/restaurant.html.twig', [
            "restaurant" => $restaurant,
            "reviews" => $reviews,
            "cuisine" => Restaurant::CUISINE,
            "service" => Review::SERVICE,
            "food" => Review::FOOD,
            "occasion" => Review::OCCASION,
            "cleanliness" => Review::CLEANLINESS,
            "value" => Review::VALUE
        ]);
    }
    */

    #[Route("/collections")]
    public function collections(): Response
    {
        // Start from the collection so collections without items are listed too
        $collections = $this->entityManager
            ->createQuery('
                SELECT c.name AS CollectionName, c.image AS CollectionImage, m.firstName AS CollectionOwner, COUNT(i.id) AS CollectionItemCount

                FROM App\Entity\Collection c
                    JOIN c.guest m
                    LEFT JOIN App\Entity\Item i WITH i.collection = c

                GROUP BY c.id, c.name, c.image, m.firstName
                ORDER BY CollectionItemCount DESC, c.name ASC
            ')
            ->getResult();

        return $this->render('pages/collections.html.twig', [
            "collections" => $collections,
            "cuisine" => "",
            "food" => "",
            "service" => "",
            "value" => "",
            "cleanliness" => ""
        ]);
    }

    #[Route("/collections/{slug}")]
    public function collection(string $slug): Response
    {
        $collection = $this->entityManager
            ->getRepository(Collection::class)
            ->findOneBy(["name" => $slug]);

        if (!$collection) {
            throw $this->createNotFoundException('This collection does not exist.');
        }

        // Find all items in this collection
        $items = $this->entityManager
            ->createQuery('
                SELECT i

                FROM App\Entity\Item i

                WHERE i.collection = ?1

                ORDER BY i.dateAdded DESC
            ')
            ->setParameter(1, $collection)
            ->setFirstResult(0)
            ->setMaxResults(10)
            ->getResult();

        return $this->render('pages/collection.html.twig', [
            "items" => $items,
            "collection" => $collection
        ]);
    }

    #[Route("/collections/{slug}/{slug2}")]
    public function item(string $slug, string $slug2): Response
    {
        // Find the item by name within this collection
        $item = $this->entityManager
            ->createQuery('
                SELECT i, m, c

                FROM App\Entity\Item i
                    JOIN i.collection c
                    JOIN c.guest m

                WHERE i.name = ?1 AND c.name = ?2
            ')
            ->setParameter(1, $slug2)
            ->setParameter(2, $slug)
            ->setMaxResults(1)
            ->getOneOrNullResult();

        if (!$item) {
            throw $this->createNotFoundException('This item does not exist.');
        }

        return $this->render('pages/item.html.twig', [
            "item" => $item,
            "collection" => $slug
        ]);
    }
}
