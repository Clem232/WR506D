<?php

namespace App\Doctrine\Traits;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

trait UuidTrait
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    private ?\Symfony\Component\Uid\Uuid $uuid = null;

    public function getUuid(): ?\Symfony\Component\Uid\Uuid
    {
        return $this->uuid;
    }

    public function defineUuid(): void
    {
        if (null === $this->uuid) {
            $this->uuid = Uuid::v4();
        }
    }
}
