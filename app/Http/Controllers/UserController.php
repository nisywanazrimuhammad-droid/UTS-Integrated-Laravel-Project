<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with('profile')
            ->withCount([
                'stockMovements as movement_count',
                'purchases as purchase_count',
                'sales as sale_count',
            ])
            ->orderBy('id')
            ->get();

        return view('users.index', compact('users'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'username' => ['required', 'string', 'min:3', 'max:50', 'unique:users,username'],
            'email' => ['nullable', 'email', 'max:100', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6'],
            'role' => ['required', 'in:admin,staff,customer'],
            'full_name' => ['required', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string'],
        ]);

        DB::transaction(function () use ($validated) {
            $username = strtolower(trim($validated['username']));
            $email = !empty($validated['email']) ? strtolower(trim($validated['email'])) : ($username . '@inventrack.local');

            $user = User::create([
                'name' => $validated['full_name'],
                'username' => $username,
                'email' => $email,
                'password' => $validated['password'],
                'role' => $validated['role'],
            ]);

            UserProfile::create([
                'user_id' => $user->id,
                'full_name' => $validated['full_name'],
                'phone' => $validated['phone'] ?? null,
                'address' => $validated['address'] ?? null,
            ]);
        });

        return redirect()->route('users.index')
            ->with('success', "Pengguna '{$validated['username']}' berhasil ditambahkan.");
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'username' => ['required', 'string', 'min:3', 'max:50', Rule::unique('users')->ignore($user->id)],
            'email' => ['nullable', 'email', 'max:100', Rule::unique('users')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:6'],
            'role' => ['required', 'in:admin,staff,customer'],
            'full_name' => ['required', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string'],
        ]);

        DB::transaction(function () use ($user, $validated) {
            $username = strtolower(trim($validated['username']));
            $email = !empty($validated['email']) ? strtolower(trim($validated['email'])) : ($username . '@inventrack.local');

            $updateData = [
                'name' => $validated['full_name'],
                'username' => $username,
                'email' => $email,
                'role' => $validated['role'],
            ];

            if (!empty($validated['password'])) {
                $updateData['password'] = $validated['password'];
            }

            $user->update($updateData);

            $user->profile()->updateOrCreate(
                ['user_id' => $user->id],
                [
                    'full_name' => $validated['full_name'],
                    'phone' => $validated['phone'] ?? null,
                    'address' => $validated['address'] ?? null,
                ]
            );
        });

        return redirect()->route('users.index')
            ->with('success', "Data pengguna '{$validated['username']}' berhasil diperbarui.");
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('danger', 'Anda tidak dapat menghapus akun Anda sendiri yang sedang aktif.');
        }

        if ($user->stockMovements()->exists() || $user->purchases()->exists() || $user->sales()->exists()) {
            return back()->with('danger', 'Pengguna tidak dapat dihapus karena memiliki riwayat pencatatan transaksi.');
        }

        $user->delete();

        return redirect()->route('users.index')
            ->with('success', 'Pengguna berhasil dihapus.');
    }
}
