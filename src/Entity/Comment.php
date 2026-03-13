<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Delete;
use App\Entity\Trait\TimestampTrait;
use App\Entity\Trait\UuidTrait;
use App\Repository\CommentRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CommentRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ApiResource(
    operations: [
        new Get(normalizationContext: ['groups' => ['comment:read']]),
        new GetCollection(normalizationContext: ['groups' => ['comment:read']]),
        new Post(
            denormalizationContext: ['groups' => ['comment:write']],
            security: "is_granted('ROLE_CLIENT') or is_granted('ROLE_AGENT') or is_granted('ROLE_ADMIN')"
        ),
        new Delete(
            security: "is_granted('ROLE_ADMIN') or is_granted('ROLE_SUPER_ADMIN')"
        ),
    ],
    normalizationContext: ['groups' => ['comment:read']],
    denormalizationContext: ['groups' => ['comment:write']],
    paginationEnabled: true
)]
class Comment
{
    use UuidTrait, TimestampTrait;

    #[ORM\Column(type: 'text')]
    #[Assert\NotBlank(message: 'Le contenu du commentaire est obligatoire')]
    #[Groups(['comment:read', 'comment:write', 'ticket:read'])]
    private string $content = '';

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[Groups(['comment:read'])]
    private ?User $author = null;

    #[ORM\ManyToOne(targetEntity: Ticket::class, inversedBy: 'comments')]
    #[Groups(['comment:read', 'comment:write'])]
    private ?Ticket $ticket = null;

    public function getContent(): string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;
        return $this;
    }

    public function getAuthor(): ?User
    {
        return $this->author;
    }

    public function setAuthor(?User $author): self
    {
        $this->author = $author;
        return $this;
    }

    public function getTicket(): ?Ticket
    {
        return $this->ticket;
    }

    public function setTicket(?Ticket $ticket): self
    {
        $this->ticket = $ticket;
        return $this;
    }
}
