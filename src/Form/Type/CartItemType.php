<?php

namespace App\Form\Type;

use App\Entity\CartItem;
use App\Entity\Product;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\RangeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
// ...

class CartItemType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('product', EntityType::class,
            [
                'class' => Product::class
            ])
            ->add('quantity', RangeType::class,
                [
                    'required' => true,
                    'error_bubbling' => true,
                    'attr' => [
                            'min' => 1,
                            'max' => 10
                    ]
                ]);
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => CartItem::class,
        ]);
    }
}