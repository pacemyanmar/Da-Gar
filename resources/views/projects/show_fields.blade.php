<!-- Id Field -->
<div class="form-group">
    {!! Form::label('id', 'Id:') !!}
    <p>{!! $project->id !!}</p>
</div>

<!-- Project Field -->
<div class="form-group">
    {!! Form::label('project', 'Project:') !!}
    <p>{!! $project->project !!}</p>
</div>

<!-- Type Field -->
<div class="form-group">
    {!! Form::label('type', 'Type:') !!}
    <p>{!! $project->type !!}</p>
</div>

<!-- Sections Field -->
<div class="form-group">
    {!! Form::label('sections', 'Sections:') !!}
    <p>{!! $project->sections !!}</p>
</div>

<!-- Dblink Field -->
<div class="form-group">
    {!! Form::label('dblink', 'Dblink:') !!}
    <p>{!! $project->dblink !!}</p>
</div>

<!-- Created At Field -->
<div class="form-group">
    {!! Form::label('created_at', 'Created At:') !!}
    <p>{!! $project->created_at !!}</p>
</div>

<!-- Updated At Field -->
<div class="form-group">
    {!! Form::label('updated_at', 'Updated At:') !!}
    <p>{!! $project->updated_at !!}</p>
</div>

