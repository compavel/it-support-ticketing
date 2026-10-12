<?php

use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TicketController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Redirect root to login
Route::get('/', function () {
    return redirect()->route('login');
});

// Temporary one-time setup route (shared hosting has no SSH).
// Only exists when SETUP_KEY is set in .env — delete that line after setup.
if (env('SETUP_KEY')) {
    Route::get('/setup/{key}', function (string $key) {
        abort_unless(hash_equals((string) env('SETUP_KEY'), $key), 404);

        \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
        \Illuminate\Support\Facades\Artisan::call('db:seed', ['--force' => true]);

        return response(
            "Setup complete! Semua table & akun demo sudah jadi.\n"
            .'SEKARANG: hapus baris SETUP_KEY dari file .env, lalu buka /login.'
        );
    });
}

// Auth Routes (Guest)
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->name('login.post');
});

// Logout (Auth)
Route::post('/logout', [LoginController::class, 'logout'])->name('logout')
    ->middleware('auth');

// Authenticated Routes
Route::middleware(['auth', 'is_active'])->group(function () {

    // Admin Dashboard
    Route::get('/admin/dashboard', [DashboardController::class, 'admin'])
        ->name('admin.dashboard')
        ->middleware('role:admin');

    // Technician Dashboard
    Route::get('/technician/dashboard', [DashboardController::class, 'technician'])
        ->name('technician.dashboard')
        ->middleware('role:technician');

    // Tickets (all authenticated users)
    Route::resource('tickets', TicketController::class);

    // Ticket Assignment (admin only)
    Route::post('/tickets/{ticket}/assign', [TicketController::class, 'assign'])
        ->name('tickets.assign')
        ->middleware('role:admin');

    // Comments
    Route::post('/tickets/{ticket}/comments', [CommentController::class, 'store'])
        ->name('comments.store');
    Route::delete('/comments/{comment}', [CommentController::class, 'destroy'])
        ->name('comments.destroy');

    // Admin User Management
    Route::prefix('admin')->name('admin.')->middleware('role:admin')->group(function () {
        Route::resource('users', UserController::class);
    });
});
