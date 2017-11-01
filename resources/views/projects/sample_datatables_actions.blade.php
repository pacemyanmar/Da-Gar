@php
$form_id = (isset($form_id))?$form_id:1;
$sample_id = (isset($sample_id))?$sample_id:null;
$double = (isset($double))?$double:null;
@endphp

{!! Form::open(['route' => ['projects.surveys.destroy', $project_id, $samples_id], 'method' => 'delete']) !!}
<div class="row">
<div class='btn-group'>
    <a href="{{ route('projects.surveys.create', [$project_id, $samples_id, $form_id, $sample_id, $double]) }}" class='btn btn-primary btn-sm'>
        <i class="glyphicon glyphicon-edit"></i>
    </a>
</div>
</div>
<div class="row">
<div class='btn-group'>
    @if(Auth::user()->role->level > 9)
    <a href="{{ route('projects.surveys.show', [$project_id, $samples_id]) }}" class='btn btn-default btn-xs'>
        <i class="glyphicon glyphicon-eye-open"></i>
    </a>

    {!! Form::button('<i class="glyphicon glyphicon-trash"></i>', [
        'type' => 'submit',
        'class' => 'btn btn-danger btn-xs',
        'onclick' => "return confirm('".trans('messages.are_you_sure')."')"
    ]) !!}
    @endif
</div>
</div>
{!! Form::close() !!}

