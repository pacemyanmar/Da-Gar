<li class="{{ Request::is('projects*') ? 'active' : '' }}">
    <a href="{!! route('projects.index') !!}"><i class="fa fa-edit"></i><span>{!! trans_choice('messages.projects', 2) !!}</span></a>
</li>
@can('index', \App\Models\SampleData::class)
<li class="{{ Request::is('sampleDatas*') ? 'active' : '' }}">
    <a href="{!! route('sampleDatas.index') !!}"><i class="fa fa-edit"></i><span>{!! trans_choice('messages.sample_datas', 2) !!}</span></a>
</li>
@endcan
@can('index', \App\Models\SmsLog::class)
<li class="{{ Request::is('smsLogs*') ? 'active' : '' }}">
    <a href="{!! route('smsLogs.index') !!}"><i class="fa fa-edit"></i><span>{!! trans_choice('messages.sms_logs', 2) !!}</span></a>
</li>
@endcan
@can('index', \App\Models\User::class)
<li class="{{ Request::is('users*') ? 'active' : '' }}">
    <a href="{!! route('users.index') !!}"><i class="fa fa-edit"></i><span>{!! trans_choice('messages.users', 2) !!}</span></a>
</li>
@endcan
@can('index', \App\Models\Role::class)
<li class="{{ Request::is('roles*') ? 'active' : '' }}">
    <a href="{!! route('roles.index') !!}"><i class="fa fa-edit"></i><span>{!! trans_choice('messages.roles', 2) !!}</span></a>
</li>
@endcan
@can('index', \App\Models\Setting::class)
<li class="{{ Request::is('settings*') ? 'active' : '' }}">
    <a href="{!! route('settings.index') !!}"><i class="fa fa-edit"></i><span>{!! trans_choice('messages.settings', 2) !!}</span></a>
</li>
@endcan
