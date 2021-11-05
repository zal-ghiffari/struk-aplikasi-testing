<?php

namespace App;

use Illuminate\Support\Collection;

class EntryGenerator
{

    private $salesAccount = 'SALE';
    private $cashAccount = 'CASH';
    private $clientAccount = 'CLIENT';
    private $discountAccount = 'DISCOUNT';
    private $taxAccount = 'TAX';

    public function generate(Operation $operation): Collection
    {
        if ($operation instanceof Invoice) {
            return $this->generateInvoice($operation);
        }
    }

    public function generateInvoice(Invoice $invoice)
    {
        $entries = collect();

        $entries->add([
            'account' => $this->salesAccount,
            'amount' => -$invoice->subtotal(),
        ]);

        // add tax
        $entries->add([
            'account' => $this->taxAccount,
            'amount' => -$invoice->taxValue(),
        ]);

        // add discount
        $entries->add([
            'account' => $this->discountAccount,
            'amount' => $invoice->discountValue(),
        ]);

        // add payment
        // $entries->add([
        //     'account' => $this->cashAccount,
        //     'amount' => $invoice->totalPaid(),
        // ]);

        // add the remaining debit on account
        $entries->add([
            'account' => $this->clientAccount,
            'amount' => 0 - $entries->sum('amount'),
        ]);

        return $entries
            ->where('amount', '!=', 0)
            ->sortByDesc('amount')->values();
    }
}
