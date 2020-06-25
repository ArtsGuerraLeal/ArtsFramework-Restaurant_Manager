<?php

namespace App\Form;


use App\Entity\Patient;
use App\Entity\Staff;
use App\Entity\Treatment;
use App\Repository\StaffRepository;
use App\Repository\TreatmentRepository;
use Doctrine\DBAL\Types\TextType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Security;

class PatientAppointmentType extends AbstractType
{
    /**
     * @var TreatmentRepository
     */
    private $treatmentRepository;

    private $security;
    /**
     * @var StaffRepository
     */
    private $staffRepository;


    public function __construct(StaffRepository $staffRepository, TreatmentRepository $treatmentRepository,Security $security)
    {
        $this->treatmentRepository = $treatmentRepository;
        $this->security = $security;
        $this->staffRepository = $staffRepository;
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $user = $this->security->getUser();

        $builder
            ->add('treatment', EntityType::class, [
                'class' => Treatment::class,
                'choice_label' => function(Treatment $treatment) {
                    return sprintf(' %s', $treatment->getName());
                },
                'placeholder' => 'Choose an Treatment',
                'choices' => $this->treatmentRepository->findByCompany($user->getCompany()),
                'multiple' => true
            ])

            ->add('staff', EntityType::class, [
                'class' => Staff::class,
                'choice_label' => function(Staff $staff) {
                    return sprintf(' %s', $staff->getFirstName()." ". $staff->getLastName());
                },
                'placeholder' => 'Choose an Staff',
                'choices' => $this->staffRepository->findByCompany($user->getCompany()),

            ])
            ->add('cost', MoneyType::class,['label'=>'Cost',
                'currency'=> 'USD'])

            ->add('discount', MoneyType::class,['label'=>'Discount',
                'currency'=> 'USD'])

            ->add('totalcost', MoneyType::class,['label'=>'Total Cost',
                'currency'=> 'USD',
                    'attr'=>['readonly'=>true]
                ]
            )

            ->add('beginAt',DateTimeType::class, [
                'widget'=> 'single_text',
                'html5' => false,
            ])
            ->add('endAt',DateTimeType::class, [
                'widget'=> 'single_text',
                'html5' => false,
            ])
            ->add('color',ChoiceType::class,[
                'choices' => [
                    'Red' =>'red',
                    'Orange' =>'orange',
                    'Yellow' =>'yellow',
                    'Green' =>'green',
                    'Blue' =>'blue',
                    'Purple' =>'purple'
                ]

            ])


            ->add('save',SubmitType::class,[
                'attr' => [
                    'class' => 'btn btn-success'
                ]
            ])
        ;
    }


}
