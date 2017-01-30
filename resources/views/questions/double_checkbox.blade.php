<div class="form-group">
	{!! Form::checkbox("result[".$element->inputid."]", $element->value, (isset($double_results) && $element->value == $double_results->{$element->inputid}), ['data-class'=>$element->inputid, 'data-origin'=>(isset($results) && $element->value == $results->{$element->inputid}),'class' => 'magic-checkbox '.$element->className.' '.$sectionClass, 'id' => $element->id, 'autocomplete' => 'off']) !!}
	<label class="normal-text" for="{!! $element->id !!}">{!! $element->label !!} @if($element->value != '') <span class="label label-primary badge">{!! $element->value !!}</span> @endif
	@if($element->status != 'published') <span class="label label-warning badge">{!! $element->status !!}</span> @endif
	<span class="hide label label-danger badge {!! $element->inputid .' '.$element->id!!}">{!! "Data not match!" !!}</span>
		@if($element->other)
		{!! Form::text("result[".$element->inputid."]", (isset($results))?$results->{$element->inputid}:null, ['class' => $element->className.' form-control input-sm', 'autocomplete' => 'off', 'id' => $element->id.'other', 'style' => 'width:80%']) !!}
			@push('document-ready')
				$("input[name='result[{!! $element->inputid !!}]']").change(function(e){
					if($("input[name='result[{!! $element->inputid !!}]']:checked").val() == {!! $element->value !!}) {
						$("#{!! $element->id.'other' !!}").prop('disabled', false).prop('required', true).addClass('has-error');
					} else {
						$("#{!! $element->id.'other' !!}").prop('disabled', true).prop('required', false).removeClass('has-error');
					}
				});

				if($("#{!! $element->id.'other' !!}").val() != "") {
					$("#{!! $element->id.'other' !!}").prop('required', true).addClass('has-error');
					$("#{!! $element->id !!}").prop('checked', true);
				} else {
					$("#{!! $element->id.'other' !!}").prop('disabled', true).prop('required', false).removeClass('has-error');
				}
			@endpush
		@endif
    </label>
</div>
@if(!empty($element->skip) && !isset($editing))
	@push('document-ready')
	if($("input[name='result[{!! $element->inputid !!}]']").is(':checked')) {
			$("{!! $element->skip !!}").prop("disabled", true);
		} else {
			$("{!! $element->skip !!}").prop("disabled", false);
		}
	$("input[name='result[{!! $element->inputid !!}]']").change(function(){
		if($("input[name='result[{!! $element->inputid !!}]']").is(':checked')) {
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
