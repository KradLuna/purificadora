<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\EmployeesSaleController;
use App\Http\Controllers\HistoricSaleController;
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
        return redirect()->route('sales.summary');
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
    Route::get('users/data', [UserController::class, 'data'])->name('users.data');
    Route::resource('users', UserController::class);
    Route::get('products/data', [ProductController::class, 'data'])->name('products.data');
    Route::resource('products', ProductController::class);
    Route::get('historic-sales/data', [HistoricSaleController::class, 'data'])->name('historic-sales.data');
    // Route::delete('historic-sales/{sale}', [HistoricSaleController::class, 'destroy'])->name('historic-sales.destroy');
    // Route::put('historic-sales/{sale}', [HistoricSaleController::class, 'update'])->name('historic-sales.update');
    // Route::resource('historic-sales', HistoricSaleController::class)->except('destroy', 'update');
    Route::resource('historic-sales', HistoricSaleController::class)
        ->parameters(['historic-sales' => 'sale']); // con esto hacemos que espere el modelo de sale y no de historic
    Route::delete('records/{record}', [RecordController::class, 'destroy'])->name('records.destroy');
    Route::get('sales/summary', [SaleController::class, 'summary'])->name('sales.summary');
});
/**
 * EMPLEADOS Y ADMINS
 */

Route::middleware(['auth', 'role:' . implode('|', User::ROLES)])->group(function () { //son los roles: administrador|empleado
    Route::get('sales/data', [SaleController::class, 'data'])->name('sales.data');
    Route::resource('sales', SaleController::class);
    Route::get('records-data', [RecordController::class, 'data'])->name('records.data');
    Route::resource('records', RecordController::class)->except('destroy');
});

// Route::get('/', function () {
//     return view('auth.login');
// })->middleware('guest');
