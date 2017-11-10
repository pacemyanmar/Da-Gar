<div class="form-group">
    {!! Form::checkbox("result[".$element->inputid."]",
    $element->value,
    (isset($double_results) && !empty($double_results['section'.$section->sort]) &&
    $element->value == $double_results['section'.$section->sort]->{$element->inputid}),
    [
    'data-class'=>$element->inputid,
    'data-origin'=>(isset($results)  && !empty($results['section'.$section->sort]))? $results['section'.$section->sort]->{$element->inputid}:null,
    'class' => 'magic-checkbox '.$element->className.' '.$sectionClass,
    'id' => $element->id,
    'autocomplete' => 'off',
        'data-skip' => $element->skip,
        'data-goto' => $element->goto
    ]) !!}
    <label class="normal-text" for="{!! $element->id !!}">{!! $element->label !!} @if($element->value != '')
            <span class="label label-primary badge">{!! $element->value !!}</span> @endif
        @if($element->status != 'published')
            <span class="label label-warning badge">{!! $element->status !!}</span> @endif
            <span class="hide label label-danger badge {!! $element->inputid .' '.$element->id!!}">{!! "Data not match!" !!}</span>
    </label>
    @if($element->other)
        @php
            $options = [
            'class' => $element->className.' form-control zawgyi '.$sectionClass,
            'id' => $element->id.'_other',
            'placeholder' => Kanaung\Facades\Converter::convert($element->label,'unicode','zawgyi'),
            'aria-describedby'=> $element->id.'-addons',
            'autocomplete' => 'off'
            ];
        @endphp
        {!! Form::text("result[".$element->inputid."_other]", (isset($results) && !empty($results['section'.$section->sort]))?Kanaung\Facades\Converter::convert($results['section'.$section->sort]->{$element->inputid.'_other'},'unicode','zawgyi'):null, $options) !!}
    @endif
</div>
