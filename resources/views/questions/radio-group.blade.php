@php
$layoutError = false;
@endphp
<table class="table table-responsive" >
	<thead>
		<th></th>
		@foreach ($question->surveyInputs->pluck('extras','inputid') as $element)
			@if(isset($element['group']))
				<th>{!! $element['group'] !!}</th>
			@else
				@php
					$layoutError = true;
				@endphp
			@endif
		@endforeach
	</thead>
	@foreach ($question->surveyInputs->groupBy('label') as $label => $element)
	<tr>
		@if(isset($label))
		<td>{!! $label !!}</td>
		@foreach($element as $radio)
		<td>
		{!! Form::radio("result[".$radio->inputid."]", $radio->value, (isset($results) && $radio->value == $results->{$radio->inputid}), ['id' => $radio->id,'class' => ' magic-radio '.$radio->className.' '.$sectionClass, 'autocomplete' => 'off']) !!}
		<label class="normal-text" for='{{ $radio->id }}'><!-- dummy for magic radio -->
		@if($radio->value != '') <span class="label label-primary badge">{!! $radio->value !!}</span> @endif
		@if(str_contains(strtolower($label), 'other'))
		{!! Form::text("result[".$radio->inputid."]", (isset($results))?$results->{$radio->inputid}:null, ['class' => $radio->className, 'autocomplete' => 'off', 'id' => $radio->id.'other']) !!}
			@push('document-ready')
				$("input[name='result[{!! $radio->inputid !!}]']").change(function(e){
					if($("input[name='result[{!! $radio->inputid !!}]']:checked").val() == {!! $radio->value !!}) {
						$("#{!! $radio->id.'other' !!}").prop('required', true).addClass('has-error');
					} else {
						$("#{!! $radio->id.'other' !!}").prop('required', false).removeClass('has-error');
					}
				});

				if($("input[name='result[{!! $radio->inputid !!}]']:checked").val() == {!! $radio->value !!}) {
					$("#{!! $radio->id.'other' !!}").prop('required', true).addClass('has-error');
				}
			@endpush
		@endif
		</label>
		</td>
		@endforeach
		@else
		@php
		$layoutError = true;
		@endphp
		@endif
	</tr>
	@endforeach
	@if($layoutError === true)
	<tr>
		<div class="alert alert-warning">
			<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
			<strong>Layout Error!</strong> Something wrong with your question layout. You are not allowed to use "matrix" layout here.
		</div>
	</tr>
	@endif
</table>
