<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\GoogleController;
use App\Models\Project;
use App\Models\User;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Artisan;
use App\Jobs\SendFilamentDatabaseNotification;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/



Route::get('/', function () {
    return view('welcome');
});

// Route untuk Login Google
Route::get('/auth/google', [GoogleController::class, 'redirectToGoogle'])
    ->middleware('google.app.panel')
    ->name('login.google');

Route::get('/auth/google/callback', [GoogleController::class, 'handleGoogleCallback'])
    ->middleware('google.app.panel')
    ->name('login.google.callback');

// Rute untuk Menerima Undangan
Route::get('/projects/{project}/accept-invite/{user}', function (Project $project, User $user) {
    if (! auth()->check()) {
        session(['url.intended' => request()->fullUrl()]);
        return redirect()->guest('/app/login');
    }

    if ((int) $user->id !== (int) auth()->id()) {
        abort(403);
    }

    // 2. Masukkan user ke project (Pakai syncWithoutDetaching biar gak error kalau klik 2x)
    $project->members()->syncWithoutDetaching([$user->id]);

    // Tandai notifikasi undangan sebagai read + hapus action (biar tombol hilang)
    $user->notifications()
        ->whereNull('read_at')
        ->where('data->viewData->type', 'project_invite')
        ->where('data->viewData->project_id', $project->id)
        ->where('data->viewData->user_id', $user->id)
        ->update([
            'read_at' => now(),
            'data->actions' => [],
            'data->viewData->status' => 'accepted',
            'data->title' => 'Invitation accepted',
            'data->body' => 'You joined the project: ' . e($project->name),
        ]);

    // 3. Kirim Notif ke semua member + leader (kecuali admin & user yang join)
    $notification = Notification::make()
        ->title(e($user->name) . ' joined the team')
        ->success();

    $notifyIds = $project->members()
        ->pluck('users.id')
        ->push($project->leader_id)
        ->unique()
        ->filter(fn ($id) => (int) $id !== (int) $user->id)
        ->values()
        ->all();

    SendFilamentDatabaseNotification::dispatch(
        $notification->toArray(),
        $notifyIds,
    );

    // 4. Redirect ke halaman Project dengan pesan sukses
   return redirect('/app/projects')->with('status', 'Invitation accepted!');

})->name('project.accept-invite')->middleware(['web', 'signed']);

// Rute untuk Menolak Undangan (Cuma redirect balik)
Route::get('/projects/{project}/reject-invite/{user}', function (Project $project, User $user) {
    if (! auth()->check()) {
        session(['url.intended' => request()->fullUrl()]);
        return redirect()->guest('/app/login');
    }

    if ((int) $user->id !== (int) auth()->id()) {
        abort(403);
    }

    // Tandai notifikasi undangan sebagai read + hapus action (biar tombol hilang)
    $user->notifications()
        ->whereNull('read_at')
        ->where('data->viewData->type', 'project_invite')
        ->where('data->viewData->project_id', $project->id)
        ->where('data->viewData->user_id', $user->id)
        ->update([
            'read_at' => now(),
            'data->actions' => [],
            'data->viewData->status' => 'declined',
            'data->title' => 'Invitation declined',
            'data->body' => 'You declined the project: ' . e($project->name),
        ]);

    return back();
})->name('project.reject-invite')->middleware(['web', 'signed']);

Route::get('/buat-jembatan', function () {
    abort_unless(app()->environment(['local', 'staging', 'development']), 404);
    abort_unless(auth()->user()?->hasRole('admin'), 403);
    Artisan::call('storage:link');
    return 'Jembatan storage sukses dibuat! ðŸš€ Silakan kembali ke halaman utama.';
})->middleware('auth');

Route::get('/bersih-bersih', function () {
    abort_unless(app()->environment(['local', 'staging', 'development']), 404);
    abort_unless(auth()->user()?->hasRole('admin'), 403);
    Artisan::call('optimize:clear');
    return 'Semua cache berhasil disapu bersih! 🧹✨ Silakan kembali ke halaman utama.';
})->middleware('auth');
