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

class AdminController extends AbstractController
{
    /**
     * @Route("/stats")
     * @IsGranted("ROLE_ANALYST")
     */
    public function stats(Request $request): Response
    {
        $params = $request->query->all();
        $params = [
            "from" => new \DateTime(sprintf("%s 00:00", $params["from"] ?? "first day of this month")),
            "until" => new \DateTime(sprintf("%s 23:59:59", $params["until"] ?? "today"))
        ];

        $users = $this->entityManager->createQuery('
                SELECT u.name AS name, u.email AS email, COUNT(r) AS num
                FROM App:Review r
                JOIN r.guest u
                WHERE r.created >= :from AND r.created <= :until AND r.status = :status
                GROUP BY u.id
                ORDER BY num DESC')
            ->setParameter("from", $params["from"]->format("Y-m-d H:i:s"))
            ->setParameter("until", $params["until"]->format("Y-m-d H:i:s"))
            ->setParameter("status", Review::STATUS_PUBLISHED)
            ->setFirstResult(0)
            ->setMaxResults(10)
            ->getResult();

        $reviews = $this->entityManager->createQuery('
                SELECT r
                FROM App:Review r
                ORDER BY r.created DESC')
            ->setFirstResult(0)
            ->setMaxResults(10)
            ->getResult();

        $occasions = $this->entityManager->createQuery('
                SELECT review.occasion AS name, SUM(review.occasion) AS sum
                FROM App:Review review
                WHERE review.created >= :from AND review.created <= :until AND review.status = :status
                GROUP BY review.occasion
                ORDER BY sum DESC')
            ->setParameter("from", $params["from"]->format("Y-m-d H:i:s"))
            ->setParameter("until", $params["until"]->format("Y-m-d H:i:s"))
            ->setParameter("status", Review::STATUS_PUBLISHED)
            ->setFirstResult(0)
            ->getResult();

        return $this->render('pages/stats.html.twig', [
            "params" => $params,
            "topRestaurants" => $this->getTopRestaurants($params, 5),
            "popularRestaurants" => $this->getTopRestaurants($params + ["sort" => "total"], 5),
            "topRestaurantsByFood" => $this->getTopRestaurants($params + ["sort" => "food"], 5),
            "topRestaurantsByService" => $this->getTopRestaurants($params + ["sort" => "service"], 5),
            "topRestaurantsByCleanliness" => $this->getTopRestaurants($params + ["sort" => "cleanliness"], 5),
            "topRestaurantsByValue" => $this->getTopRestaurants($params + ["sort" => "value"], 5),
            "latestReviews" => $reviews,
            "activeUsers" => $users,
            "commonOccasions" => $occasions,
            "cuisine" => Restaurant::CUISINE,
            "service" => Review::SERVICE,
            "food" => Review::FOOD,
            "occasion" => Review::OCCASION,
            "cleanliness" => Review::CLEANLINESS,
            "value" => Review::VALUE
        ]);
    }

    /**
     * @Route("/moderation")
     * @IsGranted("ROLE_MODERATOR")
     */
    public function moderation(): Response
    {
        $restaurants = $this->entityManager->getRepository("App:Restaurant")->findAll();

        $reviews = $this->entityManager->createQuery('
                SELECT rv
                FROM App:Review rv
                WHERE rv.status = :status
                ORDER BY rv.created DESC')
            ->setParameter("status", Review::STATUS_PENDING)
            ->setFirstResult(0)
            ->setMaxResults(100)
            ->getResult();


        return $this->render('pages/moderation.html.twig', [
            "reviews" => $reviews,
            "restaurants" => $restaurants,
            "role" => User::ROLE,
            "cuisine" => Restaurant::CUISINE,
            "service" => Review::SERVICE,
            "food" => Review::FOOD,
            "occasion" => Review::OCCASION,
            "cleanliness" => Review::CLEANLINESS,
            "value" => Review::VALUE
        ]);
    }
}
