
<div class='btn-group'>
    <a href="{{ route('projects.surveys.index', $id) }}" class='btn btn-default btn-sm'>
        <i class="glyphicon glyphicon-eye-open"></i> List samples
    </a>
    @if(Auth::user()->role->level > 5)
    <a href="{{ route('projects.edit', $id) }}" class='btn btn-default btn-sm'>
        <i class="glyphicon glyphicon-edit"></i> Edit
    </a>
    @endif
</div>
@if(Auth::user()->role->level > 5)
<div class="btn-group">
    <a href="{{ route('projects.response.filter', [$id, 'state']) }}" class='btn btn-default btn-sm'>
        <i class="glyphicon glyphicon-equalizer"></i> Response
    </a>
    <a href="{{ route('projects.response.double', $id) }}" class='btn btn-default btn-sm'>
        <i class="glyphicon glyphicon-transfer"></i> Double Entry
    </a>
</div>
<div class="btn-group">
{!! Form::open(['route' => ['projects.destroy', $id], 'method' => 'delete']) !!}

    {!! Form::button('<i class="glyphicon glyphicon-trash"></i>', [
        'type' => 'submit',
        'class' => 'btn btn-danger btn-sm',
        'onclick' => "return confirm('Are you sure?')"
    ]) !!}

{!! Form::close() !!}
</div>

@endif
@if(Auth::user()->role->level > 8)
<div class="btn-group">
{!! Form::open(['route' => ['translate', $id], 'method' => 'post', 'class' => 'translation']) !!}
<div class="input-group">
      @php
        $trans = json_decode($project_trans,true);
      @endphp

      <input type="text" name="columns[project]" class="form-control" placeholder="Add Translation" @if(!empty($trans) && array_key_exists(config('app.locale'), $trans ))
        value="{!! $trans[config('app.locale')] !!}"
      @endif>
      <input type="hidden" name="model" value="project">
      <span class="input-group-btn">
        <button class="btn btn-default" type="submit">Save</button>
      </span>

</div><!-- /input-group -->
{!! Form::close() !!}
</div>
@endif
