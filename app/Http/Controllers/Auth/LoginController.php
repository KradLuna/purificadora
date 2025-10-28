<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    // Especifica el campo de autenticación
    public function username()
    {
        return 'phone_number';
    }

    // Sobrescribe el método de login para añadir logging
    public function login(Request $request)
    {
        $this->validateLogin($request);
        if ($this->attemptLogin($request)) {

            return $this->sendLoginResponse($request);
        }

        return $this->sendFailedLoginResponse($request);
    }

    protected function validateLogin(Request $request)
    {
        $request->validate([
            'phone_number' => 'required|string',
            'password' => 'required|string',
        ]);
    }

    protected function attemptLogin(Request $request)
    {
        return $this->guard()->attempt(
            $this->credentials($request),
            $request->filled('remember')
        );
    }

    protected function credentials(Request $request)
    {
        return [
            'phone_number' => $request->phone_number,
            'password' => $request->password,
            'is_active' => 1 // Opcional: si quieres verificar usuarios activos
        ];
    }

    public function redirectTo()
    {
        $user = Auth::user();
        if ($user->isAdmin) {
            return route('sales.index');
        }

        if ($user->hasRole(User::ROLES[1])) {
            return route('records.index');
        }

        return route('login');
    }

    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }
}
