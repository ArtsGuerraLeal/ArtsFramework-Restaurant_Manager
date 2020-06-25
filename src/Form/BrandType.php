<?php

namespace App\Form;

use App\Entity\Brand;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BrandType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name')
            ->add('attachment',FileType::class , [
                'mapped' => false,
                'required' => false,
            ])
            ->add('store',CollectionType::class,[
                'entry_type'=>BrandStoreType::class,
                'entry_options'=>[
                    'label' => false
                ],
                'by_reference' => false,
                'allow_add' => true,
                'allow_delete' => true,
                'label'=>false

            ])
            ->add('save',SubmitType::class,[
                'attr' => [
                    'class' => 'btn btn-success'
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Brand::class,
        ]);
    }
}
