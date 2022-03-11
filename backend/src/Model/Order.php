<?php
namespace Maisenvios\Middleware\Model;

class Order {
    private $id;
    private $orderId;
    private $storeId;
    private $integrated;
    private $createdAt;

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
     * Get the value of orderId
     */ 
    public function getOrderId()
    {
        return $this->orderId;
    }

    /**
     * Set the value of orderId
     *
     * @return  self
     */ 
    public function setOrderId($orderId)
    {
        $this->orderId = $orderId;

        return $this;
    }

    /**
     * Get the value of storeId
     */ 
    public function getStoreId()
    {
        return $this->storeId;
    }

    /**
     * Set the value of storeId
     *
     * @return  self
     */ 
    public function setStoreId($storeId)
    {
        $this->storeId = $storeId;

        return $this;
    }

    /**
     * Get the value of integrated
     */ 
    public function getIntegrated()
    {
        return $this->integrated;
    }

    /**
     * Set the value of integrated
     *
     * @return  self
     */ 
    public function setIntegrated($integrated)
    {
        $this->integrated = $integrated;

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

    /* 
    * Creates an Order object 
    */
    public static function create($arr) {
        $order = new Order();
        ($arr['id']) ? $order->setId($arr['id']) : null; 
        ($arr['name']) ? $order->setOrderId($arr['orderId']) : null; 
        ($arr['key_mais']) ? $order->setStoreId($arr['storeId']) : null; 
        ($arr['key_primary']) ? $order->setIntegrated($arr['integrated']) : null; 
        ($arr['token_primary']) ? $order->setCreatedAt($arr['createdAt']) : null; 
        return $order;
    }

    public function createFromVtexFeed($order, $storeId) {
        $obj = new Order();
        $obj->setOrderId($order->orderId);
        $obj->setStoreId($storeId);
        $obj->setIntegrated(0);
        return $obj;
    }
}