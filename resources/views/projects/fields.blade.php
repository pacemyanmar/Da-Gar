@php
    /**
     *  db value to name array
     */
     $dblink = [
        'enumerator' => 'Enumerator',
        'spotchecker' => 'Spot Checker',
        'voter' => 'Voter List',
     ];

     $dbgroup = ['1' => 'Group 1','2' => 'Group 2','3' => 'Group 3','4' => 'Group 4','5' => 'Group 5'];
     $type = [
        'db2sample' => 'SBO Survey',
        'tabulation' => 'Tabulation Survey',
        'sample2db' => 'Incident',
     ]

@endphp
<!-- Name Field -->
<div class="form-group col-sm-12 has-warning">
    <div class="row">
        <div class="col-sm-10">
            {!! Form::label('project', 'Name:', ['class' => 'toggle']) !!}
            @if(isset($project))
                <h3 class="toggle" style="display:initial">{!! $project->project !!}</h3>
            @endif
            {!! Form::text('project', null, ['class' => 'form-control toggle', 'placeholder' => 'Choose carefully.']) !!}
        </div>
        <div class="col-sm-2">
            {!! Form::label('unique_code', 'SMS Code:', ['class' => 'toggle']) !!}
            @if(isset($project))
                <h3 class="toggle" style="display:initial">{!! $project->unique_code !!}</h3>
            @endif
            {!! Form::text('unique_code', null, ['class' => 'form-control toggle', 'placeholder' => 'Unique Code']) !!}
        </div>
    </div>
</div>

@if(!isset($project) || (isset($project) && $project->status == 'new'))
    <div class="col-sm-12">
        <div class="row">
            <!-- DB Link Field -->
            <div class="col-sm-3">
                <div class="form-group has-error">
                    {!! Form::label('dblink', 'Sample list to link: ', ['class' => 'toggle']) !!}
                    {!! Form::select('dblink', $dblink,(isset($project))?$project->dblink:null, ['class' => 'form-control toggle']) !!}
                    <span class="text-red toggle">* Red fields cannot change after form built.</span>
                </div>
            </div>

            <!-- DB Link Group -->
            <div class="col-sm-3">
                <div class="form-group has-error">
                    {!! Form::label('dbgroup', 'Sample list group: ', ['class' => 'toggle']) !!}
                    {!! Form::select('dbgroup', $dbgroup,(isset($project))?$project->dbgroup:null, ['class' => 'form-control toggle']) !!}
                    <span class="text-red toggle">* Red fields cannot change after form built.</span>
                </div>
            </div>

            <!-- Type Field -->
            <div class="col-sm-3"
            '>
            <div class="form-group has-error">
                {!! Form::label('type', 'Database type: ', ['class' => 'toggle']) !!}
                {!! Form::select('type', $type,(isset($project))?$project->type:null, ['class' => 'form-control toggle']) !!}
            </div>
        </div>
        <div class="col-sm-3">
            <!-- Type Field -->
            <div class="form-group has-error">
                {!! Form::label('copies', 'Copies of form for a sample: ', ['class' => 'toggle']) !!}
                {!! Form::select('copies', ['1' => 1,'2' => 2,'3' => 3,'4' => 4,'5' => 5,'6' => 6,'7' => 7,'8' => 8,'9' => 9,'10' => 10,
                '11' => 11,'12' => 12,'13' => 13,'14' => 14,'15' => 15,'16' => 16,'17' => 17,'18' => 18,'19' => 19,'20' => 20],(isset($project))?$project->copies:null, ['class' => 'form-control toggle']) !!}
            </div>
        </div>
    </div>
    </div>
@endif
<div class="form-group col-sm-12">
    {!! Form::label('parties', 'Parties:', ['class' => 'toggle']) !!}
    @if(isset($project))
        <h3 class="toggle" style="display:initial">{!! $project->parties !!}</h3>
    @endif
    {!! Form::text('parties', null, ['class' => 'form-control toggle', 'placeholder' => 'Commas seperates list of parties']) !!}
</div>
<!-- Type Field -->
<div class="form-group col-sm-12">
    {!! Form::label('index_columns', 'Columns to show in list (Choose appropriate columns): ',['class' => 'toggle']) !!}
    <table class="table toggle">
        <tr>
            <td>
                {!! Form::checkbox("index_columns[location_code]", trans('sample.location_code'), null, ['class' => 'magic-checkbox ', 'id' => 'idcode']) !!}
                <label class="normal-text" for="idcode">{!! trans('sample.location_code') !!}
                </label>
            </td>
            <td>
                {!! Form::checkbox("index_columns[form_id]", 'Form No.', null, ['class' => 'magic-checkbox ', 'id' => 'form_id']) !!}
                <label class="normal-text" for="form_id">Form ID
                </label>
            </td>
            <td>
                {!! Form::checkbox("index_columns[user_id]", 'user', null, ['class' => 'magic-checkbox ', 'id' => 'user']) !!}
                <label class="normal-text" for="user">Data Clerk
                </label>
            </td>
            <td>
                {!! Form::checkbox("index_columns[observer_name]", 'Observer', null, ['class' => 'magic-checkbox ', 'id' => 'observer']) !!}
                <label class="normal-text" for="observer">Observer
                </label>
            </td>
            <td>
                {!! Form::checkbox("index_columns[national_id]", trans('sample.national_id'), null, ['class' => 'magic-checkbox ', 'id' => 'nrc_id']) !!}
                <label class="normal-text" for="nrc_id">{!! trans('sample.national_id') !!}
                </label>
            </td>
            <td>
                {!! Form::checkbox("index_columns[mobile]", 'Phone', null, ['class' => 'magic-checkbox ', 'id' => 'phone']) !!}
                <label class="normal-text" for="phone">Phone
                </label>
            </td>
        </tr>
        <tr>
            <td>
                {!! Form::checkbox("index_columns[level1]", trans('sample.level1'), null, ['class' => 'magic-checkbox ', 'id' => 'level1']) !!}
                <label class="normal-text" for="level1">{!! trans('sample.level1') !!}
                </label>
            </td>
            <td>
                {!! Form::checkbox("index_columns[level2]", trans('sample.level2'), null, ['class' => 'magic-checkbox ', 'id' => 'level2']) !!}
                <label class="normal-text" for="level2">{!! trans('sample.level2') !!}
                </label>
            </td>
            <td>
                {!! Form::checkbox("index_columns[level3]", trans('sample.level3'), null, ['class' => 'magic-checkbox ', 'id' => 'level3']) !!}
                <label class="normal-text" for="level3">{!! trans('sample.level3') !!}
                </label>
            </td>
            <td>
                {!! Form::checkbox("index_columns[level4]", trans('sample.level4'), null, ['class' => 'magic-checkbox ', 'id' => 'level4']) !!}
                <label class="normal-text" for="level4">{!! trans('sample.level4') !!}
                </label>
            </td>
            <td>
                {!! Form::checkbox("index_columns[level5]", trans('sample.level5'), null, ['class' => 'magic-checkbox ', 'id' => 'level5']) !!}
                <label class="normal-text" for="level5">{!! trans('sample.level5') !!}
                </label>
            </td>
        </tr>
        <tr></tr>
    </table>
</div>

<!-- sections Field -->
<div class="form-group col-sm-12 col-lg-12">

    <table class="table table-striped table-bordered" id="table">
        <thead class="no-border">
        <tr>
            <th>Sample Label</th>
            <th>Sample ID</th>
            <th class=" toggle"><i class=" fa fa-plus btn btn-sm btn-success btn-flat btn-green toggle" id="btnAddSample"></i></th>
        </tr>
        </thead>
        <tbody id="samples" class="no-border-x no-border-y ui-sortable">
        @if(isset($project))
            @if(isset($project->samples) && is_array($project->samples))
                @foreach($project->samples as $sample_key => $sample)
                    <tr class="sample" style="display: table-row;">
                        <td style="vertical-align: middle">
                            <p class="toggle" style="display:initial">{!! $sample_key !!}</p>
                            <input value="{!! $sample_key !!}" class="form-control samplename  toggle" type="text">
                        </td>
                        <td style="vertical-align: middle">
                            <p class="toggle" style="display:initial">{!! $sample !!}</p>
                            <input value="{!! $sample !!}" class="form-control sampleid  toggle" type="text">
                        </td>
                        <td style="vertical-align: middle" class=" toggle">
                            <i onclick="removeItem(this)" class="remove fa fa-trash-o  toggle"
                               style="cursor: pointer;font-size: 20px;color: red;"></i>
                        </td>
                    </tr>
                @endforeach

            @endif
        @else
            <tr class="sample" style="display: table-row;">
                <td style="vertical-align: middle">
                    <input class="form-control samplename  toggle" type="text">
                </td>
                <td style="vertical-align: middle">
                    <input class="form-control sampleid  toggle" type="text" placeholder="Unique number or word">
                </td>
                <td style="vertical-align: middle" class=" toggle">
                    <i onclick="removeItem(this)" class="remove fa fa-trash-o toggle"
                       style="cursor: pointer;font-size: 20px;color: red;"></i>
                </td>
            </tr>
        @endif
        </tbody>
    </table>
</div>

<!-- sections Field -->
<div class="form-group col-sm-12 col-lg-12">

    <table class="table table-striped table-bordered" id="table">
        <thead class="no-border">
        <tr>
            <th>Section</th>
            <th>Descriptions</th>
            <th class="toggle">Optional</th>
            <th class="toggle">Double Entry</th>
            <th class="toggle">Disable SMS</th>
            <th class="toggle"><i class=" fa fa-plus btn btn-sm btn-success btn-flat btn-green" id="btnAdd"></i></th>
        </tr>
        </thead>
        <tbody id="container" class="no-border-x no-border-y ui-sortable">
        @if(isset($project))
            @if(!$project->sections->isEmpty())
                @foreach($project->sections as $section_key => $section)
                    <tr class="item" style="display: table-row;">
                        {!! Form::hidden("sections[][sectionid]", isset($section->id)?$section->id:null) !!}
                        <td style="vertical-align: middle">
                            <p class="toggle"
                               style="display:initial">{!! (isset($section->sectionname))?$section->sectionname:'' !!}</p>
                            {!! Form::text("sections[][sectionname]", (isset($section->sectionname))?$section->sectionname:null, ['class' => 'form-control sectionname toggle']) !!}
                        </td>
                        <td style="vertical-align: middle">
                            <p class="toggle"
                               style="display:initial">{!! (isset($section->descriptions))?$section->descriptions:'' !!}</p>
                            {!! Form::text("sections[][descriptions]", (isset($section->descriptions))?$section->descriptions:null, ['class' => 'form-control descriptions toggle']) !!}
                        </td>
                        <td style="" class="toggle">
                            <div class="">
                                {!! Form::checkbox("sections[][optional]", 1, ($section->optional)?$section->optional:null, ['class' => 'magic-checkbox optional ', 'id' => 'optional'.$section_key]) !!}
                                <label class="normal-text" for="optional{!! $section_key !!}"></label>
                            </div>
                        </td>
                        <td style="" class="toggle">
                            <div class="">
                                {!! Form::checkbox("sections[][indouble]", 1, ($section->indouble)?$section->indouble:null, ['class' => 'magic-checkbox double ', 'id' => 'double'.$section_key]) !!}
                                <label class="normal-text" for="double{!! $section_key !!}"></label>
                            </div>
                        </td>
                        <td style="" class="toggle">
                            <div class="">
                                {!! Form::checkbox("sections[][disablesms]", 1, ($section->disablesms)?$section->disablesms:null, ['class' => 'magic-checkbox disablesms ', 'id' => 'disablesms'.$section_key]) !!}
                                <label class="normal-text" for="disablesms{!! $section_key !!}"></label>
                            </div>
                        </td>
                        <td style="vertical-align: middle" class="toggle">
                            <i onclick="removeItem(this)" class="remove fa fa-trash-o"
                               style="cursor: pointer;font-size: 20px;color: red;"></i>
                        </td>
                    </tr>
                @endforeach

            @endif
        @else
            <tr class="item toggle" style="display: table-row;">
                <td style="vertical-align: middle">
                    <input class="form-control sectionname toggle" type="text">
                </td>
                <td style="vertical-align: middle">
                    <input class="form-control descriptions toggle" type="text">
                </td>
                <td style="">
                    <div class="toggle">
                        <input value="1" class="magic-checkbox optional" type="checkbox" id="optional0">
                        <label class="normal-text" for="optional0"></label>
                    </div>
                </td>
                <td style="">
                    <div class="toggle">
                        <input value="1" class="magic-checkbox double" type="checkbox" id="double0">
                        <label class="normal-text" for="double0"></label>
                    </div>
                </td>
                <td style="vertical-align: middle">
                    <i onclick="removeItem(this)" class="remove fa fa-trash-o"
                       style="cursor: pointer;font-size: 20px;color: red;"></i>
                </td>
            </tr>
        @endif
        </tbody>
    </table>
</div>

<!-- Submit Field -->
<div class="form-group col-sm-12">
    {!! Form::submit('Save', ['class' => 'btn btn-primary']) !!}
    <a href="{!! route('projects.index') !!}" class="btn btn-default">Cancel</a>
</div>

@push('before-body-end')
    <script type="text/javascript">
        (function ($) {
            $(document).ready(function () {
                var htmlStr = '<tr class="item" style="display: table-row;"><td style="vertical-align: middle">';
                htmlStr += '<input type="text" style="width: 100%" class="form-control sectionname"/>';
                htmlStr += '</td>';
                htmlStr += '<td style="vertical-align: middle">';
                htmlStr += '<input type="text" class="form-control descriptions"/>';
                htmlStr += '</td>';
                htmlStr += '<td style="">';
                htmlStr += '<div><input type="checkbox" value="1" class="magic-checkbox optional"/><label class="optional"></label></div>';
                htmlStr += '</td>';
                htmlStr += '<td style="">';
                htmlStr += '<div><input type="checkbox" value="1" class="magic-checkbox double"/><label class="double"></label></div>';
                htmlStr += '</td>';
                htmlStr += '<td style="">';
                htmlStr += '<div><input type="checkbox" value="1" class="magic-checkbox disablesms"/><label class="disablesms"></label></div>';
                htmlStr += '</td>';
                htmlStr += '<td style="vertical-align: middle">';
                htmlStr += '<i onclick="removeItem(this)" class="remove fa fa-trash-o" style="cursor: pointer;font-size: 20px;color: red"></i>';
                htmlStr += '</td>';
                htmlStr += '</tr>';

                $("#btnAdd").on("click", function () {
                    var item = $(htmlStr).clone();
                    $("#container").append(item);

                    $('.item').each(function (index, value) {
                        $(this).find('input.optional').attr('id', 'optional' + index);
                        $(this).find('label.optional').attr('for', 'optional' + index);
                        $(this).find('input.double').attr('id', 'double' + index);
                        $(this).find('label.double').attr('for', 'double' + index);
                        $(this).find('input.disablesms').attr('id', 'disablesms' + index);
                        $(this).find('label.disablesms').attr('for', 'disablesms' + index);
                    });
                });

                var sampleStr = '<tr class="sample" style="display: table-row;"><td style="vertical-align: middle">';
                sampleStr += '<input type="text" style="width: 100%" class="form-control samplename"/>';
                sampleStr += '</td>';
                sampleStr += '<td style="vertical-align: middle">';
                sampleStr += '<input type="text" class="form-control sampleid" placeholder="Unique number or word"/>';
                sampleStr += '</td>';
                sampleStr += '<td style="vertical-align: middle">';
                sampleStr += '<i onclick="removeItem(this)" class="remove fa fa-trash-o" style="cursor: pointer;font-size: 20px;color: red"></i>';
                sampleStr += '</td>';
                sampleStr += '</tr>';

                $("#btnAddSample").on("click", function () {
                    var sample = $(sampleStr).clone();
                    $("#samples").append(sample);
                });


                $(".editProject").on("click", function () {
                    $(".toggle").toggle();
                });
                $("#project").find(":input").filter(function () {
                    return !this.value;
                }).removeAttr("disabled");
                $("#project").on("submit", function (e) {

                    $('.item').each(function (index, value) {
                        $(this).find('.sectionname').attr('name', 'sections[' + index + '][sectionname]');
                        $(this).find('.descriptions').attr('name', 'sections[' + index + '][descriptions]');
                        $(this).find('.optional').attr('name', 'sections[' + index + '][optional]');
                        $(this).find('.double').attr('name', 'sections[' + index + '][indouble]');
                        $(this).find('.disablesms').attr('name', 'sections[' + index + '][disablesms]');
                    });
                    $('.sample').each(function (index, value) {
                        $(this).find('.samplename').attr('name', 'samples[' + index + '][name]');
                        $(this).find('.sampleid').attr('name', 'samples[' + index + '][id]');
                    });

                    $(this).find(":input").filter(function () {
                        return !this.value;
                    }).attr("disabled", "disabled");
                });

            });
        })(jQuery);

        function removeItem(e) {
            e.parentNode.parentNode.parentNode.removeChild(e.parentNode.parentNode);
        }
    </script>
@endpush
