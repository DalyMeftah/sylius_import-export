<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class SampleController
{
    private string $samplesDir;

    public function __construct(string $samplesDir)
    {
        $this->samplesDir = $samplesDir;
    }

    public function downloadAction(string $type): Response
    {
        $headers = $this->getHeadersForType($type);
        $sampleData = $this->getSampleDataForType($type);
        
        $output = fopen('php://temp', 'w+');
        
        fputcsv($output, $headers);
        
        foreach ($sampleData as $row) {
            $completeRow = array_pad($row, count($headers), '');
            fputcsv($output, $completeRow);
        }
        
        rewind($output);
        $content = stream_get_contents($output);
        fclose($output);

        $response = new Response($content);
        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', 'attachment; filename="' . $type . '_sample.csv"');

        return $response;
    }

    private function getHeadersForType(string $type): array 
    {
        return match($type) {
            'product' => [
                'Code',
                'Name',
                'Locale',
                'Enabled',
                'Description',
                'Short_description',
                'Meta_keywords',
                'Meta_description',
                'Main_taxon',
                'Taxons',
                'Channels',
                'Price',
                'image_main',
                'image_thumbnail',
                't_shirt_brand',
                't_shirt_collection',
                't_shirt_material',
                'cap_brand',
                'cap_collection',
                'cap_material',
                'dress_brand',
                'dress_collection',
                'dress_material',
                'length',
                'jeans_brand',
                'jeans_collection',
                'jeans_material',
                'damage_reduction',
                'dfsfdfdqfqd',
                'DALISTA',
                'Lm',
                'Sd',
                're',
                'Images_main',
                'COLOR',
                'SIZE',
                'MATERIAL'
            ],
            'product_attribute' => [
                'Code',
                'Name',
                'Type',
                'Position',
                'Translatable'
            ],
            'product_option' => [
                'Code', 'Name', 'Position', 'Values_Code', 'Values_EN_US', 'Values_DE', 'Values_FR', 'Values_PL', 'Values_ES', 'Values_ES_MX', 'Values_PT', 'Values_ZH'
            ],
            'vendor' => [
                'Id', 
                'Company_name', 
                'Tax_ID', 
                'Bank_Account', 
                'Phone_Number',
                'Description',
                'Country',
                'City',
                'Street',
                'Postal_Code',
                'Status',
                'Enabled'
            ],
            'taxonomy' => ['Code', 'Parent', 'Name', 'Slug', 'Description', 'Position', 'Locale'],
            default => throw new NotFoundHttpException(sprintf('Sample type "%s" not supported', $type))
        };
    }

    private function getSampleDataForType(string $type): array
    {
        return match($type) {
            'product' => [
                [
                    'TSHIRT_COOL',
                    'Cool T-Shirt',
                    'en_US',
                    '1',
                    'Detailed description',
                    'A cool t-shirt',
                    't-shirt,cool',
                    'Meta description',
                    'CATEGORY_TSHIRTS',
                    'CATEGORY_TSHIRTS|CATEGORY_MEN',
                    'CHANNEL_WEB|CHANNEL_RETAIL',
                    '1999',
                    '/path/to/main.jpg',
                    '/path/to/thumbnail.jpg',
                    'Nike',
                    'Summer 2024',
                    'Cotton',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    'Blue',
                    'L',
                    '100% Cotton'
                ]
            ],
            'product_attribute' => [
                ['t_shirt_brand', 'T-Shirt Brand', 'text', '1', 'Yes'],
                ['t_shirt_collection', 'T-Shirt Collection', 'text', '2', 'Yes'],
                ['t_shirt_material', 'T-Shirt Material', 'text', '3', 'Yes'],
                ['COLOR', 'Color', 'select', '4', 'Yes'],
                ['SIZE', 'Size', 'select', '5', 'Yes'],
                ['MATERIAL', 'Material', 'text', '6', 'Yes']
            ],
            'product_option' => [
                [
                    'SIZE', 
                    'Size', 
                    '1',
                    '*****VALUE1:*****S*****NEXT_VALUE:*****M*****NEXT_VALUE:*****L',
                    '*****VALUE1:*****Small*****NEXT_VALUE:*****Medium*****NEXT_VALUE:*****Large',
                    '*****VALUE1:*****Klein*****NEXT_VALUE:*****Mittel*****NEXT_VALUE:*****Groß',
                    '*****VALUE1:*****Petit*****NEXT_VALUE:*****Moyen*****NEXT_VALUE:*****Grand',
                    '*****VALUE1:*****Mały*****NEXT_VALUE:*****Średni*****NEXT_VALUE:*****Duży',
                    '*****VALUE1:*****Pequeño*****NEXT_VALUE:*****Mediano*****NEXT_VALUE:*****Grande',
                    '*****VALUE1:*****Pequeño*****NEXT_VALUE:*****Mediano*****NEXT_VALUE:*****Grande',
                    '*****VALUE1:*****Pequeno*****NEXT_VALUE:*****Médio*****NEXT_VALUE:*****Grande',
                    '*****VALUE1:*****小*****NEXT_VALUE:*****中*****NEXT_VALUE:*****大'
                ],
                [
                    'COLOR',
                    'Color',
                    '2',
                    '*****VALUE1:*****RED*****NEXT_VALUE:*****BLUE',
                    '*****VALUE1:*****Red*****NEXT_VALUE:*****Blue',
                    '*****VALUE1:*****Rot*****NEXT_VALUE:*****Blau',
                    '*****VALUE1:*****Rouge*****NEXT_VALUE:*****Bleu',
                    '*****VALUE1:*****Czerwony*****NEXT_VALUE:*****Niebieski',
                    '*****VALUE1:*****Rojo*****NEXT_VALUE:*****Azul',
                    '*****VALUE1:*****Rojo*****NEXT_VALUE:*****Azul',
                    '*****VALUE1:*****Vermelho*****NEXT_VALUE:*****Azul',
                    '*****VALUE1:*****红色*****NEXT_VALUE:*****蓝色'
                ]
            ],
            'vendor' => [
                [
                    '1',
                    'Madrid Company',
                    '810302810302',
                    'ES91 2100 0418 4502 0005 1332',
                    '+34 123 456 789',
                    'A trusted vendor from Madrid',
                    'ES',
                    'Madrid',
                    'Gran Via 123',
                    '28013',
                    'verified',
                    'Yes'
                ],
                [
                    '2',
                    'Barcelona Company',
                    '810302810303',
                    'ES91 2100 0418 4502 0005 1333',
                    '+34 987 654 321',
                    'Quality products from Barcelona',
                    'ES',
                    'Barcelona',
                    'Las Ramblas 456',
                    '08002',
                    'unverified',
                    'No'
                ]
            ],
            'taxonomy' => [
                ['CATEGORY_1', '', 'Main Category', 'main-category', 'Description', '0', 'en_US'],
                ['CATEGORY_1_1', 'CATEGORY_1', 'Subcategory', 'subcategory', 'Description', '1', 'en_US']
            ],
            default => []
        };
    }
} 