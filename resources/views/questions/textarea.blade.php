<div class="form-group">
    <label class="normal-text" for="{!! $element->id !!}">{!! $element->label !!}
        @if($element->status != 'published') <span
                class="label label-warning badge">{!! $element->status !!}</span> @endif
    </label>
    {!! Form::textarea($element->inputid, null, ['class' => $element->className, 'id' => $element->id, 'autocomplete' => 'off']) !!}

</div>
