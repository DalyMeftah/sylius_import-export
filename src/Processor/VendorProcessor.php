<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\Processor;

use BitBag\OpenMarketplace\Component\Vendor\Entity\Vendor;
use BitBag\OpenMarketplace\Component\Vendor\Entity\VendorInterface;
use BitBag\OpenMarketplace\Component\Vendor\Entity\Address;
use Doctrine\ORM\EntityManagerInterface;
use FriendsOfSylius\SyliusImportExportPlugin\Importer\Transformer\TransformerPoolInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Sylius\Component\Addressing\Model\CountryInterface;
use Sylius\Component\Product\Generator\SlugGeneratorInterface;

final class VendorProcessor implements ResourceProcessorInterface
{
    private const REQUIRED_FIELDS = [
        'Company_name',
        'Tax_ID',
        'Phone_Number',
        'Country',
        'City',
        'Street',
        'Postal_Code'
    ];

    private FactoryInterface $vendorFactory;
    private RepositoryInterface $vendorRepository;
    private RepositoryInterface $countryRepository;
    private MetadataValidatorInterface $metadataValidator;
    private EntityManagerInterface $entityManager;
    private SlugGeneratorInterface $slugGenerator;
    private array $headerKeys;
    private ?TransformerPoolInterface $transformerPool;

    public function __construct(
        FactoryInterface $vendorFactory,
        RepositoryInterface $vendorRepository,
        RepositoryInterface $countryRepository,
        MetadataValidatorInterface $metadataValidator,
        EntityManagerInterface $entityManager,
        SlugGeneratorInterface $slugGenerator,
        array $headerKeys,
        ?TransformerPoolInterface $transformerPool = null
    ) {
        $this->vendorFactory = $vendorFactory;
        $this->vendorRepository = $vendorRepository;
        $this->countryRepository = $countryRepository;
        $this->metadataValidator = $metadataValidator;
        $this->entityManager = $entityManager;
        $this->slugGenerator = $slugGenerator;
        $this->headerKeys = $headerKeys;
        $this->transformerPool = $transformerPool;
    }

    public function process(array $data): void
    {
        $this->metadataValidator->validateHeaders($this->headerKeys, $data);
        $this->validateRequiredFields($data);
        $this->validateUniqueFields($data);
        
        $vendor = $this->findOrCreateVendor($data);
        

        $vendor->setCompanyName($data['Company_name']);
        $vendor->setTaxIdentifier($data['Tax_ID']);
        $vendor->setBankAccountNumber($data['Bank_Account']);
        $vendor->setPhoneNumber($data['Phone_Number']);
        $vendor->setDescription($data['Description']);
        

        $slug = $this->slugGenerator->generate($data['Company_name']);
        $vendor->setSlug($slug);
        

        $address = new Address();
        
        /** @var CountryInterface|null $country */
        $country = $this->countryRepository->findOneBy(['code' => strtoupper($data['Country'])]);
        if (null === $country) {
            throw new \RuntimeException(sprintf('Country with code "%s" not found', $data['Country']));
        }
        
        $address->setCountry($country);
        $address->setCity($data['City']);
        $address->setStreet($data['Street']);
        $address->setPostalCode($data['Postal_Code']);
        

        $vendor->setVendorAddress($address);
        
        $vendor->setStatus($data['Status']);
        $vendor->setEnabled($data['Enabled'] === 'Yes');
        
        $this->entityManager->persist($address);
        $this->entityManager->persist($vendor);
        $this->entityManager->flush();
    }

    private function validateRequiredFields(array $data): void
    {
        $missingFields = [];
        foreach (self::REQUIRED_FIELDS as $field) {
            if (!isset($data[$field]) || empty(trim($data[$field]))) {
                $missingFields[] = $field;
            }
        }

        if (!empty($missingFields)) {
            throw new \RuntimeException(sprintf(
                'Missing required fields: %s',
                implode(', ', $missingFields)
            ));
        }
    }

    private function validateUniqueFields(array $data): void
    {

        $existingVendorByName = $this->vendorRepository->findOneBy(['companyName' => $data['Company_name']]);
        if ($existingVendorByName !== null && (!isset($data['Id']) || $existingVendorByName->getId() != $data['Id'])) {
            throw new \RuntimeException(sprintf(
                'Vendor with company name "%s" already exists',
                $data['Company_name']
            ));
        }


        $existingVendorByTaxId = $this->vendorRepository->findOneBy(['taxIdentifier' => $data['Tax_ID']]);
        if ($existingVendorByTaxId !== null && (!isset($data['Id']) || $existingVendorByTaxId->getId() != $data['Id'])) {
            throw new \RuntimeException(sprintf(
                'Vendor with tax ID "%s" already exists',
                $data['Tax_ID']
            ));
        }


        $existingVendorByPhone = $this->vendorRepository->findOneBy(['phoneNumber' => $data['Phone_Number']]);
        if ($existingVendorByPhone !== null && (!isset($data['Id']) || $existingVendorByPhone->getId() != $data['Id'])) {
            throw new \RuntimeException(sprintf(
                'Vendor with phone number "%s" already exists',
                $data['Phone_Number']
            ));
        }
    }

    private function findOrCreateVendor(array $data): VendorInterface
    {
        /** @var Vendor|null $vendor */
        $vendor = isset($data['Id']) 
            ? $this->vendorRepository->find($data['Id'])
            : null;

        if (null === $vendor) {
            /** @var Vendor $vendor */
            $vendor = $this->vendorFactory->createNew();
        }

        return $vendor;
    }
} 