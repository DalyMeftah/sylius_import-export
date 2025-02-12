<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\Menu;

use Sylius\Bundle\UiBundle\Menu\Event\MenuBuilderEvent;

final class AdminMenuListener
{
    public function addImportExportButton(MenuBuilderEvent $event): void
    {
        $menu = $event->getMenu();
        $catalog = $menu->getChild('catalog');
        
        if (null !== $catalog) {
            // Add import/export buttons for product attributes
            $productAttributes = $catalog->getChild('attributes');
            if (null !== $productAttributes) {
                $productAttributes
                    ->addChild('export', [
                        'route' => 'app_export_data_product_attribute',
                        'routeParameters' => ['format' => 'csv'],
                    ])
                    ->setAttribute('type', 'link')
                    ->setLabel('sylius.ui.export')
                    ->setLabelAttribute('icon', 'upload');

                $productAttributes
                    ->addChild('import', [
                        'route' => 'app_import_data_product_attribute',
                        'routeParameters' => ['format' => 'csv'],
                    ])
                    ->setAttribute('type', 'link')
                    ->setLabel('sylius.ui.import')
                    ->setLabelAttribute('icon', 'download');
            }
        }
    }
} 