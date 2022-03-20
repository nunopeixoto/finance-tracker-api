<?php

namespace Tests\Feature\Http\Controllers;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_register() : void
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

    public function test_login() : void
    {
        // User not created
        $response = $this->post('/api/login', [
            'email' => 'notyetcreated@gmail.com',
            'password' => 'a cool password'
        ]);
        $response->assertStatus(401);

        $user = User::factory()->create([
            'email' => 'john@gmail.com',
            'password' => bcrypt('a cool password')
        ]);

        // Success
        $response = $this->post('/api/login', [
            'email' => $user->email,
            'password' => 'a cool password'
        ]);
        $response->assertStatus(200);

        // Bad creds
        $response = $this->post('/api/login', [
            'email' => $user->email,
            'password' => 'a wrong password'
        ]);
        $response->assertStatus(401);

        // Invalid e-mail
        $response = $this->post('/api/login', [
            'email' => '123',
            'password' => 'a cool password'
        ]);
        $response->assertSessionHasErrors(['email']);

    }

    public function test_logout() : void
    {
        // Try to logout without login
        $response = $this->post('/api/logout', [], [
            'Accept' => 'application/json'
        ]);
        $response->assertStatus(401);

        $user = User::factory()->create();
        Sanctum::actingAs($user);

        // Logout success
        $response = $this->post('/api/logout');
        $response->assertStatus(200);
    }
}
