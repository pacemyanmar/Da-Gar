<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>{!! settings('app_name', 'Kanaung SMS Default'); !!}</title>
    <meta content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no' name='viewport'>
    @yield('meta')

    <link rel="stylesheet" href="{{ elixir('css/vendor.css') }}">
    <link rel="stylesheet" href="{{ elixir('css/app.css') }}">

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
                <b>{!! settings('app_short', 'SMS'); !!}</b>
            </a>

            <!-- Header Navbar -->
            <nav class="navbar navbar-static-top" role="navigation">
                <!-- Sidebar toggle button-->
                <a href="#" class="sidebar-toggle" data-toggle="offcanvas" role="button">
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
            <strong>Copyright © 2016 <a href="#">{!! settings('app_name', 'Kanaung SMS Default'); !!}</a>.</strong> All rights reserved.
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
                    {!! settings('app_name', 'Kanaung SMS Default'); !!}
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
    <script src="{{ elixir('js/vendor.js') }}"></script>
    <script type="text/javascript">
        @if(\App::getLocale() == 'mm')
        hyperform.add_translation("mm",{
            TextTooLong: 'စာလုံးအေရအတွက်အား %l အောက်သို့ လျှော့ချပါ (လောလောဆယ် %l စာလုံးအား အသုံးပြုထားသည်).',
            TextTooShort:"စာလုံးအေရအတွက်အား %l ထက်ပိုသုံးပါ (လောလောဆယ် %l စာလုံးအား အသုံးပြုထားသည်).",
            ValueMissing: 'ဒီအကွက်ကို ဖြည့်ပေးပါ။',
            CheckboxMissing: 'ဆက်လုပ်လိုလျှင် ဒီအကွက်အား အမှန်ခြစ်ပါ။',
            RadioMissing: 'ဒီထဲက တစ်ခုခုကို ရွေးပါ။',
            FileMissing: 'ဖိုင်တစ်ဖိုင် ရွေးပေးပါ။',
            SelectMissing: 'စာရင်းထဲမှ တစ်ခုခုအား ရွေးပါ။',
            InvalidEmail: 'အီးမေးလ်လိပ်စာ ဖြည့်ပါ။',
            InvalidURL: 'URL ဖြည့်ပါ။',
            PatternMismatch: 'သတ်မှတ်ပုံစံအတိုင်း ဖြည့်သွင်းပါ။',
            PatternMismatchWithTitle: 'သတ်မှတ်ပုံစံအတိုင်း ဖြည့်သွင်းပါ။: %l.',
            NumberRangeOverflow: '%l ထက်မများသော တန်ဖိုးတစ်ခုရွေးပါ။',
            DateRangeOverflow: '%l အောက် မငယ်သော တန်ဖိုးတစ်ခုရွေးပါ။',
            TimeRangeOverflow: '%l ထက်နောက်မကျသော အချိန်ရွေးပါ။',
            NumberRangeUnderflow: '%l အောက် မငယ်သော တန်ဖိုးတစ်ခု ရွေးပါ။',
            DateRangeUnderflow: '%l ထက် မစောသော တစ်ရက် ရွေးပါ။',
            TimeRangeUnderflow: '%l ထက် မစောသော အချိန်တစ်ခု ရွေးပါ။',
            StepMismatch: 'မှန်ကန်သော တန်ဖိုးအား ဖြည့်ပါ။ အနီးစပ်ဆုံးမှာ %l နှင့် %l တို့ဖြစ်သည်။',
            StepMismatchOneValue: 'မှန်ကန်သော တန်ဖိုးအား ဖြည့်ပါ။ အနီးစပ်ဆုံးမှာ %l ဖြစ်သည်',
            BadInputNumber: 'ဂဏန်း တစ်ခုဖြစ်ည့သွင်းပါ။'
        });
        hyperform.set_language("mm");
        @endif
        hyperform(window, {revalidate: 'oninput'});
    </script>

    <!-- app script -->
    <script src="{{ elixir('js/app.js') }}"></script>

    @yield('scripts')

    @stack('vue-scripts')

    <script type="text/javascript">
    (function($) {
        @stack('document-ready');
    })(jQuery);

    $(document).ajaxStart(function(){
        $.LoadingOverlay("show");
    });
    $(document).ajaxStop(function(){
        $.LoadingOverlay("hide");
    });


     @stack('d3-js')
    </script>
     @stack('before-body-end')
</body>
</html>
