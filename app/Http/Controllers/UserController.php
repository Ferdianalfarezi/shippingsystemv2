<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 25);
        $search = $request->input('search');

        $query = User::query();

        // Search functionality
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('username', 'like', "%{$search}%");
            });
        }

        // Order by latest
        $query->orderBy('created_at', 'asc');

        // Pagination
        if ($perPage === 'all') {
            $users = $query->paginate($query->count());
        } else {
            $users = $query->paginate($perPage);
        }

        // Count admin users
        $adminCount = User::where('role', 'admin')->count();

        return view('users.index', compact('users', 'adminCount'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username|regex:/^\S*$/',
            'password' => 'required|string|min:6|confirmed',
            'role' => 'required|in:superadmin,admin,scanner,lp,user',
        ], [
            'name.required' => 'Nama lengkap wajib diisi',
            'username.required' => 'Username wajib diisi',
            'username.unique' => 'Username sudah digunakan',
            'username.regex' => 'Username tidak boleh mengandung spasi',
            'password.required' => 'Password wajib diisi',
            'password.min' => 'Password minimal 6 karakter',
            'password.confirmed' => 'Konfirmasi password tidak cocok',
            'role.required' => 'Role wajib dipilih',
            'role.in' => 'Role tidak valid',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'username' => $validated['username'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
        ]);

        if ($request->ajax()) {
            return response()->json([
                'status' => 'success',
                'message' => 'User berhasil ditambahkan',
                'data' => $user
            ]);
        }

        return redirect()->route('users.index')
            ->with('success', 'User berhasil ditambahkan');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {
        return response()->json($user);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'username' => [
                'required',
                'string',
                'max:255',
                'regex:/^\S*$/',
                Rule::unique('users')->ignore($user->id)
            ],
            'password' => 'nullable|string|min:6|confirmed',
            'role' => 'required|in:superadmin,admin,scanner,lp,user',
        ], [
            'name.required' => 'Nama lengkap wajib diisi',
            'username.required' => 'Username wajib diisi',
            'username.unique' => 'Username sudah digunakan',
            'username.regex' => 'Username tidak boleh mengandung spasi',
            'password.min' => 'Password minimal 6 karakter',
            'password.confirmed' => 'Konfirmasi password tidak cocok',
            'role.required' => 'Role wajib dipilih',
            'role.in' => 'Role tidak valid',
        ]);

        $user->name = $validated['name'];
        $user->username = $validated['username'];
        $user->role = $validated['role'];

        // Only update password if provided
        if ($request->filled('password')) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        if ($request->ajax()) {
            return response()->json([
                'status' => 'success',
                'message' => 'User berhasil diupdate',
                'data' => $user
            ]);
        }

        return redirect()->route('users.index')
            ->with('success', 'User berhasil diupdate');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        // Prevent deleting own account
        if ($user->id === auth()->id()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Anda tidak dapat menghapus akun sendiri'
            ], 403);
        }

        $user->delete();

        if (request()->ajax()) {
            return response()->json([
                'status' => 'success',
                'message' => 'User berhasil dihapus'
            ]);
        }

        return redirect()->route('users.index')
            ->with('success', 'User berhasil dihapus');
    }
}