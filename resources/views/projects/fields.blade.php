@php
/**
 *  db value to name array
 */
 $db2name = [
    '' => 'None',
    'p2l' => 'People to List',
    'l2p' => 'List to People',
    'voters' => 'Voter List',
    'location' => 'Location',
    'enumerator' => 'Enumerator',
 ];
@endphp
<!-- Name Field -->
<div class="form-group col-sm-6">
    {!! Form::label('project', 'Name:') !!}
    @if(isset($project))
    <p class="toggle" style="display:initial">{!! $project->project !!}</p>
    @endif
    {!! Form::text('project', null, ['class' => 'form-control toggle']) !!}
</div>

<!-- Type Field -->
<div class="form-group col-sm-6">
    {!! Form::label('type', 'Related database type: ') !!}
    @if(isset($project))
    <p class="toggle" style="display:initial">{!! $db2name["$project->type"] !!}</p>
    @endif
    {!! Form::select('type', $db2name,null, ['class' => 'form-control toggle']) !!}
</div>

<!-- sections Field -->
<div class="form-group col-sm-12 col-lg-12 toggle">    

    <table class="table table-striped table-bordered" id="table">
        <thead class="no-border">
            <tr>
                <th>Sample Name</th>
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
                    <p class="toggle" style="display:initial">{!! $sample['name'] !!}</p>
                    <input value="{!! $sample['name'] !!}" required="" class="form-control samplename toggle" type="text">
                </td>
                <td style="vertical-align: middle">
                    <p class="toggle" style="display:initial">{!! $sample['id'] !!}</p>
                    <input value="{!! $sample['id'] !!}" class="form-control sampleid toggle" type="text">
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
                    <input required="" class="form-control samplename toggle" type="text">
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
                <th>Section Name</th>
                <th>Descriptions</th>
                <th><i class=" fa fa-plus btn btn-sm btn-success btn-flat btn-green toggle" id="btnAdd"></i></th>
            </tr>
        </thead>
        <tbody id="container" class="no-border-x no-border-y ui-sortable">
        @if(isset($project))
            @if(is_array($project->sections))
            @foreach($project->sections as $section_key => $section)
            <tr class="item" style="display: table-row;">
                <td style="vertical-align: middle">
                    <p class="toggle" style="display:initial">{!! $section['sectionname'] !!}</p>
                    <input value="{!! $section['sectionname'] !!}" required="" class="form-control sectionname toggle" type="text">
                </td>
                <td style="vertical-align: middle">
                    <p class="toggle" style="display:initial">{!! $section['descriptions'] !!}</p>
                    <input value="{!! $section['descriptions'] !!}" class="form-control descriptions toggle" type="text">
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
                    <input required="" class="form-control sectionname toggle" type="text">
                </td>
                <td style="vertical-align: middle">
                    <input class="form-control descriptions toggle" type="text">
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
    htmlStr +=  '<input type="text" style="width: 100%" required class="form-control sectionname"/>';
    htmlStr +=  '</td>';
    htmlStr +=  '<td style="vertical-align: middle">';
    htmlStr +=  '<input type="text" class="form-control descriptions"/>';
    htmlStr +=  '</td>';
    htmlStr +=  '<td style="vertical-align: middle">';
    htmlStr +=  '<i onclick="removeItem(this)" class="remove fa fa-trash-o" style="cursor: pointer;font-size: 20px;color: red"></i>';
    htmlStr +=  '</td>';
    htmlStr +=  '</tr>';

    $("#btnAdd").on("click", function () {
      var item = $(htmlStr).clone();
      $("#container").append(item);
    });

    var sampleStr =   '<tr class="sample" style="display: table-row;"><td style="vertical-align: middle">';
    sampleStr +=  '<input type="text" style="width: 100%" required class="form-control sectionname"/>';
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

    $("#project").on("submit", function(e) {
      $('.item').each(function (index,value) {
        var sectionname = $(this).find('.sectionname').val();
        $(this).find('.sectionname').attr('name','sections['+index+'][sectionname]');
        $(this).find('.descriptions').attr('name','sections['+index+'][descriptions]');
      });
      $('.sample').each(function (index,value) {
        $(this).find('.samplename').attr('name','samples['+index+'][name]');
        $(this).find('.sampleid').attr('name','samples['+index+'][id]');
      });
    });

  });
function removeItem(e) {
    e.parentNode.parentNode.parentNode.removeChild(e.parentNode.parentNode);
  }
    </script>
@endsection