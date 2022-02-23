<?php
namespace Maisenvios\Middleware\Model;

class SgpLog {
    private $id;
    private $shopId;
    private $orderId;
    private $status_processamento;
    private $status;
    private $objetos;
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
     * Get the value of status_processamento
     */ 
    public function getStatus_processamento()
    {
        return $this->status_processamento;
    }

    /**
     * Set the value of status_processamento
     *
     * @return  self
     */ 
    public function setStatus_processamento($status_processamento)
    {
        $this->status_processamento = $status_processamento;

        return $this;
    }

    /**
     * Get the value of status
     */ 
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set the value of status
     *
     * @return  self
     */ 
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get the value of objetos
     */ 
    public function getObjetos()
    {
        return $this->objetos;
    }

    /**
     * Set the value of objetos
     *
     * @return  self
     */ 
    public function setObjetos($objetos)
    {
        $this->objetos = $objetos;

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

    public static function createFromSgpResponse($shopId, $orderId, $obj) {
        $log = new SgpLog();
        $log->setShopId($shopId);
        $log->setOrderId($orderId);
        $log->setStatus_processamento($obj->retorno->status_processamento);
        $log->setStatus($obj->retorno->status);
        $log->setObjetos(json_encode($obj->retorno->objetos));
        return $log;
    }

    public function create($arr) {
        $log = new SgpLog();
        $log->setShopId($arr['shopId']);
        $log->setOrderId($arr['orderId']);
        $log->setStatus_processamento($arr['status_processamento']);
        $log->setStatus($arr['status']);
        $log->setObjetos($arr['objetos']);
        return $log;
    }
}