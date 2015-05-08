<?php
/**
 * @author Jethro Laviste
 */
 
namespace GoogleSSO\Authentication;

class ForceLogin implements \Zend\Authentication\Adapter\AdapterInterface
{
    /**
     * @var UserInterface
     */
    protected $user;

    /**
     * @param $user
     */
    function __construct($user)
    {
        $this->user = $user;
    }
	
	public function getUser(){
		return $this->user;	
	}

    public function authenticate()
    {
		$res = new \Zend\Authentication\Result( \Zend\Authentication\Result::SUCCESS,$this->getUser()->getId());
        return $res;
    }
}
?>