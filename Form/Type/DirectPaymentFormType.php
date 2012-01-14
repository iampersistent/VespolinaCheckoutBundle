<?php
namespace Vespolina\CheckoutBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;

class DirectPaymentFormType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder
            ->add('firstName', 'text', array(
                'required' => true,
                'label' => 'First Name',
                'attr' => array(
                    'size' => '16',
                ),
            ))
            ->add('middle', 'text', array(
                'required' => false,
                'label' => 'MI',
                'attr' => array(
                    'size' => '3',
                ),
            ))
            ->add('lastName', 'text', array(
                'required' => true,
                'label' => 'Last Name',
                'attr' => array(
                    'size' => '16',
                ),
            ))
            ->add('address', 'vespolina_address', array(
                'required' => true,
            ))
            ->add('creditCard', 'vespolina_creditcard', array(
                'required' => true,
            ))
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getParent(array $options)
    {
        return 'field';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'vespolina_secure';
    }
}
