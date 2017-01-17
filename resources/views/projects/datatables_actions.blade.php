{!! Form::open(['route' => ['projects.destroy', $id], 'method' => 'delete']) !!}
<div class='btn-group'>
    <a href="{{ route('projects.surveys.index', $id) }}" class='btn btn-default btn-xs'>
        <i class="glyphicon glyphicon-eye-open"> List samples</i>
    </a>
    <a href="{{ route('projects.edit', $id) }}" class='btn btn-default btn-xs'>
        <i class="glyphicon glyphicon-edit"> Edit </i>
    </a>

</div>
<div class="btn-group">
    {!! Form::button('<i class="glyphicon glyphicon-trash"></i>', [
        'type' => 'submit',
        'class' => 'btn btn-danger btn-xs',
        'onclick' => "return confirm('Are you sure?')"
    ]) !!}
</div>
{!! Form::close() !!}
