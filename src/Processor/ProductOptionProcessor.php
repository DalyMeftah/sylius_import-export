<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\Processor;

use Sylius\Component\Product\Model\ProductOptionInterface;
use Sylius\Component\Product\Model\ProductOptionValueInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use FriendsOfSylius\SyliusImportExportPlugin\Exception\ImporterException;

final class ProductOptionProcessor implements ResourceProcessorInterface
{
    private FactoryInterface $productOptionFactory;
    private FactoryInterface $productOptionValueFactory;
    private RepositoryInterface $productOptionRepository;
    private MetadataValidatorInterface $metadataValidator;
    private EntityManagerInterface $entityManager;
    private array $headerKeys = [
        'Code', 'Name', 'Position', 
        'Values_Code', 'Values_EN_US', 'Values_DE', 'Values_FR', 
        'Values_PL', 'Values_ES', 'Values_ES_MX', 'Values_PT', 'Values_ZH'
    ];

    private array $locales = [
        'Values_EN_US' => 'en_US',
        'Values_DE' => 'de',
        'Values_FR' => 'fr',
        'Values_PL' => 'pl',
        'Values_ES' => 'es',
        'Values_ES_MX' => 'es_MX',
        'Values_PT' => 'pt',
        'Values_ZH' => 'zh'
    ];

    public function __construct(
        FactoryInterface $productOptionFactory,
        FactoryInterface $productOptionValueFactory,
        RepositoryInterface $productOptionRepository,
        MetadataValidatorInterface $metadataValidator,
        EntityManagerInterface $entityManager
    ) {
        $this->productOptionFactory = $productOptionFactory;
        $this->productOptionValueFactory = $productOptionValueFactory;
        $this->productOptionRepository = $productOptionRepository;
        $this->metadataValidator = $metadataValidator;
        $this->entityManager = $entityManager;
    }

    public function process(array $data): void
    {
        // Add normalization logic for semicolon-delimited data
        $normalizedData = [];
        $hasDelimiter = false;
        
        // Check if we have semicolon-delimited headers
        foreach ($data as $key => $value) {
            if (strpos($key, ';') !== false) {
                $hasDelimiter = true;
                break;
            }
        }

        if ($hasDelimiter) {
            // Handle semicolon-delimited data
            $keys = explode(';', array_key_first($data));
            $values = explode(';', reset($data));
            
            foreach ($keys as $index => $headerKey) {
                if (isset($values[$index])) {
                    $normalizedData[trim($headerKey)] = trim($values[$index]);
                }
            }
        } else {
            // Handle regular comma-delimited data
            $normalizedData = $data;
        }

        // Use normalized data instead of original data
        $this->metadataValidator->validateHeaders($this->headerKeys, $normalizedData);

        // Check if product option already exists
        /** @var ProductOptionInterface $existingProductOption */
        $existingProductOption = $this->productOptionRepository->findOneBy(['code' => $normalizedData['Code']]);
        
        if ($existingProductOption !== null) {
            throw new ImporterException(
                sprintf('Product option with code "%s" already exists.', $normalizedData['Code'])
            );
        }

        /** @var ProductOptionInterface $productOption */
        $productOption = $this->productOptionFactory->createNew();
        $productOption->setCode($normalizedData['Code']);
        $productOption->setName($normalizedData['Name']);
        $productOption->setPosition((int) $normalizedData['Position']);

        // Process values
        $valuesCode = explode('*****NEXT_VALUE:*****', trim($normalizedData['Values_Code'], '*****VALUE1:*****'));
        
        // Create a map to store created option values
        $createdValues = [];

        foreach ($valuesCode as $key => $code) {
            /** @var ProductOptionValueInterface $productOptionValue */
            $productOptionValue = $this->productOptionValueFactory->createNew();
            $uniqueCode = sprintf('%s_%s', $productOption->getCode(), $code);
            $productOptionValue->setCode($uniqueCode);
            $productOption->addValue($productOptionValue);
            $createdValues[$key] = $productOptionValue;
        }

        // Set translations for each value
        foreach ($this->locales as $header => $locale) {
            if (isset($normalizedData[$header])) {
                $values = explode('*****NEXT_VALUE:*****', trim($normalizedData[$header], '*****VALUE1:*****'));
                
                foreach ($values as $key => $value) {
                    if (!isset($createdValues[$key])) {
                        continue;
                    }

                    $productOptionValue = $createdValues[$key];
                    $productOptionValue->setCurrentLocale($locale);
                    $productOptionValue->setFallbackLocale($locale);
                    $productOptionValue->setValue($value);
                }
            }
        }

        $this->entityManager->persist($productOption);
        $this->entityManager->flush();
    }

    private function parseValues(string $value): array
    {
        $values = [];
        $parts = explode('*****NEXT_VALUE:*****', $value);
        
        foreach ($parts as $part) {
            if (preg_match('/\*\*\*\*\*VALUE\d+:\*\*\*\*\*(.*?)(?:,\s*|$)/', $part, $matches)) {
                $values[] = trim($matches[1]);
            } else {
                $values[] = trim($part);
            }
        }
        
        return $values;
    }

    private function findOrCreateOptionValue(ProductOptionInterface $productOption, string $code): ProductOptionValueInterface
    {
        // Check existing values first
        foreach ($productOption->getValues() as $existingValue) {
            if ($existingValue->getCode() === $code) {
                return $existingValue;
            }
        }

        // If no existing value found, create new one
        /** @var ProductOptionValueInterface $optionValue */
        $optionValue = $this->productOptionValueFactory->createNew();
        $productOption->addValue($optionValue);
        return $optionValue;
    }
}