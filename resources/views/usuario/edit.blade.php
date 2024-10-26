@extends('layouts.app')
@section('content')
    <div class="container">
        <div class="justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">Atualizar</div>

                    <div class="card-body">
                        <form method="POST" action="{{ route('usuario.update',["usuario" => $usuario->id]) }}">
                            @method("PUT")
                            @csrf
                            <div class="form-group row">
                                <label for="name" class="col-md-4 col-form-label text-md-right">Nome Completo</label>

                                <div class="col-md-6">
                                    <input id="name" type="text" class="form-control @error('name') is-invalid @enderror" name="name" value="{{$usuario->name}}" required autocomplete="name" autofocus>

                                    @error('name')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="name" class="col-md-4 col-form-label text-md-right">Registro</label>

                                <div class="col-md-6">
                                    <input id="registro" type="text" class="form-control @error('registro') is-invalid @enderror" name="registro" value="{{$usuario->registro}}"
                                     required autocomplete="registro" autofocus onchange="removeAcentos(this)">

                                    @error('registro')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>

                            <div class="form-group row">
                                <label for="email" class="col-md-4 col-form-label text-md-right">E-mail</label>

                                <div class="col-md-6">
                                    <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{$usuario->email}}"
                                     required autocomplete="email" onchange="removeAcentos(this)">

                                    @error('email')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>
                            @if(auth()->user()->perfil == 1)
                            <div class="form-group row">
                              <label for="email_institucional" class="col-md-4 col-form-label text-md-right">E-mail institucional</label>
                                <div class="col-md-4">
                                  <input id="email_institucional" type="text" class="form-control" name="email_institucional" value="{{substr($usuario->email_institucional, 0, strpos($usuario->email_institucional, '@'))}}"
                                    required autocomplete="email_institucional" onchange="removeAcentos(this);" onkeypress="return removeKeys(event);">

                                    @error('email_institucional')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                                <div class="col-md-4 mt-1">
                                  <span>@icmesteio.org.br</span>
                                </div>
                            </div>
                            @endif
                            <div class="form-group row">
                                <label for="email" class="col-md-4 col-form-label text-md-right">Status</label>

                                <div class="col-md-6">
                                    <select class="form-control" name="situacao">
                                        <option value="0" {{$usuario->situacao == 0 ? "selected" : ""}}>Pendente</option>
                                        <option value="1" {{$usuario->situacao == 1 ? "selected" : ""}}>Aprovado</option>
                                        <option value="2" {{$usuario->situacao == 2 ? "selected" : ""}}>Suspenso</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="email" class="col-md-4 col-form-label text-md-right">Perfil</label>

                                <div class="col-md-6">
                                    <select class="form-control" name="perfil">
                                        <option value="0" {{$usuario->perfil == 0 ? "selected" : ""}}>Usuário</option>
                                        @if(auth()->user()->perfil == 1)
                                        <option value="1" {{$usuario->perfil == 1 ? "selected" : ""}}>Administrador</option>
                                        @endif
                                        <option value="2" {{$usuario->perfil == 2 ? "selected" : ""}}>Gerente</option>
                                    </select>

                                </div>
                            </div>

                            <div class="form-group row">
                                <label for="password" class="col-md-4 col-form-label text-md-right">Senha</label>

                                <div class="col-md-6">
                                    <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password"  autocomplete="new-password">

                                    @error('password')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>

                            <div class="form-group row mb-0">
                                <div class="col-md-6 offset-md-4">
                                    <button type="submit" class="btn btn-primary" onclick="return confirmUpdate(event)">
                                      Atualizar
                                    </button>
                                    <button type="button" name="excluir"  class="excluir btn btn-danger" onclick="return confirmUpdate(event)">
                                        Excluir
                                    </button>
                                    <a href="{{route('usuario.index')}}">
                                      <button type="button" class="btn btn-primary">Voltar</button>
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <br/><br/><br/>
    <form action="{{route("usuario.destroy",["usuario" => $usuario->id])}}" id="frmExcluir" method="POST">
        @method("DELETE")
        @csrf
    </form>
@endsection
@section('js')
<script>
    var flag = true;
    $(document).ready(function () {
      $('.excluir').click(function () {
        if (flag) {
          $("#frmExcluir").submit();
        }
      });
    });
</script>
<script type="text/javascript">
  function removeKeys(event) {
    const charCode = event.keyCode;
    if (charCode == 64 || charCode == 32) {
        return false;
    }
    return true;
  }
  function confirmUpdate(event) {
    @if((!Session::has('access_token') || empty(Session::get('access_token'))) && auth()->user()->perfil != 0)
      event.stopPropagation();
      if (confirm("Não ocorreu o login na Google!\n Deseja realizar operação sem propagar no directory?")) {
        flag = true;
        return true;
      } else {
        flag = false;
        return false;
      }
    @endif
    return true;
  }
</script>
@endsection
