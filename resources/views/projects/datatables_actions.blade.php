
<div class='btn-group'>
    <a href="{{ route('projects.surveys.index', $id) }}" class='btn btn-default btn-sm'>
        <i class="glyphicon glyphicon-eye-open"></i> List samples
    </a>
    @if(Auth::user()->role->level > 5)
    <a href="{{ route('projects.edit', $id) }}" class='btn btn-default btn-sm'>
        <i class="glyphicon glyphicon-edit"></i> Edit
    </a>
    @endif
</div>
@if(Auth::user()->role->level > 5)
<div class="btn-group">
    <a href="{{ route('projects.response.sample', $id) }}" class='btn btn-default btn-sm'>
        <i class="glyphicon glyphicon-equalizer"></i> Response
    </a>
    <a href="{{ route('projects.response.double', $id) }}" class='btn btn-default btn-sm'>
        <i class="glyphicon glyphicon-transfer"></i> Double Entry
    </a>
</div>
<div class="btn-group">
{!! Form::open(['route' => ['projects.destroy', $id], 'method' => 'delete']) !!}

    {!! Form::button('<i class="glyphicon glyphicon-trash"></i>', [
        'type' => 'submit',
        'class' => 'btn btn-danger btn-sm',
        'onclick' => "return confirm('Are you sure?')"
    ]) !!}

{!! Form::close() !!}
</div>
@endif
