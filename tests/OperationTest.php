<?php

use PHPUnit\Framework\TestCase;
use App\Operation;

class OperationTest extends TestCase
{
    /** @test */
    public function it_should_operation()
    {

        $operation = new Operation;

        $operation->ensureBetween(10, 9, 3);

        $this->assertTrue(true);
    }
}
