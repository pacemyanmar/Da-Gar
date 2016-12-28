<!-- Idcode Field -->
<div class="form-group col-sm-6">
    {!! Form::label('idcode', 'Idcode:') !!}
    {!! Form::number('idcode', null, ['class' => 'form-control']) !!}
</div>

<!-- Name Field -->
<div class="form-group col-sm-6">
    {!! Form::label('name', 'Name:') !!}
    {!! Form::text('name', null, ['class' => 'form-control']) !!}
</div>

<!-- Type Field -->
<div class="form-group col-sm-6">
    {!! Form::label('type', 'Type:') !!}
    {!! Form::text('type', null, ['class' => 'form-control']) !!}
</div>

<!-- Lat Long Field -->
<div class="form-group col-sm-6">
    {!! Form::label('lat_long', 'Lat Long:') !!}
    {!! Form::text('lat_long', null, ['class' => 'form-control']) !!}
</div>

<!-- Submit Field -->
<div class="form-group col-sm-12">
    {!! Form::submit('Save', ['class' => 'btn btn-primary']) !!}
    <a href="{!! route('locations.index') !!}" class="btn btn-default">Cancel</a>
</div>
