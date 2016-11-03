


<li class="{{ Request::is('projects*') ? 'active' : '' }}">
    <a href="{!! route('projects.index') !!}"><i class="fa fa-edit"></i><span>Projects</span></a>
</li>




<li class="{{ Request::is('questions*') ? 'active' : '' }}">
    <a href="{!! route('questions.index') !!}"><i class="fa fa-edit"></i><span>Questions</span></a>
</li>

