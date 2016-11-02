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
    <p class="toggle">{!! $project->project !!}</p>
    @endif
    {!! Form::text('project', null, ['class' => 'form-control toggle', 'style' => 'display: none']) !!}
</div>

<!-- Type Field -->
<div class="form-group col-sm-6">
    {!! Form::label('type', 'Type:') !!}
    @if(isset($project))
    <p class="toggle">{!! $db2name["$project->type"] !!}</p>
    @endif
    {!! Form::select('type', ['p2l' => 'People to List', 'l2p' => 'List to People'],null, ['class' => 'form-control toggle', 'style' => 'display: none']) !!}
</div>

<!-- Validation Database Field -->
<div class="form-group col-sm-6">
    {!! Form::label('dblink', 'Validation Database:') !!}
    @if(isset($project))
    <p class="toggle">{!! $db2name["$project->dblink"] !!}</p>
    @endif
    {!! Form::select('dblink', ['voterlist' => 'Voter List', 'location' => 'Location', 'enumerator' => 'Enumerator'],null, ['class' => 'form-control toggle', 'style' => 'display: none']) !!}
</div>
<!-- sections Field -->
<div class="form-group col-sm-12 col-lg-12">    

    <table class="table table-striped table-bordered" id="table">
        <thead class="no-border">
            <tr>
                <th>Section Name</th>
                <th>Descriptions</th>
                <th><i class=" fa fa-plus btn btn-sm btn-success btn-flat btn-green toggle" style="display:none;" id="btnAdd"></i></th>
            </tr>
        </thead>
        <tbody id="container" class="no-border-x no-border-y ui-sortable">
        @if(isset($project))
            @if(is_array($project->sections))
            @foreach($project->sections as $section_key => $section)
            <tr class="item" style="display: table-row;">
                <td style="vertical-align: middle">
                    <p class="toggle">{!! $section['sectionname'] !!}</p>
                    <input value="{!! $section['sectionname'] !!}" style="display:none;" required="" class="form-control sectionname toggle" type="text">
                </td>
                <td style="vertical-align: middle">
                    <p class="toggle">{!! $section['descriptions'] !!}</p>
                    <input value="{!! $section['descriptions'] !!}" style="display:none;" class="form-control descriptions toggle" type="text">
                </td>
                <td style="vertical-align: middle">
                    <i onclick="removeItem(this)" class="remove fa fa-trash-o toggle" style="cursor: pointer;font-size: 20px;color: red; display:none;"></i>
                </td>
            </tr>
            @endforeach
            @endif
            @endif
        </tbody>
    </table>
</div>

<!-- Submit Field -->
<div class="form-group col-sm-12 toggle" style="display:none;">
    {!! Form::submit('Save', ['class' => 'btn btn-primary']) !!}
    <a href="{!! route('projects.index') !!}" class="btn btn-default">Cancel</a>
</div>
