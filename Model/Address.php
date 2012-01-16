<?php
/**
 * (c) 2012 Vespolina Project http://www.vespolina-project.org
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Vespolina\CheckoutBundle\Model;

/**
 * @author Richard Shank <develop@zestic.com>
 */

abstract class Address implements AddressInterface
{
    protected $street1;
    protected $street2;
    protected $city;
    protected $state;
    protected $country;
    protected $postalCode;

    public function setStreet1($street1)
    {
        $this->street1 = $street1;
    }

    public function getStreet1()
    {
        return $this->street1;
    }

    public function setStreet2($street2)
    {
        $this->street2 = $street2;
    }

    public function getStreet2()
    {
        return $this->street2;
    }

    public function getStreet()
    {
        return $this->street1.' '.$this->street2;
    }

    public function getCity()
    {
        return $this->city;
    }

    public function setCity($city)
    {
        $this->city = $city;
    }

    public function getState()
    {
        return $this->state;
    }

    public function setState($state)
    {
        $this->state = $state;
    }

    public function getCountry()
    {
        return $this->country;
    }

    public function setCountry($country)
    {
        $this->country = $country;
    }

    public function getPostalCode()
    {
        return $this->postalCode;
    }

    public function setPostalCode($postalCode)
    {
        $this->postalCode = $postalCode;
    }
}
