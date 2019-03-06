<?php

/**
 * Melis Technology (http://www.melistechnology.com)
 *
 * @copyright Copyright (c) 2015 Melis Technology (http://www.melistechnology.com)
 *
 */

return array(
    'router' => array(
        'routes' => array(
            'melis-backoffice' => array(
                'type'    => 'Segment',
                'options' => array(
                    'route'    => '/melis[/]',
                ),
                'child_routes' => array(
                    'application-MelisCommerceOrderInvoice' => array(
                        'type'    => 'Literal',
                        'options' => array(
                            'route'    => 'MelisCommerceOrderInvoice',
                            'defaults' => array(
                                '__NAMESPACE__' => 'MelisCommerceOrderInvoice\Controller',
                                'controller'    => 'MelisCommerceOrderInvoice',
                                'action'        => '',
                            ),
                        ),
                        'may_terminate' => true,
                        'child_routes' => array(
                            'default' => array(
                                'type'    => 'Segment',
                                'options' => array(
                                    'route'    => '/[:controller[/:action]]',
                                    'constraints' => array(
                                        'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                        'action'     => '[a-zA-Z][a-zA-Z0-9_-]*',
                                    ),
                                    'defaults' => array(
                                    ),
                                ),
                            ),
                        ),
                    ),
                ),
            ),
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
        ),
    ),
    'translator' => array(
        'locale' => 'en_EN',
    ),
    'service_manager' => array(
        'invokables' => array(

        ),
        'aliases' => array(
            'MelisCommerceOrderInvoiceTable' => 'MelisCommerceOrderInvoice\Model\Tables\MelisCommerceOrderInvoiceTable'
        ),
        'factories' => array(
            // Service
            'MelisCommerceOrderInvoiceService' => 'MelisCommerceOrderInvoice\Service\Factory\MelisCommerceOrderInvoiceServiceFactory',

            // Table
            'MelisCommerceOrderInvoice\Model\Tables\MelisCommerceOrderInvoiceTable' => 'MelisCommerceOrderInvoice\Model\Tables\Factory\MelisCommerceOrderInvoiceTableFactory'
        ),
    ),
    'controllers' => array(
        'invokables' => array(
            'MelisCommerceOrderInvoice\Controller\MelisCommerceOrderInvoice' => 'MelisCommerceOrderInvoice\Controller\MelisCommerceOrderInvoiceController',
        ),
    ),
    'controller_plugins' => array(
        'invokables' => array(

        ),
    ),
    'form_elements' => array(
        'factories' => array(

        ),
    ),
    'view_manager' => array(
        'display_not_found_reason' => true,
        'display_exceptions'       => true,
        'doctype'                  => 'HTML5',
        'template_map' => array(
            'orderinvoicetemplate/default' => __DIR__ . '/../view/melis-commerce-order-invoice/melis-commerce-order-invoice/default-order-invoice-template.phtml',
            'export-invoice' => __DIR__ . '/../view/melis-commerce-order-invoice/melis-commerce-order-invoice/export-order-invoice.phtml'
        ),
        'template_path_stack' => array(
            __DIR__ . '/../view',
        ),
        'strategies' => array(
            'ViewJsonStrategy',
        ),
    ),
);
