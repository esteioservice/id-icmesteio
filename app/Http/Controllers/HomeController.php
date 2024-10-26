<?php

namespace App\Http\Controllers;

use App\Models\EmailDelegadoModel;
use App\Models\EquipeModel;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @param  Request  $request
     * @return \Illuminate\Contracts\Support\Renderable
     */

    public function index(Request $request)
    {
        $access_token = $request->session()->get('access_token');
        $request->session()->keep(['access_token']);
        $emails = EmailDelegadoModel::select("email_delegado.*")->join("email_delegado_acesso","email_delegado_acesso.emaildelegado_id","=","email_delegado.id")->join("users","users.id","=","email_delegado_acesso.user_id")->where("email_delegado_acesso.user_id","=",auth()->id())->get();
        $equipes = EquipeModel::select("equipe.*")->join("equipe_usuario","equipe_usuario.equipe_id","=","equipe.id")->join("users","users.id","=","equipe_usuario.user_id")->where("equipe_usuario.user_id","=",auth()->id())->get();
        return view('home',compact('emails','equipes', 'access_token'));
    }
}
