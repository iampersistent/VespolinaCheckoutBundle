<?php
/**
 * (c) 2011 - 2012 Vespolina Project http://www.vespolina-project.org
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */
namespace Vespolina\CheckoutBundle\Controller;

use JMS\Payment\CoreBundle\Plugin\Exception\ActionRequiredException;
use JMS\Payment\CoreBundle\Entity\FinancialTransaction;
use JMS\Payment\CoreBundle\Entity\Payment;
use JMS\Payment\CoreBundle\Entity\ExtendedData;
use JMS\Payment\CoreBundle\Entity\PaymentInstruction;

use Symfony\Component\DependencyInjection\ContainerAware;

/**
 * @author Richard D Shank <develop@zestic.com>
 */

class CheckoutController extends ContainerAware
{
    public function reviewAction($id)
    {
        $cart = $this->container->get('vespolina.checkout_cart_manager')->findCartById($id);
        return $this->container->get('templating')->renderResponse('VespolinaCheckoutBundle:Checkout:review.html.'.$this->getEngine(), array(
            'cart' => $cart,
        ));
    }

    public function paymentAction($id, $provider)
    {
        $cart = $this->container->get('vespolina.checkout_cart_manager')->findCartById($id);

        $form = $this->container->get('vespolina.secure.form');

        return $this->container->get('templating')->renderResponse('VespolinaCheckoutBundle:Checkout:'.$provider.'_payment.html.'.$this->getEngine(), array(
            'cart' => $cart,
            'form' => $form->createView(),
            'provider' => $provider,
        ));
    }

    public function processAction($id, $provider, $paymentId = null)
    {
        static $cartItems;

        $processor = $this->container->get('vespolina.'.$provider.'.processing');
        $formHandler = $this->container->get('vespolina.'.$provider.'.form.handler');

        $cart = $this->container->get('vespolina.checkout_cart_manager')->findCartById($id);
        $cartItems = $cart->getItems();

        if (null === $paymentId) {

            // recurring must happen separately
            foreach ($cartItems as $cartItem) {
                if ($cartItem->isSubscription()) {
                    $recur = $cartItem->getRecur();
                    // todo: Payment Core Recurring Profile
                    $data['PROFILESTARTDATE'] = date('Y-m-d');
                    $data['BILLINGPERIOD'] = $recur->getBillingPeriod();
                    $data['BILLINGFREQUENCY'] = $recur->getBillingFrequency();

                    $response = $processor->CreateRecurringPaymentsProfile();
                    // remove each successful item from working cart
$a = 0;
                }
            }

            if ($data = $formHandler->process('vespolina_'.$provider)) {
                // recurring -

                $transaction = $this->prepTransaction($cart->getTotal(), $data);
                $processor->setIPAddress($this->container->get('request')->getClientIp());
                $processor->setIPAddress('71.59.151.161');
                $processor->approveAndDeposit($transaction, true);
                // success

                // upgrade or new?


            }
        }
        // failure

    }

    public function successAction($id)
    {

    }

    protected function getEngine()
    {
        return $this->container->getParameter('vespolina.checkout.template_engine');
    }

    protected function prepTransaction($amount, $data)
    {
        // todo: this should be in a service
        $paymentInstruction = new PaymentInstruction($amount, 'USD', 'paypal_direct_payment', $data);
        $payment = new Payment($paymentInstruction, $amount);
        $transaction = new FinancialTransaction();
        $transaction->setRequestedAmount($amount);
        $payment->addTransaction($transaction);

        return $transaction;
    }
}
