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
                                <td>{!! ucwords($sample->{$column}) !!}</td>
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
                                <select name="sample" id="sample" class="info form-control">
                                @foreach($project->samples as $name => $sample)
                                        <option value="{!! $sample !!}">{!! $name !!}</option>
                                @endforeach
                                </select>
                        </td>
                        @else
                            <input class ="info" type="hidden" id="sample" name="sample" value="{!! $project->samples[0]!!}">
                        @endif
                        @if($project->copies > 1)
                            <td>
                                <select name="copies" id="copies" class="info form-control">
                                @for($i=1; $i <= $project->copies; $i++)
                                    <option value="{!! $i !!}">{!! $i !!}</option>
                                @endfor
                                </select>
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
