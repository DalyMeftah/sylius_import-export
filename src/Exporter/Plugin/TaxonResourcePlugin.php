<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\Exporter\Plugin;

use Sylius\Component\Core\Model\TaxonInterface;
use Sylius\Component\Resource\Model\ResourceInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

final class TaxonResourcePlugin extends ResourcePlugin
{
    public function __construct(
        RepositoryInterface $repository,
        PropertyAccessorInterface $propertyAccessor,
        EntityManagerInterface $entityManager
    ) {
        parent::__construct($repository, $propertyAccessor, $entityManager);
    }

    public function getData(string $id, array $keysToExport): array
    {
        /** @var TaxonInterface $resource */
        $resource = $this->repository->find($id);
        if (!$resource) {
            throw new \InvalidArgumentException(sprintf('Resource with id "%s" not found', $id));
        }

        return $this->getDataForResource($resource);
    }

    protected function getHeaders(): array
    {
        return [
            'Code',
            'Parent',
            'Name',
            'Slug',
            'Description',
            'Position',
            'Locale',
        ];
    }

    protected function getDataForResource(ResourceInterface $resource): array
    {
        /** @var TaxonInterface $taxon */
        $taxon = $resource;
        $translation = $taxon->getTranslation();

        $data = [
            'Code' => $taxon->getCode(),
            'Parent' => $taxon->getParent() ? $taxon->getParent()->getCode() : '',
            'Name' => $translation->getName(),
            'Slug' => $translation->getSlug(),
            'Description' => $translation->getDescription(),
            'Position' => $taxon->getPosition(),
            'Locale' => $translation->getLocale(),
        ];

        return $data;
    }
} 