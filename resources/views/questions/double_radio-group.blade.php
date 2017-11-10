@php
    $layoutError = false;
    $locale = \App::getLocale();
    $translation = (Auth::user()->role->level >= 8 && isset($editing));
@endphp

<table class="table table-responsive">
    <thead>
    <th></th>
    @foreach ($question->surveyInputs->keyBy('name') as $name => $element)

        @if(isset($element->extras['group']))
            <th>
                @if(array_key_exists('lang',$element->extras) && isset($element->extras['lang'][$locale]))
                    @if(isset($element->extras['lang'][$locale]['group']))
                        {!! $element->extras['lang'][$locale]['group'] !!}
                    @else
                        {!! $element->extras['group'] !!}
                    @endif
                @else
                    {!! $element->extras['group'] !!}
                @endif

            </th>
        @else
            @php
                $layoutError = true;
            @endphp
        @endif
    @endforeach
    </thead>
    @foreach ($question->surveyInputs->groupBy('label') as $label => $element)
        <tr>
            @if(isset($label))
                <td>{!! $label !!}</td>
                @foreach($element as $radio)
                    <td>
                        @if($radio->type == 'text')
                            @php
                                $options = [
                                    'class' => $radio->className.' form-control zawgyi ',
                                    'id' => $radio->id,
                                    'placeholder' => Kanaung\Facades\Converter::convert($radio->label,'unicode','zawgyi'),
                                    'aria-describedby'=> $radio->id.'-addons',
                                    'autocomplete' => 'off'
                                    ];
                            @endphp
                            {!! Form::input($radio->type,"result[".$radio->inputid."]", (isset($double_results) && $double_results['section'.$section->sort])?Kanaung\Facades\Converter::convert($double_results['section'.$section->sort]->{$radio->inputid},'unicode','zawgyi'):null, $options) !!}
                        @else
                            {!! Form::radio("result[".$radio->inputid."]",
                            $radio->value, (isset($double_results) && !empty($double_results['section'.$section->sort]) && $radio->value == $double_results['section'.$section->sort]->{$radio->inputid}),
                            ['data-origin' =>(isset($results) && !empty($results['section'.$section->sort]) && $radio->value == $results['section'.$section->sort]->{$radio->inputid}),
                            'id' => $radio->id,
                            'class' => ' magic-radio '.$radio->className.' '.$sectionClass,
                            'autocomplete' => 'off',
                            'data-selected' => (isset($double_results) && !empty($double_results['section'.$section->sort]) && $radio->value == $double_results['section'.$section->sort]->{$radio->inputid})]) !!}
                        @endif
                        <label class="normal-text" for='{{ $radio->id }}'>
                            @if($radio->value != '')
                                <span class="label label-primary badge">{!! $radio->value !!}</span>
                            @endif

                            @if($radio->other)
                                {!! Form::text("result[".$radio->inputid."]",
                                (isset($double_results) && !empty($double_results['section'.$section->sort]) && $radio->value == $double_results['section'.$section->sort]->{$radio->inputid})?$double_results->{$radio->inputid.'_other'}:null,
                                ['class' => $radio->className.' form-control input-sm',
                                'autocomplete' => 'off',
                                'id' => $radio->id.'other',
                                'style' => 'width:80%']) !!}
                            @endif
                        </label>
                    </td>
                @endforeach
            @else
                @php
                    $layoutError = true;
                @endphp
            @endif
        </tr>
    @endforeach
    @if($layoutError === true)
        <tr>
            <div class="alert alert-warning">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                <strong>Layout Error!</strong> Something wrong with your question layout. You are not allowed to use
                "matrix" layout here.
            </div>
        </tr>
    @endif
</table>
