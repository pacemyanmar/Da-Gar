@php
if($element['type'] == 'date') $element['className'] .= ' form-control';

$options = [
'class' => $element['className'].' form-control',
'id' => $element['id'],
'placeholder' => $element['label'],
'aria-describedby'=> $element['id'].'-addons'
];
if($element['type'] == 'number') {
if(isset($element['min'])) $options['min'] = $element['min'];
if(isset($element['max'])) $options['max'] = $element['max'];
if(isset($element['step'])) $options['step'] = $element['step'];
}
@endphp
    <div class="form-group">
    	<div class="input-group">
    		<!-- if string long to show in label show as tooltip -->
    		<span class="input-group-addon" id="{{ $element['id'] }}-addons" @if(mb_strlen($element['label']) > $wordcount) data-toggle="tooltip" data-placement="top" title="{!! $element['label'] !!}" @endif>
    		@if(mb_strlen($element['label']) > $wordcount)
    			{!! str_limit($element['label'], $wordcount - 7 ) !!} <i class="fa fa-info-circle"></i>
    		@else
				{!! $element['label'] !!}
    		@endif
    		</span>
    		{!! Form::input($element['type'],$element['name'], null, $options) !!}
    	</div>
    	
    </div>