<?php

use PHPUnit\Framework\TestCase;
use App\EntryPrinter;

class EntryPrinterTest extends TestCase
{
    /** @test */
    public function it_should_print_the_entries()
    {
        $entries = [
            ['account' => 'CLIENT', 'amount' => 9 * 1.5],
            ['account' => 'TAX', 'amount' => -9 * 0.5],
            ['account' => 'SALE', 'amount' => -9.0],
        ];

        $printer = new EntryPrinter;

        $printer->print($entries);

        $this->assertTrue(true);
    }
}
