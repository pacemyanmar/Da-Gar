<div class="modal fade" id="uploadSamples" tabindex="-1" role="dialog" aria-labelledby="uploadLabel">
    {!! Form::open(['route' => ['projects.upload.samples', $project->id], 'method' => 'post', 'class' => 'upload', 'id' => 'uploadForm', 'files' => true]) !!}
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title" id="uploadLabel">Upload</h4>
            </div>

            <div class="modal-body">
                <div class="form-group">
                    <label class="normal-text" for="idcolumn">ID Code Column Name ( required )</label>
                    <input value="@if($project->idcolumn) {!! $project->idcolumn !!} @endif" class="form-control required" type="text" name="idcolumn"
                       id="idcolumn" required>
                </div>
                <div class="input-group">
                    <input value="1" class="magic-checkbox optional" type="checkbox" name="update_structure"
                           id="structure">
                    <label class="normal-text form-label" for="structure">Update Column Structure <small>( Check if you need to add new column.)</small></label>

                </div>
                <div class="form-group"><span class="btn btn-primary btn-file" id="file">
                        <label for="upload" id="file-label">Browse File</label>
                        {!! Form::file('samplefile', ['aria-describedby' => 'file', 'id'=>'upload']); !!}
                    </span></div>

            </div>
            <div class="modal-footer">
                <span id="ajaxMesg" class="hidden"></span>
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                {!! Form::submit('Upload', ['class' => 'btn btn-primary', 'id' => 'uploadBtn']) !!}
            </div>

        </div>
    </div>
    {!! Form::close() !!}
</div>

<!-- https://www.abeautifulsite.net/whipping-file-inputs-into-shape-with-bootstrap-3 -->
@push('before-head-end')
    <style type="text/css">
        label#file-label {
            margin-bottom: 0px !important;
        }

        .btn-file {
            position: relative;
            overflow: hidden;
        }

        .btn-file input[type=file] {
            position: absolute;
            top: 0;
            right: 0;
            min-width: 100%;
            min-height: 100%;
            font-size: 100px;
            text-align: right;
            filter: alpha(opacity=0);
            opacity: 0;
            outline: none;
            background: white;
            cursor: inherit;
            display: block;
        }
    </style>
@endpush
@push('before-body-end')
    <script type="text/javascript">
        $(document).ready(function () {
            $('#uploadSamples').on('shown.bs.modal', function (event) {
                $('#upload').on('change', function (e) {
                    fileName = e.target.value.split('\\').pop();
                    $('#file-label').html(fileName);
                });
            });
        });
    </script>
@endpush