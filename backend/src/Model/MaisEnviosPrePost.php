<?php
namespace Maisenvios\Middleware\Model;

use Maisenvios\Middleware\Model\MaisEnvios\Sender;
use Maisenvios\Middleware\Model\MaisEnvios\Delivery;
use Maisenvios\Middleware\Model\MaisEnvios\Contact;
use Maisenvios\Middleware\Model\MaisEnvios\PostObject;
use Maisenvios\Middleware\Model\MaisEnvios\Complement;

class MaisEnviosPrePost {
    private $sender;
    private $delivery;
    private $contact;
    private $object;
    private $complement;
    private $service;
    private $cardpost;
    private $dc = [];

    /**
     * Get the value of sender
     */ 
    public function getSender()
    {
        return $this->sender;
    }

    /**
     * Set the value of sender
     * @param $sender Sender|string Expect either an object of type Sender or a string containg the Sender's id
     * @return  self
     */ 
    public function setSender($sender)
    {
        $this->sender = $sender;

        return $this;
    }

    /**
     * Get the value of delivery
     */ 
    public function getDelivery()
    {
        return $this->delivery;
    }

    /**
     * Set the value of delivery
     *
     * @return  self
     */ 
    public function setDelivery(Delivery $delivery)
    {
        $this->delivery = $delivery;

        return $this;
    }

    /**
     * Get the value of contact
     */ 
    public function getContact()
    {
        return $this->contact;
    }

    /**
     * Set the value of contact
     *
     * @return  self
     */ 
    public function setContact(Contact $contact)
    {
        $this->contact = $contact;

        return $this;
    }

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
    public function setObject(PostObject $object)
    {
        $this->object = $object;

        return $this;
    }

    /**
     * Get the value of complement
     */ 
    public function getComplement()
    {
        return $this->complement;
    }

    /**
     * Set the value of complement
     *
     * @return  self
     */ 
    public function setComplement(Complement $complement)
    {
        $this->complement = $complement;

        return $this;
    }

    /**
     * Get the value of service
     */ 
    public function getService()
    {
        return $this->service;
    }

    /**
     * Set the value of service
     *
     * @return  self
     */ 
    public function setService(string $service)
    {
        $this->service = $service;

        return $this;
    }

    /**
     * Get the value of cardpost
     */ 
    public function getCardpost()
    {
        return $this->cardpost;
    }

    /**
     * Set the value of cardpost
     *
     * @return  self
     */ 
    public function setCardpost(string $cardpost)
    {
        $this->cardpost = $cardpost;

        return $this;
    }

    /**
     * Get the value of dc
     */ 
    public function getDc()
    {
        return $this->dc;
    }

    /**
     * Set the value of dc
     *
     * @return  self
     */ 
    public function setDc(array $dc)
    {
        $this->dc = $dc;

        return $this;
    }

    public static function createFromVtex($order, $service, $cardpost, $sender = null) {

        $self = new MaisEnviosPrePost();

        //The sender can be either the customer Id or the object
        if ($sender === null) {
            $sender = new Sender();
            $sender->setCep('');
        }

        $self->setSender( $sender );

        $dc = [];
        $total_dimensions = [
            'height' => 0,
            'width' => 0,
            'length' => 0,
            'diameter' => 0,
            'value' => 0,
            'total' => $order->value/100,
        ];
        $total_weigth = 0;
        foreach ($order->items as $key => $item) {
            $total_weigth += $item->additionalInfo->dimension->weight;
            array_push($dc, [
                'content' => $item->name,
                'quantity' => $item->quantity,
                'value' => $item->price/100
            ]);
            $total_dimensions['height'] += $item->additionalInfo->dimension->height;
            $total_dimensions['width'] += $item->additionalInfo->dimension->width;
            $total_dimensions['length'] += $item->additionalInfo->dimension->length;
            $total_dimensions['diameter'] += $item->additionalInfo->dimension->cubicweight;
            $total_dimensions['value'] += ($item->price/100) * $item->quantity;
        }
        $delivery = new Delivery();
        $delivery->setName($order->shippingData->address->receiverName);
        $delivery->setCep($order->shippingData->address->postalCode);
        $delivery->setAddress($order->shippingData->address->street);
        $delivery->setNeighborhood($order->shippingData->address->neighborhood);
        $delivery->setCity($order->shippingData->address->city);
        $delivery->setState($order->shippingData->address->state);
        $delivery->setNumber($order->shippingData->address->number);
        $delivery->setExtent($order->shippingData->address->complement);

        $self->setDelivery($delivery);
    
        $contact = new Contact();        
        $contact->setPhone($order->clientProfileData->phone);
        $contact->setMail($order->clientProfileData->email);
        $contact->setFederalid($order->clientProfileData->document);
        $contact->setCare($order->shippingData->address->receiverName);
        $contact->setSave(false);
        $contact->setInvoice($order->packageAttachment->packages[0]->invoiceNumber);
        $contact->setRequest($order->orderId);

        $self->setContact($contact);

        $object = new PostObject();
        $object->setMdp(false);
        $object->setAr(false);
        $object->setOwnhand(false);
        $object->setWeight( $total_weigth );
        $object->setQuantity( 1 );
        $object->setType( false );

        $self->setObject($object);

        $complement = new Complement();
        $complement->setHeight( $total_dimensions['height'] );
        $complement->setWidth( $total_dimensions['width'] );
        $complement->setLength( $total_dimensions['length'] );
        $complement->setDiameter( $total_dimensions['diameter'] );
        $complement->setValue( $total_dimensions['value'] );
        $complement->setTotal( $total_dimensions['total'] );
        $complement->setType( '001' );

        $self->setComplement( $complement );

        $self->setService( $service );

        $self->setCardpost( $cardpost );

        $self->setDc( $dc );

        return $self;
    }

    public function toJson() {
        return json_encode(
            [
                'sender' => ( is_string($this->sender) ? $this->sender : $this->sender->toArray() ),
                'delivery' => $this->delivery->toArray(),
                'contact' => $this->contact->toArray(),
                'object' => $this->object->toArray(),
                'complement' => $this->complement->toArray(),
                'service' => $this->service,
                'cardpost' => $this->cardpost,
                'dc' => $this->dc,
            ]
            );
    }
}
?>