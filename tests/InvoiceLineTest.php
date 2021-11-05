<?php

declare (strict_types = 1);

use App\InvoiceLine;
use PHPUnit\Framework\TestCase;

final class InvoiceLineTest extends TestCase
{
    /** @test */
    public function totals_should_be_0_for_fresh_instances(): void
    {
        $line = new InvoiceLine("Item", 0);
        $this->assertEquals(0, $line->grossTotal());
        $this->assertEquals(0, $line->totalAfterDiscount());
        $this->assertEquals(0, $line->totalAfterTax());
    }

    /** @test */
    public function it_should_have_an_item_name()
    {
        $line = new InvoiceLine("Brushing", 10);

        $this->assertSame("Brushing", $line->item);
    }

    /** @test */
    public function total_should_equal_to_the_product_of_quantity_and_unit_price()
    {
        $line = new InvoiceLine("Brushing", 10, 5);

        $this->assertSame(50, $line->grossTotal());
    }

    /** @test */
    public function gross_total_should_equal_to_0_when_quantity_or_price_are_0()
    {
        $line = new InvoiceLine("Brushing", 0, 5);
        $this->assertSame(0, $line->grossTotal());

        $line = new InvoiceLine("Brushing", 10, 0);
        $this->assertSame(0, $line->grossTotal());
    }

    /** @test */
    public function after_discount_total_should_equal_to_gross_total_when_no_discount_provided()
    {
        $line = new InvoiceLine("Brushing", 10, 1, 0);
        $this->assertSame($line->grossTotal(), $line->totalAfterDiscount());
    }

    public function discount_value_calculation()
    {
        $line = new InvoiceLine("Item", 100, 2, 0.1);
        $this->assertSame(20, $line->discountValue());
    }

    /**
     * @test
     * @testWith [-1]
     *           [0]
     *           [-100]
     *           [-0.4]
     *           [-0.0]
     *           [-99999]
     */
    public function discount_and_tax_rates_should_be_always_be_0_for_values_less_than_0($value)
    {
        $line = new InvoiceLine("Brushing", 10, 1, $value);
        $this->assertSame(0, $line->discountRate);

        $line = new InvoiceLine("Brushing", 10, 1, 0, $value);
        $this->assertSame(0, $line->taxRate);
    }

    /**
     * @test
     * @testWith [1]
     *          [1.0]
     *          [1.1]
     *          [100]
     */
    public function discount_and_tax_rates_should_be_always_be_1_for_values_greater_than_1($value)
    {
        $line = new InvoiceLine("Brushing", 10, 1, $value);
        $this->assertSame(1, $line->discountRate);

        $line = new InvoiceLine("Brushing", 10, 1, 0, $value);
        $this->assertSame(1, $line->taxRate);
    }

    /** @test */
    public function after_discount_total_should_take_discount_rate_into_consideration()
    {
        $line = new InvoiceLine("Brushing", 10, 1, 0.1);
        $this->assertSame(1.0, $line->discountValue());
        $this->assertSame(9.0, $line->totalAfterDiscount());
    }

    /** @test */
    public function after_tax_total_should_take_tax_rate_into_consideration()
    {
        $line = new InvoiceLine("Brushing", 10, 1, 0, 0.1);
        $this->assertSame(1.0, $line->taxValue());
        $this->assertSame(11.0, $line->totalAfterTax());
    }
}
