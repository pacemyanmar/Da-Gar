<div class="form-group">
   		{!! Form::radio("result[".$element->inputid."]", $element->value, (isset($results) && !empty($results['section'.$section->sort]) && $element->value == $results['section'.$section->sort]->{$element->inputid}), ['id' => $element->id,'class' => 'magic-radio '.$element->className.' '.$sectionClass, 'autocomplete' => 'off', 'data-selected' => (isset($results) && !empty($results['section'.$section->sort]) && $element->value == $results['section'.$section->sort]->{$element->inputid})]) !!}
   	<label class="normal-text" for="{!! $element->id !!}">
   		{!! $element->label !!}
   			@if($element->value != '') <span class="label label-primary badge">{!! $element->value !!}</span> @endif
            @if($element->status != 'published') <span class="label label-warning badge">{!! $element->status !!}</span> @endif
        @if($element->other)
		{!! Form::text("result[".$element->inputid."]", (isset($results) && !empty($results['section'.$section->sort]))?$results['section'.$section->sort]->{$element->inputid}:null, ['class' => $element->className.' form-control input-sm zawgyi', 'autocomplete' => 'off', 'id' => $element->id.'other', 'style' => 'width:80%']) !!}
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
    	{!! Form::text("result[".$element->inputid."]", (isset($results) && !empty($results['section'.$section->sort]))?Kanaung\Facades\Converter::convert($results['section'.$section->sort]->{$element->inputid},'unicode','zawgyi'):null, $options) !!}
    @endif
</div>
@if(!empty($element->skip) && !isset($editing))
	@push('document-ready')
	if($("input[name='result[{!! $element->inputid !!}]']:checked").val() == {!! $element->value !!}) {
			$("{!! $element->skip !!}").prop("disabled", true);
		} else {
			$("{!! $element->skip !!}").prop("disabled", false);
		}
	$("#{!! $element->id !!}").click(function(){
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
