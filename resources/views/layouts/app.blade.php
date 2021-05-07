<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title')</title>

    <!-- Scripts -->
    <script src="https://odds-tipster.herokuapp.com/js/app.js"></script>
{{--    <script src="{{ asset('js/app.js') }}"></script>--}}

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet">

    <!-- Styles -->
    <link rel="stylesheet" href="https://odds-tipster.herokuapp.com/css/app.css">
    <link rel="stylesheet" href="https://odds-tipster.herokuapp.com/css/style.css">
{{--    <link href="{{ asset('css/app.css') }}" rel="stylesheet">--}}
{{--    <link href="{{ asset('css/style.css') }}" rel="stylesheet">--}}
</head>
<body>
    <div id="app">
        <nav class="navbar navbar-expand-md navbar-light bg-white shadow-sm">
            <div class="container">
                <a class="navbar-brand" href="{{ url('/') }}">
                    {{ config('app.name', 'Odds Tipster') }}
                </a>
                <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <!-- Left Side Of Navbar -->
                    <ul class="navbar-nav mr-auto">

                    </ul>

                    <!-- Right Side Of Navbar -->
                    <ul class="navbar-nav ml-auto">
                        <!-- Authentication Links -->
                        @guest
                            @if (Route::has('login'))
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('login') }}">{{ __('Login') }}</a>
                                </li>
                            @endif

                            @if (Route::has('register'))
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('register') }}">{{ __('Register') }}</a>
                                </li>
                            @endif
                        @else
                            <li class="nav-item dropdown">
                                <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                                    {{ Auth::user()->name }}
                                </a>

                                <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdown">
                                    <a class="dropdown-item" href="{{ route('logout') }}"
                                       onclick="event.preventDefault();
                                                     document.getElementById('logout-form').submit();">
                                        {{ __('Logout') }}
                                    </a>

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

        <main class="py-4">
            <div class="container">


                @if(isset($sport_types))
                    <div class="row">

                        <div class="col-md-3">
                            @php
                                $request_sport_type = \Request::get('sport_id');
                                $request_cat_type = \Request::get('cat_id');
                            @endphp

                            <a class="d-md-none list-group-item" data-toggle="collapse" href="#sidebarCollapse" role="button" aria-expanded="false" aria-controls="sidebarCollapse">Sport Types {{ $request_sport_type ? ' >> ' . ($sport_types->where('slug' , $request_sport_type)->first() ? $sport_types->where('slug' , $request_sport_type)->first()->name : '' )  : '' }}</a>

                            <ul id="sidebarCollapse" class="list-group list-unstyled collapse d-md-flex">
                                @foreach($sport_types->where('parent_id' ,0) as $k => $sport_type)
                                    <li class="my-1">
                                        <a href="#cat-{{ $sport_type->slug }}" aria-expanded="false" data-toggle="collapse" class="rounded-top px-2 py-1 dropdown-toggle list-group-item collapsed" >
                                            <span>{{ strtoupper($sport_type->name)  }}</span>
                                        </a>
                                        <ul class="collapse list-unstyled {{  $request_sport_type == $sport_type->id ? 'show' : '' }}" id="cat-{{ $sport_type->slug }}" >
                                            @foreach($sport_types->where('parent_id' , $sport_type->id) as $c_k => $category)
                                                <a class="list-group-item px-2 py-1 list-group-item collapsed {{ $request_cat_type == $category->id ? 'active' : '' }}" href="{{  url('/' ) . '?' . http_build_query(['sport_id' => $sport_type->id , 'cat_id' => $category->id]) }}" >{{ $category->name }}</a>
                                            @endforeach
                                        </ul>
                                    </li>

                                @endforeach
                                @if(!count($sport_types))
                                    <a class="list-group-item"><span>Sorry ..</span></a>
                                @endif
                            </ul>
                        </div>
                        <div class="col-md-9 mt-3 mt-md-0">
                            @yield('content')
                        </div>
                    </div>
                @else
                    <div>
                        @yield('content')
                    </div>
                @endif
            </div>
        </main>
    </div>
    {{--  This files will be mixed with all jses  --}}
    {{--  The Heroku will be load without https protocol  --}}
    <script src="https://odds-tipster.herokuapp.com/js/script.js"></script>
{{--    <script src="{{asset('js/script.js')}}"></script>--}}
    @yield('script')
</body>
</html>
