<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RoleController extends Controller
{
    public function index()
    {
        $roles = Role::withCount('users')->orderByDesc('is_admin')->orderBy('name')->get();

        return view('admin.roles.index', compact('roles'));
    }

    public function store(Request $request)
    {
        Role::create($this->validated($request));

        return redirect()->route('admin.roles.index')->with('success', 'Role created — you can now assign it to users.');
    }

    public function edit(Role $role)
    {
        abort_if($role->is_admin, 403, 'The Administrator role always has every permission.');

        return view('admin.roles.edit', compact('role'));
    }

    public function update(Request $request, Role $role)
    {
        abort_if($role->is_admin, 403, 'The Administrator role always has every permission.');

        $role->update($this->validated($request, $role));

        return redirect()->route('admin.roles.index')->with('success', 'Role updated. Its users have the new permissions right away.');
    }

    public function destroy(Role $role)
    {
        if ($role->is_admin) {
            return back()->with('error', 'The Administrator role can\'t be deleted.');
        }

        if ($role->users()->exists()) {
            return back()->with('error', "Move the users off {$role->name} before deleting it.");
        }

        $role->delete();

        return back()->with('success', 'Role deleted.');
    }

    private function validated(Request $request, ?Role $role = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100', Rule::unique('roles', 'name')->ignore($role?->id)],
            'permissions' => 'nullable|array',
            'permissions.*' => ['string', Rule::in(array_keys(Role::PERMISSIONS))],
        ]);

        $data['permissions'] = array_values($data['permissions'] ?? []);

        return $data;
    }
}
