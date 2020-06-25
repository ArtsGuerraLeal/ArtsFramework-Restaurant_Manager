<?php

namespace App\Form;


use App\Entity\Patient;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\BirthdayType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PatientType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('firstName')
            ->add('lastName')
            ->add('gender',ChoiceType::class,[
                'choices' => [
                    'Male' =>'M',
                    'Female' =>'F'
                    ]


                ])
            ->add('birthdate',BirthdayType::class, [
                'placeholder' => [
                    'year' => ' ', 'month' => ' ', 'day' => ' ',
               ] ])

            ->add('email',EmailType::class)
            ->add('phone')
            ->add('religion')
            ->add('maritalStatus')

            ->add('address',CollectionType::class,[
                'entry_type'=>AddressType::class,
                'entry_options'=>[
                    'label' => false
                ],
                'by_reference' => false,
                'allow_add' => true,
                'allow_delete' => true,
            ])
            ->add('attachment',FileType::class , [
                'mapped' => false,
                'required' => false,
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
            'data_class' => Patient::class,
        ]);
    }
}
