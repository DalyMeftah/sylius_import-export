<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\Exporter\Plugin;

use Sylius\Component\Product\Model\ProductAttributeInterface;

class ProductAttributeResourcePlugin extends ResourcePlugin
{
    /**
     * {@inheritdoc}
     */
    public function init(array $idsToExport): void
    {
        parent::init($idsToExport);

        /** @var ProductAttributeInterface $resource */
        foreach ($this->resources as $resource) {
            $name = $resource->getTranslation('en_US')->getName();
            $this->addDataForResource($resource, 'Name', $name);

            $this->addDataForResource($resource, 'Type', $resource->getType());

            $this->addDataForResource($resource, 'Position', $resource->getPosition());

            $this->addDataForResource($resource, 'Translatable', $resource->isTranslatable() ? 'Yes' : 'No');
        }
    }
}