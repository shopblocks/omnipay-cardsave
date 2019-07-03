<?php

namespace Omnipay\CardSave;

use Omnipay\CardSave\Message\CompletePurchaseRequest;
use Omnipay\CardSave\Message\PurchaseRequest;
use Omnipay\Common\AbstractGateway;
use Omnipay\CardSave\Message\DummyCompletePurchase;

/**
 * CardSave Gateway
 *
 * @link http://www.cardsave.net/dev-downloads
 */
class Gateway extends AbstractGateway
{
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

    public function setIntegrationType($value)
    {
        if (empty($this->INTEGRATION_TYPES[$integration])) {
            return $this->setParameter('integrationType', $this->INTEGRATION_TYPES[0]);
        }
        
        return $this->setParameter('integrationType', $value);
    }

    public function getIntegrationType()
    {
        return $this->getParameter('integrationType');
    }

    public function setPreSharedKey($value)
    {
        return $this->setParameter('preSharedKey', $value);
    }

    public function getPreSharedKey()
    {
        return $this->getParameter('preSharedKey');
    }

    public function setOrderId($value)
    {
        return $this->setParameter('OrderId', $value);
    }

    public function getOrderId()
    {
        return $this->getParameter('OrderId');
    }

    public function purchase(array $parameters = array())
    {
        return $this->createRequest('\Omnipay\CardSave\Message\PurchaseRequest', $parameters);
    }

    public function referencedPurchase(array $parameters = array())
    {
        return $this->createRequest('\Omnipay\CardSave\Message\ReferencedPurchaseRequest', $parameters);
    }

    public function completePurchase(array $parameters = array())
    {
        if ($this->getIntegrationType() == "redirect") {
            $parameters['PreSharedKey'] = $this->getPreSharedKey();
            $parameters['Password'] = $this->getPassword();
            $parameters['MerchantID'] = $this->getMerchantID();

            return new DummyCompletePurchase($this->httpClient, $this->httpRequest, $parameters);
        }

        return $this->createRequest('\Omnipay\CardSave\Message\CompletePurchaseRequest', $parameters);
    }

    public function refund(array $parameters = array())
    {
        return $this->createRequest('\Omnipay\CardSave\Message\RefundRequest', $parameters);
    }
}
