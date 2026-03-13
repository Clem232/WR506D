<?php declare(strict_types=1);

namespace App\State\Processor;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Comment;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;

/**
 * Processor pour les commentaires : assigne automatiquement l'auteur connecté.
 *
 * @implements ProcessorInterface<Comment, Comment>
 */
final class CommentProcessor implements ProcessorInterface
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly Security $security,
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): Comment
    {
        assert($data instanceof Comment);

        $user = $this->security->getUser();
        if ($user instanceof User && $data->getAuthor() === null) {
            $data->setAuthor($user);
        }

        $this->em->persist($data);
        $this->em->flush();

        return $data;
    }
}
