<div class="form-group">


    {!! Form::checkbox("result[".$element->inputid."]",
    $element->value,
    (isset($results) && !empty($results['section'.$section->sort]) && $results['section'.$section->sort]->{$element->inputid}),
    [
    'class' => (($element->other)?'other':null).' magic-checkbox '.$element->className.' '.$sectionClass. ' '. ((!empty($element->skip))? 'skippable':null),
    'id' => $element->id,
    'autocomplete' => 'off',
    'data-skip' => $element->skip,
    'data-goto' => $element->goto
    ])
    !!}
    <label class="normal-text" for="{!! $element->id !!}">{!! $element->label !!} @if($element->value != '')
            <span class="label label-primary badge">{!! $element->value !!}</span> @endif
        @if($element->status != 'published')
            <span class="label label-warning badge">{!! $element->status !!}</span> @endif
        @if($double)
            @if(isset($results) && !empty($results['section'.$section->sort]) && isset($double_results) && !empty($double_results['section'.$section->sort]))
                @if($double_results['section'.$section->sort]->{$element->inputid} == $results['section'.$section->sort]->{$element->inputid})
                    <span class="label label-success badge"><i class="fa fa-check"></i></span>
                @else
                    <span class="label label-danger badge"><i class="fa fa-close"></i></span>
                @endif
            @elseif( isset($results) && !empty($results['section'.$section->sort]) )
                <span class="label label-warn badge"><i class="fa fa-question"> </i> No 2nd</span>
            @elseif( isset($double_results) && !empty($double_results['section'.$section->sort]) )
                <span class="label label-warn badge"><i class="fa fa-question"> </i> No 1st</span>
            @endif
        @endif
    </label>
    @if($element->other)
        @php
            $options = [
            'class' => $element->className.' form-control othertext zawgyi '.$sectionClass,
            'id' => $element->id.'_other',
            'placeholder' => Kanaung\Facades\Converter::convert($element->label,'unicode','zawgyi'),
            'aria-describedby'=> $element->id.'-addons',
            'autocomplete' => 'off',
            'disabled' => true
            ];

        @endphp
        {!! Form::text("result[".$element->inputid."_other]", (isset($results) && !empty($results['section'.$section->sort]) )?Kanaung\Facades\Converter::convert($results['section'.$section->sort]->{$element->inputid.'_other'},'unicode','zawgyi'):null, $options) !!}


    @endif
</div>

