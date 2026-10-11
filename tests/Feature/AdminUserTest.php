<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminUserTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::create(['name' => 'Admin', 'email' => 'admin@example.com', 'password' => 'Test-Pass-123']);
    }

    public function test_users_page_requires_login(): void
    {
        $this->get('/admin/users')->assertRedirect('/admin/login');
    }

    public function test_admin_creates_user_who_can_then_sign_in(): void
    {
        $this->actingAs($this->admin())->get('/admin/users')->assertOk()->assertSee('admin@example.com');

        $this->post('/admin/users', [
            'name' => 'Jane Smith',
            'email' => 'jane@example.com',
            'password' => 'Secret-123',
            'password_confirmation' => 'Secret-123',
        ])->assertRedirect(route('admin.users.index'));

        $this->post('/admin/logout');
        $this->post('/admin/login', ['email' => 'jane@example.com', 'password' => 'Secret-123'])
            ->assertRedirect(route('admin.leads.index'));
        $this->assertAuthenticatedAs(User::where('email', 'jane@example.com')->first());
    }

    public function test_create_rejects_duplicate_email_and_weak_password(): void
    {
        $this->actingAs($this->admin())->post('/admin/users', [
            'name' => 'Dup',
            'email' => 'admin@example.com',
            'password' => 'short',
            'password_confirmation' => 'short',
        ])->assertSessionHasErrors(['email', 'password']);

        $this->assertSame(1, User::count());
    }

    public function test_update_keeps_password_when_left_blank(): void
    {
        $admin = $this->admin();
        $user = User::create(['name' => 'Jane', 'email' => 'jane@example.com', 'password' => 'Secret-123']);

        $this->actingAs($admin)->get("/admin/users/{$user->id}/edit")->assertOk();

        $this->put("/admin/users/{$user->id}", ['name' => 'Jane S', 'email' => 'jane@example.com', 'password' => ''])
            ->assertRedirect(route('admin.users.index'));

        $user->refresh();
        $this->assertSame('Jane S', $user->name);
        $this->assertTrue(Hash::check('Secret-123', $user->password));
    }

    public function test_admin_can_delete_others_but_not_themselves(): void
    {
        $admin = $this->admin();
        $user = User::create(['name' => 'Jane', 'email' => 'jane@example.com', 'password' => 'Secret-123']);

        $this->actingAs($admin)->delete("/admin/users/{$admin->id}")->assertSessionHas('error');
        $this->assertModelExists($admin);

        $this->delete("/admin/users/{$user->id}")->assertSessionHas('success');
        $this->assertModelMissing($user);
    }
}
