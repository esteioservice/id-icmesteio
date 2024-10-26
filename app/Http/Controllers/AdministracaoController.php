<?php

namespace App\Http\Controllers;

use App\Models\AdministracaoModel;
use App\Models\EquipeModel;
use App\Models\EquipeUsuarioModel;
use App\Models\GrupoUsuarioModel;
use App\Models\User;
use Illuminate\Http\Request;

class AdministracaoController extends Controller
{
    /**
     * Display a listing of the resource.
     * @param  Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $administracao = AdministracaoModel::findOrFail(1);
        $gruposUsuarios = GrupoUsuarioModel::all();
        $access_token = $request->session()->get('access_token');
        $request->session()->keep(['access_token']);
        return view("administracao",compact('administracao','gruposUsuarios', 'access_token'));
    }
    public function update(Request $request)
    {
        $administracao = AdministracaoModel::findOrFail(1);
        $administracao->grupo = $request->grupo;
        $administracao->numeral = $request->numeral;
        $administracao->nome = $request->nome;
        $administracao->usuario = $request->usuario;
        $administracao->termos = $request->termos;
        if(!empty($request->senha)) $administracao->senha = $request->senha;
        if(!empty($request->logo)) $administracao->logo = $request->logo->store('img');
        $administracao->save();
        return redirect()->route('administracao')->with("success","Dados atualizados com sucesso.");
    }
    public function alterarGrupo(Request $request)
    {
        $gruposUsuarios = GrupoUsuarioModel::where("perfil_id","=","2")->get();
        foreach ($gruposUsuarios as $grupo)
        {
            $gpusuario = GrupoUsuarioModel::findOrFail($grupo->id);
            $gpusuario->adiciona = $request->input($grupo->perfil_id."-adiciona") != "" ? 1 : 0;
            $gpusuario->edita = $request->input($grupo->perfil_id."-edita") != "" ? 1 : 0;
            $gpusuario->excluir = $request->input($grupo->perfil_id."-excluir") != "" ? 1 : 0;
            $gpusuario->remover = $request->input($grupo->perfil_id."-remover") != "" ? 1 : 0;
            $gpusuario->save();
        }
        return redirect()->route('administracao')->with("success","Dados atualizados com sucesso.");

    }
}
