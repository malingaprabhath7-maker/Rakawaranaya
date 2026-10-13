<?php

namespace Tests\Unit;

use App\Support\Money;
use App\Support\Phone;
use PHPUnit\Framework\TestCase;

class SupportTest extends TestCase
{
    public function test_sri_lankan_numbers_become_whatsapp_numbers(): void
    {
        $this->assertSame('94771234567', Phone::international('077 123 4567'));
        $this->assertSame('94771234567', Phone::international('+94 77 123 4567'));
        $this->assertSame('94771234567', Phone::international('0094771234567'));
        $this->assertSame('94771234567', Phone::international('771234567'));
        $this->assertNull(Phone::international('123'));
        $this->assertNull(Phone::international(null));
        $this->assertSame('077 123 4567', Phone::display('94771234567'));
    }

    public function test_money_is_formatted_in_rupees(): void
    {
        $this->assertSame('Rs. 5,000', Money::format(5000));
        $this->assertSame('Rs. 1,250.50', Money::format(1250.5));
        $this->assertSame('1.25M', Money::short(1250000));
        $this->assertSame('850K', Money::short(850000));
        $this->assertSame('900', Money::short(900));
    }
}
