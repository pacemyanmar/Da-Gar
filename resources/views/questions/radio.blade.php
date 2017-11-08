<div class="form-group">
    {!! Form::radio("result[".$element->inputid."]",
        $element->value,
        (isset($results) && !empty($results['section'.$section->sort]) && $element->value == $results['section'.$section->sort]->{$element->inputid}),
        [
        'id' => $element->id,
        'class' => ((!empty($element->skip))?'skippable':null).(($element->other)?'other':null).' magic-radio '.$element->className.' '.$sectionClass,
        'autocomplete' => 'off',
        'data-skip' => $element->skip,
        'data-goto' => $element->goto,
        'data-selected' => (isset($results) && !empty($results['section'.$section->sort]) && $element->value == $results['section'.$section->sort]->{$element->inputid})
        ])
        !!}
    <label class="normal-text" for="{!! $element->id !!}">
        {!! $element->label !!}
        @if($element->value != '') <span class="label label-primary badge">{!! $element->value !!}</span> @endif
        @if($element->status != 'published') <span
                class="label label-warning badge">{!! $element->status !!}</span> @endif
        @if(isset($double) && $double && isset($results) && !empty($results['section'.$section->sort]) && isset($double_results) && !empty($double_results['section'.$section->sort]))
            @if($double_results['section'.$section->sort]->{$element->inputid} == $results['section'.$section->sort]->{$element->inputid})
                <span class="label label-success badge"><i class="fa fa-check"></i></span>
            @else
                <span class="label label-danger badge"><i class="fa fa-close"></i></span>
            @endif
        @endif

    </label>
    @if($element->other)
        @php
            $options = [
            'class' => $element->className.' form-control zawgyi othertext '.$sectionClass,
            'id' => $element->id.'_other',
            'placeholder' => Kanaung\Facades\Converter::convert($element->label,'unicode','zawgyi'),
            'aria-describedby'=> $element->id.'-addons',
            'autocomplete' => 'off'
            ];
        @endphp
        {!! Form::text("result[".$element->inputid."_other]", (isset($results) && !empty($results['section'.$section->sort]))?Kanaung\Facades\Converter::convert($results['section'.$section->sort]->{$element->inputid},'unicode','zawgyi'):null, $options) !!}
    @endif

</div>
@if(!empty($element->skip) && !isset($editing))
    @push('document-ready')
        if($("input[name='result[{!! $element->inputid !!}]']:checked").val() == {!! $element->value !!}) {
        $("{!! $element->skip !!}").prop("disabled", true);
        } else {
        $("{!! $element->skip !!}").prop("disabled", false);
        }
        $("#{!! $element->id !!}").click(function(){
        if($("input[name='result[{!! $element->inputid !!}]']:checked").val() == {!! $element->value !!}) {
        $("{!! $element->skip !!}").prop("disabled", true);
        @if(isset($element->extras['goto']))
            $("body, html").animate({
            scrollTop: $("{!! $element->extras['goto'] !!}").offset().top
            }, 600);
        @endif
        } else {
        $("{!! $element->skip !!}").prop("disabled", false);
        }
        });
    @endpush
@endif
