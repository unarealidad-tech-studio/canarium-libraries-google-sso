<?php

namespace GoogleSSO\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Crypt\Password\Bcrypt;

use GoogleSSO\Entity\AssociatedGmailAccount;

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

            $em = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');

            $current_user = $this->zfcUserAuthentication()->getIdentity();

            if (empty($config['use_connected_accounts']) || !$current_user) {
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

                if (!empty($result)){
                    $current_user = $em->find('\CanariumCore\Entity\User', $result->getIdentity());
                }
            }

            if (!empty($config['use_connected_accounts']) && $current_user) {
                $current_account = $em->getRepository('\GoogleSSO\Entity\AssociatedGmailAccount')->findOneBy(array(
                    'related_email_address' => $userinfo->get()->email
                ));

                if (empty($current_account)) {
                    $current_account = new AssociatedGmailAccount();
                    $current_account->setDateAdded(new \DateTime());
                    $current_account->setUser($current_user);
                    $current_account->setIsMain($current_user->getEmail() == $userinfo->get()->email ? 1 : 0);
                    $em->persist($current_account);
                }

                if (is_string($accessToken)) {
                    $accessToken = json_decode($accessToken, true);
                }

                $current_account->setRelatedEmailAddress($userinfo->get()->email);
                $current_account->setRelatedFirstName($userinfo->get()->givenName);
                $current_account->setRelatedLastName($userinfo->get()->familyName);
                $current_account->setDateUpdated(new \DateTime());
                $current_account->setGoogleAuthToken($accessToken['access_token']);

                $time = new \DateTime();
                $time->setTimestamp($accessToken['created'] + $accessToken['expires_in']);
                $current_account->setGoogleAuthTokenExpiration($time);

                $em->flush();
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
