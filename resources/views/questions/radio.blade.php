<div class="form-group">
   		{!! Form::radio("result[".$element->name."]", $element->value, null, ['id' => $element->id,'class' => 'magic-radio '.$element->className.' '.$sectionClass]) !!}
   	<label class="normal-text" for="{!! $element->id !!}">
   		{!! $element->label !!}
   	</label>
</div>
