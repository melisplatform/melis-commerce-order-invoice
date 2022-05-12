<?php

namespace MelisCommerceOrderInvoice\Controller;

use MelisCore\Controller\MelisAbstractActionController;
use Laminas\Authentication\AuthenticationService;
use Laminas\View\Model\ViewModel;
use Laminas\View\Model\JsonModel;
use Laminas\Session\Container;

class MelisCommerceOrderInvoiceController extends MelisAbstractActionController
{
    /**
     * Returns the pdf contents
     * @return JsonModel|ViewModel
     */
    public function getOrderInvoiceAction()
    {
        $invoiceId = $this->params()->fromPost('invoiceId', null);

        try {
            // FOR BACKOFFICE
            $melisCoreAuthSrv = $this->getServiceManager()->get('MelisCoreAuth');

            if ($melisCoreAuthSrv->hasIdentity()) {
                $orderInvoiceService = $this->getServiceManager()->get('MelisCommerceOrderInvoiceService');
                $invoice = $orderInvoiceService->getInvoice($invoiceId);
                /**
                 * PREPARE VARIABLES NEEDED TO FORM THE FILE NAME
                 * [date]-[orderId]-[invoiceId][custom].pdf
                 */
                $filename = $orderInvoiceService->generateFileName(
                    $invoice['ordin_date_generated'],
                    $invoice['ordin_order_id'],
                    $invoiceId
                );

                $response = $this->prepareResponse($invoice['ordin_invoice_pdf'], $filename);

                $view = new ViewModel();
                $view->setTerminal(true);
                $view->setTemplate('export-invoice');
                $view->content = $response->getContent();

                return $view;
            }
        } catch (\Exception $e) {
            // FOR FRONT
            $melisComAuthSrv = $this->getServiceManager()->get('MelisComAuthenticationService');

            if ($melisComAuthSrv->hasIdentity()) {
                if (!is_null($invoiceId) && $invoiceId != 0) {
                    $orderInvoiceService = $this->getServiceManager()->get('MelisCommerceOrderInvoiceService');
                    $invoice = $orderInvoiceService->getInvoice($invoiceId);

                    if (!empty($invoice)) {
                        $clientId = $melisComAuthSrv->getClientId();
                        //$personId = $melisComAuthSrv->getPersonId();

                        if ($invoice['ordin_user_id'] == $clientId) {
                            /**
                             * PREPARE VARIABLES NEEDED TO FORM THE FILE NAME
                             * [date]-[orderId]-[invoiceId][custom].pdf
                             */
                            $filename = $orderInvoiceService->generateFileName(
                                $invoice['ordin_date_generated'],
                                $invoice['ordin_order_id'],
                                $invoiceId
                            );

                            $response = $this->prepareResponse($invoice['ordin_invoice_pdf'], $filename);

                            $view = new ViewModel();
                            $view->setTerminal(true);
                            $view->setTemplate('export-invoice');
                            $view->content = $response->getContent();

                            return $view;
                        } else {
                            return new JsonModel([
                                'error' => 'You don\'t own this invoice'
                            ]);
                        }
                    } else {
                        return new JsonModel([
                            'error' => 'Invoiceid does not exist'
                        ]);
                    }
                } else {
                    return new JsonModel([
                        'error' => 'Invalid orderId'
                    ]);
                }
            } else {
                return new JsonModel([
                    'error' => 'You have to be authenticated to the site to access this'
                ]);
            }
        }
    }

    /**
     * Returns latest invoice id for an order
     * @return JsonModel
     */
    public function getOrderLatestInvoiceIdAction () {
        $orderId = $this->params()->fromPost('orderId', null);
        $latestInvoiceId = 0;

        if (!is_null($orderId) && $orderId != 0) {
            $orderInvoiceService = $this->getServiceManager()->get('MelisCommerceOrderInvoiceService');

            $latestInvoiceId = $orderInvoiceService->getOrderLatestInvoiceId($orderId);
        }

        return new JsonModel([
            'latestInvoiceId' => $latestInvoiceId
        ]); 
    }

    /**
     * Handles the generation of invoices
     * @return JsonModel
     */
    public function generateOrderInvoiceAction()
    {
        $orderInvoiceService = $this->getServiceManager()->get('MelisCommerceOrderInvoiceService');
        $orderId = $this->params()->fromPost('orderId', '');

        $invoiceId = $orderInvoiceService->generateOrderInvoice($orderId, 'orderinvoicetemplate/default');

        return new JsonModel([
            'id' => $invoiceId
        ]);
    }

    /**
     * Renders the invoice list
     * @return ViewModel
     */
    public function renderOrdersContentTabsContentOrderInvoiceListAction()
    {
        $columns = $this->getTool('meliscommerce', 'meliscommerce_order_invoice_list')->getColumns();
        $columns['actions'] = ['text' => 'Action', 'width' => '0%'];

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

    /**
     * Returns the data for the invoice list
     * @return JsonModel
     */
    public function getOrderInvoiceListAction()
    {
        $draw = 0;
        $tableData = [];
        $melisTool = $this->getServiceManager()->get('MelisCoreTool');
        $melisTool->setMelisToolKey('meliscommerce', 'meliscommerce_order_invoice_list');

        $orderInvoiceService = $this->getServiceManager()->get('MelisCommerceOrderInvoiceService');

        if ($this->getRequest()->isPost()) {
            $draw = (int) $this->getRequest()->getPost('draw');
            $start = (int) $this->getRequest()->getPost('start');
            $limit =  (int) $this->getRequest()->getPost('length');
            $orderId = $this->getRequest()->getPost('orderId');

            $selCol = $this->getRequest()->getPost('order');
            $colId = array_keys($melisTool->getColumns());
            $selCol = $colId[$selCol[0]['column']];
            $sortOrder = $this->getRequest()->getPost('order');
            $sortOrder = $sortOrder[0]['dir'];
            $allOrderInvoiceList = $orderInvoiceService->getOrderInvoiceList($orderId, null, null, null, null);
            $orderInvoiceList = $orderInvoiceService->getOrderInvoiceList($orderId, $start, $limit, $sortOrder, $selCol);

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

    /**
     * @param $pdfContents
     * @param string $fileName
     * @return \Laminas\Stdlib\ResponseInterface
     */
    private function prepareResponse($pdfContents, $fileName)
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

    /**
     * Returns the melis tool
     * @param $module
     * @param $melistoolkey
     * @return array|object
     */
    private function getTool($module, $melistoolkey)
    {
        $melisTool = $this->getServiceManager()->get('MelisCoreTool');
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