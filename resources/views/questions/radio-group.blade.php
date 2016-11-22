@php
$layoutError = false;
@endphp
<table class="table table-responsive" >
	<thead>
		<th></th>
		@if(isset($question->render[0]['values']))
		@foreach ($question->render[0]['values'] as $header)
		<th>{!! $header['label'] !!}</th>
		@endforeach
		@else
		@php
		$layoutError = true;
		@endphp
		@endif
	</thead>
	@foreach ($question->render as $av)
	<tr>
		@if(isset($av['values']))
		<td>{!! $av['label'] !!}</td>		
		@foreach($av['values'] as $value)
		<td>{!! Form::radio("result[".$av['name']."]", $value['value'], null, ['id' => $value['id'],'class' => ' magic-radio '.$av['name'].' '.$sectionClass]) !!}<label class="normal-text" for='{{ $value['id'] }}'><!-- dummy for magic radio --></label></td>
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
