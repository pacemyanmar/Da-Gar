<div>
	                        
    {!! Form::checkbox($element['name'], $element['value'], null, ['class' => 'magic-'.$element['className'].' magic-checkbox', 'id' => $element['id']]) !!} 
    <label for="{!! $element['id'] !!}">
    {!! $element['label'] !!}
    </label>
</div>