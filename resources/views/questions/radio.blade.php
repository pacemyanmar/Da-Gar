<div class="form-group">
   		{!! Form::radio("result[".$element->inputid."]", $element->value, (isset($results) && $element->value == $results->{$element->inputid}), ['id' => $element->id,'class' => 'magic-radio '.$element->className.' '.$sectionClass]) !!}
   	<label class="normal-text" for="{!! $element->id !!}">
   		{!! $element->label !!}
   	</label>
</div>
