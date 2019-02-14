<?php

namespace MelisCommerceOrderInvoice\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;

class MelisCommerceOrderInvoiceController extends AbstractActionController
{
    public function testAction()
    {
        $couponSvc = $this->getServiceLocator()->get('MelisComCouponService');
        $orderCoupons = $couponSvc->getCouponList(3);

        $tmp = array();
        foreach($orderCoupons as $coupon)
        {
            if($coupon->getCoupon()->coup_product_assign)
            {
                $coupon->getCoupon()->discountedBasket = $couponSvc->getCouponDiscountedBasketItems($coupon->getCoupon()->coup_id, 3);
            }

            $tmp[] = $coupon;
        }



        print_r($tmp);
        exit;


        $orderInvoiceService = $this->getServiceLocator()->get('MelisCommerceOrderInvoiceService');
        $ordersService = $this->getServiceLocator()->get('MelisComOrderService');
        $order = $ordersService->getOrderById(3);

        print_r($order);
        exit;

        $pdf = $orderInvoiceService->testService($order, null);

        $invoice1 = $orderInvoiceService->getOrderInvoiceList(15, null, null, 'DESC')[0];

        $invoice1pdf = $invoice1['ordin_invoice_pdf'];

        $pdf = preg_split("/((\r?\n)|(\r\n?))/", $pdf);
        $invoice1pdf = preg_split("/((\r?\n)|(\r\n?))/", $invoice1pdf);

        if (count($pdf) == count($invoice1pdf)) {
            foreach($pdf as $key => $value) {
                if ($value !== $invoice1pdf[$key]) {
                    print('<br>');
                    print($key . " - di pareha");
                    print('<br>');
                    var_dump($value);
                    print('<br>');
                    var_dump($invoice1pdf[$key]);
                    print('<br>');
                    print('===================================================');
                } else {

                }
            }
        } else {
            print_r("di pareha ang count");
            exit;
        }
    }

    public function getOrderInvoiceAction()
    {
        $orderId = $this->params()->fromPost('orderId', null);

        if (!is_null($orderId)) {
            $melisComAuthSrv = $this->getServiceLocator()->get('MelisComAuthenticationService');
            $orderInvoiceTable = $this->getServiceLocator()->get('MelisCommerceOrderInvoiceTable');
            $orderInvoiceService = $this->getServiceLocator()->get('MelisCommerceOrderInvoiceService');

//            $invoice = $orderInvoiceTable->getEntryByField('ordin_order_id', $orderId)->toArray()[0];
            $invoice = $orderInvoiceService->getOrderInvoiceList(15, null, null, 'DESC')[0];

            // user id
            $clientId = $melisComAuthSrv->getClientId();
            //$personId = $melisComAuthSrv->getPersonId();

            if ($invoice['ordin_user_id'] == $clientId) {
                $response = $this->prepareResponse($invoice['ordin_invoice_pdf']);

                $view = new ViewModel();
                $view->setTerminal(true);
                $view->setTemplate('export-invoice');
                $view->content = $response->getContent();

                return $view;
            } else {
                return new JsonModel([
                   'error' => 'You don\'t have permission to get this invoice'
                ]);
            }
        } else {
            return new JsonModel([
                'error' => 'No orderId'
            ]);
        }
    }

    public function checkForInvoiceAction()
    {
        $hasInvoice = false;
        $orderInvoiceTable = $this->getServiceLocator()->get('MelisCommerceOrderInvoiceTable');

        $orderId = $this->params()->fromPost('orderId');

        // *NOTE* change this one to use the service
        $invoice = $orderInvoiceTable->getEntryByField('ordin_order_id', $orderId)->toArray();

        if (!empty($invoice)) {
            $hasInvoice = true;
        }

        return new JsonModel([
            'hasInvoice' => $hasInvoice
        ]);
    }

    public function exportInvoiceAction()
    {
        $invoiceId = $this->params()->fromQuery('invoiceId');
        $orderInvoiceService = $this->getServiceLocator()->get('MelisCommerceOrderInvoiceService');

        $pdf = $orderInvoiceService->getOrderInvoice($invoiceId);

        $response = $this->prepareResponse($pdf);

        $view = new ViewModel();
        $view->setTerminal(true);
        $view->setTemplate('export-invoice');
        $view->content = $response->getContent();

        return $view;
    }

    public function exportOrderInvoiceAction()
    {
        $orderInvoiceTable = $this->getServiceLocator()->get('MelisCommerceOrderInvoiceTable');

        $orderId = $this->params()->fromQuery('orderId');

        // *NOTE* change this one to use the service
        $invoice = $orderInvoiceTable->getEntryByField('ordin_order_id', $orderId)->toArray();
        $invoice = array_pop($invoice);

        $response = $this->prepareResponse($invoice['ordin_invoice_pdf']);

        $view = new ViewModel();
        $view->setTerminal(true);
        $view->setTemplate('export-invoice');
        $view->content = $response->getContent();

        return $view;
    }

    public function generateOrderInvoiceAction()
    {
        $orderInvoiceService = $this->getServiceLocator()->get('MelisCommerceOrderInvoiceService');
        $orderId = $this->params()->fromPost('orderId', '');

        $invoiceId = $orderInvoiceService->generateOrderInvoice($orderId, null);

        return new JsonModel([
            'id' => $invoiceId
        ]);
    }

    public function renderOrdersContentTabsContentOrderInvoiceListAction()
    {
        $columns = $this->getTool('meliscommerce', 'meliscommerce_order_invoice_list')->getColumns();
        $columns['actions'] = ['text' => 'Actions', 'width' => '0%'];

        $view = new ViewModel();

        $melisKey = $this->params()->fromRoute('melisKey', '');
        $orderId = (int) $this->params()->fromQuery('orderId', '');

        $tableConfig = $this->getTool('meliscommerce', 'meliscommerce_order_invoice_list')
            ->getDataTableConfiguration(
                '#'.$orderId.'_tableOrderInvoiceList', null, null, ['order' => '[[ 0, "desc" ]]']
            );

        $view->melisKey = $melisKey;
        $view->orderId = $orderId;
        $view->tableColumns = $columns;
        $view->getToolDataTableConfig = $tableConfig;

        return $view;
    }

    public function getOrderInvoiceListAction()
    {
        $draw = 0;
        $tableData = [];

        $orderInvoiceTable = $this->getServiceLocator()->get('MelisCommerceOrderInvoiceTable');
        $orderInvoiceService = $this->getServiceLocator()->get('MelisCommerceOrderInvoiceService');

        if ($this->getRequest()->isPost()) {
            $draw = (int) $this->getRequest()->getPost('draw');
            $start = (int) $this->getRequest()->getPost('start');
            $limit =  (int) $this->getRequest()->getPost('length');
            $postValues = $this->getRequest()->getPost();
            $orderId = $this->getRequest()->getPost('orderId');

            $allOrderInvoiceList = $orderInvoiceService->getOrderInvoiceList($orderId, null, null, null);
            $orderInvoiceList = $orderInvoiceService->getOrderInvoiceList($orderId, $start, $limit, 'DESC');

            foreach ($orderInvoiceList as $invoice) {
                $tableData[] = [
                    'ordin_id' => $invoice['ordin_id'],
                    'ordin_date_generated' => $invoice['ordin_date_generated'],
                    'DT_RowAttr' => [
                        'data-invoiceid' => $invoice['ordin_id']
                    ]
                ];
            }
        }

        return new JsonModel([
            'draw' => $draw,
            'recordsTotal' => count($orderInvoiceList),
            'recordsFiltered' => count($allOrderInvoiceList),
            'data' => $tableData
        ]);
    }

    private function prepareResponse($pdfContents, $fileName = 'invoice.pdf')
    {
        $response = $this->getResponse();
        $headers  = $response->getHeaders();

        $headers->addHeaderLine("Content-Type: application/pdf");
        $headers->addHeaderLine('Accept-Ranges', 'bytes');
        $headers->addHeaderLine('Content-Length', strlen($pdfContents));
        $headers->addHeaderLine('fileName', $fileName);

        $response->setContent($pdfContents);
        $response->setStatusCode(200);

        return $response;
    }

    private function getTool($module, $melistoolkey)
    {
        $melisTool = $this->getServiceLocator()->get('MelisCoreTool');
        $melisTool->setMelisToolKey($module, $melistoolkey);

        return $melisTool;
    }

    public function renderOrdersContentTabsContentLeftHeaderTitleAction()
    {
        $orderId = (int) $this->params()->fromQuery('orderId', '');

        $view = new ViewModel();
        $view->orderId = $orderId;

        return $view;
    }

    public function renderInvoiceListContentActionExportPdfAction()
    {
        return new ViewModel();
    }

    public function renderOrderListContentFilterRefreshAction()
    {
        return new ViewModel();
    }

    public function renderOrderListContentActionExportPdfAction()
    {
        return new ViewModel();
    }
}