<?php

namespace Omnipay\CardSave;

use Omnipay\CardSave\Message\CompletePurchaseRequest;
use Omnipay\CardSave\Message\PurchaseRequest;
use Omnipay\Common\AbstractGateway;

/**
 * CardSave Gateway
 *
 * @link http://www.cardsave.net/dev-downloads
 */
class Gateway extends AbstractGateway
{
    private $integrationType = "direct";

    public function getName()
    {
        return 'CardSave';
    }

    public function getDefaultParameters()
    {
        return array(
            'merchantId' => '',
            'password' => '',
            'integrationType' => '',
            'preSharedKey' => ''
        );
    }

    public function getMerchantId()
    {
        return $this->getParameter('merchantId');
    }

    public function setMerchantId($value)
    {
        return $this->setParameter('merchantId', $value);
    }

    public function getPassword()
    {
        return $this->getParameter('password');
    }

    public function setPassword($value)
    {
        return $this->setParameter('password', $value);
    }

    public function setIntegrationType($integration)
    {
        $this->integrationType = $integration;
    }

    public function getIntegrationType()
    {
        return $this->integrationType;
    }

    public function purchase(array $parameters = array())
    {
        $parameters['integrationType'] = $this->integrationType;

        return $this->createRequest('\Omnipay\CardSave\Message\PurchaseRequest', $parameters);
    }

    public function referencedPurchase(array $parameters = array())
    {
        return $this->createRequest('\Omnipay\CardSave\Message\ReferencedPurchaseRequest', $parameters);
    }

    public function completePurchase(array $parameters = array())
    {
        return $this->createRequest('\Omnipay\CardSave\Message\CompletePurchaseRequest', $parameters);
    }

    public function refund(array $parameters = array())
    {
        return $this->createRequest('\Omnipay\CardSave\Message\RefundRequest', $parameters);
    }
}
