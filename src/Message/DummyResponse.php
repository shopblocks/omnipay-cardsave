<?php

namespace Omnipay\CardSave\Message;

use Omnipay\Common\Message\AbstractResponse;

class DummyResponse extends AbstractResponse
{
    public function __construct($form)
    {
        $this->form = $form;

        return $this;
    }

    public function isSuccessful()
    {
        return preg_match("/<html>.*<form method='post' action='https:\/\/mms.cardsaveonlinepayments.com\/Pages\/PublicPages\/PaymentForm.aspx' id='cardsave-form'.*<\/form><script>.*cardsave-form'\)\.submit.*<\/script>.*<\/html>/", $this->form);
    }

    public function isForm()
    {
        return true;
    }

    public function getData()
    {
        return $this->form;
    }
}
