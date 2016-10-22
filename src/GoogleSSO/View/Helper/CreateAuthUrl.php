<?php
namespace GoogleSSO\View\Helper;
use Zend\ServiceManager\ServiceLocatorAwareInterface;  
use Zend\View\Helper\AbstractHelper;
class CreateAuthUrl extends AbstractHelper
{
    public function __invoke(){
        $sm = $this->getView()->getHelperPluginManager()->getServiceLocator();
		$client = $sm->get('GoogleSSO\Client');
		$auth_url =  $client->createAuthUrl();

        $auth_url = str_replace('&approval_prompt=auto', '', $auth_url);

        var_dump($auth_url);exit;

        return $auth_url;
    }
}
