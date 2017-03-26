<div class="form-group">
<label class="normal-text" for="{!! $element->id !!}">{!! $element->label !!} @if($element->value != '') <span class="label label-primary badge">{!! $element->value !!}</span> @endif
	@if($element->status != 'published') <span class="label label-warning badge">{!! $element->status !!}</span> @endif
    </label>
	{!! Form::textarea("result[".$element->inputid."]", (isset($results))?Kanaung\Facades\Converter::convert($results->{$element->inputid},'unicode','zawgyi'):null, ['class' => 'zawgyi '.$element->className.' '.$sectionClass, 'id' => $element->id, 'autocomplete' => 'off']) !!}

</div>
