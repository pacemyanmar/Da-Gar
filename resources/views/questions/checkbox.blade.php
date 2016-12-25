<div class="form-group">
	{!! Form::checkbox("result[".$element->inputid."]", $element->value, (isset($results) && $element->value == $results->{$element->inputid}), ['class' => 'magic-checkbox '.$element->className.' '.$sectionClass, 'id' => $element->id]) !!}
	<label class="normal-text" for="{!! $element->id !!}">{!! $element->label !!}
    </label>
</div>
