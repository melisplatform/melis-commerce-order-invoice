<?php

/**
 * Melis Technology (http://www.melistechnology.com)
 *
 * @copyright Copyright (c) 2016 Melis Technology (http://www.melistechnology.com)
 *
 */

namespace MelisCommerceOrderInvoice\Listener;

use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\ListenerAggregateInterface;
use MelisCore\Listener\MelisCoreGeneralListener;

class MelisCommerceOrderHistoryInvoiceDataListener extends MelisCoreGeneralListener implements ListenerAggregateInterface
{
    public function attach(EventManagerInterface $events)
    {
        $sharedEvents = $events->getSharedManager();

        $callBackHandler = $sharedEvents->attach(
            '*',
            [
                'MelisCommerceOrderHistoryPlugin_melistemplating_plugin_generate_view'
            ],
            function($e){
                $sm = $e->getTarget()->getServiceLocator();
                $params = $e->getParams();
                $orderInvoiceService = $sm->get('MelisCommerceOrderInvoiceService');

                foreach ($params['orders'] as &$orders) {
                    $orders['invoiceId'] = $orderInvoiceService->getOrderLatestInvoiceId($orders['id']);
                }
            });

        $this->listeners[] = $callBackHandler;
    }
}