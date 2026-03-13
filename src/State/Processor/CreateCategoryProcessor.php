<?php declare(strict_types=1);

namespace App\State\Processor;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\ApiResource\CategoryCreation;
use App\Entity\Category;
use Doctrine\ORM\EntityManagerInterface;

/**
 * @implements ProcessorInterface<CategoryCreation, Category>
 */
final class CreateCategoryProcessor implements ProcessorInterface
{
    public function __construct(
        private readonly EntityManagerInterface $em,
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): Category
    {
        $category = new Category();
        $category->setName($data->name);
        $category->setDescription($data->description);

        $this->em->persist($category);
        $this->em->flush();

        return $category;
    }
}
