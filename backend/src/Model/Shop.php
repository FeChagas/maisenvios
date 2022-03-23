<?php
namespace Maisenvios\Middleware\Model;

class Shop {
    private $id;
    private $name;
    private $sysKey; //holds sgp key
    private $customerKey;
    private $customerToken;
    private $account;
    private $ecommerce;
    private $active;
    private $lastRunAt;

    /**
     * Get the value of id
     */ 
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set the value of id
     *
     * @return  self
     */ 
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Get the value of name
     */ 
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set the value of name
     *
     * @return  self
     */ 
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get the value of sysKey
     */ 
    public function getSysKey()
    {
        return $this->sysKey;
    }

    /**
     * Set the value of sysKey
     *
     * @return  self
     */ 
    public function setSysKey($sysKey)
    {
        $this->sysKey = $sysKey;

        return $this;
    }

    /**
     * Get the value of customerKey
     */ 
    public function getCustomerKey()
    {
        return $this->customerKey;
    }

    /**
     * Set the value of customerKey
     *
     * @return  self
     */ 
    public function setCustomerKey($customerKey)
    {
        $this->customerKey = $customerKey;

        return $this;
    }

    /**
     * Get the value of customerToken
     */ 
    public function getCustomerToken()
    {
        return $this->customerToken;
    }

    /**
     * Set the value of customerToken
     *
     * @return  self
     */ 
    public function setCustomerToken($customerToken)
    {
        $this->customerToken = $customerToken;

        return $this;
    }

    /**
     * Get the value of account
     */ 
    public function getAccount()
    {
        return $this->account;
    }

    /**
     * Set the value of account
     *
     * @return  self
     */ 
    public function setAccount($account)
    {
        $this->account = $account;

        return $this;
    }

    /**
     * Get the value of ecommerce
     */ 
    public function getEcommerce()
    {
        return $this->ecommerce;
    }

    /**
     * Set the value of ecommerce
     *
     * @return  self
     */ 
    public function setEcommerce($ecommerce)
    {
        $this->ecommerce = $ecommerce;

        return $this;
    }

    /**
     * Get the value of active
     */ 
    public function getActive()
    {
        return $this->active;
    }

    /**
     * Set the value of active
     *
     * @return  self
     */ 
    public function setActive($active)
    {
        $this->active = $active;

        return $this;
    }

    /**
     * Get the value of lastRunAt
     */ 
    public function getLastRunAt()
    {
        return $this->lastRunAt;
    }

    /**
     * Set the value of lastRunAt
     *
     * @return  self
     */ 
    public function setLastRunAt($lastRunAt)
    {
        $this->lastRunAt = $lastRunAt;

        return $this;
    }

    /* 
    * Creates an Shop object 
    */
    public static function create($arr) {
        $shop = new Shop();
        ($arr['id']) ? $shop->setId($arr['id']) : null; 
        ($arr['name']) ? $shop->setName($arr['name']) : null; 
        ($arr['key_mais']) ? $shop->setSysKey($arr['key_mais']) : null; 
        ($arr['key_primary']) ? $shop->setCustomerKey($arr['key_primary']) : null; 
        ($arr['token_primary']) ? $shop->setCustomerToken($arr['token_primary']) : null; 
        ($arr['account']) ? $shop->setAccount($arr['account']) : null; 
        ($arr['ecommerce']) ? $shop->setEcommerce($arr['ecommerce']) : null; 
        ($arr['active']) ? $shop->setActive($arr['active']) : null;
        ($arr['lastRunAt']) ? $shop->setLastRunAt($arr['lastRunAt']) : null;
        return $shop;
    }
}