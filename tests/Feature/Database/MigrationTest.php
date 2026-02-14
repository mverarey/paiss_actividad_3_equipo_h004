<?php

namespace Tests\Feature\Database;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;

class MigrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_users_table_has_expected_columns()
    {
        $this->assertTrue(Schema::hasTable('users'));
        $this->assertTrue(Schema::hasColumn('users', 'id'));
        $this->assertTrue(Schema::hasColumn('users', 'name'));
        $this->assertTrue(Schema::hasColumn('users', 'email'));
        $this->assertTrue(Schema::hasColumn('users', 'password'));
        $this->assertTrue(Schema::hasColumn('users', 'created_at'));
        $this->assertTrue(Schema::hasColumn('users', 'updated_at'));
    }

    public function test_posts_table_has_foreign_key_to_users()
    {
        $this->assertTrue(Schema::hasTable('posts'));
        $this->assertTrue(Schema::hasColumn('posts', 'user_id'));
    }

    public function test_soft_deletes_column_exists()
    {
        $this->assertTrue(Schema::hasColumn('posts', 'deleted_at'));
    }

    public function test_database_transaction_rollback()
    {
        \DB::beginTransaction();
        
        $user = \App\Models\User::factory()->create();
        $this->assertDatabaseHas('users', ['id' => $user->id]);
        
        \DB::rollBack();
        
        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }

    public function test_unique_constraint_on_email()
    {
        \App\Models\User::factory()->create(['email' => 'test@example.com']);
        
        $this->expectException(\Illuminate\Database\QueryException::class);
        
        \App\Models\User::factory()->create(['email' => 'test@example.com']);
    }

    public function test_cascade_delete_removes_related_records()
    {
        $user = \App\Models\User::factory()->create();
        $post = \App\Models\Post::factory()->create(['user_id' => $user->id]);
        
        $user->delete();
        
        // Asumiendo que tienes cascade delete configurado
        $this->assertDatabaseMissing('posts', ['id' => $post->id]);
    }

    public function test_factory_creates_valid_model()
    {
        $user = \App\Models\User::factory()->create();
        
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'email' => $user->email
        ]);
        
        $this->assertNotNull($user->email);
        $this->assertIsString($user->name);
    }
}