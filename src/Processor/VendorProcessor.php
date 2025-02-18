<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\Processor;

use BitBag\OpenMarketplace\Entity\VendorInterface;
use Doctrine\ORM\EntityManagerInterface;
use FriendsOfSylius\SyliusImportExportPlugin\Importer\Transformer\TransformerPoolInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;

final class VendorProcessor implements ResourceProcessorInterface
{
    private FactoryInterface $vendorFactory;
    private RepositoryInterface $vendorRepository;
    private MetadataValidatorInterface $metadataValidator;
    private EntityManagerInterface $entityManager;
    private array $headerKeys = ['Id', 'Company_name', 'Tax_ID', 'Status', 'Enabled'];
    private ?TransformerPoolInterface $transformerPool;

    public function __construct(
        FactoryInterface $vendorFactory,
        RepositoryInterface $vendorRepository,
        MetadataValidatorInterface $metadataValidator,
        EntityManagerInterface $entityManager,
        array $headerKeys,
        ?TransformerPoolInterface $transformerPool = null
    ) {
        $this->vendorFactory = $vendorFactory;
        $this->vendorRepository = $vendorRepository;
        $this->metadataValidator = $metadataValidator;
        $this->entityManager = $entityManager;
        $this->headerKeys = $headerKeys;
        $this->transformerPool = $transformerPool;
    }

    public function process(array $data): void
    {
        $this->metadataValidator->validateHeaders($this->headerKeys, $data);

        $vendor = null;
        if (isset($data['Id']) && !empty($data['Id'])) {
            $vendor = $this->vendorRepository->find($data['Id']);
        }

        if (null === $vendor) {
            /** @var VendorInterface $vendor */
            $vendor = $this->vendorFactory->createNew();
        }

        if (isset($data['Company_name']) && !empty($data['Company_name'])) {
            $vendor->setCompanyName($data['Company_name']);
        }

        if (isset($data['Tax_ID']) && !empty($data['Tax_ID'])) {
            $vendor->setTaxIdentifier($data['Tax_ID']);
        }

        if (isset($data['Status'])) {
            $vendor->setStatus($data['Status']);
        }

        if (isset($data['Enabled'])) {
            $vendor->setEnabled($data['Enabled'] === 'Yes' || $data['Enabled'] === '1' || $data['Enabled'] === 'true');
        }

        $this->entityManager->persist($vendor);
        $this->entityManager->flush();
    }
} 