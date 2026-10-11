<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminUserTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::create(['name' => 'Admin', 'email' => 'admin@example.com', 'password' => 'Test-Pass-123', 'role_id' => Role::admin()->id]);
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
            'role_id' => Role::admin()->id,
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

        $this->put("/admin/users/{$user->id}", ['name' => 'Jane S', 'email' => 'jane@example.com', 'password' => '', 'role_id' => Role::admin()->id])
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

    public function test_admin_creates_role_and_user_only_sees_permitted_pages(): void
    {
        $this->actingAs($this->admin())->post('/admin/roles', [
            'name' => 'Sales',
            'permissions' => ['leads.view'],
        ])->assertRedirect(route('admin.roles.index'));

        $role = Role::where('name', 'Sales')->firstOrFail();
        $this->assertSame(['leads.view'], $role->permissions);

        $sales = User::create(['name' => 'Sam', 'email' => 'sam@example.com', 'password' => 'Secret-123', 'role_id' => $role->id]);

        $this->actingAs($sales);
        $this->get('/admin')->assertOk()->assertDontSee('Affiliates')->assertDontSee('/admin/users');
        $this->get('/admin/affiliates')->assertForbidden();
        $this->get('/admin/users')->assertForbidden();
        $this->get('/admin/roles')->assertForbidden();
        $this->get('/admin/integrations')->assertForbidden();
        $this->get('/horizon')->assertForbidden();
        $this->get('/admin/account')->assertOk();
    }

    public function test_user_without_leads_access_is_sent_to_a_permitted_page(): void
    {
        $role = Role::create(['name' => 'Affiliate manager', 'permissions' => ['affiliates.manage']]);
        $user = User::create(['name' => 'Al', 'email' => 'al@example.com', 'password' => 'Secret-123', 'role_id' => $role->id]);

        $this->post('/admin/login', ['email' => 'al@example.com', 'password' => 'Secret-123'])
            ->assertRedirect(route('admin.affiliates.index'));
        $this->actingAs($user)->get('/admin')->assertRedirect(route('admin.affiliates.index'));
    }

    public function test_role_permissions_are_validated_and_admin_role_is_protected(): void
    {
        $this->actingAs($this->admin());

        $this->post('/admin/roles', ['name' => 'Bad', 'permissions' => ['everything']])->assertSessionHasErrors('permissions.0');

        $admin = Role::admin();
        $this->get("/admin/roles/{$admin->id}/edit")->assertForbidden();
        $this->delete("/admin/roles/{$admin->id}")->assertSessionHas('error');
        $this->assertModelExists($admin);
    }

    public function test_role_in_use_cannot_be_deleted(): void
    {
        $role = Role::create(['name' => 'Sales', 'permissions' => ['leads.view']]);
        User::create(['name' => 'Sam', 'email' => 'sam@example.com', 'password' => 'Secret-123', 'role_id' => $role->id]);

        $this->actingAs($this->admin())->delete("/admin/roles/{$role->id}")->assertSessionHas('error');
        $this->assertModelExists($role);
    }

    public function test_admin_cannot_change_own_role(): void
    {
        $admin = $this->admin();
        $role = Role::create(['name' => 'Sales', 'permissions' => ['leads.view']]);

        $this->actingAs($admin)
            ->put("/admin/users/{$admin->id}", ['name' => 'Admin', 'email' => 'admin@example.com', 'role_id' => $role->id])
            ->assertSessionHasErrors('role_id');

        $this->assertTrue($admin->fresh()->role->is_admin);
    }
}
