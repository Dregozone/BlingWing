<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController as BaseAbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
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

abstract class AbstractController extends BaseAbstractController
{
    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    public function __construct(EntityManagerInterface $entityManager, LoggerInterface $logger)
    {
        $this->entityManager = $entityManager;
        $this->logger = $logger;
    }

    // fix params array
    protected function fixRestaurantParams(array $params)
    {
        $params["sort"] = in_array($params["sort"] ?? "x", ["total", "newest", "rating", "food", "service", "cleanliness", "value"]) ? $params["sort"] : "rating";
        $params["cuisine"] = (int)($params["cuisine"] ?? 0);
        $params["food"] = (int)($params["food"] ?? 0);
        $params["service"] = (int)($params["service"] ?? 0);
        $params["cleanliness"] = (int)($params["cleanliness"] ?? 0);
        $params["value"] = (int)($params["value"] ?? 0);
        $params["from"] = $params["from"] ?? null;
        $params["until"] = $params["until"] ?? null;

        return $params;
    }

    protected function createRestaurantBaseQueryBuilder()
    {
        return $this->entityManager->createQueryBuilder()
            ->select("rs.name")
            ->addSelect("rs.slug")
            ->addSelect("COUNT(rv.id) AS total")
            ->addSelect("MAX(rv.created) AS newest")
            ->addSelect("AVG(rv.food) AS food")
            ->addSelect("AVG(rv.service) AS service")
            ->addSelect("AVG(rv.cleanliness) AS cleanliness")
            ->addSelect("AVG(rv.value) AS value")

            // qualities will each have a value between 0 and 1 per row (avg divided by max)
            // we weight them individually and then multiply their sum by 10
            // giving us a total score between 0 and 100
            ->addSelect("(
                (AVG(rv.food) / :foodMax)               * 5 +
                (AVG(rv.service) / :serviceMax)         * 2.5 +
                (AVG(rv.cleanliness) / :cleanlinessMax) * 1.5 +
                (AVG(rv.value) / :valueMax)             * 1
            ) * 10 AS rating")
            ->from("App:Restaurant", "rs")
            ->join("rs.reviews", "rv", "WITH", "rv.restaurant = rs.id")
            ->where("rv.status = :status")
            ->groupBy("rs.id")
            ->setParameter('serviceMax', max(array_keys(Review::SERVICE)))
            ->setParameter('foodMax', max(array_keys(Review::FOOD)))
            ->setParameter('cleanlinessMax', max(array_keys(Review::CLEANLINESS)))
            ->setParameter('valueMax', max(array_keys(Review::VALUE)))
            ->setParameter("status", Review::STATUS_PUBLISHED)
            ;
    }

    protected function getTopRestaurants(array $params = [], int $num = 10)
    {
        $params = $this->fixRestaurantParams($params);

        $qb = $this->createRestaurantBaseQueryBuilder()
            ->orderBy($params["sort"], "DESC");

        if ($params["cuisine"]) {
            $qb->andWhere("rs.cuisine = ?1");
            $qb->setParameter(1, $params["cuisine"]);
        }

        if ($params["food"]) {
            $qb->andHaving("food > ?3");
            $qb->andHaving("food < ?4");
            $qb->setParameter(3, $params["food"] - 0.5);
            $qb->setParameter(4, $params["food"] + 0.5);
        }

        if ($params["service"]) {
            $qb->andHaving("service > ?5");
            $qb->andHaving("service < ?6");
            $qb->setParameter(5, $params["service"] - 0.5);
            $qb->setParameter(6, $params["service"] + 0.5);
        }

        if ($params["value"]) {
            $qb->andHaving("value > ?7");
            $qb->andHaving("value < ?8");
            $qb->setParameter(7, $params["value"] - 0.5);
            $qb->setParameter(8, $params["value"] + 0.5);
        }

        if ($params["cleanliness"]) {
            $qb->andHaving("cleanliness > ?9");
            $qb->andHaving("cleanliness < ?10");
            $qb->setParameter(9, $params["cleanliness"] - 0.5);
            $qb->setParameter(10, $params["cleanliness"] + 0.5);
        }

        if ($params["from"]) {
            $qb->andWhere("rv.created >= :dateFrom");
            $qb->setParameter("dateFrom", $params["from"]->format("Y-m-d H:i:s"));
        }

        if ($params["until"]) {
            $qb->andWhere("rv.created <= :dateUntil");
            $qb->setParameter("dateUntil", $params["until"]->format("Y-m-d H:i:s"));
        }

        return $qb
            ->getQuery()
            ->setFirstResult(0)
            ->setMaxResults($num)
            ->getResult();
    }
}
