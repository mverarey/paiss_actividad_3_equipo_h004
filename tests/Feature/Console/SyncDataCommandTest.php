<?php

namespace Tests\Feature\Console;

use Tests\TestCase;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

class SyncDataCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_command_syncs_data_successfully()
    {
        Http::fake([
            'https://api.example.com/products' => Http::response([
                ['name' => 'Product 1', 'price' => 100],
                ['name' => 'Product 2', 'price' => 200]
            ], 200)
        ]);
        
        $this->artisan('app:sync-data')
             ->expectsOutput('Syncing data from external API...')
             ->expectsOutput('Data synced successfully!')
             ->assertExitCode(0);
        
        $this->assertDatabaseHas('products', ['name' => 'Product 1']);
        $this->assertDatabaseHas('products', ['name' => 'Product 2']);
    }

    public function test_command_accepts_arguments()
    {
        $this->artisan('app:sync-data', ['--source' => 'api'])
             ->assertExitCode(0);
    }

    public function test_command_handles_errors_gracefully()
    {
        Http::fake([
            'https://api.example.com/products' => Http::response([], 500)
        ]);
        
        $this->artisan('app:sync-data')
             ->expectsOutput('Error syncing data')
             ->assertExitCode(1);
    }

    public function test_command_shows_progress_bar()
    {
        $this->artisan('app:sync-data')
             ->expectsOutput('Processing...')
             ->assertExitCode(0);
    }
}