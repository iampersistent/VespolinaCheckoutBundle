<?php
/**
 * (c) 2011-2012 Vespolina Project http://www.vespolina-project.org
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
    public function checkoutAction()
    {
        $processManager = $this->container->get('vespolina.process_manager');
        $processOwner = $this->container->get('session')->getId();

        $checkoutProcess = $processManager->getActiveProcessByOwner('checkout_b2c', $processOwner);

        if (!$checkoutProcess) {

            $checkoutProcess = $processManager->createProcess('checkout_b2c', $processOwner);
            $checkoutProcess->init(true);   //initialize with first time set to true

            $context = $checkoutProcess->getContext();
            $context['cart'] = $this->getCart();
            $processResult = $checkoutProcess->execute();

            //Persist (in session)
            $processManager->updateProcess($checkoutProcess);

        } else{
            $processResult = $checkoutProcess->execute();

            //Persist (in session)
            $processManager->updateProcess($checkoutProcess);
        }

        if ($processResult) {

            return $processResult;
        }

        //If we get here then there was a serious error
        throw new \Exception('Checkout failed - inernal error - could not find process step to execute');
    }

    public function executeAction($processId, $processStepName) {

        $processManager = $this->container->get('vespolina.process_manager');
        $processStep = $this->getCurrentProcessStepByProcessId($processId);

        if (null === $processStep) {
            throw new \Exception('Process session expired');
        }
        //Assert that the current process step (according to the process) is the same as the step name
        //passed on to the request
        if ($processStep->getName() != $processStepName) {
           throw new \Exception('Checkout failed - internal error');
        }

        return $processStep->getProcess()->execute();
    }

    public function gotoProcessStepAction($processId, $processStepName) {

        $processManager = $this->container->get('vespolina.process_manager');
        $process = $processManager->findProcessById($processId);
        $processStep = $process->getProcessStepByName($processStepName);

        return $process->gotoProcessStep($processStep);

    }


    protected function getCart($cartId = null)
    {
        if ($cartId) {
            return $this->container->get('vespolina.cart_manager')->findCartById($cartId);
        } else {
            return $this->container->get('vespolina.cart_manager')->getActiveCart();
        }
    }

    protected function getCurrentProcessStepByProcessId($processId)
    {
        $processManager = $this->container->get('vespolina.process_manager');
        $process = $processManager->findProcessById($processId);

        if ($process) {

            return $process->getCurrentProcessStep();
        }
    }

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

        $cart->getTotal();
        if (null === $paymentId) {

            if ($extendedData = $formHandler->process('vespolina_'.$provider)) {
                // recurring must happen separately
                foreach ($cartItems as $cartItem) {
                    if ($cartItem->isRecurring()) {
                        $cartableItem = $cartItem->getCartableItem();
                        $options = $cartItem->getOptions();
                        $key = key($options);
                        $options = $options[$key];
                        $recur = $cartableItem->getOptionSet($options)->getRecur();
                        // todo: Payment Core Recurring Profile
                        $data['PROFILESTARTDATE'] = date('Y-m-d').'T00:00:00Z';
                        $data['BILLINGPERIOD'] = $recur->getBillingPeriod();
                        $data['BILLINGFREQUENCY'] = $recur->getBillingFrequency();
                        $data['AMT'] = $recur->getPrice();
                        $data['DESC'] = $recur->getPrice();

                        try {
                            $response = $processor->requestCreateRecurringPaymentsProfile(array_merge($extendedData->get('checkout_params'), $data));
                            $cart->removeItem($cartItem);
                        } catch (\Exception $e) {
                            //deal with it
                        }
                        // remove each successful item from working cart
                        // todo: put this into fulfillment at some point
                        $plan = $this->dm->getRepository('Model:Plan')->find($options->getPlan());
                        $constraints = $plan->getConstraints();



                    }
                }

                // process rest of cart

                $transaction = $this->prepTransaction($cart->getTotal(), $extendedData);
                $processor->setIPAddress($this->container->get('request')->getClientIp());
                $processor->setIPAddress('71.59.151.161');
                try {
                    $processor->approveAndDeposit($transaction, true);
                } catch (\Exception $e) {

                }
                // success

                // upgrade or new?
            }

        }
        // failure

    }

    public function successAction($id)
    {
        $cart = $this->container->get('vespolina.checkout_cart_manager')->findCartById($id);
        return $this->container->get('templating')->renderResponse('VespolinaCheckoutBundle:Checkout:success.html.'.$this->getEngine(), array(
            'cart' => $cart,
        ));
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
