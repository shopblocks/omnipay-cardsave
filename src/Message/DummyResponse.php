<?php

namespace Omnipay\CardSave\Message;

class DummyResponse extends AbstractResponse
{
    public function __construct(RequestInterface $request, $form)
    {
        $this->request = $request;

        $this->form = $form;
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
