<?php
/**
* (c) 2012 Vespolina Project http://www.vespolina-project.org
*
* This source file is subject to the MIT license that is bundled
* with this source code in the file LICENSE.
*/
namespace Vespolina\CheckoutBundle\Form\Handler;

use JMS\Payment\CoreBundle\Entity\ExtendedData;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Vespolina\CheckoutBundle\Model\DirectPayment;
/**
 * @author Richard Shank <develop@zestic.com>
 */
class DirectPaymentFormHandler
{
    protected $request;
    protected $form;

    public function __construct(Form $form, Request $request, $doINeedAManager = null)
    {
        $this->form = $form;
        $this->request = $request;
    }

    public function process($creditCard)
    {
        $this->form->setData($creditCard);

        if ('POST' == $this->request->getMethod()) {
            $this->form->bindRequest($this->request);
            if ($this->form->isValid()) {
                // this is hackie as fuck, but I'm tired of dealing the goddamn form right now
                $params = $this->request->request->get('vespolina_secure');
                $address = $params['address'];
                $creditCard->setStreet1($address['street1']);
                $creditCard->setStreet2($address['street2']);
                $creditCard->setCity($address['city']);
                $creditCard->setState($address['state']);
                $creditCard->setCountry($address['country']);
                $creditCard->setPostCode($address['postalCode']);

                $cc = $params['creditCard'];
                $creditCard->setCardNumber($cc['cardNumber']);
                $creditCard->setCvv($cc['cvv']);
                $creditCard->setExpiration($cc['expiration']['month'], $cc['expiration']['year']);

                $creditCard->setFirstName($params['firstName']);
                $creditCard->setLastName($params['lastName']);

                return $creditCard;
            }
        }

        return null;
    }
}
