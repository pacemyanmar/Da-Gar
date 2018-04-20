<div class="form-group">
    <div class="input-group">
        {!! Form::number("result[".$element->inputid."]", (isset($results) && !empty($results['section'.$section->sort]))?Kanaung\Facades\Converter::convert($results['section'.$section->sort]->{$element->inputid},'unicode','zawgyi'):null, ['class' => 'form-control']) !!}
    </div>
</div>
