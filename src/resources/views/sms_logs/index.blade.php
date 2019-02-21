@extends('layouts.app')

@section('content')
    <section class="content-header">
        <h1 class="pull-left">@if(isset($project)) {!! $project->project !!} @endif Sms Logs</h1>
        <span class="pull-right  form-inline">
            <label for="project" class="control-label">Select Project:</label>
            <select id="project" name="project" class="form-control">
                <option value="{!! route('smsLogs.index') !!}">All</option>
                @foreach($projects as $p)
                    <option value="{!! route('projects.smslog', $p->id) !!}" @if(isset($project) && $p->id === $project->id) selected="selected" @endif>{!! $p->project !!}</option>
                @endforeach
            </select>
        </span>
        <div class="clearfix"></div>
        @if(isset($project))
            <a href="{!! route('projects.smslog', $project->id) !!}" class="btn btn-primary navbar-btn @if(empty(request('section', $default = null))) active @endif">All</a>
            @foreach($project->sections as $key => $section)
                @if(!$section->disablesms)
                <a href="{!! route('projects.smslog', $project->id) !!}/?section={!! $key + 1 !!}" class="btn btn-primary navbar-btn @if(request('section', $default = null) == ($key + 1)) active @endif">SMS {!! $key + 1 !!}</a>
                @endif
            @endforeach
            <a href="{!! route('projects.smslog', $project->id) !!}/?section=unknown" class="btn btn-primary navbar-btn @if(request('section', $default = null) == 'unknown') active @endif">Unknown</a>
        @endif
    </section>

    <div class="content">
        <div class="clearfix"></div>

        @include('flash::message')

        <div class="clearfix"></div>
        <div class="box box-primary">
            <div class="box-body">
                @include('sms_logs.table')
            </div>
        </div>
    </div>
@endsection

@push('document-ready')
window.LaravelDataTables["dataTableBuilder"].columns.adjust().draw();
setInterval( function () {
window.LaravelDataTables["dataTableBuilder"].ajax.reload( null, false ); // user paging is not reset on reload
}, 10000 );
ajaxoverlay = false;
$('#project').on('change', function(e){
var filterurl = $(this).val();

window.location.href = filterurl;
});
@endpush
