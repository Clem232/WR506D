<?php declare(strict_types=1);

namespace App\Entity;

use App\Entity\Trait\TimestampTrait;
use App\Entity\Trait\UuidTrait;
use App\Repository\UserRepository;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
#[ORM\HasLifecycleCallbacks]
#[ApiResource(
    operations: [
        new Get(normalizationContext: ['groups' => ['user:read']]),
        new GetCollection(normalizationContext: ['groups' => ['user:read']]),
    ],
    normalizationContext: ['groups' => ['user:read']],
    paginationEnabled: true,
    paginationItemsPerPage: 10
)]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    use UuidTrait, TimestampTrait;

    #[ORM\Column(length: 180, unique: true)]
    #[Assert\Email]
    #[Groups(['user:read', 'ticket:read', 'comment:read'])]
    private ?string $email = null;

    #[ORM\Column]
    private array $roles = [];

    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[Groups(['user:read', 'ticket:read', 'comment:read'])]
    private ?string $name = null;

    #[ORM\Column(type: 'string', length: 20)]
    #[Assert\Choice(choices: ['ROLE_CLIENT', 'ROLE_AGENT', 'ROLE_ADMIN', 'ROLE_SUPER_ADMIN'])]
    #[Groups(['user:read'])]
    private string $role = 'ROLE_CLIENT';

    /**
     * @var Collection<int, Ticket>
     */
    #[ORM\OneToMany(targetEntity: Ticket::class, mappedBy: 'assignee')]
    private Collection $tickets;

    /**
     * @var Collection<int, Ticket>
     */
    #[ORM\OneToMany(targetEntity: Ticket::class, mappedBy: 'creator')]
    private Collection $createdTickets;

    public function __construct()
    {
        $this->tickets = new ArrayCollection();
        $this->createdTickets = new ArrayCollection();
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;
        return $this;
    }

    public function getUserIdentifier(): string
    {
        return (string)$this->email;
    }

    public function getRoles(): array
    {
        $roles = $this->roles;
        $roles[] = $this->role;
        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;
        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;
        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getRole(): string
    {
        return $this->role;
    }

    public function setRole(string $role): self
    {
        $this->role = $role;
        return $this;
    }

    public function eraseCredentials(): void
    {
    }

    public function getTickets(): Collection
    {
        return $this->tickets;
    }

    public function getCreatedTickets(): Collection
    {
        return $this->createdTickets;
    }
}
