<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\Exporter\Plugin;

use BitBag\OpenMarketplace\Entity\VendorInterface;

class VendorResourcePlugin extends ResourcePlugin
{
    public function init(array $idsToExport): void
    {
        parent::init($idsToExport);

        /** @var VendorInterface $resource */
        foreach ($this->resources as $resource) {
            $this->addDataForResource($resource, 'Id', $resource->getId());
            $this->addDataForResource($resource, 'Company_name', $resource->getCompanyName());
            $this->addDataForResource($resource, 'Tax_ID', $resource->getTaxIdentifier());
            $this->addDataForResource($resource, 'Bank_Account', $resource->getBankAccountNumber());
            $this->addDataForResource($resource, 'Phone_Number', $resource->getPhoneNumber());
            $this->addDataForResource($resource, 'Description', $resource->getDescription());
            
            $address = $resource->getVendorAddress();
            if ($address) {
                $this->addDataForResource($resource, 'Country', $address->getCountry() ? $address->getCountry()->getCode() : '');
                $this->addDataForResource($resource, 'City', $address->getCity());
                $this->addDataForResource($resource, 'Street', $address->getStreet());
                $this->addDataForResource($resource, 'Postal_Code', $address->getPostalCode());
            }
            
            $this->addDataForResource($resource, 'Status', $resource->getStatus());
            $this->addDataForResource($resource, 'Enabled', $resource->isEnabled() ? 'Yes' : 'No');
        }
    }
}