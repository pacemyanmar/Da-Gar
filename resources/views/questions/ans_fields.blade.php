<?php
	/**
	 * count answers
	 * set array of css class based on column count
	 */

	$anscount = count($question->render);
	$css_class = [
		'', // empty
		'col-xs-12', // column count 1
		'col-xs-6', // column count 2
		'col-xs-4' // column count 3
	];

	/**
	 *	Question layout is 2cols, count is 2, if 3 cols, count is 3 else 1
	 * @var intever column count
	 */

	$col_group_count = ($question->layout == '2cols')? 2:(($question->layout == '3cols')? 3:1);

	/**
	 * @var integer Total number of input fields in a column
	 */
	$ans_in_col = round($anscount / $col_group_count);

	/**
	 * $i => increment to total column count
	 * $j => increment for outside use from input field loop
	 * $k => current input field index	 * 
	 */ 
?>
@if($question->layout == 'matrix')	
	@include('questions.radio-group')
@else
	@for($i=0,$j=0;$i<$col_group_count; $i++)
		@if($j == ($i * $ans_in_col))
			<div class="{!! $css_class[$col_group_count] !!}">
		@endif
		
		@foreach ($question->render as $k => $element)
			@if($k >= ($i * $ans_in_col) && $k < (($i + 1) * $ans_in_col))
				@if(!isset($element['value']))
					@php
						$element['value'] = $k;
					@endphp
				@endif
				@if ($element['type'] == 'checkbox')					
					@include('questions.checkbox')
				@endif
				@if ($element['type'] == 'radio')
					@include('questions.radio')
				@endif
				@if (in_array($element['type'],['text','number','email','date']))
					@include('questions.other-input')
				@endif
				@php
					$j++;
				@endphp

			@endif
		@endforeach
		@if($j <= (($i + 1) * $ans_in_col))
			</div>
		@endif
	@endfor
@endif