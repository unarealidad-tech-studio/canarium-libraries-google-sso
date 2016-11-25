<?php

namespace GoogleSSO\Authentication;

use \Zend\Authentication\Result;

class ConnectedAccount implements \Zend\Authentication\Adapter\AdapterInterface
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $object_manager;

    protected $user_info;
    protected $user_class;
    protected $user_service;

    /**
     * @param $user_class
     * @param $user_service
     * @param \Doctrine\ORM\EntityManager $object_manager
     */
    function __construct($user_class, $user_service, $object_manager)
    {
        $this->user_class = $user_class;
        $this->user_service = $user_service;
        $this->object_manager = $object_manager;
    }

    public function setUserInfo($user_info)
    {
        $this->user_info = $user_info;

    }

    public function getUserInfo()
    {
        return $this->user_info;
    }

    private function generateCode($length = 10) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    public function authenticate()
    {
        $user_info = $this->getUserInfo();

        // check if associated with any account
        $associated_account = $this->object_manager->getRepository('\GoogleSSO\Entity\AssociatedGmailAccount')->findOneBy(array(
            'related_email_address' => $user_info['email'],
            'is_main' => 0
        ));

        if ($associated_account) {
            return new Result(Result::FAILURE_CREDENTIAL_INVALID, null, array(
                'The email address that you are using is already associated to an account'
            ));
        }

        $user = $this->object_manager->getRepository($this->user_class)->findOneBy(array('email' => $user_info['email']));

        if (!$user) {
            if (!empty($user_info['login_only'])) {
                return new Result(Result::FAILURE_CREDENTIAL_INVALID, null, array(
                    'You need to register first. Go to our website to register'
                ));
            } else {
                $data = array();
                $data['email'] = $user_info['email'];
                $data['password'] = $this->generateCode(6);
                $data['passwordVerify'] = $data['password'];
                $user = $this->user_service->register($data);
            }
        }

        $user->setLastLogin(new \DateTime());
        $user->setFirstName($user_info['first_name']);
        $user->setLastName($user_info['last_name']);
        $user->setDisplayName($user_info['display_name']);
        $this->object_manager->flush();

        return new Result(Result::SUCCESS, $user->getId());
    }
}
