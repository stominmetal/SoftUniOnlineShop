<?php

namespace AppBundle\Form;

use Doctrine\DBAL\Types\FloatType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, ['attr' => ['class' => 'form-control col-sm-4']])
            ->add('description', TextareaType::class, ['attr' => ['class' => 'form-control col-sm-4', 'rows' => 10]])
            ->add('catId', ChoiceType::class, [
                'attr' => ['class' => 'form-control col-sm-4'],
                'choices' => [
                    'Phones' => 1,
                    'Watches' => 2,
                    'Audio' => 3,
                    'Notebooks' => 4
                ]])
            ->add('price', NumberType::class, ['attr' => ['class' => 'form-control col-sm-4']])
            ->add('quantity', NumberType::class, ['attr' => ['class' => 'form-control col-sm-4']])
            ->add('imageName', FileType::class, ['attr' => ['class' => 'form-control col-sm-4'], 'data_class' => null]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\Products',
        ));
    }

    public function getBlockPrefix()
    {
        return 'app_bundle_product_type';
    }
}
