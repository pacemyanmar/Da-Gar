  <ul class="dropdown-menu">
    <li><a href="{{ url('lang/en') }}"><img src="{{ asset('images/flags/us.png') }}" alt=""> {{ trans('locale.en') }}</a></li>
    <li><a href="{{ url('lang/'.config('sms.second_locale.locale')) }}"><img src="{{ asset('images/flags/'.config('sms.second_locale.locale').'.png') }}" alt=""> {{ trans('locale.'.config('sms.second_locale.locale')) }}</a></li>
  </ul>
