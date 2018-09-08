@foreach($sampleColumns as $column => $label)
<!-- Id Field -->
<div class="form-group col-sm-3">
    {!! Form::label($column, $label.':') !!}
    <p>{!! $sampleDetails->{$column} !!}</p>
</div>

@endforeach

