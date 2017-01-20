<div class="form-group">
   		{!! Form::radio("result[".$element->inputid."]", $element->value, (isset($double_results) && $element->value == $double_results->{$element->inputid}), ['data-class'=>$element->inputid,'data-origin'=>(isset($results) && $element->value == $results->{$element->inputid}),'id' => $element->id,'class' => 'magic-radio '.$element->className.' '.$sectionClass, 'autocomplete' => 'off']) !!}
   	<label class="normal-text" for="{!! $element->id !!}">
   		{!! $element->label !!}
   			@if($element->value != '') <span class="label label-primary badge">{!! $element->value !!}</span> @endif
            @if($element->status != 'published') <span class="label label-warning badge">{!! $element->status !!}</span> @endif
            <span class="hide label label-danger badge {!! $element->inputid .' '.$element->id!!}">{!! "Data not match!" !!}</span>
   	</label>
</div>
@if(!empty($element->skip) && !isset($editing))
	@push('document-ready')
	if($("input[name='result[{!! $element->inputid !!}]']:checked").val() == {!! $element->value !!}) {
			$("{!! $element->skip !!}").prop("disabled", true);
		} else {
			$("{!! $element->skip !!}").prop("disabled", false);
		}
	$("#{!! $element->inputid !!}").click(function(){
		if($("input[name='result[{!! $element->inputid !!}]']:checked").val() == {!! $element->value !!}) {
			$("{!! $element->skip !!}").prop("disabled", true);
			@if(isset($element->extras['goto']))
			$("body, html").animate({
			    scrollTop: $("{!! $element->extras['goto'] !!}").offset().top
			}, 600);
			@endif
		} else {
			$("{!! $element->skip !!}").prop("disabled", false);
		}
	});
	@endpush
@endif