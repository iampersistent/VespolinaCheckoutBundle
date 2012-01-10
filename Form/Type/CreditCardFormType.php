<?php
namespace Vespolina\CheckoutBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;

class CreditCardFormType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder
            ->add('cardNumber', 'text', array(
                'required' => true,
                'label' => 'Card Number',
                'attr' => array(
                    'size' => '24',
                    'maxlength' => '24'
                ),
            ))
            ->add('expiration', new CreditCardDateFormType(), array(
                'required' => true,
                'label' => 'Expiration Date',
            ))
            ->add('cvv', 'text', array(
                'required' => true,
                'label' => 'CVV',
                'attr' => array(
                    'size' => '5',
                ),
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
        return 'vespolina_creditcard';
    }
}
