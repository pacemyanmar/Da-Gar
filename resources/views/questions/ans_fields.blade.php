<?php

if (Auth::user()->role->role_name == 'doublechecker') {
    $prefix = "double_";
} else {
    $prefix = "";
}
/**
 * count answers
 * set array of css class based on column count
 */
$surveyInputs = $project->inputs->where('question_id', $question->id)->all();
// reindex collection array
$surveyInputs = array_values($surveyInputs);
$anscount = count($surveyInputs);
$css_class = [
    '', // empty
    'col-xs-12', // column count 1
    'col-xs-6', // column count 2
    'col-xs-4', // column count 3
];

/**
 *    Question layout is 2cols, count is 2, if 3 cols, count is 3 else 1
 * @var intever column count
 */

$col_group_count = ($question->layout == '2cols') ? 2 : (($question->layout == '3cols') ? 3 : 1);

$wordcount = 50 - ($col_group_count * 10);
/**
 * @var integer Total number of input fields in a column
 */
$ans_in_col = round($anscount / $col_group_count);

/**
 * $i => increment to total column count
 * $j => increment for outside use from input field loop
 * $k => current input field index     *
 */
?>
@if($question->layout == 'matrix')
	@include('questions.'.$prefix.'radio-group')
@elseif($question->layout == 'ballot')
	@include('questions.'.$prefix.'ballot-table')
@else
	@for($i=0,$j=0;$i<$col_group_count; $i++)
		@if($j == ($i * $ans_in_col))
			<div class="{!! $css_class[$col_group_count] !!} @if($i>0) {{ ' padleft' }}@endif">
		@endif

		@foreach ($surveyInputs as $k => $element)
			@if($k >= ($i * $ans_in_col) && $k < (($i + 1) * $ans_in_col))
				@php
					$origin = (isset($results) && $element->value == $results->{$element->inputid});
					$double = (isset($double_results) && $element->value == $double_results->{$element->inputid});
					$origin_text = (isset($results))?$results->{$element->inputid}:null;
					$double_text = (isset($double_results))?$double_results->{$element->inputid}:null;
				@endphp
				@if(!isset($element->value))
					@php
						$element->value = $k;
					@endphp
				@endif
				<div class="form-inline" style="margin-top:5px;margin-bottom:5px;">
				@if ($element->type == 'checkbox')
					@include('questions.'.$prefix.'checkbox')
				@endif
				@if ($element->type == 'radio')
					@include('questions.'.$prefix.'radio')
				@endif
				@if (in_array($element->type,['registered-voters','advanced-voters']))
					@include('questions.'.$prefix.'ballot')
				@endif
				@if (in_array($element->type,['text','number','email','date']))
					@include('questions.'.$prefix.'other-input')
				@endif
				@php
					$j++;
				@endphp

				@if(Auth::user()->role->level >= 8 && isset($editing))
                        <div class="btn-group">
                        {!! Form::open(['route' => ['translate', $element->id], 'method' => 'post', 'class' => 'translation']) !!}
                        <div class="input-group">
                              <input type="text" name="columns[label]" class="form-control input-sm" placeholder="{!! trans('messages.add_translation') !!}" @if(!empty($element->label_trans) && array_key_exists(config('app.locale'), $element->label_trans ))
                                value="{!! $element->label_trans[config('app.locale')] !!}"
                              @endif>
                              <input type="hidden" name="model" value="survey_input">
                              <span class="input-group-btn">
                                <button class="btn btn-default input-sm" type="submit">{!! trans('messages.save') !!}</button>
                              </span>

                        </div><!-- /input-group -->
                        {!! Form::close() !!}
                        </div>
              	@endif
              	</div>

			@endif
		@endforeach
		@if($j <= (($i + 1) * $ans_in_col))
			</div>
		@endif
	@endfor
@endif
