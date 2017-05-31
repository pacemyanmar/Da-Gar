<div class="form-group">
   		{!! Form::radio("result[".$element->inputid."]", $element->value, (isset($double_results) && $element->value == $double_results->{$element->inputid}), ['data-class'=>$element->inputid,'data-origin'=>(isset($results) && $element->value == $results->{$element->inputid}),'id' => $element->id,'class' => 'magic-radio '.$element->className.' '.$sectionClass, 'autocomplete' => 'off', 'data-selected' => (isset($double_results) && $element->value == $double_results->{$element->inputid})]) !!}
   	<label class="normal-text" for="{!! $element->id !!}">
   		{!! $element->label !!}
   			@if($element->value != '') <span class="label label-primary badge">{!! $element->value !!}</span> @endif
            @if($element->status != 'published') <span class="label label-warning badge">{!! $element->status !!}</span> @endif
            @if(str_contains(strtolower($element->label), 'other'))
			{!! Form::text("result[".$element->inputid."]", (isset($double_results))?$double_results->{$element->inputid}:null, ['class' => $element->className.' form-control input-sm zawgyi', 'autocomplete' => 'off', 'id' => $element->id.'other']) !!}
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
            <span class="hide label label-danger badge {!! $element->inputid .' '.$element->id!!}">{!! "Data not match!" !!}</span>
   	</label>
   	@if($element->other)
    @php
    	$options = [
		'class' => $element->className.' form-control zawgyi '.$sectionClass,
		'id' => $element->id,
		'placeholder' => Kanaung\Facades\Converter::convert($element->label,'unicode','zawgyi'),
		'aria-describedby'=> $element->id.'-addons',
		'autocomplete' => 'off'
		];
    @endphp
    	{!! Form::text("result[".$element->inputid."]", (isset($results))?Kanaung\Facades\Converter::convert($results->{$element->inputid},'unicode','zawgyi'):null, $options) !!}
    @endif
</div>
@if(!empty($element->skip) && !isset($editing))
	@push('document-ready')
	if($("input[name='result[{!! $element->inputid !!}]']:checked").val() == {!! $element->value !!}) {
			$("{!! $element->skip !!}").prop("disabled", true);
		} else {
			$("{!! $element->skip !!}").prop("disabled", false);
		}
	$("input[name='result[{!! $element->inputid !!}]']").change(function(){
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
