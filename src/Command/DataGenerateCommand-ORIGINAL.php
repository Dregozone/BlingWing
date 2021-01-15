<?php

namespace App\Command;

use App\Entity\Restaurant;
use App\Entity\User;
use App\Entity\Review;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Faker\Factory as FakerFactory;
use DateTimeImmutable;
use Symfony\Component\String\Slugger\AsciiSlugger;

class DataGenerateCommand extends Command
{
    protected static $defaultName = 'app:data:generate';

    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var ParameterBagInterface
     */
    private $params;

    public function __construct(EntityManagerInterface $entityManager, ParameterBagInterface $params)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
        $this->params = $params;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->entityManager->getConnection()->executeQuery("DELETE FROM user");
        $this->entityManager->getConnection()->executeQuery("DELETE FROM restaurant");
        $this->entityManager->getConnection()->executeQuery("DELETE FROM review");

        $users = $this->createUsers();
        $this->createRestaurantsAndReviews($users, $output);

        $this->entityManager->flush();

        return Command::SUCCESS;
    }

    private function createUsers() {
        $faker = FakerFactory::create("en_GB");
        $users = [];

        for ($i=1; $i < 50; $i++) {

            $user = new User();
            $user->id = "fakeUser$i";
            $user->email = $faker->email;
            $user->name = $faker->name;
            $user->salt = base64_encode(\random_bytes(20));
            $user->password = base64_encode(\random_bytes(50));
            $user->type = $faker->randomElement([User::TYPE_ANONYMOUS, User::TYPE_SUBSCRIBER]);
            $this->entityManager->persist($user);
            $users[] = $user;
        }

        return $users;
    }

    private function createRestaurantsAndReviews($users, $output)
    {
        $faker = FakerFactory::create("en_GB");
        $slugger = new AsciiSlugger();
        $slugs = []; // collect generated to avoid duplicates

        for ($i = 0; $i < 150; $i++) {
            $restaurant = new Restaurant();

            while (true) {
                $restaurant->name = $faker->company;
                $restaurant->slug = (string)$slugger->slug(\strtolower($restaurant->name));

                if (!isset($slugs[$restaurant->slug])) {
                    $slugs[$restaurant->slug] = true;
                    break;
                }
            }

            $restaurant->owner = $faker->name;
            $restaurant->email = "info@{$restaurant->slug}.co.uk";
            $restaurant->cuisine = $faker->randomElement(array_keys(Restaurant::CUISINE));
            $restaurant->address = $faker->address;
            $restaurant->phoneNumber = $faker->phoneNumber;
            $restaurant->website = "https://{$restaurant->slug}.co.uk/";
            $this->entityManager->persist($restaurant);

            $output->writeln($restaurant->name);

            // the distribution of a restaurant’s ratings should
            // roughly resemble a (skewed) bell curve
            // therefore we first generate a skew value per restaurant,
            // and then gaussian random values per review
            $skew = round(mt_rand(-1, 2));

            for ($j = 1; $j < mt_rand(100, 1000); $j++) {
                $serviceRand = max(min(gaussRand(1, 5) + $skew, 5), 1);
                $foodRand = max(min(gaussRand(1, 5) + $skew, 5), 1);
                $valueRand = max(min(gaussRand(1, 5) + $skew, 5), 1);
                $cleanlinessRand = max(min(gaussRand(1, 5) + $skew, 5), 1);

                // generate random status, but make sure that most reviews are "published"
                $status = mt_rand(1, 70);
                $status = $status > 4 ? 3 : $status;

                $review = new Review();
                $review->restaurant = $restaurant;
                $review->status = $status;
                $review->guest = $faker->randomElement($users);
                $review->food = $foodRand;
                $review->service = $serviceRand;
                $review->cleanliness = $cleanlinessRand;
                $review->value = $valueRand;
                $review->occasion = $faker->randomElement(array_keys(Review::OCCASION));
                $review->size = $faker->numberBetween(1, 11);
                $review->comment = $faker->realText(200);
                $review->created = DateTimeImmutable::createFromMutable($faker->dateTimeThisYear());
                $this->entityManager->persist($review);
            }

            $restaurants[] = $restaurant;
        }

        return $restaurants;
    }
}

// https://natedenlinger.com/php-random-number-generator-with-normal-distribution-bell-curve/
function gaussRand($min,$max,$std_deviation=5,$step=1) {
  $rand1 = (float)mt_rand()/(float)mt_getrandmax();
  $rand2 = (float)mt_rand()/(float)mt_getrandmax();
  $gaussian_number = sqrt(-2 * log($rand1)) * cos(2 * M_PI * $rand2);
  $mean = ($max + $min) / 2;
  $random_number = ($gaussian_number * $std_deviation) + $mean;
  $random_number = round($random_number / $step) * $step;
  if($random_number < $min || $random_number > $max) {
    $random_number = gaussRand($min, $max,$std_deviation);
  }
  return $random_number;
}
