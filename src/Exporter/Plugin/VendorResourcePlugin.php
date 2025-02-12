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
            $this->addDataForResource($resource, 'Status', $resource->getStatus());
            $this->addDataForResource($resource, 'Enabled', $resource->isEnabled() ? 'Yes' : 'No');
        }
    }
}