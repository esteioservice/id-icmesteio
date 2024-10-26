@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8 mt-5">
            <div class="card">
                <div class="card-header">{{ __('Login') }}</div>

                <div class="card-body">
                    <form method="POST" action="{{ route('login') }}">
                        @csrf

                        <div class="form-group row">
                            <label for="email" class="col-md-4 col-form-label text-md-right">Usuário</label>

                            <div class="col-md-6">
                                <input id="email" type="text" class="form-control" name="username" value="{{ old('email') }}" required autocomplete="email" autofocus>


                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="password" class="col-md-4 col-form-label text-md-right">Senha</label>

                            <div class="col-md-6">
                                <input id="password" type="password" class="form-control" name="password" required autocomplete="current-password">
                            </div>
                        </div>
                        <!--
                        <div class="form-group row">
                          <label for="google" class="col-sm-6 col-md-6 col-form-label text-md-right text-sm-right" style="margin-left: 65px !important;">Fazer login na Google</label>
                            <div class="col-sm-4 col-md-1 text-md-left text-sm-left">
                              <input type="checkbox" id="google" name="google" class="form-control" style="margin-top: 9px; width:20px !important; height:20px !important;">
                            </div>
                        </div>
                        -->
                        @if(Session::has('error'))
                        <div class="form-group row">
                            <label class="col-md-4 "></label>

                            <div class="col-md-6">
                                <span class="invalid-feedback" style="display: block!important;" role="alert">
                                    <strong>{{ Session::get('error') }}</strong>
                                </span>
                            </div>
                        </div>
                        @endif
                        <div class="form-group row">
                            <div class="col-md-8 offset-md-2 text-center">
                                <button type="submit" class="btn btn-primary">
                                    Acessar
                                </button>
                                &nbsp;&nbsp;&nbsp;
                                @if (Route::has('register'))
                                    <a href="{{ route('register') }}"><button type="button" class="btn btn-primary" >
                                        Cadastrar
                                    </button>
                                    </a>
                                @endif
                            </div>

                        </div>
                        <div class="col-md-8 offset-md-2 text-center">

                            @if (Route::has('password.request'))
                                <a  href="{{ route('password.request') }}">
                                    Esqueci minha senha
                                </a>
                            @endif
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
