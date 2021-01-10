<?php

namespace App\DataPersister;

use ApiPlatform\Core\DataPersister\ContextAwareDataPersisterInterface;
use App\Entity\Review;
use App\Entity\User;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Security\Core\Security;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Security\Guard\GuardAuthenticatorHandler;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * This is an "interceptor" for persistance operations on API objects
 *
 * @var [type]
 */
final class CustomDataPersister implements ContextAwareDataPersisterInterface
{
    /**
     * @var DataPersisterInterface
     */
    private $decorated;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var Security
     */
    private $security;

    /**
     * @var GuardAuthenticatorHandler
     */
    private $guardHandler;

    /**
     * @var RequestStack
     */
    private $requestStack;

    public function __construct(
        ContextAwareDataPersisterInterface $decorated,
        EntityManagerInterface $entityManager,
        Security $security,
        GuardAuthenticatorHandler $guardHandler,
        RequestStack $requestStack
    )
    {
        $this->decorated = $decorated;
        $this->entityManager = $entityManager;
        $this->security = $security;
        $this->guardHandler = $guardHandler;
        $this->requestStack = $requestStack;
    }

    public function supports($data, array $context = []): bool
    {
        return $this->decorated->supports($data, $context);
    }

    public function persist($data, array $context = [])
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
            $token = new UsernamePasswordToken($data, null, 'main', $data->getRoles());
            $request = $this->requestStack->getCurrentRequest();
            $this->guardHandler->authenticateWithToken($token, $request);
        }

        elseif ($data instanceof Review && ($context["collection_operation_name"] ?? 0) === "post") {
            if (!$user) {
                throw new BadRequestHttpException("No user entity is available.");
            }

            $data->guest = $user;
            $data->status = Review::STATUS_PENDING;
        }

        return $this->decorated->persist($data, $context);
    }

    public function remove($data, array $context = [])
    {
        return $this->decorated->remove($data, $context);
    }
}
