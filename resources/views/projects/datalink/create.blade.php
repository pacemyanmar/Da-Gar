@extends('layouts.app')

@push('after-body-start')
<a class="btn btn-primary pull-right btn-float btn-float-up save-all" style="display:inline;margin-right:15px;" href="#" data-id="survey-form"> Save All</a>
           <a class="pull-right btn-float btn-float-bottom btn-float-to-up" style="display:inline;font-size: 40px;" href="#"><i class="fa fa-arrow-circle-up"></i></a>
@endpush
@section('content')

    <section class="content-header">
        <h1 class="pull-left">{!! Form::label('name', $project->project) !!}</h1>
        <h1 class="pull-right">
           <a class="btn btn-primary pull-right save-all" style="display:inline;margin-top: -10px;margin-bottom: 5" href="#" data-id="survey-form"> Save All</a>
        </h1>
    </section>
    <div class="content">
        <div class="clearfix"></div>
        @include('flash::message')

        <div class="clearfix"></div>
        @yield('info-table')

        <div id="survey-form">
        @foreach($project->sections as $section_key => $section)
        @php
            //section as css class name
            $sectionClass = str_slug($section['sectionname'], $separator = "-")
        @endphp
        <div class="panel panel-primary" id="{!! $sectionClass !!}">
            <div class="panel-heading">
                <div class="panel-title">
                    {!! $section['sectionname'] !!} <small> {!! (!empty($section['descriptions']))?" | ".$section['descriptions']:"" !!}</small>
                </div>                  
            </div>
            <div class="panel-body">
                @include('projects.show_fields')
                <h1 class="pull-right">
                   <a class="btn btn-sm btn-info pull-right save-section" data-id="{!! $sectionClass !!}" style="display:inline;margin-top: -10px;margin-bottom: 5" href="#"> Save this section</a>
                </h1>
            </div>
        </div> 
        @endforeach
        </div>
    </div>
@endsection

<!-- copy from https://getflywheel.com/layout/add-sticky-back-top-button-website/ -->
@section('css')
<style>
 
 
</style>
@endsection
@push('vue-scripts')
<script type='text/javascript'>
$(document).ready(function() {
    $('.save-section').click(function(event){
        event.preventDefault();

        var id = $(this).data('id');
        var section_data = $('#'+id+' :input').serializeArray();
        section_data.push({name: 'samplable_type', value: $('#sample').val()});
        sendAjax(url,section_data);
        //console.log(section_data);
    });
    $('.save-all').click(function(event){
        event.preventDefault();

        var id = $(this).data('id');
        var section_data = $('#'+id+' :input').serializeArray();
        section_data.push({name: 'samplable_type', value: $('#sample').val()});
        sendAjax(url,section_data);
        //console.log(section_data);
    });
});
</script>
@endpush