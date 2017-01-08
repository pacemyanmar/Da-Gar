<table class="table table-responsive" id="questions-table">
    <thead>
        <th class="col-xs-1">No.</th>
        <th class="col-xs-9">Questions</th>
        <th class="col-xs-2">Action</th>
    </thead>
    <tbody data-section="{!! $section_key !!}">
        @foreach($questions as $question)
            @if($question->section == $section_key)
            <tr id="sort-{!! $question->id !!}">
                <td class="col-xs-1" id="{!! $question->css_id !!}">
                    <label>{!! $question->qnum !!}</label>
                    @if($question->report)
                        <span class="badge">In report</span>
                    @endif
                    @if($question->double_entry)
                        <span class="badge">Double</span>
                    @endif
                </td>
                <td class="col-xs-9">
                    <div class="row"><label>{!! $question->question !!}</label></div>
                    <div id="accordion{!! $question->css_id !!}" class="row collapse">
                        @include('questions.ans_fields')
                    </div>
                </td>
                <td class="col-xs-2">
                    {!! Form::open(['route' => ['questions.destroy', $question->id], 'method' => 'delete']) !!}
                    <div class='btn-group'>
                        <button type="button" class="btn btn-info btn-xs" data-toggle="collapse" data-target="#accordion{!! $question->css_id !!}">
                        <i class="glyphicon glyphicon-collapse"></i>
                        Click to expand
                        </button>
                        <a href="#" class='btn btn-default btn-xs' data-toggle="modal" data-target="#qModal" data-qid="{!! $question->id !!}" data-qurl="{!! route('questions.update', [$question->id]) !!}" data-qnum="{!! $question->qnum !!}" data-question="{!! $question->question !!}" data-section="{!! $section_key !!}" data-sort="{!! $question->sort !!}" data-answers='{!! $question->raw_ans !!}' data-layout='{!! $question->layout !!}' data-method='PATCH'><i class="glyphicon glyphicon-edit"></i> Edit</a>

                        {!! Form::button('<i class="glyphicon glyphicon-trash"></i>', ['type' => 'submit', 'class' => 'btn btn-danger btn-xs', 'onclick' => "return confirm('Are you sure?')"]) !!}
                    </div>
                    {!! Form::close() !!}
                </td>
            </tr>
            @endif
        @endforeach
    </tbody>
</table>
