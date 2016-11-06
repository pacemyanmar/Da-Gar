<table class="table table-responsive" id="questions-table">
    <thead>
        <th class="col-xs-1">No.</th>
        <th class="col-xs-9">Questions</th>
        <th class="col-xs-2">Action</th>
    </thead>
    <tbody>
        @foreach($questions as $question)
            @if($question->section == $section_key)
            <tr>
                <td class="col-xs-1">{!! $question->qnum !!}</td>
                <td class="col-xs-9">
                    <div class="row">{!! $question->question !!}</div>
                    <div class="row">
                        @include('questions.ans_fields')
                    </div>
                </td>
                <td class="col-xs-2">
                    {!! Form::open(['route' => ['questions.destroy', $question->id], 'method' => 'delete']) !!}
                    <div class='btn-group'>
                        <a href="#" class='btn btn-default btn-xs' data-toggle="modal" data-target="#qModal" data-qid="{!! $question->id !!}" data-qurl="{!! route('api.questions.update', [$question->id]) !!}" data-qnum="{!! $question->qnum !!}" data-question="{!! $question->question !!}" data-section="{!! $section_key !!}" data-rawans='{!! $question->raw_ans !!}' data-layout='{!! $question->layout !!}' data-method='PATCH'><i class="glyphicon glyphicon-edit"></i></a>
                        {!! Form::button('<i class="glyphicon glyphicon-trash"></i>', ['type' => 'submit', 'class' => 'btn btn-danger btn-xs', 'onclick' => "return confirm('Are you sure?')"]) !!}
                    </div>
                    {!! Form::close() !!}
                </td>
            </tr>    
            @endif
        @endforeach
    </tbody>
</table>