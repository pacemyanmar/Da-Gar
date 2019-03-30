<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>{!! setting('app_name', 'Kanaung SMS Default'); !!}</title>
    <meta content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no' name='viewport'>
    @yield('meta')

    <link rel="stylesheet" href="{{ mix('/css/app.css') }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @yield('css')

    <!-- Scripts -->
    <script>
        window.Laravel = {!!json_encode([
    'csrfToken' => csrf_token(),
]) !!}
    </script>
    @stack('before-head-end')
</head>

<body class="skin-blue sidebar-mini sidebar-collapse">
@stack('after-body-start')
@if (!Auth::guest())
    <div class="wrapper" id="app">
        <!-- Main Header -->
        <header class="main-header">

            <!-- Logo -->
            <a href="{!! url('/') !!}" class="logo">
                <b>{!! setting('app_short', 'SMS'); !!}</b>
            </a>

            <!-- Header Navbar -->
            <nav class="navbar navbar-static-top" role="navigation">
                <!-- Sidebar toggle button-->
                <a href="#" class="sidebar-toggle" data-toggle="push-menu" role="button">
                    <span class="sr-only">Toggle navigation</span>
                </a>
                <!-- Navbar Right Menu -->
                <div class="navbar-custom-menu">
                    <ul class="nav navbar-nav">
                        <li class="dropdown lang lang-menu">
                        <a class="dropdown-toggle" data-toggle="dropdown"><img src="{{ asset('images/flags/'.config('app.locale').'.png') }}"> {!! trans('locale.'.config('app.locale'))!!}</a>
                        @include('layouts.language')
                        </li>
                        <!-- User Account Menu -->
                        <li class="dropdown user user-menu">
                            <!-- Menu Toggle Button -->
                            <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                                <!-- The user image in the navbar-->
                                <img src="{{ asset('images/logo/flag_box-150.png') }}"
                                     class="user-image" alt="User Image"/>
                                <!-- hidden-xs hides the username on small devices so only the image appears. -->
                                <span class="hidden-xs">{!! Auth::user()->name !!}</span>
                            </a>
                            <ul class="dropdown-menu">
                                <!-- The user image in the menu -->
                                <li class="user-header">
                                    <img src="{{ asset('images/logo/flag_box-150.png') }}"
                                         class="img-circle" alt="User Image"/>
                                    <p>
                                        {!! Auth::user()->name !!}
                                        <small>Member since {!! Auth::user()->created_at->format('M. Y') !!}</small>
                                    </p>
                                </li>
                                <!-- Menu Footer-->
                                <li class="user-footer">
                                    <div class="pull-left">
                                        <a href="{!! route('users.show', Auth::user()->id) !!}" class="btn btn-default btn-flat">Profile</a>
                                    </div>
                                    <div class="pull-right">
                                        <a href="{!! url('/logout') !!}" class="btn btn-default btn-flat"
                                            onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                            Sign out
                                        </a>
                                        <form id="logout-form" action="{{ url('/logout') }}" method="POST" style="display: none;">
                                            {{ csrf_field() }}
                                        </form>
                                    </div>
                                </li>
                            </ul>
                        </li>
                    </ul>
                </div>
            </nav>
        </header>

        <!-- Left side column. contains the logo and sidebar -->
        @include('layouts.sidebar')
        <!-- Content Wrapper. Contains page content -->
        <div class="content-wrapper">
            @yield('content')
        </div>

        <!-- Main Footer -->
        <footer class="main-footer" style="max-height: 100px;text-align: center">
            <strong>Copyright © 2016 <a href="#">{!! setting('app_name', 'Kanaung SMS Default'); !!}</a>.</strong> All rights reserved.
        </footer>

    </div>
@else
    <nav class="navbar navbar-default navbar-static-top">
        <div class="container">
            <div class="navbar-header">

                <!-- Collapsed Hamburger -->
                <button type="button" class="navbar-toggle collapsed" data-toggle="collapse"
                        data-target="#app-navbar-collapse">
                    <span class="sr-only">Toggle Navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>

                <!-- Branding Image -->
                <a class="navbar-brand" href="{!! url('/') !!}">
                    {!! setting('app_name', 'Kanaung SMS Default'); !!}
                </a>
            </div>

            <div class="collapse navbar-collapse" id="app-navbar-collapse">
                <!-- Left Side Of Navbar -->
                <ul class="nav navbar-nav">
                    <li><a href="{!! url('/home') !!}">Home</a></li>
                </ul>

                <!-- Right Side Of Navbar -->
                <ul class="nav navbar-nav navbar-right">
                    <!-- Authentication Links -->
                    <li><a href="{!! url('/login') !!}">Login</a></li>
                    <li><a href="{!! url('/register') !!}">Register</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div id="page-content-wrapper">
        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-12">
                    @yield('content')
                </div>
            </div>
        </div>
    </div>
    @endif
    <!-- Combined vendor js -->
    <script src="{{ mix('/js/manifest.js') }}"></script>
    <script src="{{ mix('/js/vendor.js') }}"></script>

    <script type="text/javascript">
        @if(\App::getLocale() == 'mm')

        hyperform.set_language("mm");
        @endif

    </script>

    <!-- app script -->
    <script src="{{ mix('/js/app.js') }}"></script>


    @yield('scripts')

    @stack('vue-scripts')

    <script type="text/javascript">
    var ajaxoverlay = true;

    (function($) {
        @stack('document-ready');
    })(jQuery);
    if(ajaxoverlay) {
        $(document).ajaxStart(function(){
            $.LoadingOverlay("show",{
                image       : "",
                fontawesome : "fa fa-cog fa-spin"
            });
        });
        $(document).ajaxError(function(){
            $.LoadingOverlay("hide");
        });

        $(document).ajaxStop(function(){
            $.LoadingOverlay("hide");
        });

    }


     @stack('d3-js')
    </script>
     @stack('before-body-end')
    <script src="{{ mix('/js/formbuilder.js') }}"></script>
    @yield('formbuilder')
</body>
</html>
