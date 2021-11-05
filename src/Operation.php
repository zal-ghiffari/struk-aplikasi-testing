<?php

namespace App;

class Operation
{
    protected function ensureBetween($value, $min, $max)
    {
        return min($max, max($min, $value));
    }
}
