@foreach($projects as $project)
    @php
        if(!$project->locationMetas->isEmpty()) {
            if(!$project->locationMetas->where('filter_type', 'selectbox')->isEmpty()) {
                $response_filter = $project->locationMetas->where('filter_type', 'selectbox')->first()->field_name;
            }
        }
        else {
            $response_filter =  '';
        }
    @endphp
    <div class="col-sm-12 @if($loop->count !== 1) col-md-6 @endif">
        @include('projects.box-item')
    </div>
@endforeach