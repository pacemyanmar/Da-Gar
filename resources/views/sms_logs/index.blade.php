@extends('layouts.app')

@section('content')
    <section class="content-header">
        <h1 class="pull-left">Sms Logs</h1>
        <h1 class="pull-right">
           <a class="btn btn-primary pull-right" style="margin-top: -10px;margin-bottom: 5px" href="{!! route('smsLogs.create') !!}">{!! trans('messages.add_new') !!}</a>
        </h1>
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
setInterval( function () {
    window.LaravelDataTables["dataTableBuilder"].ajax.reload( null, false ); // user paging is not reset on reload
}, 3000 );
ajaxoverlay = false;
@endpush
