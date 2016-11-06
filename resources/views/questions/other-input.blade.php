@php
if($element['type'] == 'date') $element['className'] .= ' form-control';

$options = [
'class' => $element['className'],
'id' => $element['id'],
'placeholder' => $element['label']
];
if($element['type'] == 'number') {
if(isset($element['min'])) $options['min'] = $element['min'];
if(isset($element['max'])) $options['max'] = $element['max'];
if(isset($element['step'])) $options['step'] = $element['step'];
}
@endphp
    <div class="form-group">
    	{!! Form::label($element['id'], $element['label'], ['class'=>'control-label']) !!}
    		{!! Form::input($element['type'],$element['name'], null, $options) !!}
    </div>