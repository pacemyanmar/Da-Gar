@extends('layouts.app')

@section('content')
    <section class="content-header">
        <h1>            
            {!! Form::label('name', $project->name) !!}
        </h1>
    </section>
    <div class="content">
        <div class="panel panel-primary">
            <div class="panel-heading">
                <div class="panel-title">
                    Information | Validation
                </div>                  
            </div>
            <div class="panel-body">
                @include("projects.$project->dblink")
            </div>
        </div> 
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
