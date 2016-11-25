@extends('layouts.app')

@section('content')
    <section class="content-header">
        <h1>            
            {!! Form::label('name', $project->project) !!}
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
                @include("projects.$project->dblink.$project->type.search")
            </div>
        </div>
    </div>
@endsection

@section('scripts')
<script type='text/javascript'>


</script>
@endsection
