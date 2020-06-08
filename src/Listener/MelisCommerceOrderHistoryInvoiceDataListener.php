<?php

/**
 *MelisTechnology(http://www.melistechnology.com)
 *
 *@copyrightCopyright(c)2016MelisTechnology(http://www.melistechnology.com)
 *
 */

namespace MelisCommerceOrderInvoice\Listener;

use Laminas\EventManager\EventManagerInterface;
use Laminas\EventManager\ListenerAggregateInterface;
use MelisCore\Listener\MelisGeneralListener;

class MelisCommerceOrderHistoryInvoiceDataListener extends MelisGeneralListener implements ListenerAggregateInterface
{
    public function attach(EventManagerInterface $events, $priority = 1)
    {
        $this->attachEventListener(
            $events,
            '*',
            [
                'MelisCommerceOrderHistoryPlugin_melistemplating_plugin_generate_view'
            ],
            function ($e) {
                $sm = $e->getTarget()->getServiceManager();
                $params = $e->getParams();
                $orderInvoiceService = $sm->get('MelisCommerceOrderInvoiceService');

                // we use the reference to override the data on the paginator
                foreach ($params['orders'] as &$orders) {
                    $orders['invoiceId'] = $orderInvoiceService->getOrderLatestInvoiceId($orders['id']);
                }
            }
        );
    }
}
