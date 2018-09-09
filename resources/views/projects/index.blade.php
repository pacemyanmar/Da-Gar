@extends('layouts.app')

@section('content')
    <section class="content-header">
        <h1 class="pull-left">Projects</h1>
        <h1 class="pull-right">
        @if(Auth::user()->role->level > 8)
                <a href="#" class='btn btn-success' style="margin-top: -10px;margin-bottom: 5px" data-toggle="modal"
                   data-target="#upload-project" data-method='POST'>
                    <i class="glyphicon glyphicon-plus"></i> import project
                </a>
           <a class="btn btn-primary pull-right" style="margin-top: -10px;margin-bottom: 5px" href="{!! route('projects.create') !!}">{!! trans('messages.add_new') !!}</a>
        @endif
        </h1>
    </section>
    <div class="content">
        <div class="clearfix"></div>

        @include('flash::message')

        <div class="clearfix"></div>
        <div class="row">
        @include('projects.boxes')
        </div>
    </div>
@endsection
@section('scripts')
    @include('projects.upload_project')
@endsection
