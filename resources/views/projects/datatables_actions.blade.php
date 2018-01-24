<div class="row">
    <div class="col-sm-4">
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
                {{--<div class="btn-group">--}}
                    {{--<a href="{{ route('projects.smslog', $id) }}" class='btn btn-default btn-sm'>--}}
                        {{--<i class="fa fa-envelope"></i> {!! trans('messages.smslog') !!}--}}
                    {{--</a>--}}
                {{--</div>--}}
                @if(Auth::user()->role->level > 8)
                    <a href="{{ route('projects.edit', $id) }}" class='btn btn-default btn-sm'>
                        <i class="glyphicon glyphicon-edit"></i> {!! trans('messages.edit') !!}
                    </a>
                @endif
            </div>
        </div>

        <div class="row">
            @if(Auth::user()->role->level >= 7)
                <div class="btn-group">
                    <a href="{{ route('projects.analysis', $id) }}" class='btn btn-default btn-sm'>
                        <i class="fa fa-pie-chart"></i> {!! trans('messages.analysis') !!}
                    </a>
                </div>
            @endif
            @if(Auth::user()->role->level > 5)


            @endif

        </div>
    </div>
    <div class="col-sm-4 text-center">
        <div class="row">

            @if(Auth::user()->role->level >= 7)
                <div class="btn-group">
                    <a href="{{ route('projects.response.filter', [$id, 'level1']) }}" class='btn btn-default btn-sm'>
                        <i class="glyphicon glyphicon-equalizer"></i> {!! trans('messages.response') !!}
                    </a>
                    @if(config('sms.double_entry'))
                        <a href="{{ route('projects.response.filter', [$id, 'level1', 'double']) }}"
                           class='btn btn-default btn-sm'>
                            <i class="glyphicon glyphicon-transfer"></i> {!! trans('messages.double_entry') !!}
                        </a>
                    @endif
                </div>
            @endif
        </div>
        <div class="row">
            @if(Auth::user()->role->level >= 7)
                @if(config('sms.double_entry'))
                    <a href="{{ route('projects.response.double', [$id]) }}" class='btn btn-default btn-sm'>
                        <i class="glyphicon glyphicon-open"></i> {!! trans('messages.check') !!} <i
                                class="glyphicon glyphicon-open"></i>
                    </a>
                @endif
            @endif
        </div>
    </div>

    <div class="col-sm-4">

        @if(Auth::user()->role->level > 7)
            <div class="btn-group">

                {!! Form::open(['route' => ['translate', $id], 'method' => 'post', 'class' => 'form-inline translation']) !!}
                <div class="input-group">
                    <input type="text" name="columns[project]" class="form-control"
                           placeholder="{!! trans('messages.add_translation') !!}"
                           @if(!empty($project_trans))
                           value="{!! $project_trans !!}"
                            @endif>
                    <input type="hidden" name="model" value="project">
                    <span class="input-group-btn">
                    <button class="btn btn-default" type="submit">{!! trans('messages.save') !!}</button>
                </span>

                </div><!-- /input-group -->
                {!! Form::close() !!}



                <a href="{{ route('locationMetas.edit', $id) }}" class='btn btn-default btn-sm'>
                    <i class="glyphicon glyphicon-edit"></i>Edit Locations
                </a>


                {!! Form::open(['route' => ['projects.destroy', $id], 'method' => 'delete', 'class' => 'from-inline']) !!}

                {!! Form::button('<i class="glyphicon glyphicon-trash"></i>', [
                    'type' => 'submit',
                    'class' => 'btn btn-danger btn-sm',
                    'onclick' => "return confirm('".trans('messages.are_you_sure')."')"
                ]) !!}

                {!! Form::close() !!}
            </div>
        @endif

    </div>
</div>
