<?php

namespace Tests\Integration\Authorization;

use Tests\TestCase;
use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RolePermissionTest extends TestCase
{
    use RefreshDatabase;

    public function test_role_based_access_control_flow()
    {
        // 1. Crear permisos
        $viewPosts = Permission::create(['name' => 'view-posts']);
        $createPosts = Permission::create(['name' => 'create-posts']);
        $editPosts = Permission::create(['name' => 'edit-posts']);
        $deletePosts = Permission::create(['name' => 'delete-posts']);
        $manageUsers = Permission::create(['name' => 'manage-users']);
        
        // 2. Crear roles
        $viewer = Role::create(['name' => 'viewer']);
        $viewer->permissions()->attach([$viewPosts->id]);
        
        $editor = Role::create(['name' => 'editor']);
        $editor->permissions()->attach([
            $viewPosts->id,
            $createPosts->id,
            $editPosts->id
        ]);
        
        $admin = Role::create(['name' => 'admin']);
        $admin->permissions()->attach([
            $viewPosts->id,
            $createPosts->id,
            $editPosts->id,
            $deletePosts->id,
            $manageUsers->id
        ]);
        
        // 3. Crear usuarios con diferentes roles
        $viewerUser = User::factory()->create();
        $viewerUser->roles()->attach($viewer);
        
        $editorUser = User::factory()->create();
        $editorUser->roles()->attach($editor);
        
        $adminUser = User::factory()->create();
        $adminUser->roles()->attach($admin);
        
        $post = Post::factory()->create();
        
        // 4. Viewer puede ver pero no crear
        $viewResponse = $this->actingAs($viewerUser)->get('/posts');
        $viewResponse->assertStatus(200);
        
        $createResponse = $this->actingAs($viewerUser)->post('/posts', [
            'title' => 'Test',
            'content' => 'Content'
        ]);
        $createResponse->assertStatus(403);
        
        // 5. Editor puede crear y editar
        $editorCreateResponse = $this->actingAs($editorUser)->post('/posts', [
            'title' => 'Editor Post',
            'content' => 'Content'
        ]);
        $editorCreateResponse->assertStatus(201);
        
        $editorEditResponse = $this->actingAs($editorUser)->put("/posts/{$post->id}", [
            'title' => 'Updated Title'
        ]);
        $editorEditResponse->assertStatus(200);
        
        // 6. Editor no puede eliminar
        $editorDeleteResponse = $this->actingAs($editorUser)->delete("/posts/{$post->id}");
        $editorDeleteResponse->assertStatus(403);
        
        // 7. Admin puede hacer todo
        $adminDeleteResponse = $this->actingAs($adminUser)->delete("/posts/{$post->id}");
        $adminDeleteResponse->assertStatus(204);
        
        $manageUsersResponse = $this->actingAs($adminUser)->get('/admin/users');
        $manageUsersResponse->assertStatus(200);
    }

    public function test_dynamic_permission_assignment()
    {
        $user = User::factory()->create();
        $role = Role::create(['name' => 'custom-role']);
        $permission = Permission::create(['name' => 'special-action']);
        
        // Usuario sin permiso
        $this->assertFalse($user->hasPermission('special-action'));
        
        // Asignar rol con permiso
        $role->permissions()->attach($permission);
        $user->roles()->attach($role);
        
        // Ahora tiene permiso
        $user->refresh();
        $this->assertTrue($user->hasPermission('special-action'));
        
        // Revocar permiso del rol
        $role->permissions()->detach($permission);
        
        $user->refresh();
        $this->assertFalse($user->hasPermission('special-action'));
    }

    public function test_permission_inheritance_through_multiple_roles()
    {
        $user = User::factory()->create();
        
        $roleA = Role::create(['name' => 'role-a']);
        $permA = Permission::create(['name' => 'perm-a']);
        $roleA->permissions()->attach($permA);
        
        $roleB = Role::create(['name' => 'role-b']);
        $permB = Permission::create(['name' => 'perm-b']);
        $roleB->permissions()->attach($permB);
        
        // Asignar ambos roles
        $user->roles()->attach([$roleA->id, $roleB->id]);
        
        $user->refresh();
        
        // Usuario tiene permisos de ambos roles
        $this->assertTrue($user->hasPermission('perm-a'));
        $this->assertTrue($user->hasPermission('perm-b'));
    }

    public function test_context_based_authorization()
    {
        $author = User::factory()->create();
        $otherUser = User::factory()->create();
        
        $post = Post::factory()->create(['user_id' => $author->id]);
        
        // Autor puede editar su propio post
        $authorEdit = $this->actingAs($author)->put("/posts/{$post->id}", [
            'title' => 'Updated by author'
        ]);
        $authorEdit->assertStatus(200);
        
        // Otro usuario no puede editar
        $otherUserEdit = $this->actingAs($otherUser)->put("/posts/{$post->id}", [
            'title' => 'Updated by other'
        ]);
        $otherUserEdit->assertStatus(403);
        
        // Admin puede editar cualquier post
        $admin = User::factory()->create();
        $adminRole = Role::create(['name' => 'admin']);
        $editAny = Permission::create(['name' => 'edit-any-post']);
        $adminRole->permissions()->attach($editAny);
        $admin->roles()->attach($adminRole);
        
        $adminEdit = $this->actingAs($admin)->put("/posts/{$post->id}", [
            'title' => 'Updated by admin'
        ]);
        $adminEdit->assertStatus(200);
    }
}