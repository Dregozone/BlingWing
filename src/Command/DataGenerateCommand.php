<?php

namespace App\Command;

use App\Entity\Item;
use App\Entity\Member;
use App\Entity\Collection;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Faker\Factory as FakerFactory;
use DateTimeImmutable;
use DateTime;
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
        $this->entityManager->getConnection()->executeQuery("DELETE FROM collection");
        $this->entityManager->getConnection()->executeQuery("DELETE FROM item");
        $this->entityManager->getConnection()->executeQuery("DELETE FROM member");

        $members = $this->createMembers();
        $collections = $this->createCollections($members);
        $items = $this->createItems($collections);
        
        //$this->createCollectionsAndItems($members, $output);

        $this->entityManager->flush();

        return Command::SUCCESS;
    }

    private function createMembers() {
        $faker = FakerFactory::create("en_GB");
        $members = [];

        for ($i=1; $i < 50; $i++) {

            $member = new Member();
            $member->id = "fakeMember$i";
            $member->email = $faker->email;
            $member->title = $faker->name;
            $member->firstName = $faker->name;
            $member->lastName = $faker->name;
            $member->salt = base64_encode(\random_bytes(20));
            $member->password = base64_encode(\random_bytes(50));
            $member->dateAdded = DateTimeImmutable::createFromMutable($faker->dateTimeThisYear());
            $member->type = $faker->randomElement([Member::TYPE_ANONYMOUS, Member::TYPE_SUBSCRIBER]);
            $this->entityManager->persist($member);
            $members[] = $member;
        }

        return $members;
    }

    private function createItems($collections) {
        $faker = FakerFactory::create("en_GB");
        $items = [];

        for ($i=1; $i < 50; $i++) {

            $item = new Item();
            $item->id = "fakeItem$i";
            $item->name = $faker->name;
            $item->price = RAND(1, 100);
            $item->status = RAND(0,5);
            $item->collection = $collections[RAND(0, 4)];
            $item->dateAdded = DateTimeImmutable::createFromMutable($faker->dateTimeThisYear());
            $this->entityManager->persist($item);
            $items[] = $item;
        }

        return $items;
    }

    private function createCollections($members) {
        $faker = FakerFactory::create("en_GB");
        $collections = [];

        for ($i=1; $i < 50; $i++) {

            $collection = new Collection();
            $collection->id = "fakeCollection$i";
            $collection->name = $faker->name;
            $collection->guest = $members[0];
            $collection->image = $faker->name;
            $collection->dateAdded = DateTimeImmutable::createFromMutable($faker->dateTimeThisYear());
            $this->entityManager->persist($collection);
            $collections[] = $collection;
        }

        return $collections;
    }

    /*
    private function createCollectionsAndItems($members, $output)
    {
        $faker = FakerFactory::create("en_GB");
        $slugger = new AsciiSlugger();
        $slugs = []; // collect generated to avoid duplicates

        for ($i = 0; $i < 150; $i++) {
            $collection = new Collection();

            while (true) {
                $collection->name = $faker->company;
                $collection->slug = (string)$slugger->slug(\strtolower($collection->name));

                if (!isset($slugs[$collection->slug])) {
                    $slugs[$collection->slug] = true;
                    break;
                }
            }

            $collection->name = $faker->name;
            //$collection->email = "info@{$collection->slug}.co.uk";
            //$collection->cuisine = $faker->randomElement(array_keys(Collection::CUISINE));
            //$order->address = $faker->address;
            //$order->phoneNumber = $faker->phoneNumber;
            //$order->website = "https://{$order->slug}.co.uk/";
            $this->entityManager->persist($collection);

            $output->writeln($collection->name);

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

                $item = new Item();
                $item->collections = $collection;
                $item->status = $status;
                $item->guest = $faker->randomElement($members);
                //$item->food = $foodRand;
                //$item->service = $serviceRand;
                //$item->cleanliness = $cleanlinessRand;
                $item->price = $valueRand;
                //$item->occasion = $faker->randomElement(array_keys(Item::OCCASION));
                //$item->size = $faker->numberBetween(1, 11);
                $item->name = $faker->realText(200);
                //$item->created = DateTimeImmutable::createFromMutable($faker->dateTimeThisYear());
                $this->entityManager->persist($item);
            }

            $collections[] = $collection;
        }

        return $collections;
    }
    */
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
