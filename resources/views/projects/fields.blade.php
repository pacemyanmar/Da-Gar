@php
/**
 *  db value to name array
 */
 $dblink = [
    '' => 'None',
    'voter' => 'Voter List',
    'location' => 'Location',
    'enumerator' => 'Enumerator',
 ];
 $type = [
    '' => 'None',
    'sample2db' => 'Sample to Database (inner join)',
    'db2sample' => 'Database to sample (left join)',
 ]

@endphp
<!-- Name Field -->
<div class="form-group col-sm-6">
    {!! Form::label('project', 'Name:', ['class' => 'toggle']) !!}
    @if(isset($project))
    <h3 class="toggle" style="display:initial">{!! $project->project !!}</h3>
    @endif
    {!! Form::text('project', null, ['class' => 'form-control toggle']) !!}
</div>


<!-- DB Link Field -->
<div class="form-group col-sm-6">
    {!! Form::label('dblink', 'Related database name: ') !!}
    @if(isset($project))
    <p class="toggle" style="display:initial">{!! $dblink["$project->dblink"] !!}</p>
    @endif
    {!! Form::select('dblink', $dblink,null, ['class' => 'form-control toggle']) !!}
</div>

<!-- Type Field -->
<div class="form-group col-sm-3">
    {!! Form::label('type', 'Related database type: ') !!}
    @if(isset($project))
    <br />
    <p class="toggle" style="display:initial">{!! $type["$project->type"] !!}</p>
    @endif
    {!! Form::select('type', $type,null, ['class' => 'form-control toggle']) !!}
</div>
<!-- Type Field -->
<div class="form-group col-sm-3">
    {!! Form::label('copies', 'Copies per observer or location: ') !!}
    @if(isset($project))
    <br />
    <p class="toggle" style="display:initial">{!! $project->copies !!}</p>
    @endif
    {!! Form::select('copies', ['1' => 1,'2' => 2,'3' => 3,'4' => 4,'5' => 5,'6' => 6,'7' => 7,'8' => 8,'9' => 9,'10' => 10,
    '11' => 11,'12' => 12,'13' => 13,'14' => 14,'15' => 15,'16' => 16,'17' => 17,'18' => 18,'19' => 19,'20' => 20],null, ['class' => 'form-control toggle']) !!}
</div>

<!-- Type Field -->
<div class="form-group col-sm-6">
    {!! Form::label('index_columns', 'Columns to show in list: ',['class' => 'toggle']) !!}
    <table class="table toggle">
        <tr>
            <td>
                {!! Form::checkbox("index_columns[state]", 'State', null, ['class' => 'magic-checkbox ', 'id' => 'state']) !!}
                <label class="normal-text" for="state">State
                </label>
            </td>
            <td>
                {!! Form::checkbox("index_columns[district]", 'District', null, ['class' => 'magic-checkbox ', 'id' => 'district']) !!}
                <label class="normal-text" for="district">District
                </label>
            </td>
            <td>
                {!! Form::checkbox("index_columns[township]", 'Township', null, ['class' => 'magic-checkbox ', 'id' => 'township']) !!}
                <label class="normal-text" for="township">Township
                </label>
            </td>
            <td>
                {!! Form::checkbox("index_columns[village_tract]", 'Village Tract', null, ['class' => 'magic-checkbox ', 'id' => 'village_tract']) !!}
                <label class="normal-text" for="village_tract">Village Tract
                </label>
            </td>
            <td>
                {!! Form::checkbox("index_columns[village]", 'Village', null, ['class' => 'magic-checkbox ', 'id' => 'village']) !!}
                <label class="normal-text" for="village">Village
                </label>
            </td>
        </tr>
        <tr>
            <td>
                {!! Form::checkbox("index_columns[observer]", 'Observer', null, ['class' => 'magic-checkbox ', 'id' => 'observer']) !!}
                <label class="normal-text" for="observer">Observer
                </label>
            </td>
            <td>
                {!! Form::checkbox("index_columns[nrc_id]", 'NRC ID', null, ['class' => 'magic-checkbox ', 'id' => 'nrc_id']) !!}
                <label class="normal-text" for="nrc_id">NRC ID
                </label>
            </td>
            <td>
                {!! Form::checkbox("index_columns[phone]", 'Phone', null, ['class' => 'magic-checkbox ', 'id' => 'phone']) !!}
                <label class="normal-text" for="phone">Phone
                </label>
            </td>
            <td>
                {!! Form::checkbox("index_columns[address]", 'Address', null, ['class' => 'magic-checkbox ', 'id' => 'address']) !!}
                <label class="normal-text" for="address">Address
                </label>
            </td>
        </tr>
        <tr></tr>
    </table>
</div>

<!-- sections Field -->
<div class="form-group col-sm-12 col-lg-12 toggle">

    <table class="table table-striped table-bordered" id="table">
        <thead class="no-border">
            <tr>
                <th>Sample Label</th>
                <th>Sample ID</th>
                <th><i class=" fa fa-plus btn btn-sm btn-success btn-flat btn-green toggle" id="btnAddSample"></i></th>
            </tr>
        </thead>
        <tbody id="samples" class="no-border-x no-border-y ui-sortable">
        @if(isset($project))
            @if(isset($project->samples) && is_array($project->samples))
            @foreach($project->samples as $sample_key => $sample)
            <tr class="sample" style="display: table-row;">
                <td style="vertical-align: middle">
                    <p class="toggle" style="display:initial">{!! $sample_key !!}</p>
                    <input value="{!! $sample_key !!}" class="form-control samplename toggle" type="text">
                </td>
                <td style="vertical-align: middle">
                    <p class="toggle" style="display:initial">{!! $sample !!}</p>
                    <input value="{!! $sample !!}" class="form-control sampleid toggle" type="text">
                </td>
                <td style="vertical-align: middle">
                    <i onclick="removeItem(this)" class="remove fa fa-trash-o toggle" style="cursor: pointer;font-size: 20px;color: red;"></i>
                </td>
            </tr>
            @endforeach

            @endif
            @else
            <tr class="sample" style="display: table-row;">
                <td style="vertical-align: middle">
                    <input class="form-control samplename toggle" type="text">
                </td>
                <td style="vertical-align: middle">
                    <input class="form-control sampleid toggle" type="text" placeholder="Unique number or word">
                </td>
                <td style="vertical-align: middle">
                    <i onclick="removeItem(this)" class="remove fa fa-trash-o toggle" style="cursor: pointer;font-size: 20px;color: red;"></i>
                </td>
            </tr>
            @endif
        </tbody>
    </table>
</div>

<!-- sections Field -->
<div class="form-group col-sm-12 col-lg-12 toggle">

    <table class="table table-striped table-bordered" id="table">
        <thead class="no-border">
            <tr>
                <th>Section</th>
                <th>Descriptions</th>
                <th>Optional</th>
                <th>Double Entry</th>
                <th><i class=" fa fa-plus btn btn-sm btn-success btn-flat btn-green toggle" id="btnAdd"></i></th>
            </tr>
        </thead>
        <tbody id="container" class="no-border-x no-border-y ui-sortable">
        @if(isset($project))
            @if(is_array($project->sections))
            @foreach($project->sections as $section_key => $section)
            <tr class="item" style="display: table-row;">
                <td style="vertical-align: middle">
                    <p class="toggle" style="display:initial">{!! (isset($section['sectionname']))?$section['sectionname']:'' !!}</p>
                    {!! Form::text("sections[$section_key][sectionname]", null, ['class' => 'form-control sectionname toggle']) !!}
                </td>
                <td style="vertical-align: middle">
                    <p class="toggle" style="display:initial">{!! (isset($section['descriptions']))?$section['descriptions']:'' !!}</p>
                    {!! Form::text("sections[$section_key][descriptions]", null, ['class' => 'form-control descriptions toggle']) !!}
                </td>
                <td style="">
                    <div class="toggle">
                    {!! Form::checkbox("sections[$section_key][optional]", 'Optional', null, ['class' => 'magic-checkbox optional ', 'id' => 'optional'.$section_key]) !!}
                    <label class="normal-text" for="optional{!! $section_key !!}"></label>
                    </div>
                </td>
                <td style="">
                    <div class="toggle">
                    {!! Form::checkbox("sections[$section_key][double]", 'Double', null, ['class' => 'magic-checkbox double ', 'id' => 'double'.$section_key]) !!}
                    <label class="normal-text" for="double{!! $section_key !!}"></label>
                    </div>
                </td>
                <td style="vertical-align: middle">
                    <i onclick="removeItem(this)" class="remove fa fa-trash-o toggle" style="cursor: pointer;font-size: 20px;color: red;"></i>
                </td>
            </tr>
            @endforeach

            @endif
            @else
            <tr class="item" style="display: table-row;">
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
                    <i onclick="removeItem(this)" class="remove fa fa-trash-o toggle" style="cursor: pointer;font-size: 20px;color: red;"></i>
                </td>
            </tr>
            @endif
        </tbody>
    </table>
</div>

<!-- Submit Field -->
<div class="form-group col-sm-12 toggle">
    {!! Form::submit('Save', ['class' => 'btn btn-primary']) !!}
    <a href="{!! route('projects.index') !!}" class="btn btn-default">Cancel</a>
</div>

@section('scripts')
    @parent
    <script type="text/javascript">

  $(document).ready(function(){
    var htmlStr =   '<tr class="item" style="display: table-row;"><td style="vertical-align: middle">';
    htmlStr +=  '<input type="text" style="width: 100%" class="form-control sectionname"/>';
    htmlStr +=  '</td>';
    htmlStr +=  '<td style="vertical-align: middle">';
    htmlStr +=  '<input type="text" class="form-control descriptions"/>';
    htmlStr +=  '</td>';
    htmlStr +=  '<td style="">';
    htmlStr +=  '<div><input type="checkbox" value="Optional" class="magic-checkbox optional"/><label class="optional"></label></div>';
    htmlStr +=  '</td>';
    htmlStr +=  '<td style="">';
    htmlStr +=  '<div><input type="checkbox" value="Double" class="magic-checkbox double"/><label class="double"></label></div>';
    htmlStr +=  '</td>';
    htmlStr +=  '<td style="vertical-align: middle">';
    htmlStr +=  '<i onclick="removeItem(this)" class="remove fa fa-trash-o" style="cursor: pointer;font-size: 20px;color: red"></i>';
    htmlStr +=  '</td>';
    htmlStr +=  '</tr>';

    $("#btnAdd").on("click", function () {
      var item = $(htmlStr).clone();
      $("#container").append(item);

      $('.item').each(function (index,value) {
        $(this).find('input.optional').attr('id','optional'+index);
        $(this).find('label.optional').attr('for','optional'+index);
        $(this).find('input.double').attr('id','double'+index);
        $(this).find('label.double').attr('for','double'+index);
      });
    });

    var sampleStr =   '<tr class="sample" style="display: table-row;"><td style="vertical-align: middle">';
    sampleStr +=  '<input type="text" style="width: 100%" class="form-control samplename"/>';
    sampleStr +=  '</td>';
    sampleStr +=  '<td style="vertical-align: middle">';
    sampleStr +=  '<input type="text" class="form-control sampleid" placeholder="Unique number or word"/>';
    sampleStr +=  '</td>';
    sampleStr +=  '<td style="vertical-align: middle">';
    sampleStr +=  '<i onclick="removeItem(this)" class="remove fa fa-trash-o" style="cursor: pointer;font-size: 20px;color: red"></i>';
    sampleStr +=  '</td>';
    sampleStr +=  '</tr>';

    $("#btnAddSample").on("click", function () {
      var sample = $(sampleStr).clone();
      $("#samples").append(sample);
    });


    $(".editProject").on("click", function () {
      $(".toggle").toggle();
    });
    $("#project").find(":input").filter(function(){ return !this.value; }).removeAttr("disabled");
    $("#project").on("submit", function(e) {

      $('.item').each(function (index,value) {
        $(this).find('.sectionname').attr('name','sections['+index+'][sectionname]');
        $(this).find('.descriptions').attr('name','sections['+index+'][descriptions]');
        $(this).find('.optional').attr('name','sections['+index+'][optional]');
        $(this).find('.double').attr('name','sections['+index+'][double]');
      });
      $('.sample').each(function (index,value) {
        $(this).find('.samplename').attr('name','samples['+index+'][name]');
        $(this).find('.sampleid').attr('name','samples['+index+'][id]');
      });

        $(this).find(":input").filter(function(){ return !this.value; }).attr("disabled", "disabled");
    });

  });
function removeItem(e) {
    e.parentNode.parentNode.parentNode.removeChild(e.parentNode.parentNode);
  }
    </script>
@endsection
