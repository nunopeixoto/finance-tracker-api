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
        // OK
        $response = $this->post('/api/register', [
            'name' => 'John Doe',
            'email' => 'johndoe@gmail.com',
            'password' => 'a cool password',
            'password_confirmation' => 'a cool password'
        ]);
        $response->assertStatus(201);

        // Duplicate e-mail
        $response = $this->post('/api/register', [
            'name' => 'John Doe',
            'email' => 'johndoe@gmail.com',
            'password' => 'a cool password',
            'password_confirmation' => 'a cool password'
        ]);
        $response->assertSessionHasErrors(['email']);

        // Password doesen't match
        $response = $this->post('/api/register', [
            'name' => 'Johana Doe',
            'email' => 'johanadoe@gmail.com',
            'password' => 'a cool password',
            'password_confirmation' => 'another cool password'
        ]);
        $response->assertSessionHasErrors(['password']);

        // Empty name
        $response = $this->post('/api/register', [
            'name' => '',
            'email' => 'johanadoe@gmail.com',
            'password' => 'a cool password',
            'password_confirmation' => 'a cool password'
        ]);
        $response->assertSessionHasErrors(['name']);
    }
}
