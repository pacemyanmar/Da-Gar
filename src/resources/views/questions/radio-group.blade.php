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
                @if($translation)
                    {!! Form::open(['route' => ['translate', 'group'], 'method' => 'post', 'class' => 'translation']) !!}
                    <div class="input-group">
                        <input type="text" name="columns[group]" class="form-control input-sm"
                               placeholder="{!! trans('messages.add_translation') !!}">
                        <input type="hidden" name="qid" value="{!! $question->id !!}">
                        <input type="hidden" name="input" value="{!! $element->inputid !!}">
                        <input type="hidden" name="model" value="survey_input">
                        <span class="input-group-btn">
				          <button class="btn btn-default input-sm" type="submit">{!! trans('messages.save') !!}</button>
				        </span>
                    </div>
                    {!! Form::close() !!}
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
            @if(!empty($label))
                <td>
                    {!! $label !!}
                    @if($translation)
                        {!! Form::open(['route' => ['translate', 'group'], 'method' => 'post', 'class' => 'translation']) !!}
                        @foreach($element->where('label', $label) as $input)
                            <input type="hidden" name="input[]" value="{!! $input->id !!}">
                            <input type="hidden" name="group[]" value="{!! $input->extras['group'] !!}">
                        @endforeach
                        <div class="input-group">
                            <input type="text" name="columns[label]" class="form-control input-sm"
                                   placeholder="{!! trans('messages.add_translation') !!}">
                            <input type="hidden" name="model" value="survey_input">
                            <span class="input-group-btn">
		          <button class="btn btn-default input-sm" type="submit">{!! trans('messages.save') !!}</button>
		        </span>
                        </div>
                        {!! Form::close() !!}
                    @endif
                </td>
                @foreach($element as $radio)
                    <td>
                        <div class="form-group">

                            @php
                                $options = [
                                    'class' => ((!empty($radio->skip))?' skippable ':null).(($radio->other)?' other ':null).(($radio->type == 'radio')?'  magic-radio ':null).$radio->className.' form-control zawgyi ',
                                    'id' => $radio->id,
                                    'placeholder' => Kanaung\Facades\Converter::convert($radio->label,'unicode','zawgyi'),
                                    'aria-describedby'=> $radio->id.'-addons',
                                    'autocomplete' => 'off',
                                    'data-selected' => (isset($results) && array_key_exists('section'.$section->sort, $results) && !empty($results['section'.$section->sort]) && $radio->value == $results['section'.$section->sort]->{$radio->inputid})
                                    ];
                            @endphp

                            @if($radio->type == 'text')

                                {!! Form::text("result[".$radio->inputid."]", (isset($results)&& !empty($results['section'.$section->sort]))?Kanaung\Facades\Converter::convert($results['section'.$section->sort]->{$radio->inputid},'unicode','zawgyi'):null, $options) !!}
                            @else
                                {!!
                                 Form::radio("result[".$radio->inputid."]",
                                 $radio->value, (isset($results) && !empty($results['section'.$section->sort]) && $radio->value == $results['section'.$section->sort]->{$radio->inputid}),
                                 $options ) !!}
                            @endif
                            <label class="normal-text" for='{{ $radio->id }}'><!-- dummy for magic radio -->
                                @if(isset($double) && config('sms.double_entry'))
                                    @if(isset($results) && !empty($results['section'.$section->sort]) && isset($double_results) && !empty($double_results['section'.$section->sort]))
                                        @if($double_results['section'.$section->sort]->{$radio->inputid} == $results['section'.$section->sort]->{$radio->inputid})
                                            <span class="label label-success badge"><i class="fa fa-check"></i></span>
                                        @else
                                            <span class="label label-danger badge"><i class="fa fa-close"></i></span>
                                        @endif
                                    @elseif( isset($results) && !empty($results['section'.$section->sort]) )
                                        <span class="label label-warn badge"><i class="fa fa-question"></i> No 2nd</span>
                                    @elseif( isset($double_results) && !empty($double_results['section'.$section->sort]) )
                                        <span class="label label-warn badge"><i class="fa fa-question"></i> No 1st</span>
                                    @endif
                                @endif

                                @if($radio->value != '')
                                    <span class="label label-primary badge">{!! $radio->value !!}</span>
                                @endif
                                @if($radio->other)
                                    {!! Form::text("result[".$radio->inputid."_other]",
                                    (isset($results) && !empty($results['section'.$section->sort]) && $radio->value == $results['section'.$section->sort]->{$radio->inputid})?$results['section'.$section->sort]->{$radio->inputid.'_other'}:null,
                                    ['class' => $radio->className.' form-control input-sm othertext ',
                                    'autocomplete' => 'off',
                                    'id' => $radio->id.'other',
                                    'style' => 'width:80%']) !!}
                                @endif
                            </label>
                        </div>
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
