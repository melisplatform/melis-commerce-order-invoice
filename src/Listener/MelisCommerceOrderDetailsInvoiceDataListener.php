<?php

/**
 * Melis Technology (http://www.melistechnology.com)
 *
 * @copyright Copyright (c) 2016 Melis Technology (http://www.melistechnology.com)
 *
 */

namespace MelisCommerceOrderInvoice\Listener;

use Laminas\EventManager\EventManagerInterface;
use Laminas\EventManager\ListenerAggregateInterface;
use MelisCore\Listener\MelisGeneralListener;

class MelisCommerceOrderDetailsInvoiceDataListener extends MelisGeneralListener implements ListenerAggregateInterface
{
    public function attach(EventManagerInterface $events, $priority = 1)
    {
        $this->attachEventListener(
            $events,
            '*',
            [
                'MelisCommerceOrderPlugin_melistemplating_plugin_generate_view'
            ],
            function($e){
                $sm = $e->getTarget()->getServiceManager();
                $params = $e->getParams();

                if (!empty($params['order'])) {
                    $orderId = $params['order']['id'];

                    $orderInvoiceService = $sm->get('MelisCommerceOrderInvoiceService');

                    $invoice = $orderInvoiceService->getOrderInvoiceList($orderId, null, $limit = 1, 'DESC');

                    if (!empty($invoice)) {
                        $invoice = $invoice[0];
                        $invoice['url_to_download_pdf'] = '/CommerceOrderInvoice/getInvoice';
                    }

                    $params['orderInvoice'] = $invoice;
                }
            }
        );
    }
}