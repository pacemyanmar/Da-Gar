<!-- Idcode Field -->
<div class="form-group col-sm-6">
    {!! Form::label('idcode', 'Idcode:') !!}
    {!! Form::text('idcode', null, ['class' => 'form-control']) !!}
</div>

<!-- Type Field -->
<div class="form-group col-sm-6">
    {!! Form::label('sample', 'Sample:') !!}
    {!! Form::text('sample', null, ['class' => 'form-control']) !!}
</div>

<!-- Type Field -->
<div class="form-group col-sm-6">
    {!! Form::label('type', 'Type:') !!}
    {!! Form::text('type', null, ['class' => 'form-control']) !!}
</div>

<!-- Name Field -->
<div class="form-group col-sm-6">
    {!! Form::label('name', 'Name:') !!}
    {!! Form::text('name', null, ['class' => 'form-control']) !!}
</div>

<!-- Gender Field -->
<div class="form-group col-sm-6">
    {!! Form::label('gender', 'Gender:') !!}
    {!! Form::text('gender', null, ['class' => 'form-control']) !!}
</div>

<!-- Nrc Id Field -->
<div class="form-group col-sm-6">
    {!! Form::label('nrc_id', 'Nrc Id:') !!}
    {!! Form::text('nrc_id', null, ['class' => 'form-control']) !!}
</div>

<!-- Dob Field -->
<div class="form-group col-sm-6">
    {!! Form::label('dob', 'Dob:') !!}
    {!! Form::date('dob', null, ['class' => 'form-control date']) !!}
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
<div class="form-group col-sm-12 col-lg-12">
    {!! Form::label('address', 'Address:') !!}
    {!! Form::textarea('address', null, ['class' => 'form-control']) !!}
</div>

<!-- Village Field -->
<div class="form-group col-sm-6">
    {!! Form::label('village', 'Village:') !!}
    {!! Form::text('village', null, ['class' => 'form-control']) !!}
</div>

<!-- Village Tract Field -->
<div class="form-group col-sm-6">
    {!! Form::label('village_tract', 'Village Tract:') !!}
    {!! Form::text('village_tract', null, ['class' => 'form-control']) !!}
</div>

<!-- Township Field -->
<div class="form-group col-sm-6">
    {!! Form::label('township', 'Township:') !!}
    {!! Form::text('township', null, ['class' => 'form-control']) !!}
</div>

<!-- District Field -->
<div class="form-group col-sm-6">
    {!! Form::label('district', 'District:') !!}
    {!! Form::text('district', null, ['class' => 'form-control']) !!}
</div>

<!-- State Field -->
<div class="form-group col-sm-6">
    {!! Form::label('state', 'State:') !!}
    {!! Form::text('state', null, ['class' => 'form-control']) !!}
</div>

<!-- Submit Field -->
<div class="form-group col-sm-12">
    {!! Form::submit('Save', ['class' => 'btn btn-primary']) !!}
    <a href="{!! route('sampleDatas.index') !!}" class="btn btn-default">Cancel</a>
</div>

@push("before-head-end")
<style>
    input[type=date]::-webkit-inner-spin-button, input[type=date]::-webkit-calendar-picker-indicator {
   display: none;
}
</style>
@endpush

@push('document-ready')
    $( ".date" ).datepicker({ dateFormat: 'yy-mm-dd',changeYear: true,changeMonth: true});
@endpush
