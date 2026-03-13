<?php declare(strict_types=1);

namespace App\ApiResource;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use App\State\Provider\VersionProvider;

/**
 * Ressource API qui expose le numéro de version de l'application.
 * Le numéro de version est lu depuis la variable d'environnement VERSION.
 */
#[ApiResource(
    operations: [
        new Get(
            uriTemplate: '/version',
            provider: VersionProvider::class,
            shortName: 'Version',
            description: 'Retourne le numéro de version de l\'API',
            openapiContext: [
                'summary' => 'Numéro de version de l\'API',
                'description' => 'Retourne la version courante de l\'API, lue depuis la variable d\'environnement VERSION.',
            ]
        )
    ]
)]
class Version
{
    public function __construct(
        public readonly string $version = '0.0.0',
    ) {
    }
}
