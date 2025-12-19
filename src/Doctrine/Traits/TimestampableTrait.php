<?php
namespace App\Doctrine\Traits;

use Doctrine\ORM\Mapping as ORM;

trait TimestampableTrait
{
    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }
    public function setCreatedAt(\DateTimeImmutable $d): void { $this->createdAt = $d; }

    public function getUpdatedAt(): ?\DateTimeImmutable { return $this->updatedAt; }
    public function setUpdatedAt(?\DateTimeImmutable $d): void { $this->updatedAt = $d; }

    public function prePersistTimestamps(): void {
        $this->createdAt = new \DateTimeImmutable();
    }
    public function preUpdateTimestamps(): void {
        $this->updatedAt = new \DateTimeImmutable();
    }
}
