<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookApiTest extends TestCase
{
    use RefreshDatabase;

    private function authenticatedToken(): string
    {
        $user = User::factory()->create();

        return $user->createToken('test-token')->plainTextToken;
    }

    public function test_authenticated_user_can_perform_book_crud(): void
    {
        $token = $this->authenticatedToken();

        // CREATE: menambahkan buku
        $create = $this->withToken($token)->postJson('/api/books', [
            'title' => 'Belajar Laravel',
            'author' => 'Mahasiswa',
            'category' => 'Teknologi',
            'publication_year' => 2024,
        ]);

        $create->assertCreated()
            ->assertJsonPath('data.title', 'Belajar Laravel');

        $id = $create->json('data.id');

        $this->assertDatabaseHas('books', [
            'id' => $id,
            'title' => 'Belajar Laravel',
        ]);

        // READ: menampilkan seluruh buku
        $this->withToken($token)
            ->getJson('/api/books')
            ->assertOk()
            ->assertJsonFragment(['title' => 'Belajar Laravel']);

        // READ: menampilkan satu buku
        $this->withToken($token)
            ->getJson("/api/books/{$id}")
            ->assertOk()
            ->assertJsonPath('data.id', $id);

        // UPDATE: mengubah buku
        $this->withToken($token)
            ->putJson("/api/books/{$id}", [
                'title' => 'Belajar Laravel Lanjutan',
            ])
            ->assertOk()
            ->assertJsonPath('data.title', 'Belajar Laravel Lanjutan');

        $this->assertDatabaseHas('books', [
            'id' => $id,
            'title' => 'Belajar Laravel Lanjutan',
        ]);

        // DELETE: menghapus buku
        $this->withToken($token)
            ->deleteJson("/api/books/{$id}")
            ->assertOk();

        $this->assertDatabaseMissing('books', [
            'id' => $id,
        ]);
    }

    public function test_book_validation_failure_returns_422(): void
    {
        $token = $this->authenticatedToken();

        $this->withToken($token)
            ->postJson('/api/books', [
                'title' => '',
                'author' => 'Mahasiswa',
                'category' => 'Teknologi',
                'publication_year' => 2024,
            ])
            ->assertUnprocessable();
    }
}
