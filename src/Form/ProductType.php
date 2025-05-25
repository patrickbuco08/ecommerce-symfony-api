<?php

namespace Bocum\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class ProductType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TextType::class, [
                'constraints' => [new Assert\NotBlank()]
            ])
            ->add('category_id', IntegerType::class, [
                'constraints' => [new Assert\NotBlank(), new Assert\Type('integer')]
            ])
            ->add('description', TextType::class, [
                'required' => false
            ])
            ->add('price', MoneyType::class, [
                'constraints' => [new Assert\NotBlank(), new Assert\Type('numeric')]
            ])
            ->add('stock', IntegerType::class, [
                'constraints' => [new Assert\NotBlank(), new Assert\Type('integer')]
            ])
            ->add('rating', IntegerType::class, [
                'constraints' => [new Assert\NotBlank(), new Assert\Type('integer')],
                'required' => false
            ]);

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'csrf_protection' => false, // For APIs
        ]);
    }
}
