<?php declare(strict_types=1);

namespace App\State\Provider;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\ApiResource\Version;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * Provider custom qui retourne le numéro de version depuis une variable d'environnement.
 */
final class VersionProvider implements ProviderInterface
{
    public function __construct(
        #[Autowire(env: 'VERSION')]
        private readonly string $version,
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): Version
    {
        return new Version($this->version);
    }
}
