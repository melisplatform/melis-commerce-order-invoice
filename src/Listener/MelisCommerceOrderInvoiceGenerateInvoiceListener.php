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

class MelisCommerceOrderInvoiceGenerateInvoiceListener extends MelisCoreGeneralListener implements ListenerAggregateInterface
{
    public function attach(EventManagerInterface $events)
    {
        $sharedEvents = $events->getSharedManager();

        $callBackHandler = $sharedEvents->attach(
            '*',
            [
                'meliscommerce_service_checkout_step2_postpayment_proccess_end'
            ],
            function($e){
                $sm = $e->getTarget()->getServiceLocator();
                $params = $e->getParams();

                if ($params['results']['success']) {
                    $orderId = $params['results']['orderId'];

                    $orderService = $sm->get('MelisComOrderService');
                    $orderStatus = $orderService->getOrderStatusByOrderId($orderId);

                    if ($orderStatus[0]->osta_id == 1) {
                        $orderInvoiceService = $sm->get('MelisCommerceOrderInvoiceService');
                        $invoiceId = $orderInvoiceService->generateOrderInvoice($orderId, 'orderinvoicetemplate/default');
                    }
                }
            },
            -1000);

        $this->listeners[] = $callBackHandler;
    }
}