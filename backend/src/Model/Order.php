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

    public function createFromVtexFeed($order, $storeId) {
        $order = new Order();
        $order->setOrderId($order->orderId);
        $order->setStoreId($storeId);
        $order->setIntegrated(0);
        return $order;
    }
}