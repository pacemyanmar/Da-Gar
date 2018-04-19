<!-- Project Field -->
<div class="form-group col-sm-12">
    {!! Form::label('project_id', 'Project:') !!}
    {!! Form::label('project_id',$project->project) !!}
    {!! Form::hidden('project_id', $project->id) !!}
</div>

<!-- Default Location Code Field -->
<div class="form-group col-sm-12">
    <p class="bg-warning text-danger">Please set one field as primary. It will be used as id code for survey to store
        and filter by.</p>
    <p class="bg-warning text-danger">Remove unused fields</p>
</div>

<div class="form-group col-sm-12">
    <table class="table table-responsive">
        <tr>
            <th>Label</th>
            <th>DB Field Name</th>
            <th>Field Type</th>
            <th>Field Action
                <i class=" fa fa-plus btn btn-sm btn-success btn-flat btn-green" onclick="addItem()"></i>
            </th>
        </tr>
        <tbody id="container" class="no-border-x no-border-y ui-sortable">

        @if($locationMetas->isEmpty())
            <tr>
                <td>
                    {!! Form::text("label", null, ["class" => "form-control field_label field"]) !!}
                </td>
                <td>
                    {!! Form::text("field_name", null, ["class" => "form-control field_name field"]) !!}
                </td>
                <td>
                    {!! Form::text("field_type", 'primary', ["class" => "form-control field_type field", "readonly"]) !!}                </td>
                <td>
                    Required <i class="fa fa-star text-danger"></i>
                </td>
            </tr>
            <tr>
                <td>
                    {!! Form::text("label", null, ["class" => "form-control field_label field"]) !!}
                </td>
                <td>
                    {!! Form::text("field_name", null, ["class" => "form-control field_name field"]) !!}
                </td>
                <td>
                    {!! Form::select("field_type", ["primary" => "Primary Code", "code" => "Code","text" => "Text","textarea" => "Paragraph","integer" => "Number"],'text', ["class" => "form-control field_type field"]) !!}
                </td>
                <td>
                    <i onclick="addItem()" class="add-new fa fa-plus btn btn-sm btn-success"
                       style="cursor: pointer;"></i>
                    <i onclick="removeItem(this)" class="remove fa fa-trash-o"
                       style="cursor: pointer;font-size: 20px;color: red;"></i>
                </td>
            </tr>
        @else
            @foreach($locationMetas as $location)
                @if($location->field_name)
                    <tr>
                        <td>
                            {!! Form::text("label", ($location->label)?$location['label']:title_case($location->field_name), ["class" => "form-control field_label field"]) !!}
                        </td>
                        <td>
                            {!! Form::text("field_name", $location->field_name, ["class" => "form-control field_name field", "readonly"]) !!}
                        </td>
                        <td>

                            @if($location['field_type'] == 'primary')
                                {!! Form::text("field_type", 'primary', ["class" => "form-control field_type field", "readonly"]) !!}
                            @else
                                {!! Form::select("field_type", ["primary" => "Primary Code","code" => "Code","text" => "Text","textarea" => "Paragraph","integer" => "Number"], $location['field_type'], ["class" => "form-control field_type field"]) !!}
                            @endif
                        </td>
                        <td>
                            <i onclick="addItem()" class="add-new fa fa-plus btn btn-sm btn-success"
                               style="cursor: pointer;"></i>
                            <i onclick="removeItem(this)" class="remove fa fa-trash-o"
                               style="cursor: pointer;font-size: 20px;color: red;"></i>
                        </td>
                    </tr>
                @endif
            @endforeach
        @endif
        </tbody>
    </table>
</div>
<!-- Submit Field -->
<div class="form-group col-sm-12">
    {!! Form::submit('Continue', ['class' => 'btn btn-primary']) !!}
    <a href="{!! route('locationMetas.index') !!}" class="btn btn-default">Cancel</a>
</div>

@push('before-body-end')
    <script type="text/javascript">
        var htmlStr = '<tr>\n' +
            '        <td>\n' +
            '            {!! Form::text("label", null, ["class" => "form-control field_label field"]) !!}\n' +
            '        </td>\n' +
            '        <td>\n' +
            '            {!! Form::text("field_name", null, ["class" => "form-control field_name field"]) !!}\n' +
            '        </td>\n' +
            '        <td>\n' +
            '            {!! Form::select("field_type", ["code" => "Code","text" => "Text","textarea" => "Paragraph","integer" => "Number"],null, ["class" => "form-control field_type field"]) !!}\n' +
            '        </td>\n' +
            '        <td>\n' +
            '            <i onclick="addItem()" class="add-new fa fa-plus btn btn-sm btn-success"\n' +
            '               style="cursor: pointer;"></i>\n' +
            '\n' +
            '            <i onclick="removeItem(this)" class="remove fa fa-trash-o"\n' +
            '               style="cursor: pointer;font-size: 20px;color: red;"></i>\n' +
            '        </td>\n' +
            '    </tr>';

        function addItem() {
            var item = $(htmlStr).clone();
            $("#container").append(item);
        }

        function removeItem(e) {
            e.parentNode.parentNode.parentNode.removeChild(e.parentNode.parentNode);
        }

        (function ($) {
            $(document).ready(function () {
                $("form").on("submit", function (e) {
                    $('.field_label').each(function (index, value) {
                        $(this).attr('name', 'fields[' + index + '][label]');
                    });
                    $('.field_name').each(function (index, value) {
                        $(this).attr('name', 'fields[' + index + '][field_name]');
                    });
                    $('.field_type').each(function (index, value) {
                        $(this).attr('name', 'fields[' + index + '][field_type]');
                    });
                });
            });
        })(jQuery);


    </script>
@endpush