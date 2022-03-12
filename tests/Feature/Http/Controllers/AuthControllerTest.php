<?php

namespace Tests\Feature\Http\Controllers;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_auth_register()
    {
        $response = $this->post('/api/register', [
            'name' => 'John Doe',
            'email' => 'johndoe@gmail.com',
            'password' => 'a cool password',
            'password_confirmation' => 'a cool password'
        ]);

        $response->assertStatus(201);
    }
}
