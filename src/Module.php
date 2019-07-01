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
use MelisCommerceOrderInvoice\Listener\MelisCommerceOrderHistoryInvoiceDataListener;

class Module
{
    public function onBootstrap(MvcEvent $e)
    {
        $eventManager        = $e->getApplication()->getEventManager();
        $moduleRouteListener = new ModuleRouteListener();
        $moduleRouteListener->attach($eventManager);

        $sm = $e->getApplication()->getServiceManager();
        $routeMatch = $sm->get('router')->match($sm->get('request'));

        $this->createTranslations($e,$routeMatch);

        $eventManager->attach(new MelisCommerceOrderInvoiceGenerateInvoiceListener());
        $eventManager->attach(new MelisCommerceOrderDetailsInvoiceDataListener());
        $eventManager->attach(new MelisCommerceOrderHistoryInvoiceDataListener());
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

    public function createTranslations($e, $routeMatch)
    {
        $sm = $e->getApplication()->getServiceManager();
        $translator = $sm->get('translator');
        $param = $routeMatch->getParams();
        // Checking if the Request is from Melis-BackOffice or Front
        $renderMode = (isset($param['renderMode'])) ? $param['renderMode'] : 'melis';
        if ($renderMode == 'melis') {
            $container = new Container('meliscore');
            $locale = $container['melis-lang-locale'];
        } else {
            $container = new Container('melisplugins');
            $locale = $container['melis-plugins-lang-locale'];
        }


        if (!empty($locale)) {


            $translationType = array(
                'interface',
            );
            $translationList = array();
            if(file_exists($_SERVER['DOCUMENT_ROOT'].'/../module/MelisModuleConfig/config/translation.list.php')){
                $translationList = include 'module/MelisModuleConfig/config/translation.list.php';
            }
            foreach($translationType as $type){
                $transPath = '';
                $moduleTrans = __NAMESPACE__."/$locale.$type.php";
                if(in_array($moduleTrans, $translationList)){
                    $transPath = "module/MelisModuleConfig/languages/".$moduleTrans;
                }
                if(empty($transPath)){
                    // if translation is not found, use melis default translations
                    $defaultLocale = (file_exists(__DIR__ . "/../language/$locale.$type.php"))? $locale : "en_EN";
                    $transPath = __DIR__ . "/../language/$defaultLocale.$type.php";
                }
                $translator->addTranslationFile('phparray', $transPath);
            }
        }
        $lang = explode('_', $locale);
        $lang = $lang[0];
    }
}
