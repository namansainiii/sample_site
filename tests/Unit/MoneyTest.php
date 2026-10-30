<?php

namespace Tests\Unit;

use App\Support\Money;
use PHPUnit\Framework\TestCase;

class MoneyTest extends TestCase
{
    public function test_formats_with_indian_digit_grouping(): void
    {
        $this->assertSame('₹0.00', Money::inr(0));
        $this->assertSame('₹999.00', Money::inr(999));
        $this->assertSame('₹1,000.00', Money::inr(1000));
        $this->assertSame('₹1,23,456.50', Money::inr('123456.5'));
        $this->assertSame('₹1,23,45,678.99', Money::inr(12345678.99));
        $this->assertSame('-₹1,500.00', Money::inr(-1500));
    }
}
