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
use Symfony\Component\Form\Extension\Core\Type\CollectionType;


use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;

use App\Form\DataTransformer\DataTransformer;

use App\Entity\ClientEntity;
use App\Entity\BranchEntity;
use App\Entity\InvoiceTypeEntity;
use App\Entity\UserEntity;

class InvoiceForm extends AbstractType
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
            ->add('client', HiddenType::class)
            ->add('branch', HiddenType::class, array('data' => $options['branchId']))
            ->addEventListener(FormEvents::PRE_SET_DATA, function(FormEvent $event) {

                $form = $event->getForm();
                $data = $event->getData();
                $client = $data->getClient();

                $form
                    ->add('client_desc', TextType::class, array(
                        'label' => 'Client',
                        'label_attr' => array(
                            'class' => 'middle required  col-form-label col-3'
                        ),
                        'required' => true,
                        'attr' => array(                            
                            'class' => 'form-control',
                        ),
                        'mapped' => false,
                        'data' => $client ? $client->getFullName() : ''
                    ))
                    
                    ->add('invoice_date', TextType::class, array(
                        'label' => 'Invoice Date',
                        'label_attr' => array(
                            'class' => 'middle required col-form-label col-3'
                        ),
                        'attr' => [ 'class' => 'form-control datepicker', 'required' => 'required'],
                        'required' => true,
                        'mapped' => false,
                        'data' => $data->getInvoiceDate() ? date_format($data->getInvoiceDate(), 'm/d/Y') : ''
                    ));
            });

           $builder->get('branch')->addModelTransformer(new DataTransformer($this->manager, BranchEntity::class, false, $options['branchId']));
           $builder->get('client')->addModelTransformer(new DataTransformer($this->manager, ClientEntity::class, false));




    }

    public function getName()
    {
        return 'invoice';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' 	  => 'App\Entity\InvoiceEntity',
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
            // a unique key to help generate the secret token
            'intention'       => 'invoiceEntity_intention',
            'action'          => 'n',
            'branchId'    => null
        ));
    }
}