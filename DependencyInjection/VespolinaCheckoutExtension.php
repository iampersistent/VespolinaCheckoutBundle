<?php
/**
 * (c) 2012 Vespolina Project http://www.vespolina-project.org
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Vespolina\CheckoutBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;

/**
 * @author Richard Shank <develop@zestic.com>
 */
class VespolinaCheckoutExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $processor = new Processor();
        $configuration = new Configuration();
        $config = $processor->processConfiguration($configuration, $configs);

        $loader = new XmlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));

        $loader->load(sprintf('checkout.xml'));
        $loader->load(sprintf('form.xml'));

        $container->setAlias('vespolina.default.processor.plugin', $config['default_processor']);
        if (isset($config['address'])) {
            $this->configureAddress($config['address'], $container);
        }
    }

    protected function configureAddress(array $config, ContainerBuilder $container)
    {
        if (isset($config['form'])) {
            $formConfig = $config['form'];
            if (isset($formConfig['type'])) {
                $container->setParameter('vespolina.address.form.type.class', $formConfig['type']);
            }
            if (isset($formConfig['name'])) {
                $container->setParameter('vespolina_address', $formConfig['name']);
            }
            if (isset($formConfig['data_class'])) {
                $container->setParameter('vespolina.address.form.model.data_class.class', $formConfig['data_class']);
            }
        }
    }
}