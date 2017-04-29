<?php

namespace AppBundle\Form;

use AppBundle\Entity\Products;
use Doctrine\DBAL\Types\FloatType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EditProductType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, ['attr' => ['class' => 'form-control col-sm-4']])
            ->add('description', TextareaType::class, ['attr' => ['class' => 'form-control col-sm-4', 'rows' => 10]])
            ->add('catId', ChoiceType::class, [
                'label' => 'Category',
                'attr' => ['class' => 'form-control col-sm-4'],
                'choices' => $this->buildChoices()
            ])
            ->add('price', NumberType::class, ['attr' => ['class' => 'form-control col-sm-4']])
            ->add('quantity', NumberType::class, ['attr' => ['class' => 'form-control col-sm-4']])
            ->add('discount', NumberType::class, ['attr' => ['class' => 'form-control col-sm-4']])
            ->add('orderValue', NumberType::class, ['attr' => ['class' => 'form-control col-sm-4']]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => Products::class,
        ));
    }
}
