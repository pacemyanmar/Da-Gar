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

                        <th>{!! trans('sample.location_code') !!}</th>
                        <th>{!! trans('sample.level1') !!}</th>
                        <th>{!! trans('sample.level3') !!}</th>
                        <th>{!! trans('sample.level4') !!}</th>
                        <th>{!! trans('sample.level5') !!}</th>

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

                        <td>{!! ucwords($sample->data->location_code) !!}</td>
                        <td>{!! ucwords($sample->data->level1) !!}</td>
                        <td>{!! ucwords($sample->data->level3) !!}</td>
                        <td>{!! ucwords($sample->data->level4) !!}</td>
                        <td>{!! ucwords($sample->data->level5) !!}</td>


                        @if(count($project->samples) > 1)
                            <td>
                                <select id="sample"
                                        class="info form-control" {!! (isset($sample->data->sample))?'disabled="disabled"':'name="sample"' !!}>
                                    @foreach($project->samples as $name => $sample_value)
                                        <option value="{!! $sample_value !!}" {!! (isset($sample->data->sample) && $sample->data->sample == $sample_value)?' selected="selected"':'' !!}>{!! $name !!}</option>
                                    @endforeach
                                </select>

                            </td>
                        @endif
                        @if(isset($sample->data->sample))
                            <input type="hidden" name="sample" value="{!! $sample->data->sample !!}">
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


                <table class="table table-striped table-bordered table-hover">
                    <thead>
                    <tr>
                        <th>{!! trans('sample.observer_code') !!}</th>
                        <th>{!! trans('sample.observer_name') !!}</th>
                        <th>{!! trans('sample.phone') !!}</th>
                        <th>{!! trans('sample.phone2') !!}</th>
                        <th>{!! trans('sample.observer_field') !!}</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($sample->data->observers as $observer)
                        <tr>
                            <td>{!! ucwords($observer->code) !!}</td>
                            <td>{!! ucwords($observer->full_name) !!}</td>
                            <td>{!! ucwords($observer->phone_1) !!}</td>
                            <td>{!! ucwords($observer->phone_2) !!}</td>
                            <td>{!! $observer->observer_field !!}</td>
                        </tr>
                    @endforeach
                    </tbody>

                </table>

            </div>
        </div>
    </div>
</div>
