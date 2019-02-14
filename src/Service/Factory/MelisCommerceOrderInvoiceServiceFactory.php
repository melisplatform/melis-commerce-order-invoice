<?php

/**
 * Melis Technology (http://www.melistechnology.com)
 *
 * @copyright Copyright (c) 2016 Melis Technology (http://www.melistechnology.com)
 *
 */

namespace MelisCommerceOrderInvoice\Service\Factory;

use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\FactoryInterface;
use MelisCommerceOrderInvoice\Service\MelisCommerceOrderInvoiceService;

class MelisCommerceOrderInvoiceServiceFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $sl)
    {
        $service = new MelisCommerceOrderInvoiceService();
        $service->setServiceLocator($sl);

        return $service;
    }
}