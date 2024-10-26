<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Support\Facades\Crypt;
//use Illuminate\Support\Facades\Hash;
use App\Models\User;

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

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }
    public function login(Request $request)
    {
        $input = $request->all();

        $this->validate($request, [
            'username' => 'required',
            'password' => 'required',
        ]);
        $users = User::all();
        foreach ($users as $user) {
          if ($user->registro == $input['username'] && Crypt::decryptString($user->password) == $input['password']) {
            if ($user->situacao == 1) {
              auth()->login($user);
              /*
              if (isset($request->google) && $request->google == "on") {
                return redirect()->route('oauth2');
              }
              */
              if ($user->perfil != 0) {
                return redirect()->route('oauth2');
              }
              return redirect()->route('home');
            } else {
              $status = "não autorizado";
              if ($user->situacao == 0) {
                $status = "pendente";
              } else if ($user->situacao == 2) {
                $status = "suspenso";
              }
              return redirect()->route('login')->with('error','Usuário ' . $status . '.');
            }
          }
        }
        return redirect()->route('login')->with('error','Usuário ou senha incorretos.');

        /* ROTINA DESATIVADA HASH FOI TROCADO PARA CRIPTOGRAFIA
        if(auth()->attempt(array("registro" => $input['username'], 'password' => $input['password'])))
        {
            if(auth()->user()->situacao != 0)
                return redirect()->route('home');
            else
            {
                auth()->logout();
                return redirect()->route('login')
                    ->with('error','Usuário não autorizado.');
            }
        }else{
            return redirect()->route('login')
                ->with('error','Usuário ou senha incorretos.');
        }
         */
    }
}
