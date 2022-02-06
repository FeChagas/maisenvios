<?php
namespace Maisenvios\Middleware\Model;

class Shipping {
    private $id;
    private $idShop;
    private $name;
    private $correios;
    private $active;

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
     * Get the value of idShop
     */ 
    public function getIdShop()
    {
        return $this->idShop;
    }

    /**
     * Set the value of idShop
     *
     * @return  self
     */ 
    public function setIdShop($idShop)
    {
        $this->idShop = $idShop;

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
     * Get the value of correios
     */ 
    public function getCorreios()
    {
        return $this->correios;
    }

    /**
     * Set the value of correios
     *
     * @return  self
     */ 
    public function setCorreios($correios)
    {
        $this->correios = $correios;

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

    public function create($arr) {
        $shipping = new Shipping();
        $shipping->setId($arr['id']);
        $shipping->setIdShop($arr['idShop']);
        $shipping->setName($arr['name']);
        $shipping->setCorreios($arr['correios']);
        $shipping->setActive($arr['active']);
        return $shipping;
    }
}