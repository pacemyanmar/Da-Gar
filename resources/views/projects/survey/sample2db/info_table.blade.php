@section('before-head-end')
<script type="text/javascript">
window.url="{!! route('projects.surveys.result.save', ['project' => $project->id, 'sample' => $sample->id]) !!}"
</script>
@endsection
@section('info-table')
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
                                <th>Name</th>
                                <th>Father</th>
                                <th>Address</th>
                                @if(count($project->samples) > 1)
                                <th>Sample</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>{!! $sample->name !!}</td>
                                <td>{!! $sample->father !!}</td>
                                <td>{!! $sample->address !!}</td>
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
                            </tr>
                        </tbody>
                    </table>
                    </div>
                </div>
            </div>
        </div>
@endsection
@section('script')
<script type="text/javascript">
jQuery(document).ready(function($) {

});
</script>
@endsection
