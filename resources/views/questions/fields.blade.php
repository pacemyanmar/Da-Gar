<!-- Qnum Field -->
<div class="form-group col-sm-6">
    {!! Form::label('qnum', 'No.:') !!}
    {!! Form::text('qnum', null, ['class' => 'form-control']) !!}
</div>

<!-- Questions Field -->
<div class="form-group col-sm-6">
    {!! Form::label('question', 'Question:') !!}
    {!! Form::text('question', null, ['class' => 'form-control']) !!}
</div>

<div class="form-group col-sm-6">
<select name="layout" id="layout" class="form-control">
	<option value="">-- Select One/None --</option>
	<option value="2cols">2 Columns</option>
	<option value="3cols">3 Columns</option>
	<option value="matrix">Matrix Table (Use only with radio button)</option>
</select>
</div>

<!-- Project Id Field -->
<!--div class="form-group col-sm-6"-->
    {{-- Form::label('answers', 'Answers:') --}}
    {!! Form::hidden('project_id', $project->id) !!}
    {!! Form::hidden('sort', 0) !!}
    {!! Form::hidden('raw_ans') !!}
    {!! Form::hidden('section', null) !!}
