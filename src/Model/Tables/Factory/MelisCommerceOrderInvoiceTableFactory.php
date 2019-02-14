<?php

/**
 * Melis Technology (http://www.melistechnology.com)
 *
 * @copyright Copyright (c) 2016 Melis Technology (http://www.melistechnology.com)
 *
 */

namespace MelisCommerceOrderInvoice\Model\Tables\Factory;

use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\FactoryInterface;
use Zend\Db\ResultSet\HydratingResultSet;
use Zend\Db\TableGateway\TableGateway;
use Zend\Stdlib\Hydrator\ObjectProperty;

use MelisCommerceOrderInvoice\Model\MelisCommerceOrderInvoice;
use MelisCommerceOrderInvoice\Model\Tables\MelisCommerceOrderInvoiceTable;

class MelisCommerceOrderInvoiceTableFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $sl)
    {
        $hydratingResultSet = new HydratingResultSet(new ObjectProperty(), new MelisCommerceOrderInvoice());
        $tableGateway = new TableGateway('melis_ecom_order_invoice', $sl->get('Zend\Db\Adapter\Adapter'), null, $hydratingResultSet);

        return new MelisCommerceOrderInvoiceTable($tableGateway);
    }
}