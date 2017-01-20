<table class="table table-responsive" id="questions-table">
    <thead>
        <th class="col-xs-1">{!! trans('messages.no_') !!}</th>
        <th class="col-xs-11">{!! trans('messages.question') !!}</th>
    </thead>
    <tbody>
        @foreach($project->questions as $question)
            @if($question->section == $section_key)
            <tr id="{!! $question->css_id !!}">
                <td class="col-xs-1">
                <label>{!! $question->qnum !!}</label>
                    @if($question->report)
                        <span class="badge">In report</span>
                    @endif
                    @if($question->double_entry)
                        <span class="badge">Double</span>
                    @endif
                </td>
                <td class="col-xs-11">
                    <div class="row"><label>{!! $question->question !!}</label></div>
                    <div class="row">

                        @include('questions.ans_fields')

                    </div>
                </td>
            </tr>
            @endif
        @endforeach
    </tbody>
</table>
