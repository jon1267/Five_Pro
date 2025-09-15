<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Book;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }

    public function test_api_book_index_successful()
    {
        Book::factory()->count(10)->create();

        $response = $this->getJson('/api/books');
        $response->assertStatus(200);
    }

    public function test_api_book_store_successful()
    {
        $book = [
            'title' => "Book No 14.",
            'description' => "Book 14 description is simply dummy text of the printing and typesetting industry.",
            'publisher' => "Jones-Nord-Schmidt",
            'year' => "1991-01-15",
            'edition' => "2",
            'format' => 'Paparazzo',
            'pages' => '490',
            'country' => 'Canada',
            'isbn' => '9780446521440',
        ];
        $response = $this->postJson('/api/books', $book);

        $response->assertStatus(201);
        $response->assertJson($book);
        $this->assertDatabaseHas('books', $book);
    }


    public function test_api_book_invalid_validation_return_error()
    {
        $book = [
            'title' => '',
            'description' => '',
            'publisher' => '',
            'year' => '',
        ];

        $response = $this->postJson('/api/books', $book);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['title', 'description', 'publisher', 'year']);
    }

    public function test_api_book_update_successful()
    {
        $book = [
            'title' => "Book No 14.",
            'description' => "Book 14 description is simply dummy text of the printing and typesetting industry.",
            'publisher' => "Jones-Nord-Schmidt",
            'year' => "1991-01-15",
            'edition' => "2",
            'format' => 'Paparazzo',
            'pages' => '490',
            'country' => 'Canada',
        ];
        $response = $this->postJson('/api/books', $book);

        $book['isbn'] = '9780446521440';
        $response = $this->putJson('/api/books/' . $response->json()['id'], $book);
        $response->assertStatus(200);
        $response->assertJson($book);
        $this->assertDatabaseHas('books', $book);
    }

    public function test_api_book_is_possible_delete_book()
    {
        $book = [
            'title' => "Book No 14.",
            'description' => "Book 14 description is simply dummy text of the printing and typesetting industry.",
            'publisher' => "Jones-Nord-Schmidt",
            'year' => "1991-01-15",
            'edition' => "2",
            'format' => 'Paparazzo',
            'pages' => '490',
            'country' => 'Canada',
            'isbn' => '9780446521440',
        ];

        $response1 = $this->postJson('/api/books', $book);

        $response2 = $this->deleteJson('/api/books/' . $response1->json()['id']);
        $response2->assertStatus(204);
        $this->assertDatabaseMissing('books', $book);
    }
}
