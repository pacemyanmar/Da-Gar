<div class="form-group">
    <label class="normal-text" for="{!! $element->id !!}">{!! $element->label !!}
        @if($element->status != 'published') <span
                class="label label-warning badge">{!! $element->status !!}</span> @endif
    </label>
    {!! Form::textarea("result[".$element->inputid."]",  (isset($results) && isset($results['section'.$section->sort]->{$element->inputid}))?Kanaung\Facades\Converter::convert($results['section'.$section->sort]->{$element->inputid},'unicode','zawgyi'):null, ['class' => $element->className, 'id' => $element->id, 'autocomplete' => 'off']) !!}

</div>

