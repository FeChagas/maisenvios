<?php

namespace Maisenvios\Middleware\Model\MaisEnvios;

class Contact {
    private $phone;
    private $mail;
    private $federalid;
    private $invoice;
    private $care;
    private $request;
    private $save;
    private $observation;

    /**
     * Get the value of phone
     */ 
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * Set the value of phone
     *
     * @return  self
     */ 
    public function setPhone($phone)
    {
        $this->phone = $phone;

        return $this;
    }

    /**
     * Get the value of mail
     */ 
    public function getMail()
    {
        return $this->mail;
    }

    /**
     * Set the value of mail
     *
     * @return  self
     */ 
    public function setMail($mail)
    {
        $this->mail = $mail;

        return $this;
    }

    /**
     * Get the value of federalid
     */ 
    public function getFederalid()
    {
        return $this->federalid;
    }

    /**
     * Set the value of federalid
     *
     * @return  self
     */ 
    public function setFederalid($federalid)
    {
        $this->federalid = $federalid;

        return $this;
    }

    /**
     * Get the value of invoice
     */ 
    public function getInvoice()
    {
        return $this->invoice;
    }

    /**
     * Set the value of invoice
     *
     * @return  self
     */ 
    public function setInvoice($invoice)
    {
        $this->invoice = $invoice;

        return $this;
    }

    /**
     * Get the value of care
     */ 
    public function getCare()
    {
        return $this->care;
    }

    /**
     * Set the value of care
     *
     * @return  self
     */ 
    public function setCare($care)
    {
        $this->care = $care;

        return $this;
    }

    /**
     * Get the value of request
     */ 
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * Set the value of request
     *
     * @return  self
     */ 
    public function setRequest($request)
    {
        $this->request = $request;

        return $this;
    }

    /**
     * Get the value of save
     */ 
    public function getSave()
    {
        return $this->save;
    }

    /**
     * Set the value of save
     *
     * @return  self
     */ 
    public function setSave(bool $save)
    {
        $this->save = $save;

        return $this;
    }

    /**
     * Get the value of observation
     */ 
    public function getObservation()
    {
        return $this->observation;
    }

    /**
     * Set the value of observation
     *
     * @return  self
     */ 
    public function setObservation($observation)
    {
        $this->observation = $observation;

        return $this;
    }

    public function toArray(){
        return [
            'phone' => $this->phone,
            'mail' => $this->mail,
            'federalid' => $this->federalid,
            'invoice' => $this->invoice,
            'care' => $this->care,
            'request' => $this->request,
            'save' => $this->save,
            'observation' => $this->observation,
        ];
    }
}