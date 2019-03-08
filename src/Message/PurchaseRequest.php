<?php

namespace Omnipay\CardSave\Message;

use DOMDocument;
use SimpleXMLElement;
use Omnipay\Common\Message\AbstractRequest;

/**
 * CardSave Purchase Request
 */
class PurchaseRequest extends AbstractRequest
{
    protected $endpoint = 'https://gw1.cardsaveonlinepayments.com:4430/';
    protected $namespace = 'https://www.thepaymentgateway.net/';

    private $INTEGRATION_TYPES = [
        'direct' => 0,
        'redirect' => 1
    ];

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

    public function getIntegrationType()
    {
        return $this->getParameter('integrationType');
    }

    public function setIntegrationType($integration)
    {
        return $this->setParameter('integrationType', $this->INTEGRATION_TYPES[$integration]);
    }

    public function getPreSharedKey()
    {
        return $this->getParameter('preSharedKey');
    }

    public function setPreSharedKey($preSharedKey)
    {
        return $this->setParameter('preSharedKey', $preSharedKey);
    }

    public function getOrderId()
    {
        return $this->getParameter('OrderId');
    }

    public function setOrderId($value)
    {
        return $this->setParameter('OrderId', $value);
    }

    public function getReturnForm()
    {
        return $this->getParameter('returnForm');
    }

    public function setReturnForm($value)
    {
        return $this->setParameter('returnForm', $value);
    }

    public function getData()
    {
        if ($this->getIntegrationType() === $this->INTEGRATION_TYPES['redirect']) {
            // A different endpoint is used for hosted payments
            $this->endpoint = 'https://mms.cardsaveonlinepayments.com/Pages/PublicPages/PaymentForm.aspx';

            $data = [];
            $data['HashDigest'] = "";
            $data['PreSharedKey'] = $this->getPreSharedKey();
            $data['MerchantID'] = $this->getMerchantId();
            $data['Amount'] = $this->getAmountInteger();
            $data['CurrencyCode'] = $this->getCurrencyNumeric();

            $data["EchoAVSCheckResult"] = 'false';
            $data["EchoCV2CheckResult"] = 'true';
            $data["EchoThreeDSecureAuthenticationCheckResult"] = 'false';
            $data["EchoCardType"] = 'false';

            $data['OrderID'] = $this->getTransactionId();
            $data['TransactionType'] = 'SALE';
            $now = \Carbon\Carbon::now();
            $data['TransactionDateTime'] = $now->format('Y-m-d H:i:s');
            $data['CallbackURL'] = $this->getNotifyUrl();
            
            $data["OrderDescription"] = '';
            $data["CustomerName"] = '';
            $data["Address1"] = '';
            $data["Address2"] = '';
            $data["Address3"] = '';
            $data["Address4"] = '';
            $data["City"] = '';
            $data["State"] = '';
            $data["PostCode"] = '';
            $data["CountryCode"] = '';
            $data["EmailAddress"] = '';
            $data["PhoneNumber"] = '';
            $data["EmailAddressEditable"] = 'false';
            $data["PhoneNumberEditable"] = 'false';

            $data['CV2Mandatory'] = 'true';
            $data['Address1Mandatory'] = 'false';
            $data['CityMandatory'] = 'false';
            $data['PostCodeMandatory'] = 'false';
            $data['StateMandatory'] = 'false';
            $data['CountryMandatory'] = 'false';

            $data["ResultDeliveryMethod"] = 'POST';
            $data["ServerResultURL"] = '';
            $data["PaymentFormDisplaysResult"] = 'false';
            $data['ThreeDSecureComapatMode'] = 'false';

            $hashDigest = $this->generateHash($data);
            $data['HashDigest'] = $hashDigest;

            unset($data['PreSharedKey']);

            return $data;
        }

        $this->validate('amount', 'card');
        $this->getCard()->validate();

        $data = new SimpleXMLElement('<CardDetailsTransaction/>');
        $data->addAttribute('xmlns', $this->namespace);

        $data->PaymentMessage->MerchantAuthentication['MerchantID'] = $this->getMerchantId();
        $data->PaymentMessage->MerchantAuthentication['Password'] = $this->getPassword();
        $data->PaymentMessage->TransactionDetails['Amount'] = $this->getAmountInteger();
        $data->PaymentMessage->TransactionDetails['CurrencyCode'] = $this->getCurrencyNumeric();
        $data->PaymentMessage->TransactionDetails->OrderID = $this->getTransactionId();
        $data->PaymentMessage->TransactionDetails->OrderDescription = $this->getDescription();
        $data->PaymentMessage->TransactionDetails->MessageDetails['TransactionType'] = 'SALE';

        $data->PaymentMessage->CardDetails->CardName = $this->getCard()->getName();
        $data->PaymentMessage->CardDetails->CardNumber = $this->getCard()->getNumber();
        $data->PaymentMessage->CardDetails->ExpiryDate['Month'] = $this->getCard()->getExpiryDate('m');
        $data->PaymentMessage->CardDetails->ExpiryDate['Year'] = $this->getCard()->getExpiryDate('y');
        $data->PaymentMessage->CardDetails->CV2 = $this->getCard()->getCvv();

        if ($this->getCard()->getIssueNumber()) {
            $data->PaymentMessage->CardDetails->IssueNumber = $this->getCard()->getIssueNumber();
        }

        if ($this->getCard()->getStartMonth() && $this->getCard()->getStartYear()) {
            $data->PaymentMessage->CardDetails->StartDate['Month'] = $this->getCard()->getStartDate('m');
            $data->PaymentMessage->CardDetails->StartDate['Year'] = $this->getCard()->getStartDate('y');
        }

        $data->PaymentMessage->CustomerDetails->BillingAddress->Address1 = $this->getCard()->getAddress1();
        $data->PaymentMessage->CustomerDetails->BillingAddress->Address2 = $this->getCard()->getAddress2();
        $data->PaymentMessage->CustomerDetails->BillingAddress->City = $this->getCard()->getCity();
        $data->PaymentMessage->CustomerDetails->BillingAddress->PostCode = $this->getCard()->getPostcode();
        $data->PaymentMessage->CustomerDetails->BillingAddress->State = $this->getCard()->getState();
        // requires numeric country code
        // $data->PaymentMessage->CustomerDetails->BillingAddress->CountryCode = $this->getCard()->getCountryNumeric;
        $data->PaymentMessage->CustomerDetails->CustomerIPAddress = $this->getClientIp();

        return $data;
    }

    public function sendData($data)
    {
        if ($this->getIntegrationType() === $this->INTEGRATION_TYPES['redirect'] && is_array($data)) {
            $form = "<form method='post' action='{$this->endpoint}' id='cardsave-form'>";
            foreach ($data as $key => $value) {
                $form .= "<input type='hidden' name='{$key}' value='{$value}'>";
            }
            $form .= "</form>";

            $form .= "<script>document.getElementById('cardsave-form').submit();</script>";

            if ($this->getReturnForm()) {
                return $form;
            }

            echo ($form);
            exit;
        }

        // the PHP SOAP library sucks, and SimpleXML can't append element trees
        // TODO: find PSR-0 SOAP library
        $document = new DOMDocument('1.0', 'utf-8');
        $envelope = $document->appendChild(
            $document->createElementNS('http://schemas.xmlsoap.org/soap/envelope/', 'soap:Envelope')
        );
        $envelope->setAttribute('xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');
        $envelope->setAttribute('xmlns:xsd', 'http://www.w3.org/2001/XMLSchema');
        $body = $envelope->appendChild($document->createElement('soap:Body'));
        $body->appendChild($document->importNode(dom_import_simplexml($data), true));

        // post to Cardsave
        $headers = array(
            'Content-Type' => 'text/xml; charset=utf-8',
            'SOAPAction' => $this->namespace.$data->getName());

        $httpResponse = $this->httpClient->post($this->endpoint, $headers, $document->saveXML())->send();

        return $this->response = new Response($this, $httpResponse->getBody());
    }

    private function generateHash($data)
    {
        $hashString = "";

        $hashString .= "PreSharedKey=" . ($data['PreSharedKey'] ?? '');
        $hashString .= "&MerchantID=" . ($data['MerchantID'] ?? '');
        $hashString .= "&Password=" . ($this->getPassword() ?? '');
        $hashString .= "&Amount=" . ($data['Amount'] ?? 0);
        $hashString .= "&CurrencyCode=" . ($data['CurrencyCode'] ?? '');

        $hashString .= "&EchoAVSCheckResult=false";
        $hashString .= "&EchoCV2CheckResult=true";
        $hashString .= "&EchoThreeDSecureAuthenticationCheckResult=false";
        $hashString .= "&EchoCardType=false";

        $hashString .= "&OrderID=" . ($data['OrderID'] ?? '');
        $hashString .= "&TransactionType=" . ($data['TransactionType'] ?? 'SALE');
        $hashString .= "&TransactionDateTime=" . ($data['TransactionDateTime'] ?? '');

        $hashString .= "&CallbackURL=" . ($data['CallbackURL'] ?? '');
        $hashString .= "&OrderDescription=" . ($data['OrderDescription'] ?? '');

        $hashString .= "&CustomerName=" . ($data['CustomerName'] ?? '');
        $hashString .= "&Address1=" . ($data['Address1'] ?? '');
        $hashString .= "&Address2=" . ($data['Address2'] ?? '');
        $hashString .= "&Address3=" . ($data['Address3'] ?? '');
        $hashString .= "&Address4=" . ($data['Address4'] ?? '');
        $hashString .= "&City=" . ($data['City'] ?? '');
        $hashString .= "&State=" . ($data['State'] ?? '');
        $hashString .= "&PostCode=" . ($data['PostCode'] ?? '');
        $hashString .= "&CountryCode=" . ($data['CountryCode'] ?? '');

        $hashString .= "&EmailAddress=";
        $hashString .= "&PhoneNumber=";
        $hashString .= "&EmailAddressEditable=false";
        $hashString .= "&PhoneNumberEditable=false";

        $hashString .= "&CV2Mandatory=true";
        $hashString .= "&Address1Mandatory=false";
        $hashString .= "&CityMandatory=false";
        $hashString .= "&PostCodeMandatory=false";
        $hashString .= "&StateMandatory=false";
        $hashString .= "&CountryMandatory=false";
        $hashString .= "&ResultDeliveryMethod=POST";

        $hashString .= "&ServerResultURL=";
        $hashString .= "&PaymentFormDisplaysResult=false";

        return sha1($hashString);
    }
}
