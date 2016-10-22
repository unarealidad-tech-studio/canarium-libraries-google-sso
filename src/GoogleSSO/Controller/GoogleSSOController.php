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
		$client = $this->getServiceLocator()->get('GoogleSSO\Client');
		$plus = $this->getServiceLocator()->get('GoogleSSO\Plus');
//		$people = $this->getServiceLocator()->get('GoogleSSO\People');

        $config = $this->getServiceLocator()->get('Config');
        $config = (!empty($config['googlesso']) ? $config['googlesso'] : array());

        if (empty($config['auth_class_service'])) {
            $config['auth_class_service'] = 'GoogleSSO\Authentication\ForceLogin';
        }

		if (isset($_GET['code'])) {
//			$userEntityClass = $this->getServiceLocator()->get('zfcuser_user_service')->getOptions()->getUserEntityClass();
//			$roleEntityClass = $this->getServiceLocator()->get('BjyAuthorize\Config')['role_providers']['BjyAuthorize\Provider\Role\ObjectRepositoryProvider']['role_entity_class'];
//			$auth = $this->getServiceLocator()->get('zfcuser_auth_service');
			$accessToken = $client->authenticate(trim($_GET['code']));
			$client->setAccessToken($accessToken);

			$userinfo = $plus->userinfo;

            $auth_service = $this->getServiceLocator()->get($config['auth_class_service']);
            $auth_service->setUserInfo(array(
                'email' => $userinfo->get()->email,
                'first_name' => $userinfo->get()->givenName,
                'last_name' => $userinfo->get()->familyName,
                'display_name' => $userinfo->get()->name
            ));
            $result = $this->zfcUserAuthentication()->getAuthService()->authenticate($auth_service);

            if (!$result->isValid()) {
                $flash_messenger = $this->flashMessenger()->setNamespace('zfcuser-login-form');
                foreach ($result->getMessages() as $message) {
                    $flash_messenger->addMessage($message);
                }

                return $this->redirect()->toUrl($this->url()->fromRoute('zfcuser/login'));
            }

            // var_dump($people->people_connections->listPeopleConnections('people/me'));exit;

            try {
                // check if cookie needs to be set, only when prior auth has been successful
                $rememberMeService = $this->getServiceLocator()->get('goaliorememberme_rememberme_service');
                $rememberMeService->createSerie($result->getIdentity());

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

            $session = new \Zend\Session\Container('canarium');

            if (!empty($session->redirect_url)) {
                $redirectTo = $session->redirect_url;
                unset($session->redirect_url);
                return $this->redirect()->toUrl($redirectTo);
            } else {
                $redirectTo = $this->getServiceLocator()->get('zfcuser_user_service')->getOptions()->getLoginRedirectRoute();
                return $this->redirect()->toRoute($redirectTo);
            }
		}

		return $this->response;
    }
}
