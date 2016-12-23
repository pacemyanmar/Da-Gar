<div class="form-group">
	{!! Form::checkbox("result[".$element->name."]", $element->value, null, ['class' => 'magic-checkbox '.$element->className.' '.$sectionClass, 'id' => $element->id]) !!}
	<label class="normal-text" for="{!! $element->id !!}">{!! $element->label !!}
    </label>
</div>
