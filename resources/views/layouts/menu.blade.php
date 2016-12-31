<li class="{{ Request::is('projects*') ? 'active' : '' }}">
    <a href="{!! route('projects.index') !!}"><i class="fa fa-edit"></i><span>Projects</span></a>
</li>


<li class="{{ Request::is('sampleDatas*') ? 'active' : '' }}">
    <a href="{!! route('sampleDatas.index') !!}"><i class="fa fa-edit"></i><span>SampleDatas</span></a>
</li>


<li class="{{ Request::is('smsLogs*') ? 'active' : '' }}">
    <a href="{!! route('smsLogs.index') !!}"><i class="fa fa-edit"></i><span>SmsLogs</span></a>
</li>


<li class="{{ Request::is('settings*') ? 'active' : '' }}">
    <a href="{!! route('settings.index') !!}"><i class="fa fa-edit"></i><span>Settings</span></a>
</li>
