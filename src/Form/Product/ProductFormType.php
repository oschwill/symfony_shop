<?php


namespace App\Form\Product;

use App\Entity\Product;
use App\Entity\ProductPictures;
use App\Form\Product\ProductPictureType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $limitPictures = $options['limit_pictures'];

         $formOptions = [
            'entry_type' => ProductPictureType::class,
            'entry_options' => ['label' => false],
            'allow_add' => true,
            'allow_delete' => true,
            'by_reference' => false,
            'label' => 'Produktbilder',
            'required' => false,
            'prototype' => true,
            'prototype_name' => '__name__',
            'attr' => ['class' => 'product-pictures-collection'],
        ];

        if ($limitPictures) {
            $formOptions['data'] = array_fill(0, 3, new ProductPictures());
        }

        $builder
            ->add('title', TextType::class, [
                'label' => 'Titel*',
                'required' => true,
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Beschreibung*',
                'required' => false,
                'attr' => [
                    'style' => 'height: 150px; resize: none;' // Höhe festlegen und Resize deaktivieren
                ],
            ])
            ->add('price', MoneyType::class, [
                'label' => 'Preis*',
                'currency' => 'EUR', // or any other currency
                'required' => true,
                'attr' => [
                    'type' => 'number',
                    'step' => '0.01',
                    'min' => '0', 
                ],
            ])
             ->add('productPictures', CollectionType::class, $formOptions);
            
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Product::class,
            'limit_pictures' => false,
        ]);
    }
}