<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Scripts -->
    <!-- <script src="{{ asset('js/jquery.min.js') }}"></script> -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet">

    <!-- Styles -->
    <link rel="stylesheet" href="{{asset('css/font-awesome-4.6.2/css/font-awesome.min.css')}}" rel="stylesheet">
    <link href="{{ asset('css/bootstrap.min.css') }}" rel="stylesheet">
    <style>
        body{
            font-family: 'Nunito', sans-serif
        }
        .active{
            background-color: #384ad3;
            color : white !important;
        }
        .menu > div{
            color:black;
            text-align: center;
            width: 200px;
            padding: 10px;
            margin: 15px 0 15px 15px;
            cursor: pointer;
        }
        .menu > div:hover{
            transition: all .4s ease;
            -webkit-transition: all .4s ease;
            background-color: #384ad3;
            color : white;
        }
.titulo > div > span{
    font-weight: 600;
}
    </style>
    @yield('css')
</head>
<body>
    <div id="app">
        @if (!isset($hiddenNavBar))
        <nav class="navbar navbar-expand-md navbar-light bg-white shadow-sm">
            <div class="container">
                <a class="navbar-brand" href="{{ url('/home') }}">
                    <img src="{{url("storage/".$logo)}}" width="200" height="80">
                </a>
                <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <!-- Left Side Of Navbar -->
                    <ul class="navbar-nav mr-auto">
                    @guest

                    @else
                      <li class="nav-item dropdown mr-5">
                        <a href="/docs/termos" target="_blank" style="text-decoration: none;color: black">
                          Termos de uso
                        </a>
                      </li>

                      <li class="nav-item dropdown">
                        <a href="/docs/politicas" target="_blank" style="text-decoration: none;color: black">
                          Políticas de privacidade
                        </a>
                      </li>
                    @endguest
                    </ul>

                    <!-- Right Side Of Navbar -->
                    <ul class="navbar-nav ml-auto">
                        <!-- Authentication Links -->
                        @guest

                        @else
                            <li class="nav-item dropdown">
                                <a id="navbarDropdown" style="text-decoration: none;color: black" class=""   onclick="event.preventDefault();
                                                     document.getElementById('logout-form').submit();" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                                    Sair
                                </a>

                                <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdown">

                                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                        @csrf
                                    </form>
                                </div>
                            </li>
                        @endguest
                    </ul>
                </div>
            </div>
        </nav>
        @endif
        @if (!isset($hiddenButtons))
        <main class="py-4">
            @include('layouts.partials.alert')
            @if(auth()->check() && request()->path() != "register")
            <div class="container">
                <div class="row justify-content-left menu">
                    <div class="mt-3 p-4" {{request()->path() != "administracao" ? "class=active" : ""}} onclick=window.location.href='{{url('home')}}'>Páginal Inicial</div>
                    @if(auth()->user()->perfil == 1)
                    <div class="mt-3 p-4" {{request()->path() == "administracao" ? "class=active" : ""}} onclick=window.location.href='{{route('administracao')}}'>Administração</div>
                    @endif
                    <div id="googleLog" class="mt-3" onclick="oAuth2GoogleLog();">
                      <img src="{{asset('img/google_icon.png')}}" >
                      @if(isset($access_token) && $access_token)
                        Logout
                      @else
                        Login
                      @endif
                    </div>
                </div>
            </div>
            @endif
            @yield('content')
        </main>
        @endif
    </div>
    <script type="text/javascript" src="{{asset('js/bootstrap.min.js')}}"></script>
    <script type="text/javascript" src="{{ asset('js/default.js') }}"></script>

    @yield('js')
</body>
</html>
