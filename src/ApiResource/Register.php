<?php

namespace App\ApiResource;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Post;
use App\State\UserRegisterProcessor;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(
    operations: [
        new Post(
            uriTemplate: '/register',
            processor: UserRegisterProcessor::class,
            shortName: 'Register',
            description: 'Inscription d\'un nouvel utilisateur (client)',
            openapiContext: [
                'summary' => 'Inscription',
                'description' => 'Permet à un nouvel utilisateur de s\'inscrire en tant que client.',
            ]
        )
    ]
)]
class Register
{
    #[Assert\NotBlank]
    #[Assert\Email]
    public ?string $email = null;

    #[Assert\NotBlank]
    #[Assert\Length(min: 6)]
    public ?string $password = null;

    #[Assert\Length(min: 2, max: 255)]
    public ?string $name = null;
}
