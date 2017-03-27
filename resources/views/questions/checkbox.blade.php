<div class="form-group">

    @if($element->other)
    {!! Form::checkbox("result[".$element->inputid."]", $element->value, (isset($results) && $results->{$element->inputid}), ['class' => 'magic-checkbox '.$element->className.' '.$sectionClass, 'id' => $element->id, 'autocomplete' => 'off']) !!}
	<label class="normal-text" for="{!! $element->id !!}">{!! $element->label !!} @if($element->value != '') <span class="label label-primary badge">{!! $element->value !!}</span> @endif
	@if($element->status != 'published') <span class="label label-warning badge">{!! $element->status !!}</span> @endif
    </label>
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
    @else
    {!! Form::checkbox("result[".$element->inputid."]", $element->value, (isset($results) && $element->value == $results->{$element->inputid}), ['class' => 'magic-checkbox '.$element->className.' '.$sectionClass, 'id' => $element->id, 'autocomplete' => 'off']) !!}
	<label class="normal-text" for="{!! $element->id !!}">{!! $element->label !!} @if($element->value != '') <span class="label label-primary badge">{!! $element->value !!}</span> @endif
	@if($element->status != 'published') <span class="label label-warning badge">{!! $element->status !!}</span> @endif
    </label>
    @endif
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
