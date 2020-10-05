  <ul class="dropdown-menu">
    <li><a href="{{ url('lang/'.config('sms.primary_locale.locale')) }}"><img src="{{ asset('images/flags/'.config('sms.primary_locale.locale').'.png') }}" alt=""> {{ trans('locale..config('sms.primary_locale.locale')) }}</a></li>
    <li><a href="{{ url('lang/'.config('sms.second_locale.locale')) }}"><img src="{{ asset('images/flags/'.config('sms.second_locale.locale').'.png') }}" alt=""> {{ trans('locale.'.config('sms.second_locale.locale')) }}</a></li>
  </ul>
