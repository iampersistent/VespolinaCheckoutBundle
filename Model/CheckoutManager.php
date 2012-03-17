<?php
/**
 * (c) 2012 Vespolina Project http://www.vespolina-project.org
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Vespolina\CheckoutBundle\Model;

use JMS\Payment\CoreBundle\Plugin\PluginInterface;
use Vespolina\CartBundle\Model\CartInterface;
use Vespolina\CartBundle\Model\CartManagerInterface;
use Vespolina\CheckoutBundle\Handler\CheckoutHandlerInterface;
use Vespolina\CheckoutBundle\Model\CheckoutManagerInterface;
use Vespolina\ProductBundle\Model\ProductManagerInterface;

/**
 * @author Richard Shank <develop@zestic.com>
 */
class CheckoutManager implements CheckoutManagerInterface
{
    protected $cartManager;
    protected $checkoutHandlers;
    protected $creditCard;
    protected $paymentProcessor;
    protected $productManager;

    public function __construct(PluginInterface $paymentProcessor, CartManagerInterface $cartManager, ProductManagerInterface $productManager)
    {
        $this->cartManager = $cartManager;
        $this->checkoutHandlers = array();
        $this->paymentProcessor = $paymentProcessor;
        $this->productManager = $productManager;
    }

    /**
     * @inheritdoc
     */
    public function addCheckoutHandler(CheckoutHandlerInterface $handler)
    {
        $types = (array)$handler->getTypes();
        foreach ($types as $type) {
            $this->checkoutHandlers[$type] = $handler;
        }
        $rp = new \ReflectionProperty($handler, 'checkoutManager');
        $rp->setAccessible(true);
        $rp->setValue($handler, $this);
        $rp->setAccessible(false);
    }

    public function setCreditCard($creditCard)
    {
        $this->creditCard = $creditCard;
    }

    public function getCreditCard()
    {
        return $this->creditCard;
    }

    /**
     * @inheritdoc
     */
    public function getCheckoutHandler($type)
    {
        if (isset($this->checkoutHandlers[$type])) {
            return $this->checkoutHandlers[$type];
        }

        return null;
    }

    public function setPaymentProcessor(PluginInterface $paymentProcessor)
    {
        $this->paymentProcessor = $paymentProcessor;
    }

    public function getPaymentProcessor()
    {
        return $this->paymentProcessor;
    }

    public function processCart(CartInterface $cart)
    {
        // todo: look at using JMS PluginController

        // recurring must happen separately
        foreach ($cart->getRecurringItems() as $cartItem) {

            $cartableItem = $cartItem->getCartableItem();
            $handler = $this->getCheckoutHandler($cartableItem->getType());

            $recurringInstructions = $cartItem->getPaymentInstruction();
            $recurringInstructions->setCreditCardProfile($this->getCreditCard());

            try {
                $recurringTransaction = $handler->initializeCharge($cartableItem);
            } catch (\Exception $e) {
                // todo: clean up in handler
                throw new \Exception($e->getMessage());
            }
            $handler->processorSuccess($cartableItem, $recurringTransaction);

            // todo: refactor!
            if ($cartableItem->getDiscount()) {
//                $this->updateRecurringAmountOnProducts($cart->getOwner()->getSpreads()); // move out so it only happens once
            }

            $cartableItem->setRecur($recurringTransaction);
            $cartableItem->setProcessing(false); // doesn't belong here
            $this->productManager->updateProduct($cartableItem);
        }


        if (false == true && $cart->getNonRecurringItems()->count()) {
            // process rest of cart
            // todo: this is not working correctly, extended data is in $creditCard
            $extendedData = null;
            $transaction = $this->prepTransaction($cart->getTotal(), $extendedData);
            $this->processor->setIPAddress($this->container->get('request')->getClientIp());
            try {
                $this->processor->approveAndDeposit($transaction, true);
            } catch (\Exception $e) {
                $url = $this->container->get('router')->generate('foodtrekker_payment_error', array('id' => $cart->getId()));
                return new RedirectResponse($url);
            }
        }

        $this->cartManager->setCartState($cart, $cart::STATE_CLOSED);
    }
}