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
//		$people = $this->getServiceLocator()->get('GoogleSSO\People');

		if (isset($_GET['code'])) {
			$userEntityClass = $this->getServiceLocator()->get('zfcuser_user_service')->getOptions()->getUserEntityClass();
			$roleEntityClass = $this->getServiceLocator()->get('BjyAuthorize\Config')['role_providers']['BjyAuthorize\Provider\Role\ObjectRepositoryProvider']['role_entity_class'];
			$auth = $this->getServiceLocator()->get('zfcuser_auth_service');
			$accessToken = $client->authenticate(trim($_GET['code']));
			$client->setAccessToken($accessToken);

			$userinfo = $plus->userinfo;

//            var_dump($people->people_connections->listPeopleConnections('people/me'));exit;
			$userExist = $objectManager->getRepository($userEntityClass)->findOneBy(array('email' => $userinfo->get()->email));
			if (!$userExist) {
                $data['displayName'] = $userinfo->get()->name;
				$data['email'] = $userinfo->get()->email;
				$data['password'] = $this->generateCode(6);
				$data['passwordVerify'] = $data['password'];
				$user = $this->getServiceLocator()->get('zfcuser_user_service')->register($data);
			} else {
				$user = $userExist;
			}

			$user->setLastLogin(new \DateTime());
			$objectManager->flush();
			
			$login = new \GoogleSSO\Authentication\ForceLogin($user);
			$this->zfcUserAuthentication()->getAuthService()->authenticate( $login );

            try {
                // check if cookie needs to be set, only when prior auth has been successful
                $rememberMeService = $this->getServiceLocator()->get('goaliorememberme_rememberme_service');
                $rememberMeService->createSerie($user->getId());

                /**
                 *  If the user has first logged in with a cookie,
                 *  but afterwords login with identity/credential
                 *  we remove the "cookieLogin" session.
                 */
                $session = new \Zend\Session\Container('zfcuser');
                $session->offsetSet("cookieLogin", false);
            } catch (\Zend\ServiceManager\Exception\ServiceNotFoundException $e) {
                //ignore for backward compatibility
            }

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
