<?php
/**
 * @author Jethro Laviste
 */

namespace GoogleSSO\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Crypt\Password\Bcrypt;
class GoogleSSOController extends AbstractActionController
{
    public function oauth2callbackAction(){
		$objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');

		$client = $this->getServiceLocator()->get('GoogleSSO\Client');
		$plus = $this->getServiceLocator()->get('GoogleSSO\Plus');

		if (isset($_GET['code'])) {
			$userEntityClass = $this->getServiceLocator()->get('zfcuser_user_service')->getOptions()->getUserEntityClass();
			$roleEntityClass = $this->getServiceLocator()->get('BjyAuthorize\Config')['role_providers']['BjyAuthorize\Provider\Role\ObjectRepositoryProvider']['role_entity_class'];
			$auth = $this->getServiceLocator()->get('zfcuser_auth_service');
			$accessToken = $client->authenticate(trim($_GET['code']));
			$client->setAccessToken($accessToken);

			$userinfo = $plus->userinfo;
			$userExist = $objectManager->getRepository($userEntityClass)->findOneBy(array('email' => $userinfo->get()->email));
			if (!$userExist) {
				$data['email'] = $userinfo->get()->email;
				$data['password'] = $this->generateCode(6);
				$data['passwordVerify'] = $data['password'];
				$data['company'] = '';
				$user = $this->getServiceLocator()->get('zfcuser_user_service')->register($data);
			} else {
				$user = $userExist;
			}

			$user->setLastLogin(new \DateTime());
			$objectManager->flush();
			
			$login = new \GoogleSSO\Authentication\ForceLogin($user);
			$this->zfcUserAuthentication()->getAuthService()->authenticate( $login );

			$redirectTo = $this->getServiceLocator()->get('zfcuser_user_service')->getOptions()->getLoginRedirectRoute();

			return $this->redirect()->toRoute($redirectTo);
		}

		return $this->response;
    }

	public function generateCode($length = 10) {
		$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$charactersLength = strlen($characters);
		$randomString = '';
		for ($i = 0; $i < $length; $i++) {
			$randomString .= $characters[rand(0, $charactersLength - 1)];
		}
		return $randomString;
	}
}
