@foreach($sampleColumns as $column => $label)
<!-- {{ $label }} Field -->
<div class="form-group col-sm-3">
    {!! Form::label($column, $label.':') !!}
    {!! Form::text($column, null, ['class' => 'form-control']) !!}
</div>

@endforeach


<!-- Submit Field -->
<div class="form-group col-sm-12">
    {!! Form::submit('Save', ['class' => 'btn btn-primary']) !!}
    <a href="{!! route('sample-details.index') !!}" class="btn btn-default">Cancel</a>
</div>
