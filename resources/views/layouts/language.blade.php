  <ul class="dropdown-menu">
    <li><a href="{{ url('lang/en') }}"><img src="{{ asset('images/flags/us.png') }}" alt=""> {{ trans('locale.en') }}</a></li>
    <li><a href="{{ url('lang/'.config('app.second_locale.locale')) }}"><img src="{{ asset('images/flags/'.config('app.second_locale.country').'.png') }}" alt=""> {{ trans('locale.'.config('app.second_locale.locale')) }}</a></li>
  </ul>
