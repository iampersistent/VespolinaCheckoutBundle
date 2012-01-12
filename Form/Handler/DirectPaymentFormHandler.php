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

    public function process($parameters)
    {
        $directPayment = new DirectPayment();
        $this->form->setData($directPayment);

        if ('POST' == $this->request->getMethod()) {
            $this->form->bindRequest($this->request);

            if ($this->form->isValid()) {

                // when the binding is working this can be called in $directPayment
                $params = $this->request->request->get($parameters);
                $address = $params['address'];
                $address['street'] = $address['street2'] ? $address['street1'].' '.$address['street2'] : $address['street1'];
                $params['address'] = $address;
                $data = $this->mapData($directPayment::$mapping, $params);
                $exp = $data['EXPDATE'];
                $data['EXPDATE'] = sprintf('%02d%d', $exp['month'], $exp['year']);

                $extendedData = new ExtendedData();
                $extendedData->set('checkout_params', $data);

                return $extendedData;
            }
        }
        return null;
    }

    protected function mapData($mapping, $rawData)
    {
        $data = array();
        foreach ($mapping as $property => $map) {
            if (!isset($rawData[$property])) {
                continue;
            }
            $value = $rawData[$property];

            if (is_array($map)) {
                $data = array_merge($data, $this->mapData($map, $value));
            } else {
                $data[$map] = $value;
            }
        }
        return $data;
    }
}
