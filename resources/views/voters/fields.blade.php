<!-- Name Field -->
<div class="form-group col-sm-6">
    {!! Form::label('name', 'Name:') !!}
    {!! Form::text('name', null, ['class' => 'form-control']) !!}
</div>

<!-- Gender Field -->
<div class="form-group col-sm-6">
    {!! Form::label('gender', 'Gender:') !!}
    {!! Form::select('gender', ['male' => 'Male', 'female' => 'Female', 'other' => 'Other'], null, ['class' => 'form-control']) !!}
</div>

<!-- Nrc Id Field -->
<div class="form-group col-sm-6">
    {!! Form::label('nrc_id', 'NRC ID:') !!}
    {!! Form::text('nrc_id', null, ['class' => 'form-control']) !!}
</div>

<!-- Father Field -->
<div class="form-group col-sm-6">
    {!! Form::label('father', 'Father:') !!}
    {!! Form::text('father', null, ['class' => 'form-control']) !!}
</div>

<!-- Mother Field -->
<div class="form-group col-sm-6">
    {!! Form::label('mother', 'Mother:') !!}
    {!! Form::text('mother', null, ['class' => 'form-control']) !!}
</div>

<!-- Address Field -->
<div class="form-group col-sm-6">
    {!! Form::label('address', 'Address:') !!}
    {!! Form::text('address', null, ['class' => 'form-control']) !!}
</div>

<!-- Dob Field -->
<div class="form-group col-sm-6">
    {!! Form::label('dob', 'Date of Birth:') !!}
    {!! Form::date('dob', null, ['class' => 'form-control']) !!}
</div>

<!-- Submit Field -->
<div class="form-group col-sm-12">
    {!! Form::submit('Save', ['class' => 'btn btn-primary']) !!}
    <a href="{!! route('voters.index') !!}" class="btn btn-default">Cancel</a>
</div>
