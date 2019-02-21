<!-- Id Field -->
<div class="form-group col-sm-6">
    {!! Form::label('id', 'Id:') !!}
    {!! Form::text('id', null, ['class' => 'form-control']) !!}
</div>

<!-- Service Id Field -->
<div class="form-group col-sm-6">
    {!! Form::label('service_id', 'Service Id:') !!}
    {!! Form::text('service_id', null, ['class' => 'form-control']) !!}
</div>

<!-- From Number Field -->
<div class="form-group col-sm-6">
    {!! Form::label('from_number', 'From Number:') !!}
    {!! Form::text('from_number', null, ['class' => 'form-control']) !!}
</div>

<!-- To Number Field -->
<div class="form-group col-sm-6">
    {!! Form::label('to_number', 'To Number:') !!}
    {!! Form::text('to_number', null, ['class' => 'form-control']) !!}
</div>

<!-- Content Field -->
<div class="form-group col-sm-6">
    {!! Form::label('content', 'Content:') !!}
    {!! Form::text('content', null, ['class' => 'form-control']) !!}
</div>

<!-- Error Message Field -->
<div class="form-group col-sm-6">
    {!! Form::label('status_message', 'Error Message:') !!}
    {!! Form::text('status_message', null, ['class' => 'form-control']) !!}
</div>

<!-- Search Result Field -->
<div class="form-group col-sm-6">
    {!! Form::label('search_result', 'Search Result:') !!}
    {!! Form::text('search_result', null, ['class' => 'form-control']) !!}
</div>

<!-- Phone Field -->
<div class="form-group col-sm-6">
    {!! Form::label('phone', 'Phone:') !!}
    {!! Form::text('phone', null, ['class' => 'form-control']) !!}
</div>

<!-- Submit Field -->
<div class="form-group col-sm-12">
    {!! Form::submit('Save', ['class' => 'btn btn-primary']) !!}
    <a href="{!! route('smsLogs.index') !!}" class="btn btn-default">Cancel</a>
</div>
