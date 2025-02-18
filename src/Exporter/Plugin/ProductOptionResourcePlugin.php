<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\Exporter\Plugin;

use Doctrine\ORM\EntityManagerInterface;
use Sylius\Component\Product\Model\ProductOptionInterface;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

final class ProductOptionResourcePlugin extends ResourcePlugin
{
    public function getData(string $id, array $keysToExport): array
    {
        /** @var ProductOptionInterface $productOption */
        $productOption = $this->repository->find($id);
        
        if ($productOption === null) {
            return [];
        }

        $values = $productOption->getValues();
        $result = [
            'Code' => $productOption->getCode(),
            'Name' => $productOption->getName(),
            'Position' => $productOption->getPosition()
        ];

        $codes = [];
        $translations = [
            'en_US' => [],
            'de' => [],
            'fr' => [],
            'pl' => [],
            'es' => [],
            'es_MX' => [],
            'pt' => [],
            'zh' => []
        ];

        foreach ($values as $value) {
            $codes[] = $value->getCode();
            
            
            foreach ($translations as $locale => &$trans) {
                try {
                    $valueTranslations = $value->getTranslations();
                    $found = false;
                    
                    
                    foreach ($valueTranslations as $translation) {
                        $translationLocale = strtolower($translation->getLocale());
                        $requestedLocale = strtolower($locale);
                        
                        if ($translationLocale === $requestedLocale || 
                            strpos($translationLocale, $requestedLocale . '_') === 0) {
                            $trans[] = $translation->getValue();
                            $found = true;
                            break;
                        }
                    }
                    
                    if (!$found) {
                        $trans[] = '';
                    }
                } catch (\Exception $e) {
                    $trans[] = '';
                }
            }
        }

        foreach ($keysToExport as $key) {
            if (!isset($result[$key])) {
                switch ($key) {
                    case 'Values_Code':
                        $filteredCodes = array_filter($codes);
                        $indexedValues = array_map(function($value, $index) {
                            return "*****VALUE" . ($index + 1) . ":*****" . $value . ",  " ;
                        }, array_values($filteredCodes), array_keys($filteredCodes));
                        $result[$key] = implode('', $indexedValues);
                        break;
                    case 'Values_EN_US':
                        $filteredValues = array_filter($translations['en_US']);
                        $indexedValues = array_map(function($value, $index) {
                            return "*****VALUE" . ($index + 1) . ":*****" . $value . ",  " ;
                        }, array_values($filteredValues), array_keys($filteredValues));
                        $result[$key] = implode('', $indexedValues);
                        break;
                    case 'Values_DE':
                        $filteredValues = array_filter($translations['de']);
                        $indexedValues = array_map(function($value, $index) {
                            return "*****VALUE" . ($index + 1) . ":*****" . $value;
                        }, array_values($filteredValues), array_keys($filteredValues));
                        $result[$key] = implode('', $indexedValues);
                        break;
                    case 'Values_FR':
                        $filteredValues = array_filter($translations['fr']);
                        $indexedValues = array_map(function($value, $index) {
                            return "*****VALUE" . ($index + 1) . ":*****,  " . $value . ",  " ;
                        }, array_values($filteredValues), array_keys($filteredValues));
                        $result[$key] = implode('', $indexedValues);
                        break;
                    case 'Values_PL':
                        $filteredValues = array_filter($translations['pl']);
                        $indexedValues = array_map(function($value, $index) {
                            return "*****VALUE" . ($index + 1) . ":*****" . $value . ",  " ;
                        }, array_values($filteredValues), array_keys($filteredValues));
                        $result[$key] = implode('', $indexedValues);
                        break;
                    case 'Values_ES':
                        $filteredValues = array_filter($translations['es']);
                        $indexedValues = array_map(function($value, $index) {
                            return "*****VALUE" . ($index + 1) . ":*****" . $value . ",  " ;
                        }, array_values($filteredValues), array_keys($filteredValues));
                        $result[$key] = implode('', $indexedValues);
                        break;
                    case 'Values_ES_MX':
                        $filteredValues = array_filter($translations['es_MX']);
                        $indexedValues = array_map(function($value, $index) {
                            return "*****VALUE" . ($index + 1) . ":*****" . $value . ",  " ;
                        }, array_values($filteredValues), array_keys($filteredValues));
                        $result[$key] = implode('', $indexedValues);
                        break;
                    case 'Values_PT':
                        $filteredValues = array_filter($translations['pt']);
                        $indexedValues = array_map(function($value, $index) {
                            return "*****VALUE" . ($index + 1) . ":*****" . $value . ",  " ;
                        }, array_values($filteredValues), array_keys($filteredValues));
                        $result[$key] = implode('', $indexedValues);
                        break;
                    case 'Values_ZH':
                        $filteredValues = array_filter($translations['zh']);
                        $indexedValues = array_map(function($value, $index) {
                            return "*****VALUE" . ($index + 1) . ":*****" . $value . ",  " ;
                        }, array_values($filteredValues), array_keys($filteredValues));
                        $result[$key] = implode('', $indexedValues);
                        break;
                    default:
                        $result[$key] = '';
                }
            }
        }

        return $result;
    }
}