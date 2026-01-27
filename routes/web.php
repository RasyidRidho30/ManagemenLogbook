<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Components\ProjekCardController;
use App\Http\Controllers\Views\ProjekViewController;

Route::view('/', 'Auth.Login')->name('login');
Route::view('/login', 'Auth.Login')->name('login');
Route::view('/signup', 'Auth.SignUp');

// Halaman Edit Profile (pelanggan dapat membuka melalui /profile/edit)
Route::get('/profile/edit', function () {
    return view('Auth.EditProfile');
})->name('profile.edit');

Route::view('/projek', 'ProjekPage.Read');
Route::view('/projek/add', 'ProjekPage.Add');

Route::post('/projek/render-cards', [ProjekCardController::class, 'renderCards']);


Route::prefix('projek/{id}')->group(function () {
    Route::get('/dashboard', [ProjekViewController::class, 'dashboard'])->name('projek.dashboard');

    Route::get('/list', function ($id) {
        return "Halaman List Projek $id";
    });

    Route::get('/jobs', [ProjekViewController::class, 'jobs'])->name('projek.jobs');

    Route::get('/edit', [ProjekViewController::class, 'edit'])->name('projek.edit');

    Route::get('/logbook', function ($id) {
        return "Halaman Logbook Projek $id";
    });
});
