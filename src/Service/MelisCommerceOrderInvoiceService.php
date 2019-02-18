<?php

/**
 * Melis Technology (http://www.melistechnology.com)
 *
 * @copyright Copyright (c) 2016 Melis Technology (http://www.melistechnology.com)
 *
 */

namespace MelisCommerceOrderInvoice\Service;

use MelisCore\Service\MelisCoreGeneralService;
use Spipu\Html2Pdf\Html2Pdf;
use Spipu\Html2Pdf\Exception\Html2PdfException;
use Spipu\Html2Pdf\Exception\ExceptionFormatter;
use Zend\View\Model\ViewModel;
use Zend\Http\Request as HttpRequest;

class MelisCommerceOrderInvoiceService extends MelisCoreGeneralService
{
    protected $invoiceId;
    protected $date;

    /**
     * html2pdf library - converts html to pdf
     * @param $order
     * @param null $template
     * @return string
     */
    private function html2pdf($order, $template = null)
    {
        try {
            $viewRendererService = $this->getServiceLocator()->get('ViewRenderer');
            $data = $this->prepareOrderItems($order);

            $view = new ViewModel();
            $view->invoiceDate = $this->date;
            $view->invoiceNumber = $this->invoiceId;
            $view->data = $data;
            $view->addresses = $order->getAddresses();

            if (is_null($template)) {
                $view->setTemplate('orderinvoicetemplate/default');
            } else {
                $view->setTemplate($template);
            }

            $contents = $viewRendererService->render($view);

            $html2pdf = new Html2Pdf('P', 'A4', 'en');
            $html2pdf->writeHTML($contents);
            $pdf = $html2pdf->output('', 'S');

            return $pdf;
        } catch (Html2PdfException $e) {
            $html2pdf->clean();
            $formatter = new ExceptionFormatter($e);

            echo $formatter->getHtmlMessage();
        }
    }

    /**
     * Generates the pdf using the html2pdf library
     * @param $orderId
     * @param null $template
     * @return mixed
     */
    public function generateOrderInvoice($orderId, $template = null)
    {
        $arrayParameters = $this->makeArrayFromParameters(__METHOD__, func_get_args());
        $arrayParameters = $this->sendEvent(
            'meliscommerce_order_invoice_generate_order_invoice_start',
            $arrayParameters
        );

        //tables and services
        $orderInvoiceTable = $this->getServiceLocator()->get('MelisCommerceOrderInvoiceTable');
        $ordersService = $this->getServiceLocator()->get('MelisComOrderService');

        //order data
        $order = $ordersService->getOrderById($orderId);

        //client data & client ID
        $clientData = $order->getClient();
        $clientId = $clientData->cli_id;

        $this->date = date("Y-m-d H:i:s");

        //save invoice
        $invoiceId = $orderInvoiceTable->save([
            'ordin_user_id' => $clientId,
            'ordin_order_id' => $order->getId(),
            'ordin_date_generated' => $this->date,
            'ordin_invoice_pdf' => 'test'
        ]);

        $this->invoiceId = $invoiceId;

        //get pdf output
        $pdfContents = $this->html2pdf($order, null);

        //update invoice
        $orderInvoiceTable->save([
            'ordin_invoice_pdf' => $pdfContents
        ], $invoiceId);


        //logic here
        $arrayParameters['results'] = $invoiceId;

        $arrayParameters = $this->sendEvent(
            'meliscommerce_order_invoice_generate_order_invoice_end',
            $arrayParameters
        );

        return $arrayParameters['results'];
    }

    /**
     * Retrieve list of invoices. one will only be retrieved in the front and all will be listed on the back
     * @param $orderId
     * @param int $limit
     */
    public function getOrderInvoiceList($orderId, $start, $limit = 1, $order)
    {
        $arrayParameters = $this->makeArrayFromParameters(__METHOD__, func_get_args());

        $arrayParameters = $this->sendEvent(
            'meliscommerce_order_invoice_get_order_invoice_list_start',
            $arrayParameters
        );

        // logic stars here
        $orderInvoiceTable = $this->getServiceLocator()->get('MelisCommerceOrderInvoiceTable');

        $invoiceList = $orderInvoiceTable->getOrderInvoiceList(
            $arrayParameters['orderId'],
            $arrayParameters['start'],
            $arrayParameters['limit'],
            $arrayParameters['order']
        )->toArray();

        $arrayParameters['results'] = $invoiceList;

        // end
        $arrayParameters = $this->sendEvent(
            'meliscommerce_order_invoice_get_order_invoice_list_end',
            $arrayParameters
        );

        return $arrayParameters['results'];
    }

    /**
     * Returns the PDF contents of the invoice
     * @param $orderInvoiceId
     */
    public function getOrderInvoice($invoiceId)
    {
        $arrayParameters = $this->makeArrayFromParameters(__METHOD__, func_get_args());

        $arrayParameters = $this->sendEvent(
            'meliscommerce_order_invoice_get_order_invoice_start',
            $arrayParameters
        );

        // logic stars here
        $orderInvoiceTable = $this->getServiceLocator()->get('MelisCommerceOrderInvoiceTable');

        $invoice = $orderInvoiceTable->getEntryById($arrayParameters['invoiceId'])->toArray()[0];

        $arrayParameters['results'] = $invoice['ordin_invoice_pdf'];

        $arrayParameters = $this->sendEvent(
            'meliscommerce_order_invoice_get_order_invoice_end',
            $arrayParameters
        );

        return $arrayParameters['results'];
    }

    /**
     * This will prepare the list of items along with the needed data for the invoice pdf
     * @param $order
     */
    private function prepareOrderItems($order)
    {
        $data = [];
        $basket = $order->getBasket();
        $currency = $this->getCurrency($basket[0]->obas_currency);
        $subtotal = 0;
        $orderCoupons = $this->getCoupons($order->getId());
        $shipping = $this->getShippingCost($order);
        $totalCouponDiscount = 0;
        $total = 0;

        // PREPARE ORDER ITEMS DATA & SUBTOTAL
        foreach ($basket as $item) {
            $discount = $this->getItemDiscount($orderCoupons, $item);

            $data['items'][] = [
                'obas_product_name' => $item->obas_product_name,
                'obas_price_net' => $currency['cur_symbol'] .
                    number_format(
                        $item->obas_price_net,
                        2
                    ),
                'obas_quantity' => $item->obas_quantity,
                'discount' => $discount > 0 ? $currency['cur_symbol'] . $discount : '',
                'currency' => $currency['cur_symbol'],
                'amount' => $currency['cur_symbol'] .
                    number_format(
                        ($item->obas_price_net - $discount) * $item->obas_quantity,
                        2
                    )
            ];

            $subtotal += number_format(
                ($item->obas_price_net - $discount) * $item->obas_quantity,
                2
            );
        }

        // PREPARE COUPONS
        $coupons = $this->getCouponDetails($orderCoupons, $subtotal);

        foreach ($coupons as $coupon) {
            $data['coupons'][] = [
                'code' => '(' . $coupon['couponCode'] . ') ',
                'discount' => '-' . $currency['cur_symbol'] . number_format($coupon['couponDiscount'], 2)
            ];

            $totalCouponDiscount += $coupon['couponDiscount'];
        }

        // PREPARE FINAL DATA
        $data['subtotal'] = $currency['cur_symbol'] . number_format($subtotal, 2);
        $data['shipping'] = $shipping > 0 ? $currency['cur_symbol'] . number_format($shipping, 2) : '';

        if ($totalCouponDiscount >= $subtotal) {
            $total = $shipping;
        } else {
            $total = ($subtotal - $totalCouponDiscount) + $shipping;
        }

        $data['total'] = $currency['cur_symbol'] . number_format($total, 2);

        return $data;
    }

    /**
     * Gets the coupon for the order
     * @param $orderCoupons
     * @param $subTotal
     * @return array
     */
    private function getCouponDetails($orderCoupons, $subTotal) {
        $couponDetails = array();

        if (!empty($orderCoupons)) {
            foreach ($orderCoupons as $coupon) {
                if (!$coupon->getCoupon()->coup_product_assign) {
                    $couponDetails[] = [
                        'couponCode' => $coupon->getCoupon()->coup_code,
                        'couponIsInPercentage' => ($coupon->getCoupon()->coup_percentage) ? true : false,
                        'couponValue' => (
                            $coupon->getCoupon()->coup_percentage) ?
                            $coupon->getCoupon()->coup_percentage.'%' :
                            $coupon->getCoupon()->coup_discount_value,
                        'couponDiscount' => ($coupon->getCoupon()->coup_percentage) ?
                            ($coupon->getCoupon()->coup_percentage / 100) * $subTotal :
                            $coupon->getCoupon()->coup_discount_value,
                    ];
                }
            }
        }

        return $couponDetails;
    }

    /**
     * Get the discount for each item for coupons that are linked to products
     * @param $orderCoupons
     * @param $item
     * @return float|int
     */
    private function getItemDiscount($orderCoupons, $item) {
        $discount = 0;

        if (!empty($orderCoupons)) {
            foreach ($orderCoupons as $coupon) {
                if ($coupon->getCoupon()->coup_product_assign) {
                    foreach ($coupon->getCoupon()->discountedBasket as $item2) {
                        if ($item2->cord_basket_id == $item->obas_id) {
                            if (!empty($coupon->getCoupon()->coup_percentage)) {
                                $discount = ($coupon->getCoupon()->coup_percentage / 100) * $item->obas_price_net;
                            }
                            elseif (!empty($coupon->getCoupon()->coup_discount_value)) {
                                $discount = $coupon->getCoupon()->coup_discount_value * $item2->cord_quantity_used;
                            }
                        }
                    }
                }
            }
        }

        return $discount;
    }

    /**
     * Get all coupons for the order
     * @param $orderId
     * @return array
     */
    private function getCoupons($orderId) {
        $couponSvc = $this->getServiceLocator()->get('MelisComCouponService');
        $orderCoupons = $couponSvc->getCouponList($orderId);
        $coupons = array();

        foreach($orderCoupons as $coupon) {
            if ($coupon->getCoupon()->coup_product_assign) {
                $coupon->getCoupon()->discountedBasket = $couponSvc->getCouponDiscountedBasketItems(
                    $coupon->getCoupon()->coup_id,
                    $orderId
                );
            }

            $coupons[] = $coupon;
        }

        return $coupons;
    }

    /**
     * Gets the shipping cost of the order
     * @param $order
     * @return int
     */
    private function getShippingCost($order)
    {
        $shipping = 0;

        foreach($order->getPayment() as $payment) {
            $shipping += $payment->opay_price_shipping;
        }

        return $shipping;
    }

    /**
     * Gets the currency for the items
     * We are just directly getting the first item t oget the currency id because
     * there is no case that the items on the basket will have different currency
     * @param $currId
     * @return mixed
     */
    private function getCurrency($currId)
    {
        $currTbl = $this->getServiceLocator()->get('MelisEcomCurrencyTable');
        $curr = $currTbl->getEntryById($currId)->toArray()[0];

        return $curr;
    }
}