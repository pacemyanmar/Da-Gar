<div class="">
   	
   		{!! Form::radio($element['name'], $element['value'], null, ['id' => $element['id'],'class' => 'magic-radio magic-'.$element['className']]) !!}
   	<label for="{!! $element['id'] !!}">
   		{!! $element['label'] !!}
   	</label>
</div>