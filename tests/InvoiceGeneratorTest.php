<?php

use App\EntryGenerator;
use App\Invoice;
use App\InvoiceLine;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use PHPUnit\Framework\TestCase;

class InvoiceGeneratorTest extends TestCase
{

    /** @var EntryGenerator  */
    private $generator;

    public function setUp(): void
    {
        $this->generator = new EntryGenerator();

        Collection::macro('select', function () {
            $keys = func_get_args();
            return $this->map(function ($value) use ($keys) {
                return Arr::only($value, $keys);
            });
        });
    }

    /** @test */
    public function it_should_return_an_array()
    {
        $invoice = new Invoice;

        $this->assertIsArray($this->generator->generate($invoice)->toArray());
    }

    /** @test */
    public function debit_should_equals_to_credit()
    {
        $invoice = new Invoice;
        $invoice->addLine(new InvoiceLine('Item 1', 100));
        $invoice->addLine(new InvoiceLine('Item 2', 100));

        $entries = $this->generator->generate($invoice);

        $this->assertEquals(0, $entries->sum('amount'));
    }

    /** @test */
    public function simple_sales_on_account()
    {
        $invoice = new Invoice;

        $invoice->addLine(
            new InvoiceLine('Item 1', 10, 1)
        );

        $entries = $this->generator->generate($invoice);

        $this->assertEquals([
            ['account' => 'CLIENT', 'amount' => 10],
            ['account' => 'SALE', 'amount' => -10],
        ], $entries->toArray());
    }

    /** @test */
    public function sales_with_item_discount()
    {
        $invoice = new Invoice;

        $invoice->addLine(
            new InvoiceLine('Item 1', 10, 1, 0.1)
        );

        $entries = $this->generator->generate($invoice);

        $this->assertEquals([
            ['account' => 'CLIENT', 'amount' => 9.0],
            ['account' => 'SALE', 'amount' => -9.0],
        ], $entries->toArray());
    }

    /** @test */
    public function sales_with_tax()
    {
        $invoice = new Invoice;

        $invoice->addLine(
            new InvoiceLine('Item 1', 10, 1, 0, 0.5)
        );

        $entries = $this->generator->generate($invoice);

        $this->assertEquals([
            ['account' => 'CLIENT', 'amount' => 15],
            ['account' => 'TAX', 'amount' => -5.0],
            ['account' => 'SALE', 'amount' => -10],
        ], $entries->select('account', 'amount')->toArray());
    }

    /** @test */
    public function sales_with_discount_and_tax()
    {
        $invoice = new Invoice;

        $invoice->addLine(
            new InvoiceLine('Item 1', 10, 1, 0.1, 0.5)
        );

        $entries = $this->generator->generate($invoice);

        $this->assertEquals([
            ['account' => 'CLIENT', 'amount' => 9 * 1.5],
            ['account' => 'TAX', 'amount' => -9 * 0.5],
            ['account' => 'SALE', 'amount' => -9.0],
        ], $entries->select('account', 'amount')->toArray());
    }

    /** @test */
    public function sales_with_global_discount()
    {
        $invoice = new Invoice;

        $invoice->addLine(new InvoiceLine('Item', 100, 1));

        $invoice->setDiscountRate(.2);

        $entries = $this->generator->generate($invoice);

        $this->assertCount(3, $entries);

        $this->assertEquals([
            ['account' => 'CLIENT', 'amount' => 80],
            ['account' => 'DISCOUNT', 'amount' => 20],
            ['account' => 'SALE', 'amount' => -100],
        ], $entries->toArray());
    }
}
