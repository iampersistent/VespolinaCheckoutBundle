<?php

namespace Vespolina\CheckoutBundle\Model;
/**
 * @author Richard Shank <develop@zestic.com>
 */

class DirectPayment
{
    protected $address;
    protected $firstName;
    protected $middle;
    protected $lastName;
    protected $creditCard;

    public static $mapping = array(
        'address' => array(
            'street' => 'STREET',
            'city' => 'CITY',
            'state' => 'STATE',
            'postalCode' => 'ZIP',
            'country' => 'COUNTRYCODE',
        ),
        'creditCard' => array(
            'carType' => 'CREDITCARETYPE',
            'cardNumber' => 'ACCT',
            'expiration' => 'EXPDATE',
            'cvv' => 'CVV2',
        ),
        'firstName' => 'FIRSTNAME',
        'lastName' => 'LASTNAME',
    );

    public function setAddress($address)
    {
        $this->address = $address;
    }

    public function getAddress()
    {
        return $this->address;
    }

    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;
    }

    public function getFirstName()
    {
        return $this->firstName;
    }

    public function setLastName($lastName)
    {
        $this->lastName = $lastName;
    }

    public function getLastName()
    {
        return $this->lastName;
    }

    public function setMiddle($middle)
    {
        $this->middle = $middle;
    }

    public function getMiddle()
    {
        return $this->middle;
    }

    public function setCreditCard($creditCard)
    {
        $this->creditCard = $creditCard;
    }

    public function getCreditCard()
    {
        return $this->creditCard;
    }

    public function getMappedData()
    {
        return $this->mapData(self::$mapping, $this);
    }

    protected function mapData($mapping, $object)
    {
        $data = array();
        foreach ($mapping as $property => $map) {
            $getter = 'get'.ucfirst($property);
            if (is_array($map)) {
                $data = array_merge($data, $this->mapData($map, $object->$getter()));
            } else {
                $data[$map] = $object->$getter();
            }
        }
        return $data;
    }
}
