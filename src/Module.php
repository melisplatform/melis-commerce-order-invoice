<?php

/**
 * Melis Technology (http://www.melistechnology.com)
 *
 * @copyright Copyright (c) 2016 Melis Technology (http://www.melistechnology.com)
 *
 */

namespace MelisCommerceOrderInvoice;

use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;
use Zend\Stdlib\ArrayUtils;
use Zend\Session\Container;

use MelisCommerceOrderInvoice\Listener\MelisCommerceOrderInvoiceGenerateInvoiceListener;
use MelisCommerceOrderInvoice\Listener\MelisCommerceOrderDetailsInvoiceDataListener;

class Module
{
    public function onBootstrap(MvcEvent $e)
    {
        $eventManager        = $e->getApplication()->getEventManager();
        $moduleRouteListener = new ModuleRouteListener();
        $moduleRouteListener->attach($eventManager);

        $this->createTranslations($e);

//        $sm = $e->getApplication()->getServiceManager();
//        $routeMatch = $sm->get('router')->match($sm->get('request'));
//        if (!empty($routeMatch))
//        {
//            $routeName = $routeMatch->getMatchedRouteName();
//            $module = explode('/', $routeName);
//
//            if (!empty($module[0]))
//            {
//                if ($module[0] == 'melis-backoffice')
//                {
//                    $eventManager->getSharedManager()->attach(__NAMESPACE__,
//                        MvcEvent::EVENT_DISPATCH, function($e) {
//                            $e->getTarget()->layout('layout/layoutMelisPageHistoric');
//                        });
//
                    $eventManager->attach(new MelisCommerceOrderInvoiceGenerateInvoiceListener());
                    $eventManager->attach(new MelisCommerceOrderDetailsInvoiceDataListener());
//                    $eventManager->attach(new MelisPageHistoricPageEventListener());
//                }
//            }
//        }
    }

    public function getConfig()
    {
        $config = [];
        $configFiles = [
            include __DIR__ . '/../config/module.config.php',
            include __DIR__ . '/../config/app.tools.php',
            include __DIR__ . '/../config/app.interface.php',
        ];

        foreach ($configFiles as $file) {
            $config = ArrayUtils::merge($config, $file);
        }

        return $config;
    }

    public function getAutoloaderConfig()
    {
        return [
            'Zend\Loader\StandardAutoloader' => [
                'namespaces' => [
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ],
            ],
        ];
    }

    public function createTranslations($e)
    {
        $sm = $e->getApplication()->getServiceManager();
        $translator = $sm->get('translator');

        $container = new Container('meliscore');
        $locale = $container['melis-lang-locale'] ? $container['melis-lang-locale'] : 'fr_FR';

        $translator->addTranslationFile('phparray', __DIR__ . '/../language/' . $locale . '.interface.php');
    }
}
