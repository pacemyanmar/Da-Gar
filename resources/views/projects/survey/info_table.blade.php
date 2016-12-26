
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
                                @if($project->in_index)
                                    @foreach($project->in_index as $column)

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
                                @if($project->in_index)
                                    @foreach($project->in_index as $column)

                                    @endforeach
                                @else
                                    @foreach($sample->fillable as $column)
                                        <td>{!! ucwords($sample->{$column}) !!}</td>
                                    @endforeach
                                @endif
                                @if(count($project->samples) > 1)
                                <td>
                                        <select id="sample" class="form-control">
                                        @foreach($project->samples as $name => $sample)
                                                <option value="{!! $sample !!}">{!! $name !!}</option>
                                        @endforeach
                                        </select>
                                </td>
                                @else
                                    <input type="hidden" id="sample" value="{!! $project->samples[0]!!}">
                                @endif
                                @if($project->copies > 1)
                                    <td>
                                        <select id="copies" class="form-control">
                                        @for($i=1; $i <= $project->copies; $i++)
                                            <option value="{!! $i !!}">{!! $i !!}</option>
                                        @endfor
                                        </select>
                                    </td>
                                @endif
                            </tr>
                        </tbody>
                    </table>
                    </div>
                </div>
            </div>
        </div>
