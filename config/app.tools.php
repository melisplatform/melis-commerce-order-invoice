<?php
    return [
        'plugins' => [
            'meliscommerce' => [
                'tools' => [
                    'meliscommerce_order_list' => [
                        'table' => [
                            'actionButtons' => [
                                'export-pdf' => [
                                    'module' => 'MelisCommerceOrderInvoice',
                                    'controller' => 'MelisCommerceOrderInvoice',
                                    'action' => 'render-order-list-content-action-export-pdf'
                                ],
                            ],
                        ],
                    ],
                    'meliscommerce_order_invoice_list' => [
                        'table' => [
                            'target' => '#tableOrderInvoiceList',
                            'ajaxUrl' => '/melis/MelisCommerceOrderInvoice/MelisCommerceOrderInvoice/getOrderInvoiceList',
                            'dataFunction' => 'initOrderInvoiceTable',
                            'ajaxCallback' => '',
                            'filters' => [
                                'left' => [
                                    'order-basket-list-table-filter-limit' => array(
                                        'module' => 'MelisCommerce',
                                        'controller' => 'MelisComOrder',
                                        'action' => 'render-order-content-filter-limit'
                                    ),
                                ],
                                'center' => [

                                ],
                                'right' => [
                                    'order-status-table-filter-refresh' => array(
                                        'module' => 'MelisCommerceOrderInvoice',
                                        'controller' => 'MelisCommerceOrderInvoice',
                                        'action' => 'render-order-list-content-filter-refresh'
                                    ),
                                ],
                            ],
                            'columns' => [
                                'ordin_id' => [
                                    'text' => 'ID',
                                ],
                                'ordin_date_generated' => [
                                    'text' => 'DATE',
                                ],
                            ],
                            'searchables' => [

                            ],
                            'actionButtons' => [
                                'export-pdf' => array(
                                    'module' => 'MelisCommerceOrderInvoice',
                                    'controller' => 'MelisCommerceOrderInvoice',
                                    'action' => 'render-invoice-list-content-action-export-pdf'
                                ),
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ];