<!-- Project Field -->
<div class="form-group col-sm-12">
    {!! Form::label('project_id', 'Project:') !!}
    {!! Form::label('project_id',$project->project) !!}
    {!! Form::hidden('project_id', $project->id) !!}
</div>

<!-- Default Location Code Field -->
<div class="form-group col-sm-6">
    {!! Form::label('default_field', 'Default Field:') !!}
    {!! Form::text('default_field', 'Location Code', ['class' => 'form-control','disabled']) !!}
</div>

<!-- Default Location Code Field -->
<div class="form-group col-sm-6">
    {!! Form::label('default_field', 'Default Type:') !!}
    {!! Form::text('default_field', 'code', ['class' => 'form-control','disabled']) !!}
</div>

<div class="form-group col-sm-12">
<table class="table table-responsive">
    <tr>
        <th>Field Name</th>
        <th>Field Type</th>
        <th>Field Action
            <i class=" fa fa-plus btn btn-sm btn-success btn-flat btn-green"  onclick="addItem()"></i>
        </th>
    </tr>
    <tbody id="container" class="no-border-x no-border-y ui-sortable">

    @if($locationMetas->isEmpty())
    <tr>
        <td>
            {!! Form::text("field_name", null, ["class" => "form-control field_name field"]) !!}
        </td>
        <td>
            {!! Form::select("field_type", ["code" => "Code","text" => "Text","textarea" => "Paragraph","integer" => "Number"],null, ["class" => "form-control field_type field"]) !!}
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
            <tr>
                <td>
                    {!! Form::text("field_name", ucwords($location['field_name']), ["class" => "form-control field_name field"]) !!}
                </td>
                <td>
                    {!! Form::select("field_type", ["code" => "Code","text" => "Text","textarea" => "Paragraph","integer" => "Number"], $location['field_type'], ["class" => "form-control field_type field"]) !!}
                </td>
                <td>
                    <i onclick="addItem()" class="add-new fa fa-plus btn btn-sm btn-success"
                       style="cursor: pointer;"></i>
                    <i onclick="removeItem(this)" class="remove fa fa-trash-o"
                       style="cursor: pointer;font-size: 20px;color: red;"></i>
                </td>
            </tr>
        @endforeach
    @endif
    </tbody>
</table>
</div>
<!-- Submit Field -->
<div class="form-group col-sm-12">
    {!! Form::submit('Create Table', ['class' => 'btn btn-primary']) !!}
    <a href="{!! route('locationMetas.index') !!}" class="btn btn-default">Cancel</a>
</div>

@push('before-body-end')
    <script type="text/javascript">
        var htmlStr = '<tr>\n' +
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