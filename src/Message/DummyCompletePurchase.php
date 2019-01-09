<?php

namespace Omnipay\CardSave\Message;

class DummyCompletePurchase {
    private $data = null;

    public function __construct($httpClient, $httpRequest)
    {
        $this->data = $httpRequest->request->all();
    }

    public function send()
    {
        return $this;
    }

    public function isSuccessful()
    {
        return (int) $this->data['StatusCode'] === 0;
    }

    public function isRedirect()
    {
        return (int) $this->data['StatusCode'] === 3;
    }

    public function getTransactionReference()
    {
        return $this->data['CrossReference'];
    }

    public function getMessage()
    {
        return $this->data['Message'];
    }

    public function getRedirectUrl()
    {
        throw new \Exception("Not supported");
    }
}
