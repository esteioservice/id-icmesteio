<?php
  function foundUserOnGoogle($user, $google_list)
  {
    foreach ($google_list as $nuser) {
      if ($nuser->getPrimaryEmail() ==  $user->email_institucional) {
        return true;
      }
    }
    return false;
  }
?>
@extends('layouts.app')
@section('css')
    <link rel="stylesheet" type="text/css" href="css/datatables.min.css"/>
    <link rel="stylesheet" type="text/css" href="{{asset('css/jquery.dataTables.min.css')}}"/>
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
        .btn-incluir{
            background-color: transparent;
            opacity: 0.9;
            padding: 5px 10px;
            border-radius: 5px;
            color: #096909;
            cursor: pointer;
            text-decoration: none;
        }
        .btn-incluir:hover{
            opacity: 1;
            color: #000957;
        }
        .btn-remover{
            background-color: transparent;
            opacity: 0.9;
            padding: 5px 10px;
            border-radius: 5px;
            color: #a81b1b;
            cursor: pointer;
            text-decoration: none;
        }
        .btn-remover:hover{
            opacity: 1;
            color: #000957;
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
        <div class="justify-content-center">
            <table id="example" class="display cell-border" style="width:100%">
                <thead>
                <tr>
                    <th>Registro</th>
                    <th>Nome</th>
                    <th>E-mail</th>
                    <th>Status</th>
                    @if(auth()->user()->perfil == 1 || $permissoes->edita)
                    <th></th>
                    @endif
                    @if($google_users != null)
                    <th></th>
                    @endif
                </tr>
                </thead>
                <tbody>
                    @foreach($usuarios as $usuario)
                    <tr>
                        <td>{{$usuario->registro}}</td>
                        <td>{{$usuario->name}}</td>
                        <td>{{$usuario->email_institucional}}</td>
                        @if($usuario->situacao == 1)
                        <td class="atualiza-status" data-id="{{$usuario->id}}" data-value="{{$usuario->situacao}}">
                            <div class="label-status green">
                                <span>Aprovado</span>
                            </div>
                        </td>
                        @elseif($usuario->situacao == 0)
                        <td class="atualiza-status" data-id="{{$usuario->id}}" data-value="{{$usuario->situacao}}">
                            <div class="label-status red">
                                <span >Pendente</span>
                            </div>

                        </td>
                        @elseif($usuario->situacao == 3)
                        <td class="atualiza-status" data-id="{{$usuario->id}}" data-value="{{$usuario->situacao}}">
                            <div class="label-status red">
                                <span >Pre-aprovado</span>
                            </div>

                        </td>
                        @elseif($usuario->situacao == 2)
                        <td class="atualiza-status" data-id="{{$usuario->id}}" data-value="{{$usuario->situacao}}">
                            <div class="label-status yellow">
                                <span>Suspenso</span>
                            </div>
                        </td>
                        @endif
                        @if(auth()->user()->perfil == 1 || $permissoes->edita)
                        <td style="text-align: center">
                            <a class="btn-editar" href="{{route("usuario.show",['usuario' => $usuario->id])}}">
                                <i class="fa fa-pencil" style=""></i>
                            </a>
                        </td>
                        @endif
                        @if($google_users != null)
                          <td style="text-align: center">
                            @if (foundUserOnGoogle($usuario, $google_users))
                              @if (Session::has('deleted') && Session::has('email') &&
                                Session::get('deleted') == "true" && urldecode(Session::get('email')) == $usuario->email_institucional)
                                <a class="btn-incluir" onclick="lockScreen()" href="{{route("usuario.addToGoogle",['email' => urlencode($usuario->email_institucional)])}}">
                                  <i class="fa fa-google" style=""></i>
                                </a>
                              @else
                                @if(auth()->user()->perfil == 1  || $permissoes->excluir)
                                  <a class="btn-remover" onclick="lockScreen()" href="{{route("usuario.deleteFromGoogle",['email' => urlencode($usuario->email_institucional)])}}">
                                    <i class="fa fa-trash" style=""></i>
                                  </a>
                                @endif
                              @endif
                            @else
                              @if (Session::has('added') && Session::has('email') &&
                                Session::get('added') == "true" && urldecode(Session::get('email')) == $usuario->email_institucional)
                                @if(auth()->user()->perfil == 1  || $permissoes->excluir)
                                  <a class="btn-remover" onclick="lockScreen()" href="{{route("usuario.deleteFromGoogle",['email' => urlencode($usuario->email_institucional)])}}">
                                    <i class="fa fa-trash" style=""></i>
                                  </a>
                                @endif
                              @else
                                <a class="btn-incluir" onclick="lockScreen()" href="{{route("usuario.addToGoogle",['email' => urlencode($usuario->email_institucional)])}}">
                                  <i class="fa fa-google" style=""></i>
                                </a>
                              @endif
                            @endif
                          </td>
                        @endif
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    <div class="popup">
        <div class="card ">
            <div class="card-header">Atualizar</div>
            <div class="card-body">
                <form action="{{route("usuario.updateSituacao")}}" method="POST" id="frmSituacao">
                    @csrf
                    <input type="hidden" name="id" id="id">
                    <div class="form-group row">
                        <label for="email" class="col-md-4 col-form-label text-md-right">Status</label>
                        <div class="col-md-6">
                            <select class="form-control" id="status" name="situacao">
                                <option value="0">Pendente</option>
                                <option value="1">Aprovado</option>
                                <option value="2">Suspenso</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label  class="col-md-4"></label>
                        <div class="col-md-6 text-right">
                            <button type="submit" class="btn btn-primary" onclick="return confirmUpdate(event)">Atualizar</button>
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
  <script type="text/javascript" src="js/datatables.min.js"></script>
  <script type="text/javascript" src="{{asset('js/DataTables-1.11.3/js/jquery.dataTables.min.js')}}"></script>
  <script type="text/javascript">
    $(document).ready(function() {
      $('#example').DataTable({
        'language': {
          'url': '{{asset('js/Portuguese-Brasil.json')}}',
        },
        order: [[4, 'asc']],
      });
      $('.atualiza-status').click(function () {
        let value = $(this).data().value;
        let id = $(this).data().id;
        $('#id').val(id);
        $('#status').val(value);
        $(".background").fadeIn(250);
        $(".popup").fadeIn(250);
      });
      $(".background").click(function () {
        $(".background").fadeOut(250);
        $(".popup").fadeOut(250);
      })
    });
    </script>
    <script type="text/javascript">
      function confirmUpdate(event) {
        @if($google_users == null && auth()->user()->perfil != 0)
          event.stopPropagation();
          if (confirm("Não ocorreu o login na Google!\n Deseja realizar operação sem propagar no directory?")) {
            return true;
          }
          return false
        @endif
        return true;
      }
    </script>
@endsection
