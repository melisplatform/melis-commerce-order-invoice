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
use Zend\I18n\Translator\Translator;

class MelisCommerceOrderInvoiceService extends MelisCoreGeneralService
{
    protected $invoiceId;
    protected $date;
    protected $clientLangId;
    protected $clientLangLocale;

    /**
     * html2pdf library - converts html to pdf
     * @param $order
     * @param null $template
     * @return string
     */
    private function html2pdf ($order, $template)
    {
        try {
            $viewRendererService = $this->getServiceLocator()->get('ViewRenderer');
            $data = $this->prepareData($order);

            $view = new ViewModel();
            $view->invoiceNumber = $this->invoiceId;
            $view->order = $order;
            $view->data = $data;
            $view->date = $this->date;
            // THIS COULD BE OVERRIDDEN ON THE MODULE CONFIG
            $view->setTemplate($template);
            $view->clientLangLocale = $this->clientLangLocale;

            //new translator depends on the client's locale
            $translator = new Translator();
            $translator->addTranslationFile('phparray', __DIR__ . '/../../language/' . $this->clientLangLocale . '.interface.php', 'default', $this->clientLangLocale);

            $view->textTranslator = $translator;
            // YOU CAN USE THIS EVENT TO OVERRIDE THE VIEW
            $this->sendEvent('meliscommerceorderinvoice_pdf_view', ['view' => $view]);

            $contents = $viewRendererService->render($view);

            $html2pdf = new Html2Pdf('P', 'A4', 'en');
            $html2pdf->writeHTML($contents);
            $pdf = $html2pdf->output('', 'S');
        } catch (Html2PdfException $e) {
            $html2pdf->clean();
            $formatter = new ExceptionFormatter($e);

            echo $formatter->getHtmlMessage();
        }

        return $pdf;
    }

    /**
     * Returns the latest invoice id of an order or returns 0 if the order
     * has no invoice yet
     * @param $orderId
     * @param null $template
     * @return mixed
     */
    public function getOrderLatestInvoiceId ($orderId) {
        $arrayParameters = $this->makeArrayFromParameters(__METHOD__, func_get_args());
        $arrayParameters = $this->sendEvent(
            'meliscommerce_order_invoice_get_latest_invoiceid_by_orderid_start',
            $arrayParameters
        );

        $invoice = $this->getOrderInvoiceList($arrayParameters['orderId'], null, 1, 'DESC');

        if (!empty($invoice)) {
            $invoice = $invoice[0];
            $arrayParameters['result'] = $invoice['ordin_id'];
        } else {
            $arrayParameters['result'] = 0;
        }

        $arrayParameters = $this->sendEvent(
            'meliscommerce_order_invoice_get_latest_invoiceid_by_orderid_end',
            $arrayParameters
        );

        return $arrayParameters['result'];
    }

    /**
     * Returns an invoice based on the invoice id
     * @param $invoiceId
     * @return mixed
     */
    public function getInvoice ($invoiceId) {
        $arrayParameters = $this->makeArrayFromParameters(__METHOD__, func_get_args());
        $arrayParameters = $this->sendEvent(
            'meliscommerce_order_invoice_get_invoice_start',
            $arrayParameters
        );

        //tables and services
        $orderInvoiceTable = $this->getServiceLocator()->get('MelisCommerceOrderInvoiceTable');
        $invoice = $orderInvoiceTable->getEntryById($arrayParameters['invoiceId'])->toArray();

        $arrayParameters['result'] = !empty($invoice) ? $invoice[0] : '';

        $arrayParameters = $this->sendEvent(
            'meliscommerce_order_invoice_get_invoice_end',
            $arrayParameters
        );

        return $arrayParameters['result'];
    }

    /**
     * Generates the pdf using the html2pdf library
     * @param $orderId
     * @param null $template
     * @return mixed
     */
    public function generateOrderInvoice ($orderId, $template)
    {
        $arrayParameters = $this->makeArrayFromParameters(__METHOD__, func_get_args());
        $arrayParameters = $this->sendEvent(
            'meliscommerce_order_invoice_generate_order_invoice_start',
            $arrayParameters
        );

        //tables and services
        $orderInvoiceTable = $this->getServiceLocator()->get('MelisCommerceOrderInvoiceTable');
        $ordersService = $this->getServiceLocator()->get('MelisComOrderService');
        $melisEcomLangTable = $this->getServiceLocator()->get('MelisEcomLangTable');
        //order data
        $order = $ordersService->getOrderById($arrayParameters['orderId']);

        //client data & client ID
        $clientData = $order->getClient();
        $clientId = $clientData->cli_id;
        $this->clientLangId = $order->getPerson()->cper_lang_id;
        $this->clientLangLocale = $melisEcomLangTable->getEntryById($this->clientLangId)->toArray()[0]['elang_locale'];

        $dateFormat = explode(" ", $this->getDateFormatByLocate($this->clientLangLocale))[0];
        //date created, this will also be used in the pdf
        $this->date = strftime($dateFormat, strtotime(date("Y-m-d H:i:s")));

        //save invoice to get the ID
        $invoiceId = $orderInvoiceTable->save([
            'ordin_user_id' => $clientId,
            'ordin_order_id' => $order->getId(),
            'ordin_date_generated' => date("Y-m-d H:i:s"),
            'ordin_invoice_pdf' => 'this will be overwritten'
        ]);

        $this->invoiceId = $invoiceId;

        $pdfContents = $this->html2pdf($order, $arrayParameters['template']);


        //update invoice with the correct pdf
        $orderInvoiceTable->save([
            'ordin_invoice_pdf' => $pdfContents
        ], $invoiceId);

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
    public function getOrderInvoiceList ($orderId, $start, $limit, $order)
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
    public function getOrderInvoice ($invoiceId)
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
    private function prepareData ($order)
    {
        $data = [];
        $basket = $order->getBasket();
        $currency = $this->getCurrency($basket[0]->obas_currency);
        $subtotal = 0;
        $orderCoupons = $this->getCoupons($order->getId());
        $shipping = $this->getShippingCost($order);
        $totalCouponDiscount = 0;
        $hasItemDiscount = false;

        // PREPARE ORDER ITEMS DATA & SUBTOTAL
        foreach ($basket as $item) {
            $discountDetails = $this->getItemDiscountDetails($orderCoupons, $item);

            // CHECK IF THERE IS A DISCOUNT ASSOCIATED TO THE PRODUCT
            if ($discountDetails['discount'] > 0) {
                $hasItemDiscount = true;
            }

            if ($discountDetails['discount'] > 0) {
                // PRODUCT WITH DISCOUNT
                $data['items'][] = [
                    'obas_product_name' => $item->obas_product_name,
                    'obas_quantity' => $discountDetails['qty_used'],
                    'currency' => $currency['cur_symbol'],
                    'obas_price_net' => $this->formatPrice($currency['cur_symbol'], $item->obas_price_net),
                    'discount' => $discountDetails['discountTotal'] > 0 ? $this->formatPrice($currency['cur_symbol'], $discountDetails['discountTotal']) : '',
                    'unitDiscountPrice' => $discountDetails['discount'] > 0 ? $this->formatPrice($currency['cur_symbol'], $item->obas_price_net - $discountDetails['discount']) : '',
                    'amount' => $this->formatPrice(
                        $currency['cur_symbol'],
                        ($item->obas_price_net * $discountDetails['qty_used']) - $discountDetails['discountTotal']
                    )
                ];

                // PRODUCT WITHOUT DISCOUNT
                $data['items'][] = [
                    'obas_product_name' => $item->obas_product_name,
                    'obas_quantity' => $item->obas_quantity - $discountDetails['qty_used'],
                    'currency' => $currency['cur_symbol'],
                    'obas_price_net' => $this->formatPrice($currency['cur_symbol'], $item->obas_price_net),
                    'discount' => '',
                    'unitDiscountPrice' => '',
                    'amount' => $this->formatPrice(
                        $currency['cur_symbol'],
                        $item->obas_price_net * ($item->obas_quantity - $discountDetails['qty_used'])
                    )
                ];
            } else {
                $data['items'][] = [
                    'obas_product_name' => $item->obas_product_name,
                    'obas_quantity' => $item->obas_quantity,
                    'currency' => $currency['cur_symbol'],
                    'obas_price_net' => $this->formatPrice($currency['cur_symbol'], $item->obas_price_net),
                    'discount' => $discountDetails['discountTotal'] > 0 ? $this->formatPrice($currency['cur_symbol'], $discountDetails['discountTotal']) : '',
                    'unitDiscountPrice' => $discountDetails['discount'] > 0 ? $this->formatPrice($currency['cur_symbol'], $item->obas_price_net - $discountDetails['discount']) : '',
                    'amount' => $this->formatPrice(
                        $currency['cur_symbol'],
                        ($item->obas_price_net * $item->obas_quantity) - $discountDetails['discountTotal']
                    )
                ];
            }

            $subtotal += number_format(
                ($item->obas_price_net * $item->obas_quantity) - $discountDetails['discountTotal'],
                2
            );
        }

        // PREPARE COUPONS
        $coupons = $this->getCouponDetails($orderCoupons, $subtotal);

        foreach ($coupons as $coupon) {
            $data['coupons'][] = [
                'code' => '(' . $coupon['couponCode'] . ') ',
                'discount' => '- ' . $this->formatPrice($currency['cur_symbol'], $coupon['couponDiscount'])
            ];

            $totalCouponDiscount += $coupon['couponDiscount'];
        }

        // PREPARE FINAL DATA
        $data['hasItemDiscount'] = $hasItemDiscount;
        $data['subtotal'] = $this->formatPrice($currency['cur_symbol'],$subtotal);
        $data['shipping'] = $shipping > 0 ? $this->formatPrice($currency['cur_symbol'], $shipping) : '';

        if ($totalCouponDiscount >= $subtotal) {
            $total = $shipping;
        } else {
            $total = ($subtotal - $totalCouponDiscount) + $shipping;
        }

        $data['total'] = $this->formatPrice($currency['cur_symbol'],$total);

        return $data;
    }

    /**
     * Gets the coupon for the order
     * @param $orderCoupons
     * @param $subTotal
     * @return array
     */
    private function getCouponDetails ($orderCoupons, $subTotal) {
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
     * @return array
     */
    private function getItemDiscountDetails ($orderCoupons, $item) {
        $discountDetails = [
            'discountTotal' => 0,
            'discount' => 0,
            'qty_used' => 0
        ];

        if (!empty($orderCoupons)) {
            foreach ($orderCoupons as $coupon) {
                if ($coupon->getCoupon()->coup_product_assign) {
                    foreach ($coupon->getCoupon()->discountedBasket as $discountedBasket) {
                        if ($discountedBasket->cord_basket_id == $item->obas_id) {
                            if (!empty($coupon->getCoupon()->coup_percentage)) {
                                $discountDetails['discountTotal'] = (($coupon->getCoupon()->coup_percentage / 100) * $item->obas_price_net)
                                    * $discountedBasket->cord_quantity_used;
                            }
                            elseif (!empty($coupon->getCoupon()->coup_discount_value)) {
                                $discountDetails['discountTotal'] = $coupon->getCoupon()->coup_discount_value * $discountedBasket->cord_quantity_used;
                            }

                            $discountDetails['discount'] = (($coupon->getCoupon()->coup_percentage / 100) * $item->obas_price_net);
                            $discountDetails['qty_used'] = $discountedBasket->cord_quantity_used;
                        }
                    }
                }
            }
        }

        return $discountDetails;
    }

    /**
     * Get all coupons for the order
     * @param $orderId
     * @return array
     */
    private function getCoupons ($orderId) {
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
    private function getShippingCost ($order)
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
    private function getCurrency ($currId)
    {
        $currTbl = $this->getServiceLocator()->get('MelisEcomCurrencyTable');
        $curr = $currTbl->getEntryById($currId)->toArray()[0];

        return $curr;
    }

    /**
     * Returns formatted price based on the client's language
     * @param $currency
     * @param $price
     * @return mixed|string
     */
    private function formatPrice ($currency, $price) {
        $price = number_format($price, 2);

        if ($this->clientLangLocale == 'fr_FR') {
            $formattedPrice = str_replace('.', ',', (string) $price) . $currency;
        } else {
            $formattedPrice = $currency . $price;
        }

        return $formattedPrice;
    }

    /**
     * Returns the date format depending on what locale
     * @param String $locale
     * @return string
     */
    private function getDateFormatByLocate($locale = en_EN)
    {
        $dFormat = '';
        switch($locale) {
            case 'fr_FR':
                $dFormat = '%d/%m/%Y %H:%M:%S';
                break;
            case 'en_EN':
            default:
                $dFormat = '%m/%d/%Y %H:%M:%S';
                break;
        }

        return $dFormat;
    }
}