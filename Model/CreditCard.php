<?php

namespace Vespolina\CheckoutBundle\Model;
/**
 * @author Richard Shank <develop@zestic.com>
 */

class CreditCard
{
    protected $cardNumber;
    protected $expiration;
    protected $cvv;

    public function setCardNumber($cardNumber)
    {
        $this->cardNumber = $cardNumber;
    }

    public function getCardNumber()
    {
        return $this->cardNumber;
    }
}
