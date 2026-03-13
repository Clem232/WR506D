<?php declare(strict_types=1);

namespace App\Tests\Functional;

use App\Entity\Category;
use App\Entity\Ticket;
use App\Entity\User;
use App\Security\Tokens;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Uid\Uuid;

/**
 * Tests fonctionnels vérifiant la création, modification et suppression
 * d'un ticket par un utilisateur "classique" (ROLE_CLIENT).
 */
class TicketCrudTest extends WebTestCase
{
    private ?EntityManagerInterface $em = null;
    private ?User $clientUser = null;
    private ?User $adminUser = null;
    private ?Category $category = null;
    private ?string $clientToken = null;
    private ?string $adminToken = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->em = static::getContainer()->get(EntityManagerInterface::class);
        $tokens = static::getContainer()->get(Tokens::class);
        $hasher = static::getContainer()->get(UserPasswordHasherInterface::class);

        // Créer un utilisateur client
        $this->clientUser = new User();
        $this->clientUser->setEmail('client-test-' . uniqid() . '@test.com');
        $this->clientUser->setName('Client Test');
        $this->clientUser->setRole('ROLE_CLIENT');
        $this->clientUser->setPassword($hasher->hashPassword($this->clientUser, 'password123'));
        $this->em->persist($this->clientUser);

        // Créer un utilisateur admin
        $this->adminUser = new User();
        $this->adminUser->setEmail('admin-test-' . uniqid() . '@test.com');
        $this->adminUser->setName('Admin Test');
        $this->adminUser->setRole('ROLE_SUPER_ADMIN');
        $this->adminUser->setPassword($hasher->hashPassword($this->adminUser, 'password123'));
        $this->em->persist($this->adminUser);

        // Créer une catégorie
        $this->category = new Category();
        $this->category->setName('Test Category ' . uniqid());
        $this->category->setDescription('Catégorie de test');
        $this->em->persist($this->category);

        $this->em->flush();

        // Générer les tokens
        $this->clientToken = $tokens->generateTokenForUser($this->clientUser->getEmail());
        $this->adminToken = $tokens->generateTokenForUser($this->adminUser->getEmail());
    }

    /**
     * Teste qu'un client peut créer un ticket via l'API.
     */
    public function testClientCanCreateTicket(): void
    {
        $client = static::createClient();

        $client->request('POST', '/api/tickets', [], [], [
            'CONTENT_TYPE' => 'application/ld+json',
            'HTTP_AUTHORIZATION' => $this->clientToken,
        ], json_encode([
            'title' => 'Mon ticket de test',
            'description' => 'Description de mon problème',
            'status' => 'OPEN',
            'priority' => 'HIGH',
            'category' => '/api/categories/' . $this->category->getId(),
        ]));

        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);

        $response = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals('Mon ticket de test', $response['title'] ?? null);
        $this->assertEquals('OPEN', $response['status'] ?? null);
        $this->assertEquals('HIGH', $response['priority'] ?? null);
    }

    /**
     * Teste qu'un client peut modifier son propre ticket.
     */
    public function testClientCanUpdateOwnTicket(): void
    {
        $client = static::createClient();

        // Créer un ticket d'abord
        $ticket = new Ticket();
        $ticket->setTitle('Ticket à modifier');
        $ticket->setDescription('Description originale');
        $ticket->setStatus('OPEN');
        $ticket->setPriority('LOW');
        $ticket->setCategory($this->category);
        $ticket->setCreator($this->clientUser);
        $this->em->persist($ticket);
        $this->em->flush();

        // Modifier le ticket
        $client->request('PATCH', '/api/tickets/' . $ticket->getId(), [], [], [
            'CONTENT_TYPE' => 'application/merge-patch+json',
            'HTTP_AUTHORIZATION' => $this->clientToken,
        ], json_encode([
            'title' => 'Ticket modifié',
            'priority' => 'HIGH',
        ]));

        $this->assertResponseIsSuccessful();

        $response = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals('Ticket modifié', $response['title'] ?? null);
        $this->assertEquals('HIGH', $response['priority'] ?? null);
    }

    /**
     * Teste qu'un client NE PEUT PAS supprimer un ticket (réservé au super-admin).
     */
    public function testClientCannotDeleteTicket(): void
    {
        $client = static::createClient();

        // Créer un ticket
        $ticket = new Ticket();
        $ticket->setTitle('Ticket à ne pas supprimer');
        $ticket->setDescription('Ce ticket ne doit pas être supprimé par un client');
        $ticket->setStatus('OPEN');
        $ticket->setPriority('MEDIUM');
        $ticket->setCategory($this->category);
        $ticket->setCreator($this->clientUser);
        $this->em->persist($ticket);
        $this->em->flush();

        // Tenter de supprimer en tant que client
        $client->request('DELETE', '/api/tickets/' . $ticket->getId(), [], [], [
            'HTTP_AUTHORIZATION' => $this->clientToken,
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    /**
     * Teste qu'un super-admin PEUT supprimer un ticket.
     */
    public function testSuperAdminCanDeleteTicket(): void
    {
        $client = static::createClient();

        // Créer un ticket
        $ticket = new Ticket();
        $ticket->setTitle('Ticket à supprimer par admin');
        $ticket->setDescription('Ce ticket sera supprimé par un super-admin');
        $ticket->setStatus('OPEN');
        $ticket->setPriority('MEDIUM');
        $ticket->setCategory($this->category);
        $ticket->setCreator($this->clientUser);
        $this->em->persist($ticket);
        $this->em->flush();

        $ticketId = $ticket->getId();

        // Supprimer en tant que super-admin
        $client->request('DELETE', '/api/tickets/' . $ticketId, [], [], [
            'HTTP_AUTHORIZATION' => $this->adminToken,
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);
    }

    /**
     * Teste qu'un client ne peut pas modifier le ticket d'un autre utilisateur.
     */
    public function testClientCannotUpdateOtherUserTicket(): void
    {
        $client = static::createClient();
        $hasher = static::getContainer()->get(UserPasswordHasherInterface::class);
        $tokens = static::getContainer()->get(Tokens::class);

        // Créer un autre client
        $otherUser = new User();
        $otherUser->setEmail('other-client-' . uniqid() . '@test.com');
        $otherUser->setName('Other Client');
        $otherUser->setRole('ROLE_CLIENT');
        $otherUser->setPassword($hasher->hashPassword($otherUser, 'password123'));
        $this->em->persist($otherUser);

        // Créer un ticket appartenant à l'autre client
        $ticket = new Ticket();
        $ticket->setTitle('Ticket autre utilisateur');
        $ticket->setDescription('Ce ticket appartient à un autre utilisateur');
        $ticket->setStatus('OPEN');
        $ticket->setPriority('LOW');
        $ticket->setCategory($this->category);
        $ticket->setCreator($otherUser);
        $this->em->persist($ticket);
        $this->em->flush();

        // Tenter de modifier en tant que notre client
        $client->request('PATCH', '/api/tickets/' . $ticket->getId(), [], [], [
            'CONTENT_TYPE' => 'application/merge-patch+json',
            'HTTP_AUTHORIZATION' => $this->clientToken,
        ], json_encode([
            'title' => 'Tentative de modification',
        ]));

        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    /**
     * Teste la validation : un ticket sans titre est rejeté.
     */
    public function testTicketCreationValidation(): void
    {
        $client = static::createClient();

        $client->request('POST', '/api/tickets', [], [], [
            'CONTENT_TYPE' => 'application/ld+json',
            'HTTP_AUTHORIZATION' => $this->clientToken,
        ], json_encode([
            'title' => '',
            'description' => '',
            'priority' => 'INVALID',
            'category' => '/api/categories/' . $this->category->getId(),
        ]));

        $this->assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->em = null;
    }
}
