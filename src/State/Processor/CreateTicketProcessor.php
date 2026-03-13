<?php declare(strict_types=1);

namespace App\State\Processor;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\ApiResource\TicketCreation;
use App\Entity\Ticket;
use App\Entity\Category;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * @implements ProcessorInterface<TicketCreation, Ticket>
 */
final class CreateTicketProcessor implements ProcessorInterface
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly Security $security,
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): Ticket
    {
        $category = $this->em->getRepository(Category::class)->find($data->categoryId);
        if (!$category) {
            throw new BadRequestHttpException('Category not found');
        }

        $user = $this->security->getUser();
        if (!$user instanceof User) {
            throw new BadRequestHttpException('User not authenticated');
        }

        $ticket = new Ticket();
        $ticket->setTitle($data->title);
        $ticket->setDescription($data->description);
        $ticket->setCategory($category);
        $ticket->setPriority($data->priority);
        $ticket->setStatus($data->status);
        $ticket->setCreator($user);

        $this->em->persist($ticket);
        $this->em->flush();

        return $ticket;
    }
}
