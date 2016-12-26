@php
$form_id = (isset($form_id))?$form_id:1;
$sample_id = (isset($sample_id))?$sample_id:null;
@endphp
{!! Form::open(['route' => ['projects.surveys.destroy', $project_id, $id], 'method' => 'delete']) !!}
<div class='btn-group'>
    <a href="{{ route('projects.surveys.show', [$project_id, $id]) }}" class='btn btn-default btn-xs'>
        <i class="glyphicon glyphicon-eye-open"></i>
    </a>
    <a href="{{ route('projects.surveys.create', [$project_id, $id, $form_id, $sample_id]) }}" class='btn btn-default btn-xs'>
        <i class="glyphicon glyphicon-edit"></i>
    </a>
    {!! Form::button('<i class="glyphicon glyphicon-trash"></i>', [
        'type' => 'submit',
        'class' => 'btn btn-danger btn-xs',
        'onclick' => "return confirm('Are you sure?')"
    ]) !!}
</div>
{!! Form::close() !!}
