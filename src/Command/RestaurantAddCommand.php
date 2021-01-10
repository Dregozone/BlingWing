<?php

namespace App\Command;

use Exception;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use App\Entity\Restaurant;
use Symfony\Component\String\Slugger\AsciiSlugger;

class RestaurantAddCommand extends Command
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
    }

    protected function configure()
    {
        $this
            ->setName('app:restaurant:create')
            ->setDescription('Add a restaurant to the users database')
            ->addArgument('name', InputArgument::REQUIRED)
            ->addArgument('owner', InputArgument::REQUIRED)
            ->addArgument('email', InputArgument::REQUIRED)
            ->addArgument('phone', InputArgument::REQUIRED)
            ->addArgument('address', InputArgument::REQUIRED)
            ->addArgument('cuisine', InputArgument::REQUIRED)
            ->addArgument('website', InputArgument::REQUIRED);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $slugger = new AsciiSlugger();
        $restaurant = new Restaurant();

        $restaurant->name = $input->getArgument("name");
        $restaurant->slug = $slugger->slug(\strtolower($restaurant->name));
        $restaurant->cuisine = $input->getArgument("cuisine");
        $restaurant->owner = $input->getArgument("owner");
        $restaurant->email = $input->getArgument("email");
        $restaurant->address = $input->getArgument("address");
        $restaurant->phoneNumber = $input->getArgument("phone");
        $restaurant->website = $input->getArgument("website");


        $this->entityManager->persist($restaurant);
        $this->entityManager->flush();

        return Command::SUCCESS;
    }
}
