<?php
namespace Vespolina\CheckoutBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;

class CreditCardDateFormType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder
            ->add('month', 'choice', array(
                'choices' => $this->getMonthChoices(),
                'required' => true,
            ))
            ->add('year', 'choice', array(
                'choices' => $this->getYearChoices(),
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
        return 'vespolina_creditcard_date';
    }

    protected function getMonthChoices()
    {
        // todo: make configurable for format
        $months = array();
        for ($m = 1 ; $m <= 12 ; $m++) {
            $mo = sprintf('%02d', $m);
            $months[$mo] = $mo;
        }
        return $months;
    }

    protected function getYearChoices()
    {
        // todo: make configurable for range and format
        $startingYear = (integer)date('y');
        $endingYear = $startingYear + 25;
        $years = array();
        for ($y = (string)$startingYear ; $y <= (string)$endingYear ; $y++) {
            $y = $y;
            $years[$y] = $y;
        }
        return $years;
    }
}
