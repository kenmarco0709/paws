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
use App\Entity\InventoryItemEntity;
use App\Entity\SupplierEntity;




class InventoryItemCompletedOrderForm extends AbstractType
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
            ->add('quantity', TextType::class, array(
                'label' => 'Quantity',
                'label_attr' => array(
                    'class' => 'middle required'
                ),
                'attr' => [ 'class' => 'form-control amt'],
                'required' => false,
            ))
            ->add('selling_price', TextType::class, array(
                'label' => 'Selling Price',
                'label_attr' => array(
                    'class' => 'middle required'
                ),
                'attr' => [ 'class' => 'form-control amt'],
                'required' => false,
            ))
            ->add('buying_price', TextType::class, array(
                'label' => 'Buying Price',
                'label_attr' => array(
                    'class' => 'middle required'
                ),
                'attr' => [ 'class' => 'form-control amt'],
                'required' => false,
            ))
            ->add('remarks', TextAreaType::class, array(
                'label' => 'Remarks',
                'label_attr' => array(
                    'class' => 'middle required'
                ),
                'attr' => [ 'class' => 'form-control'],
                'required' => false,
            ))
            ->add('supplier', HiddenType::class)
            ->addEventListener(FormEvents::PRE_SET_DATA, function(FormEvent $event) {

                $form = $event->getForm();
                $data = $event->getData();
                $supplier = $data->getSupplier();
                $form
                    ->add('supplier_desc', TextType::class, array(
                        'label' => 'Supplier',
                        'label_attr' => array(
                            'class' => 'middle required'
                        ),
                        'required' => true,
                        'attr' => array(                            
                            'class' => 'form-control',
                        ),
                        'mapped' => false,
                        'data' => $supplier ? $supplier->getDescription() : ''
                    ));
            })
            ->add('inventory_item', HiddenType::class, array('data' => $options['inventoryItemId']));
            $builder->get('inventory_item')->addModelTransformer(new DataTransformer($this->manager, InventoryItemEntity::class, true,$options['inventoryItemId']));
            $builder->get('supplier')->addModelTransformer(new DataTransformer($this->manager, SupplierEntity::class, false));



    }

    public function getName()
    {
        return 'inventoryItemCompletedOrder';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' 	  => 'App\Entity\InventoryItemCompletedOrderEntity',
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
            // a unique key to help generate the secret token
            'intention'       => 'inventoryItemCompletedOrderEntity_intention',
            'action'          => 'n',
            'inventoryItemId'    => null 
        ));
    }
}