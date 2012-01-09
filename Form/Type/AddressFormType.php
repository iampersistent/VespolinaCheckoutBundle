<?php
namespace Vespolina\CheckoutBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;

class AddressFormType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder
            ->add('street1', 'textarea', array(
                'required' => true,
            ))
            ->add('street2', 'textarea', array(
                'required' => false,
            ))
            ->add('city', 'textarea', array(
                'required' => true,
            ))
            ->add('state', 'vespolina_state', array(
                'required' => true,
            ))
            ->add('country', 'country', array(
                'required' => true,
            ))
            ->add('postalCode', 'textarea', array(
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
        return 'vespolina_address';
    }
}
