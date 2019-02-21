{!! Form::open(['route' => ['settings.destroy', $key], 'method' => 'delete']) !!}
<div class='btn-group'>
    <a href="{{ route('settings.show', $key) }}" class='btn btn-default btn-xs'>
        <i class="glyphicon glyphicon-eye-open"></i>
    </a>
    <a href="{{ route('settings.edit', $key) }}" class='btn btn-default btn-xs'>
        <i class="glyphicon glyphicon-edit"></i>
    </a>
    {!! Form::button('<i class="glyphicon glyphicon-trash"></i>', [
        'type' => 'submit',
        'class' => 'btn btn-danger btn-xs',
        'onclick' => "return confirm('".trans('messages.are_you_sure')."')"
    ]) !!}
</div>
{!! Form::close() !!}
