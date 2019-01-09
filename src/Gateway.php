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
            return new DummyCompletePurchase($this->httpClient, $this->httpRequest);
            $this->createRequest('\Omnipay\CardSave\Message\CompletePurchaseRequest', $parameters);
        }

        return $this->createRequest('\Omnipay\CardSave\Message\CompletePurchaseRequest', $parameters);
    }

    public function refund(array $parameters = array())
    {
        return $this->createRequest('\Omnipay\CardSave\Message\RefundRequest', $parameters);
    }
}
