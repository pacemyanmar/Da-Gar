@extends('layouts.app')

@section('content')
    <section class="content-header">
        <h1>            
            {!! Form::label('name', $project->project) !!}
        </h1>
    </section>
    <div class="content">
        @foreach($project->sections as $section_key => $section)
        <div class="panel panel-primary">
            <div class="panel-heading">
                <div class="panel-title">
                    {!! $section['sectionname'] !!} <small> {!! (!empty($section['descriptions']))?" | ".$section['descriptions']:"" !!}</small>
                </div>                  
            </div>
            <div class="panel-body">
                @include('projects.show_fields')
            </div>
        </div> 
        @endforeach
    </div>
@endsection

@section('scripts')
<script type='text/javascript'>


</script>
@endsection
