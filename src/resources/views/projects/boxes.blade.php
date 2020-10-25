@foreach($projects as $project)
    @if(!empty(array_filter(setting('show_projects'))))
    @if((setting('show_projects')[$project->id])??false)
        <div class="col-sm-12 @if($loop->count !== 1) col-md-6 @endif">
            @include('projects.box-item')
        </div>
    @endif
    @else
        <div class="col-sm-12 @if($loop->count !== 1) col-md-6 @endif">
            @include('projects.box-item')
        </div>
    @endif
@endforeach