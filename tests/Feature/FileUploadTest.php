<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;

class FileUploadTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_upload_avatar()
    {
        Storage::fake('public');
        
        $user = User::factory()->create();
        $file = UploadedFile::fake()->image('avatar.jpg', 600, 600);
        
        $response = $this->actingAs($user)->post('/profile/avatar', [
            'avatar' => $file
        ]);
        
        $response->assertStatus(200);
        Storage::disk('public')->assertExists('avatars/' . $file->hashName());
    }

    public function test_uploaded_file_must_be_image()
    {
        Storage::fake('public');
        
        $user = User::factory()->create();
        $file = UploadedFile::fake()->create('document.pdf', 100);
        
        $response = $this->actingAs($user)->post('/profile/avatar', [
            'avatar' => $file
        ]);
        
        $response->assertSessionHasErrors('avatar');
    }

    public function test_uploaded_file_size_is_validated()
    {
        Storage::fake('public');
        
        $user = User::factory()->create();
        $file = UploadedFile::fake()->image('huge.jpg')->size(3000); // 3MB
        
        $response = $this->actingAs($user)->post('/profile/avatar', [
            'avatar' => $file
        ]);
        
        $response->assertSessionHasErrors('avatar');
    }

    public function test_old_file_is_deleted_when_uploading_new_one()
    {
        Storage::fake('public');
        
        $user = User::factory()->create(['avatar' => 'avatars/old-avatar.jpg']);
        Storage::disk('public')->put('avatars/old-avatar.jpg', 'content');
        
        $newFile = UploadedFile::fake()->image('new-avatar.jpg');
        
        $this->actingAs($user)->post('/profile/avatar', [
            'avatar' => $newFile
        ]);
        
        Storage::disk('public')->assertMissing('avatars/old-avatar.jpg');
        Storage::disk('public')->assertExists('avatars/' . $newFile->hashName());
    }

    public function test_multiple_files_can_be_uploaded()
    {
        Storage::fake('public');
        
        $user = User::factory()->create();
        $files = [
            UploadedFile::fake()->image('photo1.jpg'),
            UploadedFile::fake()->image('photo2.jpg'),
            UploadedFile::fake()->image('photo3.jpg'),
        ];
        
        $response = $this->actingAs($user)->post('/gallery/upload', [
            'photos' => $files
        ]);
        
        foreach ($files as $file) {
            Storage::disk('public')->assertExists('gallery/' . $file->hashName());
        }
    }

    public function test_file_can_be_downloaded()
    {
        Storage::fake('public');
        
        $filename = 'test-document.pdf';
        Storage::disk('public')->put($filename, 'PDF content');
        
        $response = $this->get('/download/' . $filename);
        
        $response->assertDownload($filename);
    }

    public function test_file_upload_to_s3()
    {
        Storage::fake('s3');
        
        $user = User::factory()->create();
        $file = UploadedFile::fake()->image('photo.jpg');
        
        $this->actingAs($user)->post('/upload-to-s3', [
            'file' => $file
        ]);
        
        Storage::disk('s3')->assertExists('uploads/' . $file->hashName());
    }
}