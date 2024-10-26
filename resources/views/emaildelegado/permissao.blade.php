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
            width: 60%;
            top: 5%;
            left: 21%;
        }
        .atualiza-status:hover{
            opacity: 1;
        }
        #lista{
            margin-top: 20px;
        }

        #lista > div{
            padding: 10px;
            border-bottom: solid 1px lightgrey;
        }

    </style>
@endsection

@section('content')
    <div class="container">
        <div class="justify-content-center">
            <div class="row">
                <div class="col-md-12 mb-5">
                    <span style="font-size: 25px;font-weight: 600">E-mails que possui acesso</span>
                </div>

            </div>
            <table id="example" class="display cell-border" style="width:100%">
                <thead>
                <tr>
                    <th>Nome</th>
                    <th>E-mail</th>
                    <th style="text-align: center">Permissões</th>
                </tr>
                </thead>
                <tbody>
                @foreach($emails as $email)
                    <tr>
                        <td width="40%">{{$email->nome}}</td>
                        <td width="40%">{{ str_replace("delega.", "", $email->email) }}</td>
                        <td style="text-align: center">
                            <button class="permissao btn btn-success" data-id="{{$email->id}}" data-email="{{$email->email}}">Permissões</button>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
    <div class="popup">
        <div class="card ">
           <div class="p-3">
               <span style="font-size: 20px;font-weight: 600">Permissões de acesso:</span><br>
               <span style="font-size: 20px;" id="permissao-email" ></span>
           </div>

            <div class="card-body">
                @if(auth()->user()->perfil == 1 || ((auth()->user()->perfil == 2 && $permissoes->adiciona)))
                <form action="{{route("emailDelegado.remover")}}" id="frmRemover" method="post" onsubmit="return confirmUpdate(event);">
                    @csrf
                    <input type="hidden" name="id" id="idRemover">
                    <input type="hidden" name="group" id="group" value="">
                    <input type="hidden" name="user"  id="user"  value="">
                </form>
                <form action="{{route('emailDelegado.adicionar')}}" method="post" onsubmit="return confirmUpdate(event);">
                    @csrf
                    <input type="hidden" name="id" id="idAcesso">
                    <input type="hidden" name="group" id="group" value="">
                    <div class="row">
                        <div class="col-md-4">
                        <input type="text" class="form-control" name="chave" required placeholder="e-mail do usuário" >
                        </div>
                        <button type="submit" class="btn btn-primary" onclick="setUserMail(null)">Adicionar</button>
                    </div>
                </form>
                @endif
                <div id="lista">

                </div>
            </div>
        </div>
    </div>
    <div class="background">

    </div>
    <br/><br/><br/>
@endsection
@section('js')
    <script type="text/javascript" src="{{asset('js/datatables.min.js')}}"></script>
    <script type="text/javascript" src="{{asset('js/DataTables-1.11.3/js/jquery.dataTables.min.js')}}"></script>
    <script>
      function setUserMail(event) {
        if (event) {
          let obj  = event.srcElement;
          var user = document.getElementById("user");
          user.value = obj.id;
        }
        let per = document.getElementById("permissao-email");
        var group =  document.getElementById("group");
        group.value = per.innerHTML;
      }
      function confirmUpdate(event) {
        event.stopPropagation();
        @if ($access_token == null && (!Session::has('access_token') || Session::get('access_token') == null) && auth()->user()->perfil != 0)
          if (confirm("Não ocorreu o login na Google!\n Deseja realizar operação sem propagar no directory?")) {
            return true;
          }
          return false
        @endif
        return true;
      }
        $(document).ready(function() {
            $('#example').DataTable({
                'language': {
                    'url': '{{asset('js/Portuguese-Brasil.json')}}',
                }
            });
            $('.permissao').click(function () {
                let value = $(this).data().value;
                let id = $(this).data().id;
                let email = $(this).data().email;
                $.ajax({
                    url : '{{route('emailDelegado.getAcesso')}}',
                    method : "post",
                    data : {'id' : id, 'email' : email},
                    success : function (result) {
                        $('#idAcesso').val(id);
                        $('#permissao-email').html(email.replace('delega.', ''));
                        $('#lista').html(result);
                        $(".background").fadeIn(250);
                        $(".popup").fadeIn(250);
                    }
                })
                $('#id').val(id);
                $('#status').val(value);

            });
            $(".background").click(function () {
                $(".background").fadeOut(250);
                $(".popup").fadeOut(250);
            })

        });
    </script>
@endsection
