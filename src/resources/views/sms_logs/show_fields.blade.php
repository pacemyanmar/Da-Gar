<!-- Id Field -->
<div class="form-group">
    {!! Form::label('id', 'Id:') !!}
    <p>{!! $smsLog->id !!}</p>
</div>

<!-- Service Id Field -->
<div class="form-group">
    {!! Form::label('service_id', 'Service Id:') !!}
    <p>{!! $smsLog->service_id !!}</p>
</div>

<!-- From Number Field -->
<div class="form-group">
    {!! Form::label('from_number', 'From Number:') !!}
    <p>{!! $smsLog->from_number !!}</p>
</div>

<!-- To Number Field -->
<div class="form-group">
    {!! Form::label('to_number', 'To Number:') !!}
    <p>{!! $smsLog->to_number !!}</p>
</div>

<!-- Name Field -->
<div class="form-group">
    {!! Form::label('name', 'Name:') !!}
    <p>{!! $smsLog->name !!}</p>
</div>

<!-- Content Field -->
<div class="form-group">
    {!! Form::label('content', 'Content:') !!}
    <p>{!! $smsLog->content !!}</p>
</div>

<!-- Error Message Field -->
<div class="form-group">
    {!! Form::label('error_message', 'Error Message:') !!}
    <p>{!! $smsLog->error_message !!}</p>
</div>

<!-- Search Result Field -->
<div class="form-group">
    {!! Form::label('search_result', 'Search Result:') !!}
    <p>{!! $smsLog->search_result !!}</p>
</div>

<!-- Phone Field -->
<div class="form-group">
    {!! Form::label('phone', 'Phone:') !!}
    <p>{!! $smsLog->phone !!}</p>
</div>

