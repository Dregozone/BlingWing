<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
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
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

class FrontendController extends AbstractController
{
    /**
     * @Route("/")
     */
    public function index(): Response
    {
        // Find 10 most recently submit reviews
        $latestItems = $this->entityManager
            ->createQuery('
            SELECT 
                i.name AS ItemName, 
                i.price AS ItemPrice,
                c.name AS CollectionName
            
            FROM App:Item i
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
                
                FROM App:Collection c

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
        $restaurants = $this->entityManager->getRepository("App:Restaurant")->findAll();

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

                FROM App:Review review
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

    /**
     * @Route("/collections")
     */
    public function collections(): Response
    {
        $collections = $this->entityManager
            ->createQuery('
                SELECT COUNT(i.id) AS CollectionItemCount, c.name AS CollectionName, c.image AS CollectionImage, m.firstName AS CollectionOwner 
                
                FROM App:Item i 
                    JOIN i.collection c 
                    JOIN c.guest m 

                GROUP BY c.name, c.image, m.firstName 
                ORDER BY COUNT(i.id) DESC 
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

    /**
     * @Route("/collections/{slug}")
     */
    public function collection(string $slug): Response
    {
        // Find all items in this collection
        $items = $this->entityManager
            ->createQuery('
                SELECT i, m, c 
                
                FROM App:Item i 
                    JOIN i.collection c 
                    JOIN c.guest m 
                
                WHERE c.name = ?1
            ')
            ->setParameter(1, $slug)
            ->setFirstResult(0)
            ->setMaxResults(10)
            ->getResult();

        //dump( $items );
        
        if (!$items) {
            throw $this->createNotFoundException('This collection does not exist.');
        }

        return $this->render('pages/collection.html.twig', [
            "items" => $items,
            "collection" => $slug
        ]);
    }

    /**
     * @Route("/collections/{slug}/{slug2}")
     */
    public function item(string $slug, string $slug2): Response
    {
        // Find all items in this collection
        $item = $this->entityManager
            ->createQuery('
                SELECT i, m, c 
                
                FROM App:Item i 
                    JOIN i.collection c 
                    JOIN c.guest m 
                
                WHERE i.name = ?1
            ')
            ->setParameter(1, $slug2)
            ->setFirstResult(0)
            ->setMaxResults(10)
            ->getResult();

        //dump( $item );
        
        if (!$item) {
            throw $this->createNotFoundException('This item does not exist.');
        }

        return $this->render('pages/item.html.twig', [
            "item" => $item[0],
            "collection" => $slug
        ]);
    }
}
