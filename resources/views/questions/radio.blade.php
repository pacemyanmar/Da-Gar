<div class="radio">
   	<label for="{!! $element['id'] !!}">
   		{!! Form::radio($element['name'], $element['value'], null, ['id' => $element['id']]) !!}
   		{!! $element['label'] !!}
   	</label>
</div>