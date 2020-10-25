@foreach($projects as $project)
    @if((setting('show_projects')[$project->id])??null)
    <div class="col-sm-12 @if($loop->count !== 1) col-md-6 @endif">
        @include('projects.box-item')
    </div>
    @endif
@endforeach