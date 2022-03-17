<?php

namespace Maisenvios\Middleware\Model;

class ShopMeta {
    	
    private $id;
    private $name;
    private $value;
    private $shopId;
    private $createdAt;
    private $updatedAt;

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
     * Get the value of value
     */ 
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Set the value of value
     *
     * @return  self
     */ 
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Get the value of shopId
     */ 
    public function getShopId()
    {
        return $this->shopId;
    }

    /**
     * Set the value of shopId
     *
     * @return  self
     */ 
    public function setShopId($shopId)
    {
        $this->shopId = $shopId;

        return $this;
    }

    /**
     * Get the value of createdAt
     */ 
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set the value of createdAt
     *
     * @return  self
     */ 
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get the value of updatedAt
     */ 
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * Set the value of updatedAt
     *
     * @return  self
     */ 
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * Creates an ShopMeta object 
     */
    public static function create($arr) {
        $meta = new ShopMeta();
        ($arr['id']) ? $meta->setId($arr['id']) : null; 
        ($arr['name']) ? $meta->setName($arr['name']) : null;
        ($arr['value']) ?  $meta->setValue($arr['value']) : null;
        ($arr['shopId']) ? $meta->setShopId($arr['shopId']) : null;
        ($arr['createdAt']) ? $meta->setCreatedAt($arr['createdAt']) : null;
        ($arr['updatedAt']) ? $meta->setUpdatedAt($arr['updatedAt']) : null;
        return $meta;
    }
}