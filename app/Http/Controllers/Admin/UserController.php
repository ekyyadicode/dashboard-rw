<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Rt;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserController extends Controller
{
    public const AVAILABLE_PERMISSIONS = [
        'warga' => 'Data Warga & KK',
        'iuran' => 'Iuran Warga',
        'bank_sampah' => 'Bank Sampah',
        'posyandu' => 'Posyandu Balita',
    ];

    public function index(): View
    {
        $users = User::with('rt')
            ->orderByRaw("CASE WHEN role = 'super_admin' THEN 0 ELSE 1 END")
            ->orderBy('name')
            ->get();

        $availablePermissions = self::AVAILABLE_PERMISSIONS;

        return view('admin.users.index', compact('users', 'availablePermissions'));
    }

    public function create(): View
    {
        $rts = Rt::orderBy('nomor_rt')->get();
        $availablePermissions = self::AVAILABLE_PERMISSIONS;

        return view('admin.users.create', compact('rts', 'availablePermissions'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:50', 'unique:users,username'],
            'email' => ['nullable', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6'],
            'role' => ['required', Rule::in(['pengurus', 'super_admin'])],
            'rt_id' => ['nullable', 'required_if:role,pengurus', 'exists:rts,id'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => [Rule::in(array_keys(self::AVAILABLE_PERMISSIONS))],
            'is_active' => ['nullable', 'boolean'],
        ], [
            'name.required' => 'Nama lengkap wajib diisi.',
            'username.required' => 'Username wajib diisi.',
            'username.unique' => 'Username sudah digunakan.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah digunakan.',
            'password.required' => 'Password wajib diisi.',
            'password.min' => 'Password minimal 6 karakter.',
            'rt_id.required_if' => 'Wilayah RT wajib dipilih untuk role Pengurus.',
        ]);

        User::create([
            'name' => $validated['name'],
            'username' => $validated['username'],
            'email' => $validated['email'] ?: null,
            'password' => $validated['password'],
            'role' => $validated['role'],
            'rt_id' => $validated['role'] === 'super_admin' ? null : $validated['rt_id'],
            'permissions' => array_values($validated['permissions'] ?? []),
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'Akun pengguna berhasil ditambahkan.');
    }

    public function edit(User $user): View
    {
        $rts = Rt::orderBy('nomor_rt')->get();
        $availablePermissions = self::AVAILABLE_PERMISSIONS;

        return view('admin.users.edit', compact('user', 'rts', 'availablePermissions'));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:50', Rule::unique('users', 'username')->ignore($user->id)],
            'email' => ['nullable', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:6'],
            'role' => ['required', Rule::in(['pengurus', 'super_admin'])],
            'rt_id' => ['nullable', 'required_if:role,pengurus', 'exists:rts,id'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => [Rule::in(array_keys(self::AVAILABLE_PERMISSIONS))],
            'is_active' => ['nullable', 'boolean'],
        ], [
            'name.required' => 'Nama lengkap wajib diisi.',
            'username.required' => 'Username wajib diisi.',
            'username.unique' => 'Username sudah digunakan.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah digunakan.',
            'password.min' => 'Password minimal 6 karakter.',
            'rt_id.required_if' => 'Wilayah RT wajib dipilih untuk role Pengurus.',
        ]);

        $isActive = $request->boolean('is_active');

        if ($user->id === $request->user()->id && ! $isActive) {
            return back()
                ->withInput()
                ->with('error', 'Anda tidak dapat menonaktifkan akun Anda sendiri yang sedang digunakan login.');
        }

        if ($user->id === $request->user()->id && $validated['role'] !== 'super_admin') {
            return back()
                ->withInput()
                ->with('error', 'Anda tidak dapat mengubah role akun Anda sendiri dari Super Admin.');
        }

        $data = [
            'name' => $validated['name'],
            'username' => $validated['username'],
            'email' => $validated['email'] ?: null,
            'role' => $validated['role'],
            'rt_id' => $validated['role'] === 'super_admin' ? null : $validated['rt_id'],
            'permissions' => array_values($validated['permissions'] ?? []),
            'is_active' => $isActive,
        ];

        if (! empty($validated['password'])) {
            $data['password'] = $validated['password'];
        }

        $user->update($data);

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'Data akun pengguna berhasil diperbarui.');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        if ($user->id === $request->user()->id) {
            return redirect()
                ->route('admin.users.index')
                ->with('error', 'Anda tidak dapat menghapus akun Anda sendiri yang sedang digunakan login.');
        }

        $user->delete();

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'Akun pengguna berhasil dihapus.');
    }
}

