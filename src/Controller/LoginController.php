<?php

namespace App\Controller;

use App\Entity\User;
use App\Security\Tokens;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class LoginController extends AbstractController
{
    public function __construct(private Tokens $tokens) {}

    #[Route('/api/login', name: 'api_login', methods: ['POST'])]
    public function index(#[CurrentUser] ?User $user): Response
    {
        if (null === $user) {
            throw $this->createAccessDeniedException();
        }

        $token = $this->tokens->generateTokenForUser($user->getUserIdentifier());

        return $this->json([
            'token' => $token,
            'user' => $user->getUserIdentifier(),
        ]);
    }
}
