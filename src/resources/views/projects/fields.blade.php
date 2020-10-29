@php
    /**
     *  db value to name array
     */
     $type = [
        'fixed' => 'Survey',
        'dynamic' => 'Incident',
     ]

@endphp
<!-- Name Field -->
<div class="form-group col-sm-12 has-warning">
    <div class="row">
        <div class="col-sm-12">
            {!! Form::label('project', 'Name:', ['class' => 'toggle']) !!}
            @if(isset($project))
                <h3 class="toggle" style="display:initial">{!! $project->project !!}</h3>
            @endif
            {!! Form::text('project', null, ['class' => 'form-control toggle', 'placeholder' => 'Choose carefully.']) !!}
        </div>
    </div>
    <div class="row">

        <div class="col-sm-4">
            {!! Form::label('unique_code', 'SMS Code:', ['class' => 'toggle']) !!}
            @if(isset($project))
                <h3 class="toggle" style="display:initial">{!! $project->unique_code !!}</h3>
            @endif
            {!! Form::text('unique_code', null, ['class' => 'form-control toggle', 'placeholder' => 'Unique Code']) !!}
        </div>
        <div class="col-sm-4">
            <label class="toggle" for="report_by">Report By:</label>
            {!! Form::select("report_by", ['location' => 'Location', 'observer' => 'Observer' ], ($project->report_by)??null, ['class' => 'form-control toggle', 'id' => 'report_by']) !!}

        </div>
        <div class="col-sm-4">
            <label class="toggle" for="store_by">Store By:</label>
            {!! Form::select("store_by", ['location' => 'Location', 'observer' => 'Observer' ], ($project->store_by)??null, ['class' => 'form-control toggle', 'id' => 'store_by']) !!}

        </div>
    </div>
</div>

@if(!isset($project) || (isset($project) && $project->status == 'new'))
    <div class="col-sm-12">
        <div class="row">
            <!-- Type Field -->
            <div class="col-sm-2">
                <div class="form-group has-error">
                    {!! Form::label('type', 'Database type: ', ['class' => 'toggle']) !!}
                    {!! Form::select('type', $type,(isset($project))?$project->type:null, ['class' => 'form-control toggle']) !!}
                </div>
            </div>
            <div class="col-sm-3">
                <!-- Type Field -->
                <div class="form-group has-error">
                    {!! Form::label('copies', 'Copies: ', ['class' => 'toggle']) !!}
                    {!! Form::select('copies', ['1' => 1,'2' => 2,'3' => 3,'4' => 4,'5' => 5,'6' => 6,'7' => 7,'8' => 8,'9' => 9,'10' => 10,
                    '11' => 11,'12' => 12,'13' => 13,'14' => 14,'15' => 15,'16' => 16,'17' => 17,'18' => 18,'19' => 19,'20' => 20],(isset($project))?$project->copies:null, ['class' => 'form-control toggle']) !!}
                    <small>Copies of form for a sample/location</small>
                </div>
            </div>
            <div class="col-sm-3">
                <!-- Type Field -->
                <div class="form-group has-error">
                    {!! Form::label('frequencies', 'Survey frequency: ', ['class' => 'toggle']) !!}
                    {!! Form::select('frequencies', ['1' => 1,'2' => 2,'3' => 3,'4' => 4,'5' => 5,'6' => 6,'7' => 7,'8' => 8,'9' => 9,'10' => 10,
                    '11' => 11,'12' => 12,'13' => 13,'14' => 14,'15' => 15,'16' => 16,'17' => 17,'18' => 18,'19' => 19,'20' => 20],(isset($project))?$project->frequencies:null, ['class' => 'form-control toggle']) !!}
                    <small>Survey frequencies before project end</small>
                </div>
            </div>
        </div>
    </div>
@endif

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
                        <td style="" class="toggle">
                            <div class="">
                                {!! Form::select("sections[][layout]", ['' => 'Default', 'form16' => 'Form 16' ], ($section->layout)?$section->layout:null, ['class' => 'form-control layout toggle ', 'id' => 'layout'.$section_key]) !!}
                                <label class="normal-text" for="layout{!! $section_key !!}"></label>
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
                <td style="">
                    <div class="toggle">
                        <input value="1" class="magic-checkbox disablesms" type="checkbox" id="disablesms0">
                        <label class="normal-text" for="disablesms0"></label>
                    </div>
                </td>
                <td style="">
                    <div class="toggle">
                        <label class="normal-text" for="layout0"></label>
                        <select class="form-control layout toggle" id="layout0">
                            <option value="">Default</option>
                            <option value="form16">Form 16</option>
                        </select>
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
                htmlStr += '<td style="">';
                htmlStr += '<div><label class="normal-text"></label>\n' +
                    '                        <select class="form-control layout">\n' +
                    '                            <option value="">Default</option>\n' +
                    '                            <option value="form16">Form 16</option>\n' +
                    '                        </select></div>';
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
                        $(this).find('.layout').attr('name', 'sections[' + index + '][layout]');
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
