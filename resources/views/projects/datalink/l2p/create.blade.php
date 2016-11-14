@extends('layouts.app')
@push('after-body-start')
<a class="btn btn-primary pull-right btn-float btn-float-up" style="display:inline;margin-right:15px;" href="#"> Save All</a>
           <a class="pull-right btn-float btn-float-bottom" style="display:inline;font-size: 40px;" href="#"><i class="fa fa-arrow-circle-up"></i></a>
@endpush
@section('content')

    <section class="content-header">
        <h1 class="pull-left">{!! Form::label('name', $project->project) !!}</h1>
        <h1 class="pull-right">
           <a class="btn btn-primary pull-right" style="display:inline;margin-top: -10px;margin-bottom: 5" href="#"> Save All</a>
        </h1>
    </section>
    <div class="content">
        <div class="clearfix"></div>
        @include('flash::message')

        <div class="clearfix"></div>
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
                <h1 class="pull-right">
                   <a class="btn btn-sm btn-info pull-right" style="display:inline;margin-top: -10px;margin-bottom: 5" href="#"> Save this section</a>
                </h1>
            </div>
        </div> 
        @endforeach
    </div>
@endsection

<!-- copy from https://getflywheel.com/layout/add-sticky-back-top-button-website/ -->
@section('css')
<style>
 
 
</style>
@endsection
@push('vue-scripts')
<script type='text/javascript'>

</script>
@endpush