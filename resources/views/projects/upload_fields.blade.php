
{{--<div class="form-group">--}}
    {{--<label class="normal-text" for="idcolumn">ID Code Column Name ( required )</label>--}}
    {{--<input value="@if($project->idcolumn) {!! $project->idcolumn !!} @endif"--}}
           {{--class="form-control required" type="text" name="idcolumn"--}}
           {{--id="idcolumn" required>--}}
{{--</div>--}}
{{--@if(empty($project->idcolumn))--}}
    {{--<div class="input-group">--}}
        {{--<input value="1" class="magic-checkbox optional" type="checkbox" name="update_structure"--}}
               {{--id="structure">--}}
        {{--<label class="normal-text form-label" for="structure">Update Column Structure--}}
            {{--<small>( Check if you need to add new column.)</small>--}}
        {{--</label>--}}

    {{--</div>--}}
{{--@endif--}}
{{--<div class="form-group">--}}
        {{--<span class="btn btn-primary btn-file" id="file">--}}
            {{--<label for="upload" id="file-label">Browse File</label>--}}
            {{--{!! Form::file('samplefile', ['aria-describedby' => 'file', 'id'=>'upload']); !!}--}}
        {{--</span>--}}
{{--</div>--}}

<div>

    <!-- Nav tabs -->
    <ul class="nav nav-tabs" role="tablist">
        <li role="presentation" class="active"><a href="#withFile" aria-controls="home" role="tab" data-toggle="tab">Upload File</a></li>
        <li role="presentation"><a href="#withUrl" aria-controls="profile" role="tab" data-toggle="tab">Paste file URL</a></li>
    </ul>
    <br>
    <!-- Tab panes -->
    <div class="tab-content">
        <div role="tabpanel" class="tab-pane active" id="withFile">
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
        </div>
        <div role="tabpanel" class="tab-pane" id="withUrl">
            <div class="form-group">
                <label class="normal-text" for="idcolumn">ID Code Column Name ( required )</label>
                <input value="@if($project->idcolumn) {!! $project->idcolumn !!} @endif"
                       class="form-control required" type="text" name="idcolumn"
                       id="idcolumn">
            </div>

            <div class="form-group">
                <label class="urlupload" for="urlupload">File URL</label>
                <input class="form-control required" type="text" name="fileurl">
            </div>
        </div>
    </div>

</div>