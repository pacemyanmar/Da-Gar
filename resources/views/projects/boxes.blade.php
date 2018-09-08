@foreach($projects as $project)
    <div class="col-sm-12 @if($loop->count !== 1) col-md-6 @endif">
        @include('projects.box-item')
    </div>
@endforeach