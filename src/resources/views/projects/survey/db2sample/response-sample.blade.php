@extends('layouts.app')
@section('meta')
<meta name="csrf-token" content="{{ csrf_token() }}">
@endsection
@php
    $select_filters = $project->locationMetas->where('filter_type', 'selectbox')->pluck('field_name');

@endphp
@section('content')
    <section class="content-header">
        <h1 class="pull-left">{!! (request()->is('*double*'))?'Double Entry':'Samples' !!} Response Rate</h1>
        <span class="pull-right">
        <label>Response rate by:
           <select autocomplete="off" id="responseBy" class="form-control input-md">
               @foreach($select_filters as $filter)

                   <option value="{!! route('projects.response.filter', [$project->id, $filter]) !!}" @if($filters['type'] === $filter) selected="selected" @endif>{!! trans('samples.'.$filter) !!}</option>

               @endforeach
           </select>
           </label>
        </span>
    </section>
    <div class="content">
        <div class="clearfix"></div>

        @include('flash::message')

        <div class="clearfix"></div>
        <div class="box box-default">
            <div class="box-body">
                    @include('projects.survey.db2sample.table')
            </div>
        </div>
        <div class="box box-default">
            <div class="box-body">
               <a href="{{ url()->current() }}" class="btn btn-default">All</a>
              @foreach($project->sections as $key => $section)
                <a href="{{ url()->current() }}/?section={{ $section->sort + 1 }}" class="btn btn-default">{{$section->sectionname}}</a>
              @endforeach
            </div>
        </div>
    </div>
@endsection

@push('document-ready')
    $.ajaxSetup({
      headers:
      { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
    });
    window.LaravelDataTables["dataTableBuilder"].columns.adjust().draw();
    setInterval( function () {
    window.LaravelDataTables["dataTableBuilder"].ajax.reload( null, false ); // user paging is not reset on reload
    }, 10000 );
    ajaxoverlay = false;
    $('#responseBy').on('change', function(e){
        var filterurl = $(this).val();

        window.location.href = filterurl;
    });

@endpush
