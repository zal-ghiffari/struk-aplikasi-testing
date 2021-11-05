<?php

namespace App;

class Payment
{
    public $amount;
    public $description;

    public function __construct($amount, $description = null)
    {
        $this->amount = $amount;
        $this->description = $description;
    }

}
