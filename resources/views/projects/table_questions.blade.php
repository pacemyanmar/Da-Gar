<table class="table table-responsive" id="questions-table">
    <thead>
        <th class="col-xs-1">{!! trans('messages.no_') !!}</th>
        <th class="col-xs-9">{!! trans('messages.question') !!}</th>
        <th class="col-xs-2">{!! trans('messages.action') !!}</th>
    </thead>
    <tbody data-section="{!! $section_key !!}">
        @foreach($questions as $question)
            @if($question->section == $section_key)
            <tr id="sort-{!! $question->id !!}">
                <td class="col-xs-1" id="{!! $question->css_id !!}">
                    <label>{!! $question->qnum !!}</label>

                    @if($question->report)
                        <span class="badge">{!! trans('messages.in_report') !!}</span>
                    @endif
                    @if($question->double_entry)
                        <span class="badge">{!! trans('messages.double_entry') !!}</span>
                    @endif
                </td>
                <td class="col-xs-9">
                    <div class="row"><label>{!! $question->question !!}</label></div>
                    <div id="accordion{!! $question->css_id !!}" class="row collapse">
                        @if(Auth::user()->role->level > 8)
                        <div class="btn-group form-inline" style="margin-bottom:20px;">
                        {!! Form::open(['route' => ['translate', $question->id], 'method' => 'post', 'class' => 'translation']) !!}
                              <div class="input-group">
                              <span class="input-group-addon">{!! trans('messages.qnum') !!}</span>
                              <input type="text" name="columns[qnum]" class="form-control" placeholder="{!! trans('messages.add_translation') !!}" @if(!empty($question->qnum_trans) && array_key_exists(config('app.locale'), $question->qnum_trans ))
                                value="{!! $question->qnum_trans[config('app.locale')] !!}"
                              @endif>
                              </div>
                              <div class="input-group">
                              <span class="input-group-addon">{!! trans('messages.question') !!}</span>
                              <input type="text" name="columns[question]" class="form-control" placeholder="{!! trans('messages.add_translation') !!}" @if(!empty($question->question_trans) && array_key_exists(config('app.locale'), $question->question_trans ))
                                value="{!! $question->question_trans[config('app.locale')] !!}"
                              @endif>
                              <input type="hidden" name="model" value="question">
                              <span class="input-group-btn">
                                <button class="btn btn-default" type="submit">{!! trans('messages.save') !!}</button>
                              </span>

                        </div><!-- /input-group -->
                        {!! Form::close() !!}
                        </div>
                        @endif
                        @include('questions.ans_fields')
                    </div>
                </td>
                <td class="col-xs-2">
                    {!! Form::open(['route' => ['questions.destroy', $question->id], 'method' => 'delete']) !!}
                    <div class='btn-group'>
                        <button type="button" class="btn btn-info btn-xs" data-toggle="collapse" data-target="#accordion{!! $question->css_id !!}">
                        <i class="glyphicon glyphicon-collapse"></i>
                        {!! trans('messages.click_to_expend') !!}
                        </button>
                        <a href="#" class='btn btn-default btn-xs' data-toggle="modal" data-target="#qModal" data-qid="{!! $question->id !!}" data-double="{!! $question->double_entry !!}" data-optional="{!! $question->optional !!}" data-report="{!! $question->report !!}" data-qurl="{!! route('questions.update', [$question->id]) !!}" data-qnum="{!! $question->qnum !!}" data-question="{!! $question->question !!}" data-section="{!! $section_key !!}" data-sort="{!! $question->sort !!}" data-answers='{!! str_replace("'","&#39;",$question->raw_ans) !!}' data-layout='{!! $question->layout !!}' data-method='PATCH'><i class="glyphicon glyphicon-edit"></i> {!! trans('messages.edit') !!}</a>

                        {!! Form::button('<i class="glyphicon glyphicon-trash"></i>', ['type' => 'submit', 'class' => 'btn btn-danger btn-xs', 'onclick' => "return confirm('".trans('messages.are_you_sure')."')"]) !!}
                    </div>
                    {!! Form::close() !!}
                </td>
            </tr>
            @endif
        @endforeach
    </tbody>
</table>
