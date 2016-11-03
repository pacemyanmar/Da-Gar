@php
/**
 *  db value to name array
 */
 $db2name = [
    'p2l' => 'People to List',
    'l2p' => 'List to People',
    'voterlist' => 'Voter List',
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
    {!! Form::label('type', 'Type:') !!}
    @if(isset($project))
    <p class="toggle" style="display:initial">{!! $db2name["$project->type"] !!}</p>
    @endif
    {!! Form::select('type', ['p2l' => 'People to List', 'l2p' => 'List to People'],null, ['class' => 'form-control toggle']) !!}
</div>

<!-- Validation Database Field -->
<div class="form-group col-sm-6">
    {!! Form::label('dblink', 'Validation Database:') !!}
    @if(isset($project))
    <p class="toggle" style="display:initial">{!! $db2name["$project->dblink"] !!}</p>
    @endif
    {!! Form::select('dblink', ['voterlist' => 'Voter List', 'location' => 'Location', 'enumerator' => 'Enumerator'],null, ['class' => 'form-control toggle']) !!}
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

    $(".editProject").on("click", function () {
      $(".toggle").toggle();
    });

    $("#project").on("submit", function(e) {
      $('.item').each(function (index,value) {
        var sectionname = $(this).find('.sectionname').val();
        $(this).find('.sectionname').attr('name','sections['+index+'][sectionname]');
        $(this).find('.descriptions').attr('name','sections['+index+'][descriptions]');
      });
    });

    // For radio button and checkbox styling
    $('input').iCheck({
            checkboxClass: 'icheckbox_square-blue',
            radioClass: 'iradio_square-blue',
            increaseArea: '10%' // optional
        });

  });
function removeItem(e) {
    e.parentNode.parentNode.parentNode.removeChild(e.parentNode.parentNode);
  }
    </script>
@endsection