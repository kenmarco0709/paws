<?php

namespace App\Form;

use Doctrine\Persistence\ObjectManager;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;


use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;

use App\Form\DataTransformer\DataTransformer;
use App\Entity\AdmissionEntity;
use App\Entity\PaymentTypeEntity;
use App\Entity\BillingEntity;
use App\Entity\InvoiceEntity;

class PaymentForm extends AbstractType
{
   
    private $manager;

    public function __construct(ObjectManager $manager) {
        $this->manager = $manager;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {



        $builder
            ->add('action', HiddenType::class, array(
                'mapped' => false,
                'attr' => array(
                    'class' => 'form-action'
                )
            ))
            ->add('id', HiddenType::class)
            ->add('amount', TextType::class, array(
                'label' => 'Amount',
                'label_attr' => array(
                    'class' => 'middle required'
                ),
                'attr' => [ 'class' => 'form-control amt', 'required' => 'required'],
                'required' => true,
            ))
            ->add('amount_change', TextType::class, array(
                'label' => 'Change',
                'label_attr' => array(
                    'class' => 'middle required'
                ),
                'attr' => [ 'class' => 'form-control amt', 'required' => 'required', 'readonly' => 'readonly'],
                'required' => true,
                'mapped' => false
            ))
            ->add('reference_no', TextType::class, array(
                'label' => 'Reference No.',
                'label_attr' => array(
                    'class' => 'middle required'
                ),
                'attr' => [ 'class' => 'form-control'],
                'required' => false,
            ))
            ->add('is_deposit', CheckboxType::class, array(
                'label' => 'Is Deposit',
                'label_attr' => array(
                    'class' => 'form-check-label'
                ),
                'attr' => [ 'class' => 'form-check-input'],
                'required' => false,
            ))
            ->add('payment_type', HiddenType::class)
            ->addEventListener(FormEvents::PRE_SET_DATA, function(FormEvent $event) {

                $form = $event->getForm();
                $data = $event->getData();
                $paymentType = $data->getPaymentType();
                $form
                    ->add('payment_type_desc', TextType::class, array(
                        'label' => 'Payment Type',
                        'label_attr' => array(
                            'class' => 'middle required  col-form-label'
                        ),
                        'required' => true,
                        'attr' => array(                            
                            'class' => 'form-control',
                        ),
                        'mapped' => false,
                        'data' => $paymentType ? $paymentType->getDescription() : ''
                    ))
                    ->add('payment_date', TextType::class, array(
                        'label' => 'Payment Date',
                        'label_attr' => array(
                            'class' => 'middle required col-form-label'
                        ),
                        'attr' => [ 'class' => 'form-control datepicker', 'required' => 'required'],
                        'required' => true,
                        'mapped' => false,
                        'data' => $data->getPaymentDate() ? date_format($data->getPaymentDate(), 'm/d/Y') : ''
                    ));
            })

            ->add('invoice', HiddenType::class, array('data' => $options['invoiceId']));
            $builder->get('invoice')->addModelTransformer(new DataTransformer($this->manager, InvoiceEntity::class, true, $options['invoiceId']));
            $builder->get('payment_type')->addModelTransformer(new DataTransformer($this->manager, PaymentTypeEntity::class, false));


    }

    public function getName()
    {
        return 'payment';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' 	  => 'App\Entity\PaymentEntity',
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
            // a unique key to help generate the secret token
            'intention'       => 'paymentEntity_intention',
            'action'          => 'n',
            'invoiceId' => null 

        ));
    }
}