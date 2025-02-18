<?php

declare(strict_types=1);

namespace App\Form;

use FriendsOfSylius\SyliusImportExportPlugin\Form\ImportType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;

class VendorImportType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('format', ChoiceType::class, [
                'choices' => [
                    'CSV' => 'csv',
                ],
                'label' => 'sylius.form.import.format',
                'required' => true,
            ])
            ->add('file', FileType::class, [
                'label' => 'sylius.form.import.file',
                'required' => true,
            ]);
    }

    public function getParent()
    {
        return ImportType::class;
    }
} 