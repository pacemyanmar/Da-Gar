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
                        @if($project->index_columns)
                            @foreach($project->index_columns as $column => $columnName)
                                <th>{!! $columnName !!}</th>
                            @endforeach
                        @else
                            @foreach($sample->fillable as $column)
                                <th>{!! ucwords($column) !!}</th>
                            @endforeach
                        @endif
                        @if(count($project->samples) > 1)
                        <th>Sample</th>
                        @endif
                        @if($project->copies > 1)
                        <th>Form ID</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        @if($project->index_columns)
                            @foreach($project->index_columns as $column => $columnName)
                                @if($column == 'form_id')
                                <td>
                                    @if(isset($form))
                                        <p>{!! $form !!}</p>
                                    @else
                                    {!! ucwords($sample->{$column}) !!}
                                    @endif
                                </td>
                                @else
                                <td>{!! ucwords($sample->data->{$column}) !!}</td>
                                @endif
                            @endforeach
                        @else
                            @foreach($sample->fillable as $column)
                                <td>{!! ucwords($sample->{$column}) !!}</td>
                            @endforeach
                        @endif
                        @if(count($project->samples) > 1)
                        <td>
                            <select id="sample" class="info form-control" {!! (isset($sample->data->sample))?'disabled="disabled"':'name="sample"' !!}>
                            @foreach($project->samples as $name => $sample_value)
                                <option value="{!! $sample_value !!}" {!! (isset($sample->data->sample) && $sample->data->sample == $sample_value)?' selected="selected"':'' !!}>{!! $name !!}</option>
                            @endforeach
                            </select>
                            @if(isset($sample->data->sample))
                                <input type="hidden" name="sample" value="{!! $sample->data->sample !!}">
                            @endif
                        </td>
                        @else
                            <input class ="info" type="hidden" id="sample" name="sample" value="{!! $project->samples[0]!!}">
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
