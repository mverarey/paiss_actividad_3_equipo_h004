<?php

namespace Tests\Unit\Requests;

use Tests\TestCase;
use App\Http\Requests\StorePostRequest;
use Illuminate\Support\Facades\Validator;

class StorePostRequestTest extends TestCase
{
    public function test_title_is_required()
    {
        $request = new StorePostRequest();
        $validator = Validator::make(['title' => ''], $request->rules());
        
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('title', $validator->errors()->toArray());
    }

    public function test_title_must_be_at_least_5_characters()
    {
        $request = new StorePostRequest();
        $validator = Validator::make(['title' => 'Test'], $request->rules());
        
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('title', $validator->errors()->toArray());
    }

    public function test_content_is_required()
    {
        $request = new StorePostRequest();
        $validator = Validator::make(['content' => ''], $request->rules());
        
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('content', $validator->errors()->toArray());
    }

    public function test_published_must_be_boolean()
    {
        $request = new StorePostRequest();
        $validator = Validator::make([
            'title' => 'Valid Title',
            'content' => 'Valid content',
            'published' => 'not-a-boolean'
        ], $request->rules());
        
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('published', $validator->errors()->toArray());
    }

    public function test_valid_data_passes_validation()
    {
        $request = new StorePostRequest();
        $validator = Validator::make([
            'title' => 'Valid Post Title',
            'content' => 'This is valid content for the post.',
            'published' => true
        ], $request->rules());
        
        $this->assertFalse($validator->fails());
    }
}