@extends('layouts.app')

@section('content')
    <section class="content-header">
        <h1 class="pull-left">Projects Double Entry Response</h1>
        <span class="pull-right col-xs-3">
        <label>Select section
        <select id="responseSection" class="form-control">
            @foreach($sections as $value => $name)
            <option value="{!! route('projects.response.double',[$settings['project_id'], $value]) !!}" @if($value == $settings['section']) selected="selected" @endif>{!! $name !!}</option>
            @endforeach
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
    $('#responseSection').on('change', function(e){
        var filterurl = $(this).val();
        console.log(filterurl);
        window.location.href = filterurl;
    });
@endpush
