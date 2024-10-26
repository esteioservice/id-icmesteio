@extends('layouts.app')
@section('css')
    <style>
        .btn-editar{
            background-color: #1f81e6;
            opacity: 0.9;
            padding: 5px 10px;
            border-radius: 5px;
            color: white;
            cursor: pointer;
            text-decoration: none;
        }
        .btn-editar:hover{
            opacity: 1;
        }
        .label-status{
            opacity: 0.9;
            text-align: center;
            color: white;
            border-radius: 5px;
            cursor: pointer;
        }
        .red{
            background-color: #f71c1c;
        }
        .green{
            background-color: green;
        }
        .yellow{
            background-color: #d4d400;
        }
        .background{
            z-index: 3;
            width: 100%;
            height: 100%;
            background-color: black;
            opacity: 0.7;
            display: none;
            top: 0;
            position: absolute;
        }
        .card{
            width: 100%;
        }
        .popup{
            display: none;
            position: fixed;
            z-index: 4;
            width: 40%;
            top: 5%;
            left: 30%;
        }
        .atualiza-status:hover{
            opacity: 1;
        }
    </style>
@endsection
@section('content')
<div class="container">
    <div class="row justify-content-left">
        <div class="col-md-6 mt-5">
            <div class="card">
                <div class="card-header">Informações do Usuário</div>

                <div class="card-body">
                    <div>
                        <label><b>Usuário: </b>{{auth()->user()->registro}}</label>
                    </div>
                    <div>
                        <label><b>Nome: </b>{{auth()->user()->name}}</label>
                    </div>
                    <div>
                        <label><b>Email do Grupo: </b>{{auth()->user()->email_institucional}}</label><a href="#" class="muda-email-group">[alterar]</a>
                    </div>
                    <div>
                        <label><b>E-mail pessoal: </b>{{auth()->user()->email}}</label><a href="#" class="muda-email">[alterar]</a>
                    </div>

                    <div class="mt-2 text-right">
                        @if(in_array(auth()->user()->perfil,[1,2]))
                        <a href="{{route('register')}}"><button type="button" class="btn btn-primary">Cadastrar</button></a>
                        <a href="{{route('usuario.index')}}"><button type="button" class="btn btn-primary">Usuários</button></a>
                        @endif
                        <!--
                        <button type="button" class="btn btn-primary muda-senha">Alterar Senha</button>
                        -->
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6 mt-5">
            <div class="card">
                <div class="card-header">Emails que possui acesso</div>

                <div class="card-body">
                    <div class="row">
                        @foreach($emails as $email)
                        <div class="col-md-12">{{str_replace("delega.", "", $email->email)}}</div>
                        @endforeach
                    </div>
                    <div class="mt-2 text-right">
                        @if(in_array(auth()->user()->perfil,[1,2]))
                        <a href="{{route('emailDelegado.solicitar')}}"><button type="button" class="btn btn-primary">Solicitar</button></a>
                        @endif
                        <a href="{{route('emailDelegado.permissao')}}"><button type="button" class="btn btn-primary">Gerenciar</button></a>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6 mt-5">
            <div class="card">
                <div class="card-header">Equipes que faz parte</div>

                <div class="card-body">
                    <div class="row">
                        @foreach($equipes as $equipe)
                            <div class="col-md-12">{{str_replace($grupo, "", $equipe->equipe)}}</div>
                        @endforeach
                    </div>
                    <div class="mt-2 text-right">
                        @if(in_array(auth()->user()->perfil,[1,2]))
                        <a href="{{route('equipe.solicitar')}}"><button type="button" class="btn btn-primary">Solicitar</button></a>
                        @endif
                        <a href="{{route('equipe.permissao')}}"><button type="button" class="btn btn-primary">Gerenciar</button></a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="popup popup-senha">
    <div class="card ">
        <div class="card-header">Mudar senha</div>

        <div class="card-body">
            <form action="{{route("usuario.atualizarSenha")}}" method="POST" id="frmSituacao">
                @csrf
                <div class="form-group row">
                    <div class="col-md-12">
                    <label>Digite a nova senha:</label>
                    <input type="password" name="password" class="form-control">
                    </div>
                </div>
                <div class="form-group row">
                    <label  class="col-md-6"></label>
                    <div class="col-md-6 text-right">
                        <button type="submit" class="btn btn-primary">Atualizar</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
<div class="popup popup-email">
    <div class="card ">
        <div class="card-header">Mudar email</div>

        <div class="card-body">
            <form action="{{route("usuario.atualizarEmail")}}" method="POST" id="frmEmail">
                @csrf
                <div class="form-group row">
                    <div class="col-md-12">
                    <label>Digite o novo e-mail:</label>
                    <input type="email" name="email" class="form-control">
                    </div>
                </div>
                <div class="form-group row">
                    <label  class="col-md-6"></label>
                    <div class="col-md-6 text-right">
                        <button type="submit" class="btn btn-primary">Atualizar</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
<div class="popup popup-email-group">
    <div class="card ">
        <div class="card-header">Mudar email do grupo</div>

        <div class="card-body">
            <form action="{{route("usuario.atualizarEmailGrupo")}}" method="POST" id="frmEmail">
                @csrf
                <div class="form-group row">
                    <div class="col-md-12">
                    <label>Digite o novo e-mail do grupo:</label>
                    <input type="email" name="email" class="form-control">
                    </div>
                </div>
                <div class="form-group row">
                    <label  class="col-md-6"></label>
                    <div class="col-md-6 text-right">
                        <button type="submit" class="btn btn-primary">Atualizar</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
<div class="background">

</div>
<br/><br/><br/>
@endsection
@section('js')
    <script>
        $(document).ready(function() {
            $('.muda-senha').click(function () {
                $(".background").fadeIn(250);
                $(".popup-senha").fadeIn(250);
            });
            $('.muda-email').click(function () {
                $(".background").fadeIn(250);
                $(".popup-email").fadeIn(250);
            });
            $('.muda-email-group').click(function () {
                $(".background").fadeIn(250);
                $(".popup-email-group").fadeIn(250);
            });
            $(".background").click(function () {
                $(".background").fadeOut(250);
                $(".popup").fadeOut(250);
            })
        });
    </script>
@endsection
