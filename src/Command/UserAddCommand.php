<?php
declare(strict_types=1);

/*
 * @package    tixys/framework-bundle
 * @author     Alexander Günsche
 * @copyright  AGITsol GmbH
 */

namespace App\Command;

use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use App\Entity\User;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserAddCommand extends Command
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var UserPasswordEncoderInterface
     */
    private $encoder;

    public function __construct(EntityManagerInterface $entityManager, UserPasswordEncoderInterface $encoder)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
        $this->encoder = $encoder;
    }

    protected function configure()
    {
        $this
            ->setName('app:user:create')
            ->setDescription('Add a user to the users database')
            ->addArgument('id', InputArgument::REQUIRED, 'user id/login, only letters')
            ->addArgument('name', InputArgument::REQUIRED, 'real name')
            ->addArgument('email', InputArgument::REQUIRED, 'e-mail address')
            ->addArgument('password', InputArgument::REQUIRED, 'password')
            ->addArgument('type', InputArgument::REQUIRED, 'user type (anonymous, subscriber, moderator, analyst)')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $user = new User();
        $roles = array_flip(User::ROLE);

        $user->salt = base64_encode(random_bytes(18));
        $user->id = $input->getArgument("id");
        $user->name = $input->getArgument("name");
        $user->email = $input->getArgument("email");
        $user->type = $roles[$input->getArgument("type")];
        $user->password = $this->encoder->encodePassword($user, $input->getArgument("password"));

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return Command::SUCCESS;
    }
}
