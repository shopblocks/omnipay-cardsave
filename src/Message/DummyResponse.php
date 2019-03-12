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
        if (is_array($this->form)) {
            return !empty($this->form['endpoint']) && $this->form['endpoint'] == "https://mms.cardsaveonlinepayments.com/Pages/PublicPages/PaymentForm.aspx" && !empty($this->form['HashDigest']) && preg_match('/\w+/', $this->form['HashDigest']);
        } else {
            return preg_match("/<form method='post' action='https:\/\/mms.cardsaveonlinepayments.com\/Pages\/PublicPages\/PaymentForm.aspx' id='cardsave-form'.*<\/form><script>.*cardsave-form'\)\.submit.*<\/script>/", $this->form);
        }
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
