<?php
namespace Vespolina\CheckoutBundle\Model;

use JMS\Payment\CoreBundle\Document\CreditCardProfile;

/**
 * @author Richard Shank <develop@zestic.com>
 */

class DirectPayment extends CreditCardProfile
{
    protected $address;
    protected $creditCard;

    public function setAddress($address)
    {
        $this->address = $address;
    }

    public function getAddress()
    {
        return $this->address;
    }

    public function setCreditCard($creditCard)
    {
        $this->creditCard = $creditCard;
        $this->setCardNumber($creditCard->getCardNumber());
        $this->setCardType($creditCard->getCardType());
        $this->setCvv($creditCard->getCvv());
        $this->setExpiration($creditCard->getExpiration()->getMonth(), $creditCard->getExpiration()->getYear());
    }

    public function getCreditCard()
    {
        return $this->creditCard;
    }
}
