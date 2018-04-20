<div class="form-group">
    {!! Form::radio("result[".$element->inputid."]",
    $element->value,
    (isset($double_results['section'.$section->sort]->{$element->inputid}) && $element->value == $double_results['section'.$section->sort]->{$element->inputid}),
    [
    'data-class'=>$element->inputid,
    'data-origin'=>(isset($results['section'.$section->sort]->{$element->inputid}))? $results['section'.$section->sort]->{$element->inputid}:null,
    'id' => $element->id,
    'class' => ((!empty($element->skip))?'skippable':null).(($element->other)?' other ':null).' magic-radio '.$element->className.' '.$sectionClass,
    'autocomplete' => 'off',
    'data-skip' => $element->skip,
    'data-goto' => $element->goto,
    'data-selected' => (isset($double_results['section'.$section->sort]->{$element->inputid}) && $element->value == $double_results['section'.$section->sort]->{$element->inputid})])
    !!}
    <label class="normal-text" for="{!! $element->id !!}">
        {!! $element->label !!}
        @if($element->value != '') <span class="label label-primary badge">{!! $element->value !!}</span> @endif
        @if($element->status != 'published') <span
                class="label label-warning badge">{!! $element->status !!}</span> @endif
        @if(str_contains(strtolower($element->label), 'other'))
            {!! Form::text("result[".$element->inputid."]",
            (isset($double_results)
            && !empty($double_results['section'.$section->sort]))?$double_results['section'.$section->sort]->{$element->inputid}:null,
            ['class' => $element->className.' form-control input-sm zawgyi',
            'autocomplete' => 'off',
            'id' => $element->id.'other']) !!}

        @endif
        <span class="hide label label-danger badge {!! $element->inputid .' '.$element->id!!}"><i class="fa"></i></span>
    </label>
    @if($element->other)
        @php
            $options = [
            'class' => $element->className.' form-control other zawgyi '.$sectionClass,
            'id' => $element->id.'_other',
            'placeholder' => Kanaung\Facades\Converter::convert($element->label,'unicode','zawgyi'),
            'aria-describedby'=> $element->id.'-addons',
            'autocomplete' => 'off'
            ];
        @endphp
        {!! Form::text("result[".$element->inputid."_other]", (isset($double_results)&& !empty($double_results['section'.$section->sort]))?Kanaung\Facades\Converter::convert($double_results['section'.$section->sort]->{$element->inputid.'_other'},'unicode','zawgyi'):null, $options) !!}
    @endif
</div>

