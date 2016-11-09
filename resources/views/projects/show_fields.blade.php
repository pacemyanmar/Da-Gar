<table class="table table-responsive" id="questions-table">
    <thead>
        <th class="col-xs-1">No.</th>
        <th class="col-xs-11">Questions</th>
    </thead>
    <tbody>
        @foreach($questions as $question)
            @if($question->section == $section_key)
            <tr>
                <td class="col-xs-1"><label>{!! $question->qnum !!}</label></td>
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