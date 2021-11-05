<?php

namespace App;

class Invoice extends Operation
{

    /** @var InvoiceLine[] */
    private $lines = [];

    /** @var Payment[] */
    private $payments = [];

    private $discountRate = 0;

    private $isDiscountAfterTax = false;

    public function addLine(InvoiceLine $line): Invoice
    {
        $this->lines[] = $line;
        return $this;
    }

    public function setDiscountRate($rate): Invoice
    {
        $this->discountRate = $this->ensureBetween($rate, 0, 1);
        return $this;
    }

    public function getDiscountRate()
    {
        return $this->discountRate;
    }

    public function getLines(): array
    {
        return $this->lines;
    }

    public function addPayment(Payment $payment): Invoice
    {
        $this->payments[] = $payment;
        return $this;
    }

    public function getPayments(): array
    {
        return $this->payments;
    }

    public function subtotal()
    {
        $sum = 0;

        foreach ($this->lines as $line) {
            $sum += $line->totalAfterDiscount();
        }

        return $sum;
    }

    public function afterDiscount()
    {
        $before = $this->isDiscountAfterTax ? $this->afterTax() : $this->subtotal();
        return $before - $this->discountValue();
    }

    public function afterTax()
    {
        $isTaxAfterDiscount = !$this->isDiscountAfterTax;

        $before = $isTaxAfterDiscount ? $this->afterDiscount() : $this->subtotal();

        return $before + $this->taxValue();
    }

    public function total()
    {
        $total = $this->isDiscountAfterTax ? $this->afterDiscount() : $this->afterTax();

        // todo: add other adjustments, like positive/negative rounding adjustment,
        // shiping charges, etc...

        return $total;
    }

    public function discountValue()
    {
        $before = $this->isDiscountAfterTax ? $this->afterTax() : $this->subtotal();
        return $this->discountRate * $before;
    }

    public function taxValue()
    {
        $sum = 0;

        if (!$this->isDiscountAfterTax) {
            // we should distribute the discount on all items
            // then calculate the taxable percentage
            $discount = $this->discountValue();

            $taxable = 0;

            foreach ($this->lines as $line) {
                if ($line->taxRate() > 0) {
                    // calculate item discount
                    // we consider that $line->totalAfterDiscount is the
                    // real subtotal for item
                    $itemTotal = $line->totalAfterDiscount();
                    $itemContribution = $itemTotal / $this->subtotal();
                    $itemDiscount = $itemContribution * $discount;

                    $taxable += ($itemTotal - $itemDiscount) * $line->taxRate();
                }
            }

            return $taxable;
        }

        foreach ($this->lines as $line) {
            $sum += $line->totalAfterDiscount() * $line->taxRate();
        }

        return $sum;
    }

    public function isDiscountAfterTax()
    {
        if (\func_num_args() === 0) {
            return $this->isDiscountAfterTax;
        }

        $this->isDiscountAfterTax = \func_get_arg(0);

        return $this;
    }

}
