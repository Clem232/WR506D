<?php declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Category;
use App\Entity\Comment;
use App\Entity\Ticket;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function __construct(
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        // ── Utilisateurs ──────────────────────────────────────────────────────
        $superAdmin = $this->createUser($manager, 'superadmin@techsupport360.com', 'Admin360!', 'Super Admin', 'ROLE_SUPER_ADMIN');
        $admin      = $this->createUser($manager, 'admin@techsupport360.com',      'Admin360!', 'Admin',       'ROLE_ADMIN');
        $agent1     = $this->createUser($manager, 'agent1@techsupport360.com',     'Agent360!', 'Alice Agent', 'ROLE_AGENT');
        $agent2     = $this->createUser($manager, 'agent2@techsupport360.com',     'Agent360!', 'Bob Agent',   'ROLE_AGENT');
        $client1    = $this->createUser($manager, 'client1@example.com',           'Client360!','Jean Dupont', 'ROLE_CLIENT');
        $client2    = $this->createUser($manager, 'client2@example.com',           'Client360!','Marie Martin','ROLE_CLIENT');

        // ── Catégories ────────────────────────────────────────────────────────
        $categories = [];
        foreach ([
            ['Facturation',       'Questions relatives aux factures et paiements'],
            ['Technique',         'Problèmes techniques liés au matériel ou logiciel'],
            ['Bug logiciel',      'Signalement de bugs dans les applications'],
            ['Accès & Sécurité',  'Problèmes de connexion, mots de passe, droits'],
            ['Réseau',            'Lenteurs, coupures et configuration réseau'],
        ] as [$name, $desc]) {
            $cat = new Category();
            $cat->setName($name);
            $cat->setDescription($desc);
            $manager->persist($cat);
            $categories[] = $cat;
        }

        // ── Tickets ───────────────────────────────────────────────────────────
        $ticketData = [
            ['Facture incorrecte du mois de janvier',    'Le montant facturé ne correspond pas au devis signé.', 'HIGH',   'OPEN',        $client1, $agent1, $categories[0]],
            ['Impossible de se connecter au VPN',        'Depuis hier matin, le VPN refus ma connexion.',        'HIGH',   'IN_PROGRESS', $client1, $agent1, $categories[3]],
            ['Application de compta plante au démarrage','L\'appli crash dès l\'ouverture sur Windows 11.',     'MEDIUM', 'IN_PROGRESS', $client1, $agent2, $categories[2]],
            ['Lenteur réseau bureau 3ème étage',         'Le wifi est très lent depuis le changement de box.',   'LOW',    'OPEN',        $client2, $agent2, $categories[4]],
            ['Mise à jour logiciel bloquée',             'Windows Update reste bloqué à 45%.',                  'MEDIUM', 'RESOLVED',    $client2, $agent1, $categories[1]],
            ['Erreur 403 sur l\'extranet client',        'Les clients externes ne peuvent plus accéder au portail.','HIGH','OPEN',       $client2, $agent2, $categories[2]],
        ];

        $tickets = [];
        foreach ($ticketData as [$title, $desc, $priority, $status, $creator, $assignee, $category]) {
            $ticket = new Ticket();
            $ticket->setTitle($title);
            $ticket->setDescription($desc);
            $ticket->setPriority($priority);
            $ticket->setStatus($status);
            $ticket->setCreator($creator);
            $ticket->setAssignee($assignee);
            $ticket->setCategory($category);
            $manager->persist($ticket);
            $tickets[] = $ticket;
        }

        // ── Commentaires ──────────────────────────────────────────────────────
        $commentData = [
            [$tickets[0], $client1, 'Bonjour, pouvez-vous vérifier la facture n°2024-0142 svp ?'],
            [$tickets[0], $agent1,  'Bonjour, je regarde ça et reviens vers vous rapidement.'],
            [$tickets[1], $agent1,  'J\'ai réinitialisé votre certificat VPN, pouvez-vous réessayer ?'],
            [$tickets[1], $client1, 'Ça fonctionne maintenant, merci beaucoup !'],
            [$tickets[2], $agent2,  'Avez-vous essayé de désinstaller et réinstaller l\'application ?'],
            [$tickets[3], $client2, 'Le problème persiste même en redémarrant le routeur.'],
        ];

        foreach ($commentData as [$ticket, $author, $content]) {
            $comment = new Comment();
            $comment->setContent($content);
            $comment->setAuthor($author);
            $comment->setTicket($ticket);
            $manager->persist($comment);
        }

        $manager->flush();
    }

    private function createUser(
        ObjectManager $manager,
        string $email,
        string $plainPassword,
        string $name,
        string $role,
    ): User {
        $user = new User();
        $user->setEmail($email);
        $user->setName($name);
        $user->setRole($role);
        $user->setPassword($this->passwordHasher->hashPassword($user, $plainPassword));
        $manager->persist($user);

        return $user;
    }
}
