<?php

namespace App\Form;

use App\Entity\Equipment;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EquipmentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name')
            ->add('cost')
            ->add('purchasedOn')
            ->add('lastUsedOn')

            ->add('treatment',CollectionType::class,[
                'entry_type'=>EquipmentTreatmentType::class,
                'entry_options'=>[
                    'label' => false
                ],
                'by_reference' => false,
                'allow_add' => true,
                'allow_delete' => true,

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
            'data_class' => Equipment::class,
        ]);
    }
}
