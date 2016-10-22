<?php
/**
 * @author Jethro Laviste
 */

namespace GoogleSSO\Authentication;

use \Zend\Authentication\Result;

class NewForceLogin implements \Zend\Authentication\Adapter\AdapterInterface
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

        $user = $this->object_manager->getRepository($this->user_class)->findOneBy(array('email' => $user_info['email']));

        if (!$user) {
            $data = array();
            $data['email'] = $user_info['email'];
            $data['password'] = $this->generateCode(6);
            $data['passwordVerify'] = $data['password'];
            $user = $this->user_service->register($data);
        }

        $user->setLastLogin(new \DateTime());
        $user->setFirstName($user_info['first_name']);
        $user->setLastName($user_info['last_name']);
        $user->setDisplayName($user_info['display_name']);
        $this->object_manager->flush();

        $res = new Result(Result::SUCCESS, $user->getId());
        return $res;
    }
}
