<!-- Group Field -->
<div class="form-group col-sm-6">
    {!! Form::label('group', 'Group:') !!}
    {!! Form::text('group', null, ['class' => 'form-control']) !!}
</div>

<!-- Key Field -->
<div class="form-group col-sm-6">
    {!! Form::label('key', 'Key:') !!}
    {!! Form::text('key', null, ['class' => 'form-control']) !!}
</div>


<!-- Key Field -->
<div class="form-group col-sm-6">
    {!! Form::label(config('sms.primary_locale.locale'), title_case(config('sms.primary_locale.locale')).':') !!}
    {!! Form::text(config('sms.primary_locale.locale'), (isset($translation) && array_key_exists(config('sms.primary_locale.locale'), $translation->text))?$translation->text[config('sms.primary_locale.locale')]:null, ['class' => 'form-control']) !!}
</div>

<!-- Key Field -->
<div class="form-group col-sm-6">
    {!! Form::label(config('sms.second_locale.locale'), title_case(config('sms.second_locale.locale')).':') !!}
    {!! Form::text(config('sms.second_locale.locale'), (isset($translation) && array_key_exists(config('sms.second_locale.locale'), $translation->text))?$translation->text[config('sms.second_locale.locale')]:null, ['class' => 'form-control']) !!}
</div>

<!-- Submit Field -->
<div class="form-group col-sm-12">
    {!! Form::submit('Save', ['class' => 'btn btn-primary']) !!}
    <a href="{!! route('translations.index') !!}" class="btn btn-default">Cancel</a>
</div>
