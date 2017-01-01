<div class="form-group">
   		{!! Form::radio("result[".$element->inputid."]", $element->value, (isset($results) && $element->value == $results->{$element->inputid}), ['id' => $element->id,'class' => 'magic-radio '.$element->className.' '.$sectionClass]) !!}
   	<label class="normal-text" for="{!! $element->id !!}">
   		{!! $element->label !!}
   	</label>
</div>
@if(!empty($element->skip))
	@push('document-ready')
	$("input[name='result[{!! $element->inputid !!}]']").change(function(){
		if($("input[name='result[{!! $element->inputid !!}]']:checked").val() == {!! $element->value !!}) {
			$("{!! $element->skip !!}").prop("disabled", true);
		} else {
			$("{!! $element->skip !!}").prop("disabled", false);
		}
	});
	@endpush
@endif
