<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CategoryType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, ['attr' => ['class' => 'form-control col-sm-4']])
            ->add('imageName', FileType::class, ['attr' => ['class' => 'form-control col-sm-4']])
            ->add('discount', NumberType::class, ['attr' => ['class' => 'form-control col-sm-4']]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\Categories',
        ));
    }

    public function getBlockPrefix()
    {
        return 'app_bundle_category_type';
    }
}
