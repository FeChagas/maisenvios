<?php
namespace Maisenvios\Middleware\Model;

class Order {
    private $id;
    private $orderId;
    private $invoiceNumber;
    private $tracking;
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
     * Get the value of tracking
     */ 
    public function getTracking()
    {
        return $this->tracking;
    }

    /**
     * Set the value of tracking
     *
     * @return  self
     */ 
    public function setTracking($tracking)
    {
        $this->tracking = $tracking;

        return $this;
    }

    /**
     * Get the value of invoiceNumber
     */ 
    public function getInvoiceNumber()
    {
        return $this->invoiceNumber;
    }

    /**
     * Set the value of invoiceNumber
     *
     * @return  self
     */ 
    public function setInvoiceNumber($invoiceNumber)
    {
        $this->invoiceNumber = $invoiceNumber;

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
        (isset($arr['id'])) ? $order->setId($arr['id']) : null; 
        (isset($arr['orderId'])) ? $order->setOrderId($arr['orderId']) : null; 
        (isset($arr['invoiceNumber'])) ? $order->setInvoiceNumber($arr['invoiceNumber']) : null; 
        (isset($arr['tracking'])) ? $order->setTracking($arr['tracking']) : null; 
        (isset($arr['storeId'])) ? $order->setStoreId($arr['storeId']) : null; 
        (isset($arr['integrated'])) ? $order->setIntegrated($arr['integrated']) : null; 
        (isset($arr['createdAt'])) ? $order->setCreatedAt($arr['createdAt']) : null; 
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