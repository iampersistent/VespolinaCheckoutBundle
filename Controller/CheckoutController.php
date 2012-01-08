<?php
/**
 * (c) 2011 - 2012 Vespolina Project http://www.vespolina-project.org
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */
namespace Vespolina\CheckoutBundle\Controller;

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

    public function paymentAction($id)
    {

    }

    public function successAction($id)
    {

    }

    protected function getEngine()
    {
        return $this->container->getParameter('vespolina_checkout.template.engine');
    }
}
