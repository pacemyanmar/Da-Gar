<li class="{{ Request::is('projects*') ? 'active' : '' }}">
    <a href="{!! route('projects.index') !!}"><i class="fa fa-edit"></i><span>Projects</span></a>
</li>

<li class="{{ Request::is('voters*') ? 'active' : '' }}">
    <a href="{!! route('voters.index') !!}"><i class="fa fa-edit"></i><span>Voters</span></a>
</li>



