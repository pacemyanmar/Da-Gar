<div class="panel panel-primary">
    <div class="panel-heading">
        <div class="panel-title">
            Information | Validation
        </div>
    </div>
    <div class="panel-body">
        <div id="checktable">
            <div class="table-responsive">
                <table class="table table-striped table-bordered table-hover">
                    <thead>
                    <tr>

                        @foreach($project->locationMetas as $location)
                            @if($location->show_index)
                            <th>{!! trans('samples.'.$location->field_name) !!}</th>
                            @endif
                        @endforeach

                        @if(count($project->samples) > 1)
                            <th>{!! trans('messages.sample') !!}</th>
                        @endif
                        @if($project->copies > 1)
                            <th>{!! trans('messages.form_id') !!}</th>
                        @endif
                    </tr>
                    </thead>
                    <tbody>
                    <tr>

                        @foreach($project->locationMetas as $location)
                            @if($location->show_index)
                                <th>{!! $sample_data->{$location->field_name} !!}</th>
                            @endif
                        @endforeach


                        @if(count($project->samples) > 1)
                            <td>
                                <select id="sample"
                                        class="info form-control" {!! (isset($sample_data->sample))?'disabled="disabled"':'name="sample"' !!}>
                                    @foreach($project->samples as $name => $sample_value)
                                        <option value="{!! $sample_value !!}" {!! (isset($sample_data->sample) && $sample_data->sample == $sample_value)?' selected="selected"':'' !!}>{!! $name !!}</option>
                                    @endforeach
                                </select>

                            </td>
                        @endif
                        @if(isset($sample_data->sample))
                            <input type="hidden" name="sample" value="{!! $sample_data->sample !!}">
                        @else
                            <input type="hidden" name="sample" value="1">
                        @endif
                        @if($project->copies > 1)
                            <td>
                                @if(isset($form))
                                    <input name="copies" id="copies" type=hidden value="{!! $form !!}">
                                    <p>{!! $form !!}</p>
                                @else
                                    <select name="copies" id="copies" class="info form-control">
                                        @for($i=1; $i <= $project->copies; $i++)
                                            <option value="{!! $i !!}">{!! $i !!}</option>
                                        @endfor
                                    </select>
                                @endif
                            </td>
                        @else
                            <input class="info" type="hidden" id="copies" name="copies" value="1">
                        @endif
                    </tr>
                    </tbody>
                </table>



            </div>
        </div>
    </div>
</div>
