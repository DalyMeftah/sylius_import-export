<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\Processor;

use Sylius\Component\Product\Model\ProductOptionInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;

final class ProductOptionProcessor implements ResourceProcessorInterface
{
    private FactoryInterface $productOptionFactory;
    private RepositoryInterface $productOptionRepository;
    private MetadataValidatorInterface $metadataValidator;
    private EntityManagerInterface $entityManager;
    private array $headerKeys = ['Code', 'Name', 'Position'];

    public function __construct(
        FactoryInterface $productOptionFactory,
        RepositoryInterface $productOptionRepository,
        MetadataValidatorInterface $metadataValidator,
        EntityManagerInterface $entityManager
    ) {
        $this->productOptionFactory = $productOptionFactory;
        $this->productOptionRepository = $productOptionRepository;
        $this->metadataValidator = $metadataValidator;
        $this->entityManager = $entityManager;
    }

    public function process(array $data): void
    {
        $this->metadataValidator->validateHeaders($this->headerKeys, $data);

        $existingOption = $this->productOptionRepository->findOneBy(['code' => $data['Code']]);
        $productOption = $existingOption ?? $this->productOptionFactory->createNew();

        if (!$existingOption) {
            $productOption->setCode($data['Code']);
        }

        if (isset($data['Name']) && !empty($data['Name'])) {
            $productOption->setName($data['Name']);
        }

        if (isset($data['Position'])) {
            $productOption->setPosition((int) $data['Position']);
        }

        $this->entityManager->persist($productOption);
        $this->entityManager->flush();
    }
}