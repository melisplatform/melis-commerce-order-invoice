<?php

namespace MelisCommerceOrderInvoice\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\Authentication\AuthenticationService;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;
use Zend\Session\Container;

class MelisCommerceOrderInvoiceController extends AbstractActionController
{
    public function testAction() {
        $auth = new AuthenticationService();

        var_dump(! $auth->getStorage()->isEmpty());
        exit;
    }
    /**
     * Returns the pdf contents
     * @return JsonModel|ViewModel
     */
    public function getOrderInvoiceAction()
    {
        $invoiceId = $this->params()->fromPost('invoiceId', null);

        try {
            // FOR BACKOFFICE
            $melisCoreAuthSrv = $this->getServiceLocator()->get('MelisCoreAuth');

            if ($melisCoreAuthSrv->hasIdentity()) {
                $orderInvoiceService = $this->getServiceLocator()->get('MelisCommerceOrderInvoiceService');
                $invoice = $orderInvoiceService->getInvoice($invoiceId);

                $response = $this->prepareResponse($invoice['ordin_invoice_pdf']);

                $view = new ViewModel();
                $view->setTerminal(true);
                $view->setTemplate('export-invoice');
                $view->content = $response->getContent();

                return $view;
            }
        } catch (\Exception $e) {
            // FOR FRONT
            $melisComAuthSrv = $this->getServiceLocator()->get('MelisComAuthenticationService');

            if ($melisComAuthSrv->hasIdentity()) {
                if (!is_null($invoiceId) && $invoiceId != 0) {
                    $orderInvoiceService = $this->getServiceLocator()->get('MelisCommerceOrderInvoiceService');
                    $invoice = $orderInvoiceService->getInvoice($invoiceId);

                    if (!empty($invoice)) {
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
            $orderInvoiceService = $this->getServiceLocator()->get('MelisCommerceOrderInvoiceService');

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
        $orderInvoiceService = $this->getServiceLocator()->get('MelisCommerceOrderInvoiceService');
        $orderId = $this->params()->fromPost('orderId', '');

        $invoiceId = $orderInvoiceService->generateOrderInvoice($orderId, null);

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

    /**
     * Returns the data for the invoice list
     * @return JsonModel
     */
    public function getOrderInvoiceListAction()
    {
        $draw = 0;
        $tableData = [];

        $orderInvoiceService = $this->getServiceLocator()->get('MelisCommerceOrderInvoiceService');

        if ($this->getRequest()->isPost()) {
            $draw = (int) $this->getRequest()->getPost('draw');
            $start = (int) $this->getRequest()->getPost('start');
            $limit =  (int) $this->getRequest()->getPost('length');
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

    /**
     * @param $pdfContents
     * @param string $fileName
     * @return \Zend\Stdlib\ResponseInterface
     */
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

    /**
     * Returns the melis tool
     * @param $module
     * @param $melistoolkey
     * @return array|object
     */
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