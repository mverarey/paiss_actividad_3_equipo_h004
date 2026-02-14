<?php

namespace Tests\Unit\Helpers;

use Tests\TestCase;

class StringHelperTest extends TestCase
{
    public function test_truncate_string()
    {
        $string = 'This is a very long string that needs to be truncated';
        
        $result = str_truncate($string, 20);
        
        $this->assertEquals('This is a very lo...', $result);
    }

    public function test_slug_generation()
    {
        $title = 'Hello World! This is a Test';
        
        $slug = generate_slug($title);
        
        $this->assertEquals('hello-world-this-is-a-test', $slug);
    }

    public function test_mask_email()
    {
        $email = 'john.doe@example.com';
        
        $masked = mask_email($email);
        
        $this->assertEquals('jo****@example.com', $masked);
    }

    public function test_price_format()
    {
        $price = 1234.56;
        
        $formatted = format_price($price);
        
        $this->assertEquals('$1,234.56', $formatted);
    }
}