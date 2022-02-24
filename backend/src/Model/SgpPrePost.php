<?php
namespace Maisenvios\Middleware\Model;

class SgpPrePost {
    private $identificador;
    private $observacao;
    private $destinatario;
    private $doc;//cpf_cnpj
    private $endereco;
    private $numero;
    private $bairro;
    private $cidade;
    private $uf;
    private $cep;
    private $servico_correios;
    private $complemento;
    private $email;
    private $peso;
    private $comprimento;
    private $largura;
    private $altura;
    

    /**
     * Get the value of identificador
     */ 
    public function getIdentificador()
    {
        return $this->identificador;
    }

    /**
     * Set the value of identificador
     *
     * @return  self
     */ 
    public function setIdentificador($identificador)
    {
        $this->identificador = $identificador;

        return $this;
    }

    /**
     * Get the value of observacao
     */ 
    public function getObservacao()
    {
        return $this->observacao;
    }

    /**
     * Set the value of observacao
     *
     * @return  self
     */ 
    public function setObservacao($observacao)
    {
        $this->observacao = $observacao;

        return $this;
    }

    /**
     * Get the value of destinatario
     */ 
    public function getDestinatario()
    {
        return $this->destinatario;
    }

    /**
     * Set the value of destinatario
     *
     * @return  self
     */ 
    public function setDestinatario($destinatario)
    {
        $this->destinatario = $destinatario;

        return $this;
    }

    /**
     * Get the value of doc
     */ 
    public function getDoc()
    {
        return $this->doc;
    }

    /**
     * Set the value of doc
     *
     * @return  self
     */ 
    public function setDoc($doc)
    {
        $this->doc = $doc;

        return $this;
    }

    /**
     * Get the value of endereco
     */ 
    public function getEndereco()
    {
        return $this->endereco;
    }

    /**
     * Set the value of endereco
     *
     * @return  self
     */ 
    public function setEndereco($endereco)
    {
        $this->endereco = $endereco;

        return $this;
    }

    /**
     * Get the value of numero
     */ 
    public function getNumero()
    {
        return $this->numero;
    }

    /**
     * Set the value of numero
     *
     * @return  self
     */ 
    public function setNumero($numero)
    {
        $this->numero = $numero;

        return $this;
    }

    /**
     * Get the value of bairro
     */ 
    public function getBairro()
    {
        return $this->bairro;
    }

    /**
     * Set the value of bairro
     *
     * @return  self
     */ 
    public function setBairro($bairro)
    {
        $this->bairro = $bairro;

        return $this;
    }

    /**
     * Get the value of cidade
     */ 
    public function getCidade()
    {
        return $this->cidade;
    }

    /**
     * Set the value of cidade
     *
     * @return  self
     */ 
    public function setCidade($cidade)
    {
        $this->cidade = $cidade;

        return $this;
    }

    /**
     * Get the value of uf
     */ 
    public function getUf()
    {
        return $this->uf;
    }

    /**
     * Set the value of uf
     *
     * @return  self
     */ 
    public function setUf($uf)
    {
        $this->uf = $uf;

        return $this;
    }

    /**
     * Get the value of cep
     */ 
    public function getCep()
    {
        return $this->cep;
    }

    /**
     * Set the value of cep
     *
     * @return  self
     */ 
    public function setCep($cep)
    {
        $this->cep = $cep;

        return $this;
    }

    /**
     * Get the value of servico_correios
     */ 
    public function getServico_correios()
    {
        return $this->servico_correios;
    }

    /**
     * Set the value of servico_correios
     *
     * @return  self
     */ 
    public function setServico_correios($servico_correios)
    {
        $this->servico_correios = $servico_correios;

        return $this;
    }

    /**
     * Get the value of complemento
     */ 
    public function getComplemento()
    {
        return $this->complemento;
    }

    /**
     * Set the value of complemento
     *
     * @return  self
     */ 
    public function setComplemento($complemento)
    {
        $this->complemento = $complemento;

        return $this;
    }

    /**
     * Get the value of email
     */ 
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set the value of email
     *
     * @return  self
     */ 
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get the value of peso
     */ 
    public function getPeso()
    {
        return $this->peso;
    }

    /**
     * Set the value of peso
     *
     * @return  self
     */ 
    public function setPeso($peso)
    {
        $this->peso = $peso;

        return $this;
    }

    /**
     * Get the value of comprimento
     */ 
    public function getComprimento()
    {
        return $this->comprimento;
    }

    /**
     * Set the value of comprimento
     *
     * @return  self
     */ 
    public function setComprimento($comprimento)
    {
        $this->comprimento = $comprimento;

        return $this;
    }

    /**
     * Get the value of largura
     */ 
    public function getLargura()
    {
        return $this->largura;
    }

    /**
     * Set the value of largura
     *
     * @return  self
     */ 
    public function setLargura($largura)
    {
        $this->largura = $largura;

        return $this;
    }

    /**
     * Get the value of altura
     */ 
    public function getAltura()
    {
        return $this->altura;
    }

    /**
     * Set the value of altura
     *
     * @return  self
     */ 
    public function setAltura($altura)
    {
        $this->altura = $altura;

        return $this;
    }

    public static function createFromLojaintegrada($payload, $shipping) {
        $thisObj = new SgpPrePost();
        $date = (new \DateTime())->format('Y-m-d H:i:s');
        $thisObj->setIdentificador( $payload->numero );
        $thisObj->setDestinatario( $payload->endereco_entrega->nome );
        $thisObj->setDoc( $payload->endereco_entrega->cpf );
        $thisObj->setEndereco( $payload->endereco_entrega->endereco );
        $thisObj->setNumero( $payload->endereco_entrega->numero );
        $thisObj->setBairro( $payload->endereco_entrega->bairro );
        $thisObj->setCidade( $payload->endereco_entrega->cidade );
        $thisObj->setUf( $payload->endereco_entrega->estado );
        $thisObj->setCep( $payload->endereco_entrega->cep );
        $thisObj->setComplemento( $payload->endereco_entrega->complemento );
        $thisObj->setEmail( $payload->cliente->email );
        $thisObj->setPeso( $payload->peso_real );

        //Calcula a cubagem de todos os produtos
        $comprimento = 0;
        $largura = 0;
        $altura = 0;
        foreach ($payload->itens as $item) {
            $comprimento += $item->profundidade;
            $largura += $item->largura;
            $altura += $item->altura;
        }

        $thisObj->setComprimento( $comprimento );
        $thisObj->setLargura( $largura );
        $thisObj->setAltura( $altura );
        $thisObj->setServico_correios( $shipping->getCorreios() );
        $thisObj->setObservacao("Mensagem automática: Pedido nº '{$payload->numero} integrado via Painel Integrador +Envios ({$date})");
        
        return $thisObj;
    }

    public static function generatePayload(Array $sgpPrePosts) {
        $objetos = ["objetos" => []];
        foreach ($sgpPrePosts as $sgpPrePost) {
            $json = [];
            $json['identificador'] = $sgpPrePost->getIdentificador();
            $json['observacao'] = $sgpPrePost->getObservacao();
            $json['destinatario'] = $sgpPrePost->getDestinatario();
            $json['cpf_cnpj'] = $sgpPrePost->getDoc();
            $json['endereco'] = $sgpPrePost->getEndereco();
            $json['numero'] = $sgpPrePost->getNumero();
            $json['bairro'] = $sgpPrePost->getBairro();
            $json['cidade'] = $sgpPrePost->getCidade();
            $json['uf'] = $sgpPrePost->getUf();
            $json['cep'] = $sgpPrePost->getCep();
            $json['servico_correios'] = $sgpPrePost->getServico_correios();
            $json['complemento'] = $sgpPrePost->getComplemento();
            $json['email'] = $sgpPrePost->getEmail();
            $json['peso'] = $sgpPrePost->getPeso();
            $json['comprimento'] = $sgpPrePost->getComprimento();
            $json['largura'] = $sgpPrePost->getLargura();
            $json['altura'] = $sgpPrePost->getAltura();
            array_push($objetos['objetos'], $json);
        }
        return json_encode($objetos);
    }
}