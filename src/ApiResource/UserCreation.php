<?php declare(strict_types=1);

namespace App\ApiResource;

use ApiPlatform\Metadata\Post;
use App\State\Processor\CreateUserProcessor;
use Symfony\Component\Validator\Constraints as Assert;

#[Post(
    processor: CreateUserProcessor::class,
    security: "is_granted('ROLE_ADMIN') or is_granted('ROLE_SUPER_ADMIN')",
    securityMessage: "Seuls les administrateurs peuvent créer des utilisateurs."
)]
class UserCreation
{
    #[Assert\NotBlank(message: 'Email is required')]
    #[Assert\Email(message: 'Invalid email format')]
    public string $email;

    #[Assert\NotBlank(message: 'Password is required')]
    #[Assert\Length(
        min: 8,
        minMessage: 'Password must be at least 8 characters long'
    )]
    #[Assert\Regex(
        pattern: '/[A-Z]/',
        message: 'Password must contain at least one uppercase letter'
    )]
    #[Assert\Regex(
        pattern: '/[0-9]/',
        message: 'Password must contain at least one number'
    )]
    public string $password;

    #[Assert\NotBlank(message: 'Name is required')]
    #[Assert\Length(
        min: 2,
        max: 255,
        minMessage: 'Name must be at least 2 characters',
        maxMessage: 'Name cannot exceed 255 characters'
    )]
    public string $name;
}
