<div class="row">
<div class='btn-group'>
    @if($type == 'sample2db')
    <a href="{{ route('projects.surveys.index', $id) }}" class='btn btn-default btn-sm'>
        <i class="glyphicon glyphicon-eye-open"></i> {!! trans('messages.list_incidents') !!}
    </a>
    @else
    <a href="{{ route('projects.surveys.index', $id) }}" class='btn btn-default btn-sm'>
        <i class="glyphicon glyphicon-eye-open"></i> {!! trans('messages.list_samples') !!}
    </a>
    @endif
    @if(Auth::user()->role->level > 5)
    <a href="{{ route('projects.edit', $id) }}" class='btn btn-default btn-sm'>
        <i class="glyphicon glyphicon-edit"></i> {!! trans('messages.edit') !!}
    </a>
    @endif
</div>
@if(Auth::user()->role->level > 5)
<div class="btn-group">
    <a href="{{ route('projects.response.filter', [$id, 'state']) }}" class='btn btn-default btn-sm'>
        <i class="glyphicon glyphicon-equalizer"></i> {!! trans('messages.response') !!}
    </a>
    <a href="{{ route('projects.response.double', [$id, 1]) }}" class='btn btn-default btn-sm'>
        <i class="glyphicon glyphicon-transfer"></i> {!! trans('messages.double_entry') !!}
    </a>
</div>
<div class="btn-group">
{!! Form::open(['route' => ['projects.destroy', $id], 'method' => 'delete']) !!}

    {!! Form::button('<i class="glyphicon glyphicon-trash"></i>', [
        'type' => 'submit',
        'class' => 'btn btn-danger btn-sm',
        'onclick' => "return confirm('".trans('messages.are_you_sure')."')"
    ]) !!}

{!! Form::close() !!}
</div>

@endif
@if(Auth::user()->role->level >= 8)
<div class="btn-group">
{!! Form::open(['route' => ['translate', $id], 'method' => 'post', 'class' => 'translation']) !!}
<div class="input-group">
      <input type="text" name="columns[project]" class="form-control" placeholder="{!! trans('messages.add_translation') !!}" @if(!empty($project_trans) && array_key_exists(config('app.locale'), $project_trans ))
        value="{!! $project_trans[config('app.locale')] !!}"
      @endif>
      <input type="hidden" name="model" value="project">
      <span class="input-group-btn">
        <button class="btn btn-default" type="submit">{!! trans('messages.save') !!}</button>
      </span>

</div><!-- /input-group -->
{!! Form::close() !!}
</div>
@endif
</div>
<div class="row">
@if(Auth::user()->role->level >= 8)
    <div class="btn-group">
        <a href="{{ route('projects.analysis', $id) }}" class='btn btn-default btn-sm'>
            <i class="fa fa-pie-chart"></i> {!! trans('messages.analysis') !!}
        </a>
    </div>
@endif
</div>
