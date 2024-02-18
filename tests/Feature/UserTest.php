<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class UserTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_get_users_list(): void
    {
        $response = $this->get('/api/users');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'data' => [
                '*' => ['id', 'rol', 'name', 'last_name', 'email', 'phone', 'sex', 'age', 'date_of_birth']
            ]
        ]);

        $response->assertJsonFragment(['name' => 'Brian']);

        $response->assertJsonCount(4);
    }

    public function test_get_user_detail(): void
    {
        $response = $this->get('/api/users/1');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'data' => [
                'id', 'rol', 'name', 'last_name', 'email', 'phone', 'sex', 'age', 'date_of_birth'
            ]
        ]);


        $response->assertJsonFragment(['name' => 'Brian']);
    }

    public function test_get_non_existing_detail(): void
    {
        $response = $this->get('/api/users/14438');
        $response->assertStatus(404);
    }
}
