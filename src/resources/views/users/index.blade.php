@extends('layouts.app')

@section('content')
    <section class="content-header">
        <h1 class="pull-left">Users</h1>
        <h1 class="pull-right">

            <div class="btn-group" role="group" aria-label="...">
                <!-- Large modal -->
                <button type="button" class="btn btn-primary flat" style="margin-top: -10px;margin-bottom: 5px" data-toggle="modal" data-target=".user-import">Import</button>
                <a class="btn btn-default flat" style="margin-top: -10px;margin-bottom: 5px" href="{!! route('users.create') !!}">{!! trans('messages.add_new') !!}</a>
            </div>
        </h1>
    </section>
    <div class="content">
        <div class="clearfix"></div>

        @include('flash::message')

        <div class="clearfix"></div>
        <div class="box box-primary">
            <div class="box-body">
                    @include('users.table')
            </div>
        </div>

    @include('users.modal')

@endsection
