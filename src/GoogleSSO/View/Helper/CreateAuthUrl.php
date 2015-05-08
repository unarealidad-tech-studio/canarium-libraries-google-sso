<?php
namespace GoogleSSO\View\Helper;
use Zend\ServiceManager\ServiceLocatorAwareInterface;  
use Zend\View\Helper\AbstractHelper;
class CreateAuthUrl extends AbstractHelper
{
    public function __invoke(){
        $sm = $this->getView()->getHelperPluginManager()->getServiceLocator();
		$client = $sm->get('GoogleSSO\Client');
		return $client->createAuthUrl();
    }
}