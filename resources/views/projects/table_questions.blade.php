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
                        @if(Auth::user()->role->level > 8)
                        <div class="btn-group form-inline" style="margin-bottom:20px;">
                        {!! Form::open(['route' => ['translate', $question->id], 'method' => 'post', 'class' => 'translation']) !!}

                              @php
                                $qnum_trans = json_decode($question->qnum_trans,true);
                                $question_trans = json_decode($question->question_trans,true);
                              @endphp
                              <div class="input-group">
                              <span class="input-group-addon">Q Number</span>
                              <input type="text" name="columns[qnum]" class="form-control" placeholder="Add Translation" @if(!empty($qnum_trans) && array_key_exists(config('app.locale'), $qnum_trans ))
                                value="{!! $qnum_trans[config('app.locale')] !!}"
                              @endif>
                              </div>
                              <div class="input-group">
                              <span class="input-group-addon">Question</span>
                              <input type="text" name="columns[question]" class="form-control" placeholder="Add Translation" @if(!empty($question_trans) && array_key_exists(config('app.locale'), $question_trans ))
                                value="{!! $question_trans[config('app.locale')] !!}"
                              @endif>
                              <input type="hidden" name="model" value="question">
                              <span class="input-group-btn">
                                <button class="btn btn-default" type="submit">Save</button>
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
                        Click to expand
                        </button>
                        <a href="#" class='btn btn-default btn-xs' data-toggle="modal" data-target="#qModal" data-qid="{!! $question->id !!}" data-qurl="{!! route('questions.update', [$question->id]) !!}" data-qnum="{!! $question->qnum !!}" data-question="{!! $question->question !!}" data-section="{!! $section_key !!}" data-sort="{!! $question->sort !!}" data-answers='{!! str_replace("'","&#39;",$question->raw_ans) !!}' data-layout='{!! $question->layout !!}' data-method='PATCH'><i class="glyphicon glyphicon-edit"></i> Edit</a>

                        {!! Form::button('<i class="glyphicon glyphicon-trash"></i>', ['type' => 'submit', 'class' => 'btn btn-danger btn-xs', 'onclick' => "return confirm('Are you sure?')"]) !!}
                    </div>
                    {!! Form::close() !!}
                </td>
            </tr>
            @endif
        @endforeach
    </tbody>
</table>
