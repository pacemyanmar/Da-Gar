<!-- Id Field -->
<div class="form-group">
    {!! Form::label('id', 'Id:') !!}
    <p>{!! $question->id !!}</p>
</div>

<!-- Qnum Field -->
<div class="form-group">
    {!! Form::label('qnum', 'Qnum:') !!}
    <p>{!! $question->qnum !!}</p>
</div>

<!-- Question Field -->
<div class="form-group">
    {!! Form::label('question', 'Question:') !!}
    <p>{!! $question->question !!}</p>
</div>

<!-- Answers Field -->
<div class="form-group">
    {!! Form::label('answers', 'Answers:') !!}
    <p>{!! $question->answers !!}</p>
</div>

<!-- Sort Field -->
<div class="form-group">
    {!! Form::label('sort', 'Sort:') !!}
    <p>{!! $question->sort !!}</p>
</div>

<!-- Project Id Field -->
<div class="form-group">
    {!! Form::label('project_id', 'Project Id:') !!}
    <p>{!! $question->project_id !!}</p>
</div>

