<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\EmployeesSaleController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\RecordController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\UserController;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Auth::routes();

Route::get('/', function () {
    $user = Auth::user();

    if (is_null($user)) {
        return redirect()->route('login');
    }
    if ($user->hasRole(User::ROLES[0])) { // Admin
        return redirect()->route('sales.index');
    }
    if ($user->hasRole(User::ROLES[1])) { // Empleado
        return redirect()->route('records.index');
    }
    return redirect()->route('login');
});

/**
 * SOLAMENTE ADMINISTRADORES
 */
//. User::ROLES[0]
Route::middleware(['auth', 'role:' . User::ROLES[0]])->group(function () { //son los roles: administrador
    Route::get('/admin', function () {
        return view('admin');
    });
    Route::get('users/data', [UserController::class, 'data'])->name('users.data');
    Route::resource('users', UserController::class);
    Route::get('products/data', [ProductController::class, 'data'])->name('products.data');
    Route::resource('products', ProductController::class);
    Route::delete('records/{record}', [RecordController::class, 'destroy'])->name('records.destroy');
    Route::get('sales', [SaleController::class, 'index'])->name('sales.index');
});
/**
 * EMPLEADOS Y ADMINS
 */

Route::middleware(['auth', 'role:' . implode('|', User::ROLES)])->group(function () { //son los roles: administrador|empleado
    //Route::get('employees-sales/data', [EmployeesSaleController::class, 'data'])->name('employees-sales.data');
    Route::resource('employees-sales', EmployeesSaleController::class);
    //Route::get('employees-sales/data', [EmployeesSaleController::class, 'data'])->name('employees-sales.data');
    Route::get('sales/data', [SaleController::class, 'data'])->name('sales.data');
    Route::resource('sales', SaleController::class)->except('index');
    Route::get('records-data', [RecordController::class, 'data'])->name('records.data');
    Route::resource('records', RecordController::class)->except('destroy');
});

// Route::get('/', function () {
//     return view('auth.login');
// })->middleware('guest');
