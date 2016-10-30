<?php

namespace GoogleSSO\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use Gedmo\Mapping\Annotation as Gedmo;

use Zend\ServiceManager\ServiceManager;


/**
 * @ORM\Entity
 * @ORM\Table(name="associated_gmail_accounts")
 * @ORM\HasLifecycleCallbacks
 */

class AssociatedGmailAccount {
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="CanariumCore\Entity\User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $user;

    /**
     * @var string
     * @ORM\Column(type="string", length=255, nullable=false)
     */
    protected $related_first_name;

    /**
     * @var string
     * @ORM\Column(type="string", length=255, nullable=false)
     */
    protected $related_last_name;

    /**
     * @var string
     * @ORM\Column(type="string", length=255, nullable=false)
     */
    protected $related_email_address;

    /**
     * @var string
     * @ORM\Column(type="string", length=255, nullable=false)
     */
    protected $google_auth_token;

    /**
     * @var \DateTime
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime", nullable=false)
     */
    protected $date_added;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime", nullable=false)
     */
    protected $date_updated;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $google_auth_token_expiration;

    /**
     * @var int
     * @ORM\Column(type="smallint", nullable=false)
     */
    protected $is_main = 0;

    /**
     * @return mixed
     */
    public function getId() {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id) {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getUser() {
        return $this->user;
    }

    /**
     * @param mixed $user
     */
    public function setUser($user) {
        $this->user = $user;
    }

    /**
     * @return \DateTime
     */
    public function getDateAdded() {
        return $this->date_added;
    }

    /**
     * @param \DateTime $date_added
     */
    public function setDateAdded($date_added) {
        $this->date_added = $date_added;
    }

    /**
     * @return string
     */
    public function getRelatedFirstName() {
        return $this->related_first_name;
    }

    /**
     * @param string $related_first_name
     */
    public function setRelatedFirstName($related_first_name) {
        $this->related_first_name = $related_first_name;
    }

    /**
     * @return string
     */
    public function getRelatedLastName() {
        return $this->related_last_name;
    }

    /**
     * @param string $related_last_name
     */
    public function setRelatedLastName($related_last_name) {
        $this->related_last_name = $related_last_name;
    }

    /**
     * @return string
     */
    public function getRelatedEmailAddress() {
        return $this->related_email_address;
    }

    /**
     * @param string $related_email_address
     */
    public function setRelatedEmailAddress($related_email_address) {
        $this->related_email_address = $related_email_address;
    }

    /**
     * @return string
     */
    public function getGoogleAuthToken() {
        return $this->google_auth_token;
    }

    /**
     * @param string $google_auth_token
     */
    public function setGoogleAuthToken($google_auth_token) {
        $this->google_auth_token = $google_auth_token;
    }

    /**
     * @return \DateTime
     */
    public function getGoogleAuthTokenExpiration() {
        return $this->google_auth_token_expiration;
    }

    /**
     * @param \DateTime $google_auth_token_expiration
     */
    public function setGoogleAuthTokenExpiration($google_auth_token_expiration) {
        $this->google_auth_token_expiration = $google_auth_token_expiration;
    }

    /**
     * @return \DateTime
     */
    public function getDateUpdated() {
        return $this->date_updated;
    }

    /**
     * @param \DateTime $date_updated
     */
    public function setDateUpdated($date_updated) {
        $this->date_updated = $date_updated;
    }

    /**
     * @return int
     */
    public function getIsMain() {
        return $this->is_main;
    }

    /**
     * @param int $is_main
     */
    public function setIsMain($is_main) {
        $this->is_main = $is_main;
    }

}
