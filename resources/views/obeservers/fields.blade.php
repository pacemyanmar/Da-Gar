<!-- Name Field -->
<div class="form-group col-sm-6">
    {!! Form::label('name', 'Name:') !!}
    {!! Form::text('name', null, ['class' => 'form-control']) !!}
</div>

<!-- Code Field -->
<div class="form-group col-sm-6">
    {!! Form::label('code', 'Code:') !!}
    {!! Form::text('code', null, ['class' => 'form-control']) !!}
</div>

<!-- National Id Field -->
<div class="form-group col-sm-6">
    {!! Form::label('national_id', 'National Id:') !!}
    {!! Form::text('national_id', null, ['class' => 'form-control']) !!}
</div>

<!-- Phone 1 Field -->
<div class="form-group col-sm-6">
    {!! Form::label('phone_1', 'Phone 1:') !!}
    {!! Form::text('phone_1', null, ['class' => 'form-control']) !!}
</div>

<!-- Phone 2 Field -->
<div class="form-group col-sm-6">
    {!! Form::label('phone_2', 'Phone 2:') !!}
    {!! Form::text('phone_2', null, ['class' => 'form-control']) !!}
</div>

<!-- Address Field -->
<div class="form-group col-sm-12 col-lg-12">
    {!! Form::label('address', 'Address:') !!}
    {!! Form::textarea('address', null, ['class' => 'form-control']) !!}
</div>

<!-- Language Field -->
<div class="form-group col-sm-6">
    {!! Form::label('language', 'Language:') !!}
    {!! Form::text('language', null, ['class' => 'form-control']) !!}
</div>

<!-- Ethnicity Field -->
<div class="form-group col-sm-6">
    {!! Form::label('ethnicity', 'Ethnicity:') !!}
    {!! Form::text('ethnicity', null, ['class' => 'form-control']) !!}
</div>

<!-- Occupation Field -->
<div class="form-group col-sm-6">
    {!! Form::label('occupation', 'Occupation:') !!}
    {!! Form::text('occupation', null, ['class' => 'form-control']) !!}
</div>

<!-- Gender Field -->
<div class="form-group col-sm-6">
    {!! Form::label('gender', 'Gender:') !!}
    {!! Form::text('gender', null, ['class' => 'form-control']) !!}
</div>

<!-- Dob Field -->
<div class="form-group col-sm-6">
    {!! Form::label('dob', 'Dob:') !!}
    {!! Form::text('dob', null, ['class' => 'form-control']) !!}
</div>

<!-- Education Field -->
<div class="form-group col-sm-6">
    {!! Form::label('education', 'Education:') !!}
    {!! Form::text('education', null, ['class' => 'form-control']) !!}
</div>

<!-- Submit Field -->
<div class="form-group col-sm-12">
    {!! Form::submit('Save', ['class' => 'btn btn-primary']) !!}
    <a href="{!! route('obeservers.index') !!}" class="btn btn-default">Cancel</a>
</div>
