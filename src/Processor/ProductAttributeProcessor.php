<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\Processor;

use Sylius\Component\Product\Model\ProductAttributeInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;

final class ProductAttributeProcessor implements ResourceProcessorInterface
{
    private FactoryInterface $productAttributeFactory;
    private RepositoryInterface $productAttributeRepository;
    private MetadataValidatorInterface $metadataValidator;
    private EntityManagerInterface $entityManager;
    private array $headerKeys = ['Code', 'Name', 'Type', 'Position'];

    private array $typeToStorage = [
        'text' => 'text',
        'textarea' => 'text',
        'integer' => 'integer',
        'percent' => 'float',
        'select' => 'json',
        'datetime' => 'datetime',
        'date' => 'date',
        'checkbox' => 'boolean'
    ];

    public function __construct(
        FactoryInterface $productAttributeFactory,
        RepositoryInterface $productAttributeRepository,
        MetadataValidatorInterface $metadataValidator,
        EntityManagerInterface $entityManager
    ) {
        $this->productAttributeFactory = $productAttributeFactory;
        $this->productAttributeRepository = $productAttributeRepository;
        $this->metadataValidator = $metadataValidator;
        $this->entityManager = $entityManager;
    }

    public function process(array $data): void
    {

        $normalizedData = [];
        foreach ($data as $key => $value) {
            if (strpos($key, ';') !== false) {

                $keys = explode(';', $key);
                $values = explode(';', $value);
                

                foreach ($keys as $index => $headerKey) {
                    if (isset($values[$index])) {
                        $normalizedData[trim($headerKey)] = trim($values[$index]);
                    }
                }
                break;
            } else {
                $normalizedData[$key] = $value;
            }
        }

        $this->metadataValidator->validateHeaders($this->headerKeys, $normalizedData);


        $productAttribute = $this->productAttributeRepository->findOneBy(['code' => $normalizedData['Code']]);
        
        if (null === $productAttribute) {
            $productAttribute = $this->productAttributeFactory->createNew();
            $productAttribute->setCode($normalizedData['Code']);
        }

        $type = strtolower($normalizedData['Type']);
        $productAttribute->setType($type);
        
     
        $storageType = $this->typeToStorage[$type] ?? 'text';
        $productAttribute->setStorageType($storageType);

        $productAttribute->getTranslation('en_US')->setName($normalizedData['Name']);
        
        if (isset($normalizedData['Position'])) {
            $productAttribute->setPosition((int) $normalizedData['Position']);
        }


        $productAttribute->setTranslatable(true);

        $this->entityManager->persist($productAttribute);
        $this->entityManager->flush();
    }
}