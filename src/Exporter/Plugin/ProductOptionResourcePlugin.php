<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\Exporter\Plugin;

use Sylius\Component\Product\Model\ProductOptionInterface;
use Sylius\Component\Product\Model\ProductOptionValueInterface;

class ProductOptionResourcePlugin extends ResourcePlugin
{
    private const SUPPORTED_LOCALES = [
        'en_US' => 'English (United States)',
        'de_DE' => 'German (Germany)', 
        'fr_FR' => 'French (France)',
        'pl_PL' => 'Polish (Poland)',
        'es_ES' => 'Spanish (Spain)',
        'es_MX' => 'Spanish (Mexico)',
        'pt_PT' => 'Portuguese (Portugal)', 
        'zh_CN' => 'Chinese (China)'
    ];

    /**
     * {@inheritdoc}
     */
    public function init(array $idsToExport): void
    {
        parent::init($idsToExport);

        /** @var ProductOptionInterface $resource */
        foreach ($this->resources as $resource) {
            // Add basic data
            $this->addDataForResource($resource, 'Code', $resource->getCode());
            $this->addDataForResource($resource, 'Name', $resource->getName());
            $this->addDataForResource($resource, 'Position', $resource->getPosition() ?? 0);

            // Handle each value
            $values = [];
            /** @var ProductOptionValueInterface $value */
            foreach ($resource->getValues() as $value) {
                $values[] = $value->getCode() . ':' . $value->getValue();
            }
            
            // Join values with a separator
            $valuesString = implode('|', $values);
            $this->addDataForResource($resource, 'Values', $valuesString);
        }
    }
}