<?php

namespace Tests\Feature\Auth;

// RF05 — CRUD de usuários SEM self-service: só RH/Admin (e Líder p/ liderados)
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registro_publico_e_desabilitado(): void
    {
        $this->get('/register')->assertNotFound();

        $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ])->assertNotFound();

        $this->assertGuest(); // ninguém se cadastra sozinho
    }
}
