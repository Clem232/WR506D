<?php declare(strict_types=1);

namespace App\ApiResource;

use ApiPlatform\Metadata\Post;
use App\State\Processor\CreateTicketProcessor;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

#[Post(processor: CreateTicketProcessor::class)]
class TicketCreation
{
    #[Assert\NotBlank(message: 'Title is required')]
    #[Assert\Length(
        min: 5,
        max: 255,
        minMessage: 'Title must be at least 5 characters',
        maxMessage: 'Title cannot exceed 255 characters'
    )]
    public string $title;

    #[Assert\NotBlank(message: 'Description is required')]
    #[Assert\Length(
        min: 10,
        max: 5000,
        minMessage: 'Description must be at least 10 characters',
        maxMessage: 'Description cannot exceed 5000 characters'
    )]
    public string $description;

    #[Assert\NotBlank(message: 'Category ID is required')]
    #[Assert\Uuid(message: 'Invalid category ID format')]
    public string $categoryId;

    #[Assert\NotBlank(message: 'Priority is required')]
    #[Assert\Choice(
        choices: ['LOW', 'MEDIUM', 'HIGH'],
        message: 'Priority must be LOW, MEDIUM or HIGH'
    )]
    public string $priority = 'MEDIUM';

    #[Assert\Choice(
        choices: ['OPEN', 'IN_PROGRESS', 'RESOLVED', 'CLOSED'],
        message: 'Status must be OPEN, IN_PROGRESS, RESOLVED or CLOSED'
    )]
    public string $status = 'OPEN';
}
