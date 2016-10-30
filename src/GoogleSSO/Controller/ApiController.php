<?php

namespace GoogleSSO\Controller;

use CanariumCore\Exception\InvalidTokenException;
use CanariumCore\Exception\InvalidUserException;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\JsonModel;

class ApiController extends AbstractActionController
{
    const RequestSuccess        = 1;
    const InvalidUser           = 2;
    const FailedCreatingToken   = 3;
    const InvalidRequestMethod  = 4;
    const RegistrationFailed    = 5;
    const InvalidAccessToken    = 6;
    const UnidentifiedError     = 0;


    public $responseCodes = array(
        1 => array('status' => 'ok',     'message' => 'Request successful'),
        2 => array('status' => 'error',  'message' => 'Invalid user'),
        3 => array('status' => 'error',  'message' => 'Failed creating access token'),
        4 => array('status' => 'error',  'message' => 'Invalid request method'),
        5 => array('status' => 'error',  'message' => 'Registration failed'),
        6 => array('status' => 'error',  'message' => 'Invalid access token'),
        0 => array('status' => 'error',  'message' => 'Unidentified error occurred'),
    );

    public $entityManager;

    public function indexAction()
    {
        die('index');
    }

    public function getAssociatedAccountAction()
    {
        $current_user = $this->zfcUserAuthentication()->getIdentity();

        $error = '';

        $associated_accounts = array();

        try {
            $associated_entities = $this->getEntityManager()->getRepository('\GoogleSSO\Entity\AssociatedGmailAccount')->findBy(array(
                'user' => $current_user
            ));

            foreach ($associated_entities as $entity) {
                $associated_accounts[] = array(
                    'id' => $entity->getId(),
                    'related_first_name' => $entity->getRelatedFirstName(),
                    'related_last_name' => $entity->getRelatedLastName(),
                    'related_email_address' => $entity->getRelatedEmailAddress(),
                    'date_added' => $entity->getDateAdded()->getTimestamp(),
                    'is_main' => $entity->getIsMain() ? true : false,
                );
            }


            return $this->response(self::RequestSuccess, array(
                'associated_accounts' => $associated_accounts
            ));
        } catch (\Exception $e) {
            $error = $e->getMessage();
        }

        return $this->response(self::UnidentifiedError, array(), $error);
    }

    public function removeAssociatedAccountAction()
    {
        $current_user = $this->zfcUserAuthentication()->getIdentity();

        $error = '';

        try {
            if (!$this->getRequest()->isPost()) {
                return $this->response(self::InvalidRequestMethod);
            }

            $association_id = $this->params()->fromPost('id');
            $association_email = $this->params()->fromPost('email');

            if (empty($association_id) && empty($association_email)) {
                return $this->response(self::InvalidRequestMethod);
            }

            if ($association_id) {
                $to_delete = $this->getEntityManager()->getRepository('\GoogleSSO\Entity\AssociatedGmailAccount')->findOneBy(array(
                    'id' => $association_id,
                    'user' => $current_user,
                    'is_main' => 0
                ));
            } else {
                $to_delete = $this->getEntityManager()->getRepository('\GoogleSSO\Entity\AssociatedGmailAccount')->findOneBy(array(
                    'related_email_address' => $association_email,
                    'user' => $current_user,
                    'is_main' => 0
                ));
            }


            if (!$to_delete) {
                throw new \Exception('Invalid id');
            }

            $this->getEntityManager()->remove($to_delete);
            $this->getEntityManager()->flush();

            return $this->response(self::RequestSuccess);
        } catch (\Exception $e) {
            $error = $e->getMessage();
        }

        return $this->response(self::UnidentifiedError, array(), $error);
    }

    public function response($code, $data=array(), $message = null)
    {
        $response = $this->responseCodes[$code];

        if ($message) {
            $response['message'] = $message;
        } else {
            $response['message'] = $response['message'];
        }

        $response['code'] = $code;
        $response['data'] = $data;
        return new JsonModel($response);
    }

    public function getUserService()
    {
        return $this->getServiceLocator()->get('canariumcore_user_service');
    }

    /**
     * @return \Doctrine\ORM\EntityManager
     */
    public function getEntityManager()
    {
        if (!$this->entityManager) {
            $this->setEntityManager();
        }
        return $this->entityManager;
    }

    public function setEntityManager($em=null)
    {
        if ($em) {
            $this->entityManager = $em;
        } else {
            $this->entityManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
        }
    }
}
