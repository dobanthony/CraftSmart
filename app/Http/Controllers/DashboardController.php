<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use App\Models\Announcement;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // ✅ Admin → redirect to the real admin dashboard route that loads metrics & charts
        if ($user->role === 'admin') {
            return redirect()->route('admin.dashboard');
        }

        // ✅ Seller
        if ($user->role === 'seller') {
            $shop = $user->shop;

            // Seller has a shop → go to seller analytics
            if ($shop) {
                return redirect()->route('seller.analytics');
            }

            // Seller has no shop → show Create Shop page
            return Inertia::render('Seller/CreateShop', [
                'user' => $user,
            ]);
        }

        // ✅ Normal user → show user dashboard with announcements
        return Inertia::render('User/Dashboard', [
            'user' => $user,
            'announcements' => Announcement::latest()->take(6)->get(),
        ]);
    }
}
