<?php
    return [
        'plugins' => [
            'meliscommerceorderinvoice' => [
                'ressources' => [
                    'js' => [
                        '/MelisCommerceOrderInvoice/js/meliscommerceorderinvoice.js'
                    ],
                    'css' => [

                    ],
                    'build' => [
                        //'disable_bundle' => true,
                        // lists of assets that will be loaded in the layout
                        'css' => [
                            //'/MelisCommerceOrderInvoice/build/css/bundle.css',

                        ],
                        'js' => [
                            '/MelisCommerceOrderInvoice/build/js/bundle.js',
                        ]
                    ],
                ],
            ],
            'meliscommerce' => [
                'interface' => [
                    'meliscommerce_orders' => [
                        'interface' => [
                            'meliscommerce_orders_page' => [
                                'interface' => [
                                    'meliscommerce_orders_content' => [
                                        'interface' => [
                                            'meliscommerce_orders_content_tabs' => [
                                                'interface' => [
                                                    'meliscommerce_orders_content_tab_messages' => [
                                                        'conf' => [
                                                            'id' => 'id_meliscommerce_orders_content_tab_order_invoice',
                                                            'melisKey' => 'meliscommerce_orders_content_tab_order_invoice',
                                                            'name' => 'Invoice',
                                                            'href' => 'id_meliscommerce_orders_content_tabs_content_order_invoice',
                                                            'icon' => 'glyphicons file',
                                                            'active' => '',
                                                        ],
                                                        'forward' => [
                                                            'module' => 'MelisCommerce',
                                                            'controller' => 'MelisComOrder',
                                                            'action' => 'render-orders-content-tab',
                                                        ],
                                                    ],
                                                ],
                                            ],
                                            'meliscommerce_orders_content_tabs_content' => [
                                                'interface' => [
                                                    'meliscommerce_orders_content_tabs_content_order_invoice' => [
                                                        'conf' => [
                                                            'id' => 'id_meliscommerce_orders_content_tabs_content_order_invoice',
                                                            'melisKey' => 'meliscommerce_orders_content_tabs_content_order_invoice',
                                                            'name' => 'tr_meliscommerce_orders_content_tabs_content_order_invoice',
                                                            'active' => '',
                                                        ],
                                                        'forward' => [
                                                            'module' => 'MelisCommerce',
                                                            'controller' => 'MelisComOrder',
                                                            'action' => 'render-orders-content-tabs-content-container',
                                                        ],
                                                        'interface' => [
                                                            'meliscommerce_orders_content_tabs_content_order_invoice_header' => [
                                                                'conf' => [
                                                                    'id' => 'id_meliscommerce_orders_content_tabs_content_order_invoice_header',
                                                                    'melisKey' => 'meliscommerce_orders_content_tabs_content_order_invoice_header',
                                                                    'name' => 'tr_meliscommerce_orders_content_tabs_content_order_invoice_header',
                                                                ],
                                                                'forward' => [
                                                                    'module' => 'MelisCommerce',
                                                                    'controller' => 'MelisComOrder',
                                                                    'action' => 'render-orders-content-tabs-content-header',
                                                                ],
                                                                'interface' => [
                                                                    'meliscommerce_orders_content_tabs_content_order_invoice_left_header' => [
                                                                        'conf' => [
                                                                            'id' => 'id_meliscommerce_orders_content_tabs_content_order_invoice_left_header',
                                                                            'melisKey' => 'meliscommerce_orders_content_tabs_content_order_invoice_left_header',
                                                                            'name' => 'Regenerate Invoice',
                                                                        ],
                                                                        'forward' => [
                                                                            'module' => 'MelisCommerce',
                                                                            'controller' => 'MelisComOrder',
                                                                            'action' => 'render-orders-content-tabs-content-left-header',
                                                                        ],
                                                                        'interface' => [
                                                                            'meliscommerce_orders_content_tabs_content_order_invoice_left_header_title' => [
                                                                                'conf' => [
                                                                                    'id' => 'id_meliscommerce_orders_content_tabs_content_order_invoice_left_header_title',
                                                                                    'melisKey' => 'meliscommerce_orders_content_tabs_content_order_invoice_left_header_title',
                                                                                    'name' => 'Order Invoice',
                                                                                ],
                                                                                'forward' => [
                                                                                    'module' => 'MelisCommerceOrderInvoice',
                                                                                    'controller' => 'MelisCommerceOrderInvoice',
                                                                                    'action' => 'render-orders-content-tabs-content-left-header-title',
                                                                                ],
                                                                            ],
                                                                        ],
                                                                    ],
//                                                                    'meliscommerce_orders_content_tabs_content_baskets_right_header' => [
//                                                                        'conf' => [
//                                                                            'id' => 'id_meliscommerce_orders_content_tabs_content_baskets_right_header',
//                                                                            'melisKey' => 'meliscommerce_orders_content_tabs_content_baskets_right_header',
//                                                                            'name' => 'tr_meliscommerce_orders_content_tabs_content_baskets_right_header',
//                                                                        ],
//                                                                        'forward' => [
//                                                                            'module' => 'MelisCommerce',
//                                                                            'controller' => 'MelisComOrder',
//                                                                            'action' => 'render-orders-content-tabs-content-right-header',
//                                                                        ],
//                                                                        'interface' => [
//
//                                                                        ],
//                                                                    ],
                                                                ],
                                                            ],
                                                            'meliscommerce_orders_content_tabs_content_order_invoice_details' => [
                                                                'conf' => [
                                                                    'id' => 'id_meliscommerce_orders_content_tabs_content_order_invoice_details',
                                                                    'melisKey' => 'meliscommerce_orders_content_tabs_content_order_invoice_details',
                                                                    'name' => 'Order Invoice',
                                                                ],
                                                                'forward' => [
                                                                    'module' => 'MelisCommerce',
                                                                    'controller' => 'MelisComOrder',
                                                                    'action' => 'render-orders-content-tabs-content-details',
                                                                ],
                                                                'interface' => [
                                                                    'meliscommerce_orders_content_tabs_content_order_invoice_list' => [
                                                                        'conf' => [
                                                                            'id' => 'id_meliscommerce_orders_content_tabs_content_order_invoice_details_list',
                                                                            'melisKey' => 'meliscommerce_orders_content_tabs_content_order_invoice_details_list',
                                                                            'name' => 'Order Invoice',
                                                                        ],
                                                                        'forward' => [
                                                                            'module' => 'MelisCommerceOrderInvoice',
                                                                            'controller' => 'MelisCommerceOrderInvoice',
                                                                            'action' => 'render-orders-content-tabs-content-order-invoice-list',
                                                                        ],
                                                                    ],
                                                                ],
                                                            ],
                                                        ],
                                                    ],
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ];