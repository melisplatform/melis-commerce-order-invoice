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

class MelisCommerceOrderInvoiceGenerateInvoiceListener extends MelisGeneralListener implements ListenerAggregateInterface
{
    public function attach(EventManagerInterface $events, $priority = 1)
    {
        $this->attachEventListener(
            $events,
            '*',
            [
                'meliscommerce_service_checkout_step2_postpayment_proccess_end'
            ],
            function($e){
                $sm = $e->getTarget()->getServiceManager();
                $params = $e->getParams();

                if ($params['results']['success']) {
                    $orderId = $params['results']['orderId'];

                    $orderService = $sm->get('MelisComOrderService');
                    $orderStatus = $orderService->getOrderStatusByOrderId($orderId);

                    if ($orderStatus[0]->osta_id == 1) {
                        $orderInvoiceService = $sm->get('MelisCommerceOrderInvoiceService');
                        try {
                            $invoiceId = $orderInvoiceService->generateOrderInvoice($orderId, 'orderinvoicetemplate/default');
                        } catch (\Throwable $e) {
                            // Invoice PDF generation is a side effect of order confirmation, not
                            // part of it — payment has already been taken by this point, so a
                            // failure here (e.g. a broken template asset) must never abort the
                            // checkout flow.
                            error_log('MelisCommerceOrderInvoice: failed to generate invoice for order ' . $orderId . ': ' . $e->getMessage());
                        }
                    }
                }
            },
            -1000
        );
    }
}