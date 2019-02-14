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

class MelisCommerceOrderDetailsInvoiceDataListener extends MelisCoreGeneralListener implements ListenerAggregateInterface
{
    public function attach(EventManagerInterface $events)
    {
        $sharedEvents = $events->getSharedManager();

        $callBackHandler = $sharedEvents->attach(
            '*',
            [
                'MelisCommerceOrderPlugin_melistemplating_plugin_generate_view'
            ],
            function($e){
                $sm = $e->getTarget()->getServiceLocator();
                $params = $e->getParams();

                $orderInvoiceService = $sm->get('MelisCommerceOrderInvoiceService');

                $invoice = $orderInvoiceService->getOrderInvoiceList(15, null, $limit = 1, 'DESC')[0];
                $invoice['url_to_download_pdf'] = 'melisdev.local';

                $params['orderInvoice'] = $invoice;
            },
            -1000);

        $this->listeners[] = $callBackHandler;
    }
}