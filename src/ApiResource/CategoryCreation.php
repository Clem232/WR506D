<?php declare(strict_types=1);

namespace App\ApiResource;

use ApiPlatform\Metadata\Post;
use App\State\Processor\CreateCategoryProcessor;
use Symfony\Component\Validator\Constraints as Assert;

#[Post(processor: CreateCategoryProcessor::class)]
class CategoryCreation
{
    #[Assert\NotBlank(message: 'Category name is required')]
    #[Assert\Length(
        min: 3,
        max: 255,
        minMessage: 'Name must be at least 3 characters',
        maxMessage: 'Name cannot exceed 255 characters'
    )]
    public string $name;

    #[Assert\Length(
        max: 1000,
        maxMessage: 'Description cannot exceed 1000 characters'
    )]
    public ?string $description = null;
}
