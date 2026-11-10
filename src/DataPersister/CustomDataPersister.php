<?php

namespace App\DataPersister;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\Post;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Review;
use App\Entity\User;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\Attribute\AsDecorator;
use Symfony\Component\DependencyInjection\Attribute\AutowireDecorated;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * This is an "interceptor" for persistance operations on API objects.
 *
 * It decorates API Platform's Doctrine ORM persist processor (the successor
 * of the Doctrine data persister this class used to decorate).
 */
#[AsDecorator('api_platform.doctrine.orm.state.persist_processor')]
final class CustomDataPersister implements ProcessorInterface
{
    public function __construct(
        #[AutowireDecorated]
        private ProcessorInterface $decorated,
        private Security $security,
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        $user = $this->security->getUser();

        // This method is used for the "Write review" page:
        //
        // If the user is NOT logged in, they will first make
        // a POST /api/user request, which will create an anonymous
        // user for them and create an anonymous login.
        //
        // Then the POST /api/review call will be submitted. In either case,
        // we will attach the logged in, or anonymous, user to the review entity.

        if ($data instanceof User) {
            if ($user) {
                throw new BadRequestHttpException("You must not create users through this endpoint.");
            }

            $data->id = "anon_" . substr(md5(microtime(true)), 0, 8);
            $data->type = User::TYPE_ANONYMOUS;
            $data->salt = base64_encode(random_bytes(30));
            $data->password = base64_encode(random_bytes(80));

            // "login" automatically
            $this->security->login($data, firewallName: 'main');
        }

        elseif ($data instanceof Review && $operation instanceof Post) {
            if (!$user) {
                throw new BadRequestHttpException("No user entity is available.");
            }

            $data->guest = $user;
            $data->status = Review::STATUS_PENDING;
        }

        return $this->decorated->process($data, $operation, $uriVariables, $context);
    }
}
