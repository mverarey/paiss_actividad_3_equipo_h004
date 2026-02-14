<?php

namespace Tests\Unit\Helpers;

use Tests\TestCase;

class DateHelperTest extends TestCase
{
    public function test_format_date_returns_correct_format()
    {
        $date = '2026-02-14';
        
        $formatted = format_date($date);
        
        $this->assertEquals('14/02/2026', $formatted);
    }

    public function test_human_readable_date()
    {
        $date = now()->subDays(2);
        
        $result = human_date($date);
        
        $this->assertEquals('2 days ago', $result);
    }

    public function test_calculate_age_from_birth_date()
    {
        $birthDate = now()->subYears(25)->subDays(10);
        
        $age = calculate_age($birthDate);
        
        $this->assertEquals(25, $age);
    }

    public function test_is_weekend_returns_true_for_saturday()
    {
        $saturday = \Carbon\Carbon::parse('2026-02-14'); // Es sábado
        
        $this->assertTrue(is_weekend($saturday));
    }

    public function test_is_weekend_returns_false_for_monday()
    {
        $monday = \Carbon\Carbon::parse('2026-02-16');
        
        $this->assertFalse(is_weekend($monday));
    }

    public function test_business_days_between_dates()
    {
        $start = \Carbon\Carbon::parse('2026-02-09'); // Lunes
        $end = \Carbon\Carbon::parse('2026-02-13');   // Viernes
        
        $days = business_days_between($start, $end);
        
        $this->assertEquals(5, $days);
    }
}