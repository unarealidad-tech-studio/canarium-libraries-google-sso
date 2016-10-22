<?php
/**
 * @author Jethro Laviste
 */

namespace GoogleSSO;

use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;

class Module
{
    public function onBootstrap(MvcEvent $e)
    {
        $eventManager        = $e->getApplication()->getEventManager();
        $moduleRouteListener = new ModuleRouteListener();
        $moduleRouteListener->attach($eventManager);
    }

    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ),
            ),
        );
    }
	
	public function getViewHelperConfig()
    {
        return array(
			'invokables' => array(
              'GoogleSSOCreateAuthUrl' => 'GoogleSSO\View\Helper\CreateAuthUrl',
           ),
        );

    }
	
	public function getServiceConfig()
	{
		return array(
			'factories' => array(
                'GoogleSSO\Authentication\ForceLogin' => function($sm) {
                    $userEntityClass = $sm->get('zfcuser_user_service')->getOptions()->getUserEntityClass();
                    $userService = $sm->get('zfcuser_user_service');
                    $object_manager = $sm->get('Doctrine\ORM\EntityManager');

                    return new \GoogleSSO\Authentication\NewForceLogin($userEntityClass, $userService, $object_manager);
                },
				'GoogleSSO\Client' => function ($sm) {
					$config = $sm->get('config');
					$client = new \Google_Client();
					$client->setClientId($config['googlesso']['client_id']);
					$client->setClientSecret($config['googlesso']['client_secret']);
					$client->addScope($config['googlesso']['scope']);
					$client->setRedirectUri($config['googlesso']['redirect_uri']);
                    $client->setPrompt('select_account');
					return $client;
				},
				'GoogleSSO\Plus' => function ($sm) {
					$plus = new \Google_Service_Oauth2($sm->get('GoogleSSO\Client'));
					return $plus;
				},
//                'GoogleSSO\People' => function ($sm) {
//                    $plus = new \Google_Service_People($sm->get('GoogleSSO\Client'));
//                    return $plus;
//                },
			)
		);
	}
}
