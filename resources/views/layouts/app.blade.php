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
    <header class="text-center text-lg-start" style="background-color:#000957; color:#FFFFFF; !important;position: relative !important; left: 0; top: 0; width: 100%;">
        <nav class="navbar navbar-expand-md shadow-sm">
            <div class="container">
                <a class="navbar-brand" href="{{ url('/home') }}">
                    <img src="{{url("storage/".$logo)}}" width="200" height="80" style="background-color: white !important;">
                </a>
                <div class="col-xs-12" id="navbarSupportedContent">
                    <!-- Left Side Of Navbar -->
                    <ul class="navbar-nav mr-auto">
                    @guest

                    @else

                    @endguest
                    </ul>

                    <!-- Right Side Of Navbar -->
                    <ul class="navbar-nav ml-auto">
                        <!-- Authentication Links -->
                        @guest
                          @if (isset($hiddenNavBar))
                          <li class="nav-item">
                            <a style="text-decoration: none;color: white" class=""
                              href="{{  route('login')  }}">
                              Login
                            </a>
                          </li>
                          @endif
                        @else
                          @if (isset($hiddenNavBar))
                          <li class="nav-item">
                            <a style="text-decoration: none;color: white" class=""
                              href="{{  route('home')  }}">
                              Home
                            </a>
                          </li>
                          &nbsp;&nbsp;&nbsp;
                          @endif
                          @if (!isset($noExit))
                            <li class="nav-item dropdown">
                              <a style="text-decoration: none;color: white" class=""   onclick="event.preventDefault();
                                document.getElementById('logout-form').submit();" role="button">
                                Sair
                              </a>
                              <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdown">
                                <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                  @csrf
                                </form>
                              </div>
                            </li>
                          @endif
                        @endguest
                    </ul>
                </div>
            </div>
        </nav>

        </header>
        @if (!isset($hiddenButtons))
        <main class="py-1">
            @include('layouts.partials.alert')
            @if(auth()->check() && request()->path() != "register")
            <div class="container">
                <div class="row justify-content-left menu">
                    <div class="mt-3 p-4" {{request()->path() != "administracao" ? "class=active" : ""}} onclick=window.location.href='{{url('home')}}'>Páginal Inicial</div>
                    @if(auth()->user()->perfil == 1)
                    <div class="mt-3 p-4" {{request()->path() == "administracao" ? "class=active" : ""}} onclick=window.location.href='{{route('administracao')}}'>Administração</div>
                    @endif
                    @if(in_array(auth()->user()->perfil,[1,2]))
                    <div id="googleLog" class="mt-3" onclick="oAuth2GoogleLog();">
                      <img src="{{asset('img/google_icon.png')}}" >
                      @if(isset($access_token) && $access_token)
                        Logout
                      @else
                        Login
                      @endif
                    @endif
                    </div>
                </div>
            </div>
            @endif
            @yield('content')
        </main>
        @else
          <main id="documentReader" class="ml-3 mr-2 mt-4 mb-5">

          </main>
        @endif
    </div>
    <script type="text/javascript" src="{{asset('js/bootstrap.min.js')}}"></script>
    <script type="text/javascript" src="{{ asset('js/default.js') }}"></script>
    @yield('js')
    <footer class="text-center text-lg-start" style="background-color:#000957 !important; position: fixed !important; left: 0; bottom: 0; width: 100%;">
      <div class="container p-1">
        <div class="row">
          <div class="col-md-4 col-sm-12">
            <a class="text-light" href="{{ route('termos') }}">Termos de Uso</a>
          </div>
          <div class="col-md-4 col-sm-12" style="color:#FFFFFF;">
            © 2023 Copyright&nbsp;-&nbsp;icmesteio.org.br
          </div>
          <div class="col-md-4 col-sm-12">
            <a class="text-light" href="{{ route('politicas') }}">Politicas de Privacidade</a>
          </div>
        </div>
      </div>
    </footer>
</body>
</html>
