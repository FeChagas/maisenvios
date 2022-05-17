<?php
namespace Maisenvios\Middleware\Model\MaisEnvios;

class PostObject {
    private $object;
    private $package;
    private $mdp;
    private $ar;
    private $ownhand;
    private $weight;
    private $quantity;
    private $type;

    /**
     * Get the value of object
     */ 
    public function getObject()
    {
        return $this->object;
    }

    /**
     * Set the value of object
     *
     * @return  self
     */ 
    public function setObject($object)
    {
        $this->object = $object;

        return $this;
    }

    /**
     * Get the value of package
     */ 
    public function getPackage()
    {
        return $this->package;
    }

    /**
     * Set the value of package
     *
     * @return  self
     */ 
    public function setPackage($package)
    {
        $this->package = $package;

        return $this;
    }

    /**
     * Get the value of mdp
     */ 
    public function getMdp()
    {
        return $this->mdp;
    }

    /**
     * Set the value of mdp
     *
     * @return  self
     */ 
    public function setMdp($mdp)
    {
        $this->mdp = $mdp;

        return $this;
    }

    /**
     * Get the value of ar
     */ 
    public function getAr()
    {
        return $this->ar;
    }

    /**
     * Set the value of ar
     *
     * @return  self
     */ 
    public function setAr(bool $ar)
    {
        $this->ar = $ar;

        return $this;
    }

    /**
     * Get the value of ownhand
     */ 
    public function getOwnhand()
    {
        return $this->ownhand;
    }

    /**
     * Set the value of ownhand
     *
     * @return  self
     */ 
    public function setOwnhand(bool $ownhand)
    {
        $this->ownhand = $ownhand;

        return $this;
    }

    /**
     * Get the value of weight
     */ 
    public function getWeight()
    {
        return $this->weight;
    }

    /**
     * Set the value of weight
     *
     * @return  self
     */ 
    public function setWeight(int $weight)
    {
        $this->weight = $weight;

        return $this;
    }

    /**
     * Get the value of quantity
     */ 
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * Set the value of quantity
     *
     * @return  self
     */ 
    public function setQuantity(int $quantity)
    {
        $this->quantity = $quantity;

        return $this;
    }

    /**
     * Get the value of type
     */ 
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set the value of type
     *
     * @return  self
     */ 
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    public function toArray(){
        return [
            'object' => $this->object,
            'package' => $this->package,
            'mdp' => $this->mdp,
            'ar' => $this->ar,
            'ownhand' => $this->ownhand,
            'weight' => $this->weight,
            'quantity' => $this->quantity,
            'type' => $this->type,
        ];
    }
}