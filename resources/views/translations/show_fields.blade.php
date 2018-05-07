<!-- Id Field -->
<div class="form-group">
    {!! Form::label('id', 'Id:') !!}
    <p>{!! $translation->id !!}</p>
</div>

<!-- Group Field -->
<div class="form-group">
    {!! Form::label('group', 'Group:') !!}
    <p>{!! $translation->group !!}</p>
</div>

<!-- Key Field -->
<div class="form-group">
    {!! Form::label('key', 'Key:') !!}
    <p>{!! $translation->key !!}</p>
</div>

<!-- Text Field -->
<div class="form-group">
    {!! Form::label('text', 'Text:') !!}
    <p>{!! $translation->text !!}</p>
</div>

<!-- Created At Field -->
<div class="form-group">
    {!! Form::label('created_at', 'Created At:') !!}
    <p>{!! $translation->created_at !!}</p>
</div>

<!-- Updated At Field -->
<div class="form-group">
    {!! Form::label('updated_at', 'Updated At:') !!}
    <p>{!! $translation->updated_at !!}</p>
</div>

