<table class="table table-responsive" id="questions-table">
    <thead>
    <th class="col-xs-1">{!! trans('messages.no_') !!}</th>
    <th class="col-xs-11">{!! trans('messages.question') !!}</th>
    </thead>
    <tbody>
    @foreach($section->questions as $question)
        @if($question->layout == 'description')
            <tr id="{!! $question->css_id !!}">
                <td class="col-xs-12" colspan="2">
                    <div class="">
                        <label>{!! trans('questions.'.'q'.strtolower($question->id.$question->qnum)) !!}</label>
                    </div>
                </td>
            </tr>
        @else
            <tr id="{!! $question->css_id !!}">
                <td class="col-xs-1">
                    @if(!in_array($question->layout, ['household']))
                        <label>{!! $question->qnum !!}</label>
                    @endif
                    @if($question->report)
                        <span class="badge">In report</span>
                    @endif
                    @if($question->double_entry)
                        <span class="badge">Double</span>
                    @endif
                </td>
                <td class="col-xs-11">
                    <div class="row">
                        <label>
                            {!! trans('questions.'.'q'.strtolower($question->id.$question->qnum)) !!}
                        </label>
                    </div>
                    <div class="row">

                        @include('questions.ans_fields')

                    </div>
                </td>
            </tr>
        @endif
    @endforeach
    </tbody>
</table>
