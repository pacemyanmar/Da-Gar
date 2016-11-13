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

@push('vue-scripts')
<script type='text/javascript'>

</script>
@endpush