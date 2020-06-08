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
                                    'order-basket-list-table-filter-limit' => [
                                        'module' => 'MelisCommerce',
                                        'controller' => 'MelisComOrder',
                                        'action' => 'render-order-content-filter-limit'
                                    ],
                                ],
                                'center' => [

                                ],
                                'right' => [
                                    'order-status-table-filter-refresh' => [
                                        'module' => 'MelisCommerceOrderInvoice',
                                        'controller' => 'MelisCommerceOrderInvoice',
                                        'action' => 'render-order-list-content-filter-refresh'
                                    ],
                                ],
                            ],
                            'columns' => [
                                'ordin_id' => [
                                    'text' => 'tr_meliscommerceorderinvoice_table_header_id',
                                ],
                                'ordin_date_generated' => [
                                    'text' => 'tr_meliscommerceorderinvoice_table_header_date',
                                ],
                            ],
                            'searchables' => [

                            ],
                            'actionButtons' => [
                                'export-pdf' => [
                                    'module' => 'MelisCommerceOrderInvoice',
                                    'controller' => 'MelisCommerceOrderInvoice',
                                    'action' => 'render-invoice-list-content-action-export-pdf'
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ];