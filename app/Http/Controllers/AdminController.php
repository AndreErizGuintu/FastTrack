<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Product;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    /**
     * Display the admin dashboard.
     */
    public function dashboard()
    {
        $totalProducts = Product::count();
        $totalUsers = User::where('role', 'user')->count();
        $totalCouriers = User::where('role', 'courier')->count();

        return view('admin.dashboard', compact('totalProducts', 'totalUsers', 'totalCouriers'));
    }

    /**
     * Display a listing of all users.
     */
    public function userIndex()
    {
        $users = User::paginate(10);
        return view('admin.index', compact('users'));
    }

    /**
     * Display the form for editing the specified user.
     */
    public function userEdit(User $user)
    {
        return view('admin.edit', compact('user'));
    }

    /**
     * Update the specified user in storage.
     */
    public function userUpdate(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'role' => 'required|in:user,admin,courier',
        ]);

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
        ]);

        return redirect()->route('admin.users.index')->with('success', 'User updated successfully!');
    }
}
