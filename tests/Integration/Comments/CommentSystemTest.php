<?php

namespace Tests\Integration\Comments;

use Tests\TestCase;
use App\Models\User;
use App\Models\Post;
use App\Models\Comment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use App\Events\CommentPosted;
use App\Notifications\NewCommentNotification;

class CommentSystemTest extends TestCase
{
    use RefreshDatabase;

    public function test_complete_comment_flow_with_notifications()
    {
        Event::fake();
        Notification::fake();
        
        // 1. Crear autor del post
        $author = User::factory()->create(['name' => 'Post Author']);
        $post = Post::factory()->create(['user_id' => $author->id]);
        
        // 2. Usuario comenta en el post
        $commenter = User::factory()->create(['name' => 'Commenter']);
        
        $response = $this->actingAs($commenter)->postJson("/api/posts/{$post->id}/comments", [
            'content' => 'Great post! Very informative.'
        ]);
        
        $response->assertStatus(201);
        $response->assertJsonStructure([
            'data' => ['id', 'content', 'user', 'created_at']
        ]);
        
        // 3. Verificar comentario en base de datos
        $this->assertDatabaseHas('comments', [
            'post_id' => $post->id,
            'user_id' => $commenter->id,
            'content' => 'Great post! Very informative.'
        ]);
        
        // 4. Verificar que se disparó el evento
        Event::assertDispatched(CommentPosted::class);
        
        // 5. Verificar que el autor recibió notificación
        Notification::assertSentTo(
            $author,
            NewCommentNotification::class,
            function ($notification) use ($commenter) {
                return $notification->comment->user_id === $commenter->id;
            }
        );
        
        // 6. Responder al comentario (nested comment)
        $comment = Comment::where('post_id', $post->id)->first();
        
        $replyResponse = $this->actingAs($author)->postJson("/api/comments/{$comment->id}/reply", [
            'content' => 'Thank you for your feedback!'
        ]);
        
        $replyResponse->assertStatus(201);
        
        // 7. Verificar estructura de comentarios anidados
        $this->assertDatabaseHas('comments', [
            'parent_id' => $comment->id,
            'user_id' => $author->id,
            'content' => 'Thank you for your feedback!'
        ]);
        
        // 8. Obtener comentarios con respuestas
        $getResponse = $this->getJson("/api/posts/{$post->id}/comments");
        
        $getResponse->assertStatus(200);
        $getResponse->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'content',
                    'user' => ['id', 'name'],
                    'replies' => [
                        '*' => ['id', 'content', 'user']
                    ]
                ]
            ]
        ]);
    }

    public function test_comment_moderation_flow()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create();
        $post = Post::factory()->create();
        
        // 1. Usuario crea comentario (pendiente de moderación)
        $comment = Comment::factory()->create([
            'post_id' => $post->id,
            'user_id' => $user->id,
            'status' => 'pending'
        ]);
        
        // 2. Comentario no aparece en listado público
        $publicResponse = $this->getJson("/api/posts/{$post->id}/comments");
        $publicResponse->assertJsonCount(0, 'data');
        
        // 3. Admin aprueba comentario
        $approveResponse = $this->actingAs($admin)->putJson("/api/comments/{$comment->id}/approve");
        $approveResponse->assertStatus(200);
        
        // 4. Verificar estado actualizado
        $this->assertDatabaseHas('comments', [
            'id' => $comment->id,
            'status' => 'approved'
        ]);
        
        // 5. Ahora aparece en listado público
        $publicResponse = $this->getJson("/api/posts/{$post->id}/comments");
        $publicResponse->assertJsonCount(1, 'data');
    }

    public function test_comment_likes_and_dislikes()
    {
        $user = User::factory()->create();
        $comment = Comment::factory()->create();
        
        // 1. Usuario da like
        $likeResponse = $this->actingAs($user)->postJson("/api/comments/{$comment->id}/like");
        $likeResponse->assertStatus(200);
        
        // 2. Verificar like registrado
        $this->assertDatabaseHas('comment_likes', [
            'comment_id' => $comment->id,
            'user_id' => $user->id,
            'type' => 'like'
        ]);
        
        // 3. Verificar contador actualizado
        $comment->refresh();
        $this->assertEquals(1, $comment->likes_count);
        
        // 4. Usuario cambia a dislike
        $dislikeResponse = $this->actingAs($user)->postJson("/api/comments/{$comment->id}/dislike");
        $dislikeResponse->assertStatus(200);
        
        // 5. Verificar cambio
        $this->assertDatabaseHas('comment_likes', [
            'comment_id' => $comment->id,
            'user_id' => $user->id,
            'type' => 'dislike'
        ]);
        
        $comment->refresh();
        $this->assertEquals(0, $comment->likes_count);
        $this->assertEquals(1, $comment->dislikes_count);
    }

    public function test_delete_comment_cascade()
    {
        $user = User::factory()->create();
        $post = Post::factory()->create();
        
        // Crear comentario con respuestas
        $parentComment = Comment::factory()->create([
            'post_id' => $post->id,
            'user_id' => $user->id
        ]);
        
        $reply1 = Comment::factory()->create([
            'post_id' => $post->id,
            'parent_id' => $parentComment->id
        ]);
        
        $reply2 = Comment::factory()->create([
            'post_id' => $post->id,
            'parent_id' => $parentComment->id
        ]);
        
        // Eliminar comentario padre
        $deleteResponse = $this->actingAs($user)->deleteJson("/api/comments/{$parentComment->id}");
        $deleteResponse->assertStatus(204);
        
        // Verificar que las respuestas también fueron eliminadas
        $this->assertSoftDeleted('comments', ['id' => $parentComment->id]);
        $this->assertSoftDeleted('comments', ['id' => $reply1->id]);
        $this->assertSoftDeleted('comments', ['id' => $reply2->id]);
    }
}