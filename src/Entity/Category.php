<?php declare(strict_types=1);

namespace App\Entity;

use App\Entity\Trait\TimestampTrait;
use App\Entity\Trait\UuidTrait;
use App\Repository\CategoryRepository;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Delete;
use App\Filter\OnlyWithTodoFilter;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CategoryRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ApiResource(
    operations: [
        new Get(normalizationContext: ['groups' => ['category:read']]),
        new GetCollection(normalizationContext: ['groups' => ['category:read']]),
        new Post(
            denormalizationContext: ['groups' => ['category:write']],
            security: "is_granted('ROLE_ADMIN') or is_granted('ROLE_SUPER_ADMIN')",
            securityMessage: "Seuls les administrateurs peuvent créer des catégories."
        ),
        new Put(
            denormalizationContext: ['groups' => ['category:write']],
            security: "is_granted('ROLE_ADMIN') or is_granted('ROLE_SUPER_ADMIN')"
        ),
        new Patch(
            denormalizationContext: ['groups' => ['category:write']],
            security: "is_granted('ROLE_ADMIN') or is_granted('ROLE_SUPER_ADMIN')"
        ),
        new Delete(
            security: "is_granted('ROLE_SUPER_ADMIN')"
        ),
    ],
    normalizationContext: ['groups' => ['category:read']],
    denormalizationContext: ['groups' => ['category:write']],
    paginationEnabled: true,
    paginationItemsPerPage: 10
)]
#[ApiFilter(OnlyWithTodoFilter::class)]
class Category
{
    use UuidTrait, TimestampTrait;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[Groups(['category:read', 'category:write', 'ticket:read'])]
    private ?string $name = null;

    #[ORM\Column(type: 'text', nullable: true)]
    #[Groups(['category:read', 'category:write'])]
    private ?string $description = null;

    /**
     * @var Collection<int, Ticket>
     */
    #[ORM\OneToMany(targetEntity: Ticket::class, mappedBy: 'category')]
    private Collection $tickets;

    public function __construct()
    {
        $this->tickets = new ArrayCollection();
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;
        return $this;
    }

    public function getTickets(): Collection
    {
        return $this->tickets;
    }
}
