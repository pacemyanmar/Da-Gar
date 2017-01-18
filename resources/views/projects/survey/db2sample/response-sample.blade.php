@extends('layouts.app')

@section('content')
    <section class="content-header">
        <h1 class="pull-left">Projects Sample Response Rate</h1>
        <span class="pull-right">
        <label>Response rate by:
           <select autocomplete="off" id="responseBy" class="form-control input-md">
               <option value="{!! route('projects.response.filter', [$project->id, 'state']) !!}" @if($filter === 'state') selected="selected" @endif>State</option>
               <option value="{!! route('projects.response.filter', [$project->id, 'name']) !!}" @if($filter === 'name') selected="selected" @endif>Enumerator</option>
               <option value="{!! route('projects.response.filter', [$project->id, 'district']) !!}" @if($filter === 'district') selected="selected" @endif>District</option>
           </select>
           </label>
        </span>
    </section>
    <div class="content">
        <div class="clearfix"></div>

        @include('flash::message')

        <div class="clearfix"></div>
        <div class="box box-primary">
            <div class="box-body">
                    @include('projects.table')
            </div>
        </div>
    </div>
@endsection

@push('document-ready')
    $('#responseBy').on('change', function(e){
        var filterurl = $(this).val();
        console.log(filterurl);
        window.location.href = filterurl;
    });
@endpush
