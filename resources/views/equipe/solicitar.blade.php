<?php
  function foundGroupOnGoogle($equipe, $google_list)
  {
    foreach ($google_list as $ngroup) {
      if ($ngroup->getEmail() ==  $equipe) {
        return true;
      }
    }
    return false;
  }
?>
@extends('layouts.app')
@section('css')

    <link rel="stylesheet" type="text/css" href="{{asset('css/datatables.min.css')}}"/>
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
            position: fixed;
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
            <div class="row">
                <div class="col-md-12 mb-5">
                    <span style="font-size: 25px;font-weight: 600">Solicitar criação drive da equipe</span>
                </div>
                <div class="col-md-6 mt-1">
                    <p>Aqui você poderá solicitar a criação de um drive para sua equipe.</p>
                    <p>As equipes devem seguir o padrão: <b>equipe-xxx</b>, podendo escolher o que será colocado no lugar de <b>'xxx'</b> e não podendo possuir espaços ou letras acentuadas.</p>
                </div>
                <div class="col-md-6 mt-1">
                    <form action="{{route('equipe.store')}}" method="post">
                        @csrf
                        <div class="form-group">
                            <label>Nome da equipe</label>
                            <input type="text" name="nome" required class="form-control">
                        </div>
                        <div class="form-group">
                            <label>Equipe</label>
                            <div class="row">
                                <div class="col-md-6" style="display: flex"><span style="padding: 7px 0px">equipe-&nbsp</span>
                                <input type="text" name="equipe" required class="form-control" onkeypress="return removeKeys(event);" onchange="removeReservedWords(event);"></div>
                                <div class="col-md-6" style="padding: 7px 0px">{{ $grupo }}</div>

                            </div>
                        </div>
                        <div class="form-group text-center">
                            <button type="submit" class="btn btn-primary" onclick="return confirmUpdate(event)">Solicitar Criação da equipe</button>
                        </div>
                    </form>
                </div>
            </div>
            <table id="example" class="display cell-border" style="width:100%">
                <thead>
                <tr>
                    <th>Equipe solicitada</th>
                    <th>Nome da equipe</th>
                    <th>Status</th>
                    @if(auth()->user()->perfil == 1 || $permissoes->excluir)<th style="width: 30px">Excluir</th>
                    @elseif(auth()->user()->perfil == 2)<th style="width: 30px">Remoção</th>@endif
                    @if($google_groups != null)
                    <th></th>
                    @endif
                </tr>
                </thead>
                <tbody>
                @foreach($equipes as $equipe)
                    <tr>
                        <td>{{ str_replace($grupo, "", $equipe->equipe)}} </td>
                        <td>{{$equipe->nome}}</td>
                        @if($equipe->status == 1)
                            <td class="atualiza-status" data-id="{{$equipe->id}}" data-value="{{$equipe->status}}">
                                <div class="label-status green ">
                                    <span >Aprovado</span>
                                </div>
                            </td>

                        @elseif($equipe->status == 0)
                            <td class="atualiza-status" data-id="{{$equipe->id}}" data-value="{{$equipe->status}}">
                                <div class="label-status red ">
                                    <span >Pendente</span>
                                </div>
                            </td>
                        @elseif($equipe->status == 2)
                            <td class="atualiza-status" data-id="{{$equipe->id}}" data-value="{{$equipe->status}}">
                                <div class="label-status red">
                                    <span >À Excluir</span>
                                </div>
                            </td>
                        @endif
                        @if(auth()->user()->perfil == 1 || $permissoes->excluir)
                        <td style="width: 30px;text-align: center">
                            <a data-toggle="tooltip" data-placement="top" data-id="{{$equipe->id}}" class="excluir" title="Excluir" style="color:#f71c1c;font-size: 20px;cursor: pointer">
                                <i class="fa fa-close"></i>
                            </a>
                            </td>
                        @elseif(auth()->user()->perfil == 2)
                            <td style="width: 30px;text-align: center">
                                @if( $equipe->status != 2)
                                    <a data-toggle="tooltip" data-placement="top" data-id="{{$equipe->id}}" class="solicitar-remocao"  title="Solicitar remoção" style="color:#f71c1c;font-size: 20px;cursor: pointer">
                                        <i class="fa fa-close"></i>
                                    </a>
                                @endif
                            </td>
                        @endif
                        @if($google_groups != null)
                          <td style="text-align: center">
                            @if (foundGroupOnGoogle($equipe->equipe, $google_groups))
                              @if (Session::has('deleted') && Session::has('equipe') &&
                                Session::get('deleted') == "true" && urldecode(Session::get('equipe')) == $equipe->equipe)
                                <a class="btn-incluir" onclick="lockScreen()" href="{{route("equipe.addToGoogle",['equipe' => urlencode($equipe->equipe)])}}">
                                  <i class="fa fa-google" style=""></i>
                                </a>
                              @else
                                @if(auth()->user()->perfil == 1  || $permissoes->excluir)
                                  <a class="btn-remover" onclick="lockScreen()" href="{{route("equipe.deleteFromGoogle",['equipe' => urlencode($equipe->equipe)])}}">
                                    <i class="fa fa-trash" style=""></i>
                                  </a>
                                @endif
                              @endif
                            @else
                              @if (Session::has('added') && Session::has('equipe') &&
                                Session::get('added') == "true" && urldecode(Session::get('equipe')) == $equipe->equipe)
                                @if(auth()->user()->perfil == 1  || $permissoes->excluir)
                                  <a class="btn-remover" onclick="lockScreen()" href="{{route("equipe.deleteFromGoogle",['equipe' => urlencode($equipe->equipe)])}}">
                                    <i class="fa fa-trash" style=""></i>
                                  </a>
                                @endif
                              @else
                                <a class="btn-incluir" onclick="lockScreen()" href="{{route("equipe.addToGoogle",['equipe' => urlencode($equipe->equipe)])}}">
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

    @if(auth()->user()->perfil == 1 || $permissoes->excluir)
        <form action="{{route("equipe.excluir")}}" method="post" id="frmExcluir">
            @csrf
            <input type="hidden" id="id-excluir" name="id">
        </form>
    @elseif(auth()->user()->perfil == 2 )
        <form action="{{route("equipe.solicitaRemocao")}}" method="post" id="frmSolicitaRemocao">
            @csrf
            <input type="hidden" id="id-remocao" name="id">
        </form>
    @endif

    @if(auth()->user()->perfil == 1 || $permissoes->edita)
    <div class="popup">
        <div class="card ">
            <div class="card-header">Atualizar</div>

            <div class="card-body">
                <form action="{{route("equipe.updateSituacao")}}" method="POST" id="frmSituacao">
                    @csrf
                    <input type="hidden" name="id" id="id">
                    <div class="form-group row">
                        <label for="email" class="col-md-4 col-form-label text-md-right">Status</label>
                        <div class="col-md-6">
                            <select class="form-control" id="status" name="status">
                                <option value="0">Pendente</option>
                                <option value="1" >Aprovado</option>
                                <option value="2">À Excluir</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label  class="col-md-4"></label>
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
    @endif
@endsection
@section('js')
    <script type="text/javascript" src="{{asset('js/datatables.min.js')}}"></script>
    <script type="text/javascript" src="{{asset('js/DataTables-1.11.3/js/jquery.dataTables.min.js')}}"></script>
    <script type="text/javascript">
    function removeKeys(event) {
      const charCode = event.keyCode;
      if (charCode == 64 || charCode == 32) {
          return false;
      }
      return true;
    }
    function removeReservedWords(event) {
      let src = event.srcElement;
      let r1 = src.value.replace('delega.','');
      let r2 = r1.replace('equipe-','');
      src.value = r2;
    }
    function confirmUpdate(event) {
        if (event != null) {
          event.stopPropagation();
        }
      @if($google_groups == null && auth()->user()->perfil != 0)
        if (confirm("Não ocorreu o login na Google!\n Deseja realizar operação sem propagar no directory?")) {
          return true;
        }
        return false
      @endif
      return true;
    }
        $(document).ready(function() {
            $('.solicitar-remocao').click(function () {
             if (confirmUpdate(null)) {
               let id = $(this).data().id;
               $('#id-remocao').val(id);
               $('#frmSolicitaRemocao').submit();
             }
            });
            $('.excluir').click(function () {
              if (confirmUpdate(null)) {
                let id = $(this).data().id;
                $('#id-excluir').val(id);
                $('#frmExcluir').submit();
              }
            });
            $('#example').DataTable({
                'language': {
                    'url': '{{asset('js/Portuguese-Brasil.json')}}',
                }
            });
            $('.atualiza-status').click(function () {
              if (confirmUpdate(null)) {
                let value = $(this).data().value;
                let id = $(this).data().id;
                $('#id').val(id);
                $('#status').val(value);
                $(".background").fadeIn(250);
                $(".popup").fadeIn(250);
              }
            });
            $(".background").click(function () {
                $(".background").fadeOut(250);
                $(".popup").fadeOut(250);
            })
        });
    </script>
@endsection
