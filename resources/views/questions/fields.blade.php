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
    <option value="form16">Form 16 Table</option>
    <option value="form18">Form 18 Table</option>
</select>
</div>

<div class="form-group col-sm-6">
    <table class="table">
    <tr>
    <td>
    {!! Form::checkbox("optional", 1, null, ['class' => 'magic-checkbox ', 'id' => 'optionalq']) !!}
    <label class="normal-text" for="optionalq">Optional
    </label>
    </td>
    <td>
    {!! Form::checkbox("double_entry", 1, null, ['class' => 'magic-checkbox ', 'id' => 'doubleq']) !!}
    <label class="normal-text" for="doubleq">Double Entry
    </label>
    </td>
    <td>
    {!! Form::checkbox("report", 1, null, ['class' => 'magic-checkbox ', 'id' => 'reportq']) !!}
    <label class="normal-text" for="reportq">Show in report
    </label>
    </td>
    </tr>
    </table>
</div>
<div class="form-group col-sm-12">
    <table class="table">
        <tr>
            @foreach($observation_type as $label => $type)
                <td>
                {!! Form::checkbox("observation_type[".str_slug($type)."]", str_slug($type), null, ['class' => 'magic-checkbox observation_type', 'id' => str_slug($type)]) !!}
                <label class="normal-text" for="{!! str_slug($type) !!}">{!! $label !!}
                </label>
                </td>
            @endforeach
        </tr>
    </table>
</div>
<!-- Project Id Field -->
<!--div class="form-group col-sm-6"-->
    {{-- Form::label('answers', 'Answers:') --}}
    {!! Form::hidden('project_id', $project->id) !!}
    {!! Form::hidden('sort', count($questions)) !!}
    {!! Form::hidden('raw_ans') !!}
    {!! Form::hidden('section', null) !!}
