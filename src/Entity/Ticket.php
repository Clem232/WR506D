<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Trait\TimestampTrait;
use App\Entity\Trait\UuidTrait;
use App\Repository\TicketRepository;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use App\Doctrine\Orm\Filtres\QFilter;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use App\Validator\MaxOpenTickets;

#[ORM\Entity(repositoryClass: TicketRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[MaxOpenTickets]
#[ApiResource(
    operations: [
        new Get(
            normalizationContext: ['groups' => ['ticket:read']],
            security: "is_granted('ROLE_ADMIN') or is_granted('ROLE_SUPER_ADMIN') or object.getCreator() == user or object.getAssignee() == user",
            securityMessage: "Vous ne pouvez voir que vos propres tickets."
        ),
        new GetCollection(
            normalizationContext: ['groups' => ['ticket:read']]
        ),
        new Post(
            processor: \App\State\TicketProcessor::class,
            denormalizationContext: ['groups' => ['ticket:write']],
            validationContext: ['groups' => ['Default', 'ticket:create']]
        ),
        new Put(
            denormalizationContext: ['groups' => ['ticket:write']],
            security: "is_granted('ROLE_ADMIN') or is_granted('ROLE_SUPER_ADMIN') or object.getCreator() == user",
            securityMessage: "Vous ne pouvez modifier que vos propres tickets."
        ),
        new Patch(
            denormalizationContext: ['groups' => ['ticket:write']],
            security: "is_granted('ROLE_ADMIN') or is_granted('ROLE_SUPER_ADMIN') or object.getCreator() == user",
            securityMessage: "Vous ne pouvez modifier que vos propres tickets."
        ),
        new Delete(
            security: "is_granted('ROLE_SUPER_ADMIN')",
            securityMessage: "Seuls les super-administrateurs peuvent supprimer des tickets."
        )
    ],
    normalizationContext: ['groups' => ['ticket:read']],
    denormalizationContext: ['groups' => ['ticket:write']],
    paginationEnabled: true,
    paginationItemsPerPage: 10
)]
#[ApiFilter(SearchFilter::class, properties: ['status' => 'exact', 'priority' => 'exact', 'category.name' => 'partial'])]
#[ApiFilter(OrderFilter::class, properties: ['createdAt' => 'DESC', 'priority' => 'ASC'])]
#[ApiFilter(QFilter::class)]
class Ticket
{
    use UuidTrait, TimestampTrait;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[Groups(['ticket:read', 'ticket:write'])]
    private ?string $title = null;

    #[ORM\Column(type: 'text')]
    #[Assert\NotBlank]
    #[Groups(['ticket:read', 'ticket:write'])]
    private ?string $description = null;

    #[ORM\Column(type: 'string', length: 20)]
    #[Assert\Choice(choices: ['OPEN', 'IN_PROGRESS', 'RESOLVED', 'CLOSED'])]
    #[Groups(['ticket:read', 'ticket:write'])]
    private string $status = 'OPEN';

    #[ORM\Column(type: 'string', length: 20)]
    #[Assert\Choice(choices: ['LOW', 'MEDIUM', 'HIGH'])]
    #[Groups(['ticket:read', 'ticket:write'])]
    private string $priority = 'MEDIUM';

    #[ORM\ManyToOne(targetEntity: Category::class, inversedBy: 'tickets')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull]
    #[Groups(['ticket:read', 'ticket:write'])]
    private ?Category $category = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'tickets')]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups(['ticket:read', 'ticket:write'])]
    private ?User $assignee = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'createdTickets')]
    #[Groups(['ticket:read'])]
    private ?User $creator = null;

    #[ORM\OneToMany(targetEntity: Comment::class, mappedBy: 'ticket', cascade: ['persist', 'remove'])]
    #[Groups(['ticket:read'])]
    private Collection $comments;

    public function __construct()
    {
        $this->comments = new ArrayCollection();
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;
        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;
        return $this;
    }

    public function getPriority(): string
    {
        return $this->priority;
    }

    public function setPriority(string $priority): self
    {
        $this->priority = $priority;
        return $this;
    }

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(Category $category): self
    {
        $this->category = $category;
        return $this;
    }

    public function getAssignee(): ?User
    {
        return $this->assignee;
    }

    public function setAssignee(?User $assignee): self
    {
        $this->assignee = $assignee;
        return $this;
    }

    public function getCreator(): ?User
    {
        return $this->creator;
    }

    public function setCreator(?User $creator): self
    {
        $this->creator = $creator;
        return $this;
    }

    /**
     * @return Collection<int, Comment>
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function addComment(Comment $comment): self
    {
        if (!$this->comments->contains($comment)) {
            $this->comments->add($comment);
            $comment->setTicket($this);
        }
        return $this;
    }
}
