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
            position: fixed;
        }
        .card{
            width: 100%;
        }
        .popup{
            display: none;
            position: fixed;
            z-index: 4;
            width: 35%;
            top: 10%;
            left: 33%;
        }
        .popup-termo{
            display: none;
            position: fixed;
            z-index: 4;
            width: 35%;
            top: 5%;
            left: 35%;
        }
        .atualiza-status:hover{
            opacity: 1;
        }
        .grupo-check{
            font-size: 1px;
            text-align: center;
        }
    </style>
@endsection
@section('content')
    <div class="container">
        <div class="justify-content-center">
            <div class="card">
                <div class="card-header">Administador</div>
                <div class="card-body">
                    <form action="{{route('administracao.update')}}" method="post" enctype="multipart/form-data">
                        @csrf
                        <div class="row">
                            <div class="col-md-6">
                                <div class="row form-group">
                                    <div class="col-md-1 mt-1">
                                        <label>www.</label>
                                    </div>
                                    <div class="col-md-8">
                                        <input type="text" name="grupo" class="form-control" value="{{$administracao->grupo}}">
                                    </div>

                                </div>
                                <div class="row form-group">
                                    <div class="col-md-2 mt-1">
                                    <label>Nome: </label>
                                    </div>
                                    <div class="col-md-8">
                                    <input type="text" name="nome" class="form-control" value="{{$administracao->nome}}">
                                    </div>
                                </div>
                                <div class="row form-group">
                                    <div class="col-md-2 mt-1">
                                    <label>Numeral: </label>
                                    </div>
                                    <div class="col-md-8">
                                    <input type="text" name="numeral" class="form-control" value="{{$administracao->numeral}}">
                                    </div>
                                </div>
                                <div class="row form-group">
                                    <div class="col-md-2 mt-1">
                                    <label>Usuário</label>
                                    </div>
                                    <div class="col-md-8">
                                    <input type="text" name="usuario" class="form-control" value="{{$administracao->usuario}}">
                                    </div>
                                </div>
                                <div class="row form-group">
                                    <div class="col-md-2 mt-1">
                                    <label>Senha</label>
                                    </div>
                                    <div class="col-md-8">
                                    <input type="password" name="senha" class="form-control" value="">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 mb-2">
                                <div class="row">
                                    <div class="col-md-12">
                                        <input type="file" name="logo" id="logo" accept="image/*" style="display: none">
                                        <img src="{{asset('storage/'.$administracao->logo)}}" id="img" width="205" height="80" style="box-shadow: -2px 3px 5px 0px #c7c7c7;float: right;">
                                    </div>
                                    <div class="col-md-12 mt-4">
                                        <label for="logo" class="btn btn-primary" style="float: right;margin-right: 45px;">Inserir Logo</label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-12 mb-2">
                                <a href="{{route("usuario.index")}}"><button type="button" class="btn btn-primary">Usuários</button></a>
                                <button type="button" id="grupo-usuarios" class="btn btn-primary">Grupo Usuários</button>
                            </div>
                            <div class="col-md-11 mb-2" style="display: flex">
                                <div>
                                    <a href="{{route("emailDelegado.solicitar")}}"><button type="button" class="btn btn-primary">Aprovar e-mails</button></a>
                                </div>
                                <div class="ml-2">
                                    <a href="{{route("equipe.solicitar")}}"><button type="button" class="btn btn-primary">Aprovar Equipes</button></a>
                                </div>
                                <div class="ml-2">
                                    <button type="button" class="btn btn-primary" id="termo">Termos de serviço</button>
                                </div>
                            </div>
                            <div class="col-md-1">
                                <button class="btn btn-primary">Salvar</button>
                            </div>
                        </div>
                        <div class="popup-termo">
                            <div class="card ">
                                <div class="card-header">Termos de serviço</div>

                                <div class="card-body">
                                    <textarea name="termos" class="form-control">{{$administracao->termos}}</textarea>
                                </div>
                            </div>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>

    <div class="popup">
        <div class="card ">
            <div class="p-3">
                <span style="font-size: 20px;font-weight: 600">Grupo de usuários:</span><br>
                <span style="font-size: 20px;" id="permissao-email" ></span>
            </div>

            <div class="card-body" style="font-size: 15px">
                <form action="{{route("administracao.alterarGrupo")}}" method="POST">
                    @csrf
                    <div class="row form-group">
                        <div class="col-md-3"></div>
                        <div class="col-md-2 text-center"><b>Adicionar</b></div>
                        <div class="col-md-2 text-center"><b>Editar</b></div>
                        <div class="col-md-2 text-center"><b>Excluir</b></div>
                        <div class="col-md-2 text-center"><b>Remover</b></div>
                       
                    </div>
                    @foreach($gruposUsuarios as $grupo)
                    <div class="row form-group">
                        <div class="col-md-3">
                            @if($grupo->perfil_id == 0)
                                Usuário
                            @elseif($grupo->perfil_id == 1)
                                Administrador
                            @elseif($grupo->perfil_id == 2)
                                Gerente
                            @endif
                        </div>
                        <div class="col-md-2"><input type="checkbox" {{$grupo->perfil_id != 2 ? "disabled" : "" }} name="{{$grupo->perfil_id}}-adiciona" {{$grupo->adiciona == 1 ? "checked" : ""}} class="grupo-check form-control"></div>
                        <div class="col-md-2"><input type="checkbox" {{$grupo->perfil_id != 2 ? "disabled" : "" }} name="{{$grupo->perfil_id}}-edita" {{$grupo->edita == 1 ? "checked" : ""}} class="grupo-check form-control"></div>
                        <div class="col-md-2"><input type="checkbox" {{$grupo->perfil_id != 2 ? "disabled" : "" }} name="{{$grupo->perfil_id}}-excluir" {{$grupo->excluir == 1 ? "checked" : ""}} class="grupo-check form-control"></div>
                        
                        <div class="col-md-2"><input type="checkbox" {{$grupo->perfil_id != 2 ? "disabled" : "" }} name="{{$grupo->perfil_id}}-remover" {{$grupo->remover == 1 ? "checked" : ""}} class="grupo-check form-control"></div>
                    </div>
                    @endforeach
                    <div class="row">
                        <div class="col-md-12">
                            <button type="submit" class="btn btn-primary">Salvar</button>
                        </div>
                    </div>
                </form>

            </div>
        </div>
    </div>
    <div class="background">

    </div>
@endsection
@section('js')
<script>
    $(document).ready(function(){
        $('#grupo-usuarios').click(function () {
            $(".background").fadeIn(250);
            $(".popup").fadeIn(250);
        });
        $('#termo').click(function () {
            $(".background").fadeIn(250);
            $(".popup-termo").fadeIn(250);
        });
        $(".background").click(function () {
            $(".background").fadeOut(250);
            $(".popup").fadeOut(250);
            $(".popup-termo").fadeOut(250);
        });
        $('#logo').change(function () {

            var reader = new FileReader();

            reader.onload = function (e) {
                document.getElementById('img').src = e.target.result;
            }

            reader.readAsDataURL($(this)[0].files[0]);
        });
        $(".background").click(function () {
            $(".background").fadeOut(250);
            $(".popup").fadeOut(250);
        })
    });
</script>
@endsection

