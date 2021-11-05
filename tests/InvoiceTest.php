<?php declare (strict_types = 1);

use App\Invoice;
use App\InvoiceLine;
use PHPUnit\Framework\TestCase;

final class InvoiceTest extends TestCase
{
    /** @test */
    public function it_should_have_an_array_of_entries(): void
    {
        $invoice = new Invoice;
        $this->assertTrue(true);
    }

    /** @test */
    public function subtotal_includes_all_items()
    {
        $invoice = new Invoice;
        $invoice->addLine(new InvoiceLine('Item 1', 100));
        $invoice->addLine(new InvoiceLine('Item 2', 200));

        $this->assertEquals(300, $invoice->subtotal());
    }

    /** @test */
    public function subtotal_includes_all_items_excluding_items_discount()
    {
        $invoice = new Invoice;
        $invoice->addLine(new InvoiceLine('Item 1', 100, 1, 0.2)); // discount 20
        $invoice->addLine(new InvoiceLine('Item 2', 200));

        $this->assertEquals(300 - 20, $invoice->subtotal());
    }

    /** @test */
    public function subtotal_should_not_include_item_taxes_at_all()
    {
        $invoice = new Invoice;
        $invoice->addLine(new InvoiceLine('Item 1', 100, 1, 0, 0.1)); // tax 10
        $invoice->addLine(new InvoiceLine('Item 2', 200));

        $this->assertNotEquals(310, $invoice->subtotal());
        $this->assertEquals(300, $invoice->subtotal());
    }

    /** @test */
    public function discount_should_be_calculated_before_tax_when_isDiscountAfterTax_is_false()
    {
        $invoice = new Invoice;
        $invoice->isDiscountAfterTax(false);
        $invoice->addLine(new InvoiceLine('Item 1', 100, 1, 0, 0.2)); // tax 10%

        $invoice->setDiscountRate(0.1); // 10% discount

        $this->assertEquals(100, $invoice->subtotal());
        $this->assertEquals(10, $invoice->discountValue());
        $this->assertEquals(100 - (100 * 0.1), $invoice->afterDiscount());
    }

    /** @test */
    public function discount_should_be_calculated_after_tax_when_isDiscountAfterTax_is_true()
    {
        $invoice = new Invoice;
        $invoice->isDiscountAfterTax(true);
        $invoice->addLine(new InvoiceLine('Item 1', 100, 1, 0, 0.2)); // tax 20%

        $invoice->setDiscountRate(0.1); // 10% discount

        $this->assertEquals(100, $invoice->subtotal());

        $this->assertEquals(100 * 1.2 * 0.1, $invoice->discountValue());

        $this->assertEquals(100 * 1.2 * 0.9, $invoice->afterDiscount());

        $this->assertEquals(100 * 1.2 * 0.9, $invoice->total());
    }

    /** @test */
    public function discount_should_be_deducted_from_items_before_tax_when_isDiscountAfterTax_is_false()
    {
        $invoice = new Invoice;
        $invoice->isDiscountAfterTax(false);

        $invoice->addLine((new InvoiceLine('Taxable item', 100))->taxRate(0.2)); // tax 20%
        $invoice->addLine(new InvoiceLine('Non taxable item', 900));

        $invoice->setDiscountRate(0.1); // 10% discount

        $this->assertEquals(1000, $invoice->subtotal());

        $this->assertEquals(100, $invoice->discountValue());
        $this->assertEquals(900, $invoice->afterDiscount());

        $this->assertEquals(18, $invoice->taxValue());
        $this->assertEquals(918, $invoice->afterTax());

        $this->assertEquals(918, $invoice->total());
    }

    /** @test */
    public function discount_should_not_be_deducted_from_items_before_tax_when_isDiscountAfterTax_is_true()
    {
        $invoice = new Invoice;
        $invoice->isDiscountAfterTax(true);

        $invoice->addLine((new InvoiceLine('Taxable item', 100))->taxRate(0.2)); // tax 20%
        $invoice->addLine(new InvoiceLine('Non taxable item', 900));

        $invoice->setDiscountRate(0.1); // 10% discount

        $this->assertEquals(1000, $invoice->subtotal());

        $this->assertEquals(20, $invoice->taxValue());
        $this->assertEquals(1020, $invoice->afterTax());

        $this->assertEquals(102, $invoice->discountValue());
        $this->assertEquals(1020 - 102, $invoice->afterDiscount());

        $this->assertEquals(1020 - 102, $invoice->total());
    }

    /** @test */
    public function afterTax_should_ignore_discount_when_isDiscountAfterTax_is_true()
    {
        $invoice = new Invoice;
        $invoice->isDiscountAfterTax(true);
        $invoice->addLine(new InvoiceLine('Item 1', 100, 1, 0, 0.2)); // tax 20%

        $invoice->setDiscountRate(0.1); // 10% discount

        $this->assertEquals(100, $invoice->subtotal());

        $this->assertEquals(100 * 0.2, $invoice->taxValue());
        $this->assertEquals(100 * 1.2, $invoice->afterTax());
    }
}
