<div class="">
	{!! Form::checkbox($element['name'], $element['value'], null, ['class' => 'magic-checkbox magic-'.$element['className'], 'id' => $element['id']]) !!} 
	<label for="{!! $element['id'] !!}">{!! $element['label'] !!}
    </label>
</div>