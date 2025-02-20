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
        'Code', 'Position', 
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
        $normalizedData = [];
        $hasDelimiter = false;
        
        foreach ($data as $key => $value) {
            if (strpos($key, ';') !== false) {
                $hasDelimiter = true;
                break;
            }
        }

        if ($hasDelimiter) {
            $keys = explode(';', array_key_first($data));
            $values = explode(';', reset($data));
            
            foreach ($keys as $index => $headerKey) {
                if (isset($values[$index])) {
                    $normalizedData[trim($headerKey)] = trim($values[$index]);
                }
            }
        } else {
            $normalizedData = $data;
        }

        $this->metadataValidator->validateHeaders($this->headerKeys, $normalizedData);

        $this->validateRequiredFields($normalizedData);

        /** @var ProductOptionInterface $existingProductOption */
        $existingProductOption = $this->productOptionRepository->findOneBy(['code' => $normalizedData['Code']]);
        
        if ($existingProductOption !== null) {
            throw new ImporterException(
                sprintf('Product option with code "%s" already exists.', $normalizedData['Code'])
            );
        }

        if (empty($normalizedData['Values_Code'])) {
            throw new ImporterException('Values_Code is required and cannot be empty.');
        }

        if (empty($normalizedData['Values_EN_US'])) {
            throw new ImporterException('English (United States) values are required and cannot be empty.');
        }

        // Process values
        $valuesCode = explode('*****NEXT_VALUE:*****', trim($normalizedData['Values_Code'], '*****VALUE1:*****'));
        
        foreach ($valuesCode as $code) {
            $existingValue = $this->entityManager
                ->createQuery('
                    SELECT pov 
                    FROM Sylius\Component\Product\Model\ProductOptionValue pov 
                    WHERE pov.code LIKE :code
                ')
                ->setParameter('code', '%_' . $code)
                ->getResult();

            if (!empty($existingValue)) {
                throw new ImporterException(
                    sprintf('Product option value code "%s" already exists in another product option.', $code)
                );
            }
        }

        /** @var ProductOptionInterface $productOption */
        $productOption = $this->productOptionFactory->createNew();
        $productOption->setCode($normalizedData['Code']);
        if (isset($normalizedData['Name']) && !empty($normalizedData['Name'])) {
            $productOption->setName($normalizedData['Name']);
        }
        $productOption->setPosition((int) ($normalizedData['Position'] ?? 0));

        $createdValues = [];

        foreach ($valuesCode as $key => $code) {
            /** @var ProductOptionValueInterface $productOptionValue */
            $productOptionValue = $this->productOptionValueFactory->createNew();
            $uniqueCode = sprintf('%s_%s', $productOption->getCode(), $code);
            $productOptionValue->setCode($uniqueCode);
            $productOption->addValue($productOptionValue);
            $createdValues[$key] = $productOptionValue;
        }

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
        foreach ($productOption->getValues() as $existingValue) {
            if ($existingValue->getCode() === $code) {
                return $existingValue;
            }
        }

        /** @var ProductOptionValueInterface $optionValue */
        $optionValue = $this->productOptionValueFactory->createNew();
        $productOption->addValue($optionValue);
        return $optionValue;
    }

    private function validateRequiredFields(array $data): void
    {
        $requiredFields = [
            'Code' => 'Code is required.',
            'Values_Code' => 'Values_Code is required.',
            'Values_EN_US' => 'English (United States) values are required.'
        ];

        foreach ($requiredFields as $field => $message) {
            if (!isset($data[$field]) || trim($data[$field]) === '') {
                throw new ImporterException($message);
            }
        }
    }
}