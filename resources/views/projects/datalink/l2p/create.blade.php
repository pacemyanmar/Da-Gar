@extends('projects.datalink.create')
@section('before-head-end')
<script type="text/javascript">
window.url="{!! route('projects.voters.save', ['project' => $project->id, 'voter' => $voter->id]) !!}"
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
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>{!! $voter->name !!}</td>
                                <td>{!! $voter->father !!}</td>
                                <td>{!! $voter->address !!}</td> 
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