<?php

namespace Omnipay\CardSave\Message;

class DummyCompletePurchase {
    private $data = null;
    private $parameters = null;

    public function __construct($httpClient, $httpRequest, $parameters)
    {
        $this->data = $httpRequest->request->all();
        $this->parameters = $parameters;

        $this->data['Valid'] = $this->validHash();
    }

    public function send()
    {
        return $this;
    }

    public function isSuccessful()
    {
        return (int) $this->data['StatusCode'] === 0 && $this->data['Valid'];
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

    public function getData()
    {
        return $this->data;
    }

    private function validHash()
    {
        return $this->data['HashDigest'] === $this->generateHashCheck();
    }

    private function generateHashCheck()
    {
        $hashString = "PreSharedKey=" . $this->parameters['PreSharedKey'];
        $hashString .= "&MerchantID=" . $this->parameters['MerchantID'];
        $hashString .= "&Password=" . $this->parameters['Password'];
        $hashString .= "&StatusCode=" . $this->data['StatusCode'];
        $hashString .= "&Message=" . $this->data['Message'];
        $hashString .= "&PreviousStatusCode=" . $this->data['PreviousStatusCode'];
        $hashString .= "&PreviousMessage=" . $this->data['PreviousMessage'];
        $hashString .= "&CrossReference=" . $this->data['CrossReference'];
        $hashString .= "&AddressNumericCheckResult=" . $this->data['AddressNumericCheckResult'];
        $hashString .= "&PostCodeCheckResult=" . $this->data['PostCodeCheckResult'];
        $hashString .= "&CV2CheckResult=" . $this->data['CV2CheckResult'];
        $hashString .= "&ThreeDSecureAuthenticationCheckResult=" . $this->data['ThreeDSecureAuthenticationCheckResult'];
        $hashString .= "&CardType=" . $this->data['CardType'];
        $hashString .= "&CardClass=" . $this->data['CardClass'];
        $hashString .= "&CardIssuer=" . $this->data['CardIssuer'];
        $hashString .= "&CardIssuerCountryCode=" . $this->data['CardIssuerCountryCode'];
        $hashString .= "&Amount=" . $this->data['Amount'];
        $hashString .= "&CurrencyCode=" . $this->data['CurrencyCode'];
        $hashString .= "&OrderID=" . $this->data['OrderID'];
        $hashString .= "&TransactionType=" . $this->data['TransactionType'];
        $hashString .= "&TransactionDateTime=" . $this->data['TransactionDateTime'];
        $hashString .= "&OrderDescription=" . $this->data['OrderDescription'];
        $hashString .= "&CustomerName=" . $this->data['CustomerName'];
        $hashString .= "&Address1=" . $this->data['Address1'];
        $hashString .= "&Address2=" . $this->data['Address2'];
        $hashString .= "&Address3=" . $this->data['Address3'];
        $hashString .= "&Address4=" . $this->data['Address4'];
        $hashString .= "&City=" . $this->data['City'];
        $hashString .= "&State=" . $this->data['State'];
        $hashString .= "&PostCode=" . $this->data['PostCode'];
        $hashString .= "&CountryCode=" . $this->data['CountryCode'];
        $hashString .= "&EmailAddress=" . $this->data['EmailAddress'];
        $hashString .= "&PhoneNumber=" . $this->data['PhoneNumber'];

        return sha1($hashString);
    }
}
