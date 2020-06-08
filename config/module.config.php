<?php

/**
 * Melis Technology (http://www.melistechnology.com)
 *
 * @copyright Copyright (c) 2015 Melis Technology (http://www.melistechnology.com)
 *
 */

return [
    'router' => [
        'routes' => [
            'melis-backoffice' => [
                'type'    => 'Segment',
                'options' => [
                    'route'    => '/melis[/]',
                ],
                'child_routes' => [
                    'application-MelisCommerceOrderInvoice' => [
                        'type'    => 'Literal',
                        'options' => [
                            'route'    => 'MelisCommerceOrderInvoice',
                            'defaults' => [
                                '__NAMESPACE__' => 'MelisCommerceOrderInvoice\Controller',
                                'controller'    => 'MelisCommerceOrderInvoice',
                                'action'        => '',
                            ],
                        ],
                        'may_terminate' => true,
                        'child_routes' => [
                            'default' => [
                                'type'    => 'Segment',
                                'options' => [
                                    'route'    => '/[:controller[/:action]]',
                                    'constraints' => [
                                        'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                        'action'     => '[a-zA-Z][a-zA-Z0-9_-]*',
                                    ],
                                    'defaults' => [
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'MelisCommerceOrderInvoiceCheckInvoice' => [
                'type' => 'Literal',
                'options' => [
                    'route' => '/CommerceOrderInvoice/getOrderLatestInvoiceId',
                    'defaults' => [
                        '__NAMESPACE__' => 'MelisCommerceOrderInvoice\Controller',
                        'controller' => 'MelisCommerceOrderInvoice',
                        'action' => 'getOrderLatestInvoiceId'
                    ],
                ],
            ],
            'MelisCommerceOrderInvoiceDownloadInvoice' => [
                'type' => 'Literal',
                'options' => [
                    'route' => '/CommerceOrderInvoice/getInvoice',
                    'defaults' => [
                        '__NAMESPACE__' => 'MelisCommerceOrderInvoice\Controller',
                        'controller' => 'MelisCommerceOrderInvoice',
                        'action' => 'getOrderInvoice'
                    ],
                ],
            ],
        ],
    ],
    'service_manager' => [
        'aliases' => [
            // Table
            'MelisCommerceOrderInvoiceTable'    => \MelisCommerceOrderInvoice\Model\Tables\MelisCommerceOrderInvoiceTable::class,
            // Service
            'MelisCommerceOrderInvoiceService'  => \MelisCommerceOrderInvoice\Service\MelisCommerceOrderInvoiceService::class
        ],
    ],
    'controllers' => [
        'invokables' => [
            'MelisCommerceOrderInvoice\Controller\MelisCommerceOrderInvoice' => \MelisCommerceOrderInvoice\Controller\MelisCommerceOrderInvoiceController::class,
        ],
    ],
    'view_manager' => [
        'template_map' => [
            'orderinvoicetemplate/default'  => __DIR__ . '/../view/melis-commerce-order-invoice/melis-commerce-order-invoice/default-order-invoice-template.phtml',
            'export-invoice'                => __DIR__ . '/../view/melis-commerce-order-invoice/melis-commerce-order-invoice/export-order-invoice.phtml'
        ],
        'template_path_stack' => [
            __DIR__ . '/../view',
        ],
    ],
];
