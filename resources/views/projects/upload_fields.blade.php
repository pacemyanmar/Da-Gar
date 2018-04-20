<div class="form-group">
    <label class="normal-text" for="idcolumn">ID Code Column Name ( required )</label>
    <input value="@if($project->idcolumn) {!! $project->idcolumn !!} @endif"
           class="form-control required" type="text" name="idcolumn"
           id="idcolumn" required>
</div>
@if(empty($project->idcolumn))
    <div class="input-group">
        <input value="1" class="magic-checkbox optional" type="checkbox" name="update_structure"
               id="structure">
        <label class="normal-text form-label" for="structure">Update Column Structure
            <small>( Check if you need to add new column.)</small>
        </label>

    </div>
@endif
<div class="form-group">
        <span class="btn btn-primary btn-file" id="file">
            <label for="upload" id="file-label">Browse File</label>
            {!! Form::file('samplefile', ['aria-describedby' => 'file', 'id'=>'upload']); !!}
        </span>
</div>