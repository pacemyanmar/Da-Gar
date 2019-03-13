@extends('layouts.app')

@section('content')
    <section class="content-header">
        <h1 class="pull-left">Bulk Sms</h1>
        <h1 class="pull-right">
            <a class="btn btn-primary" style="margin-top: -10px;margin-bottom: 5px;margin-right: 5px" href="{!! url('bulkSms/send') !!}">Send SMS</a>

            <a href="#" class='btn btn-success' style="margin-top: -10px;margin-bottom: 5px;margin-right: 5px" data-toggle="modal"
               data-target="#upload-sms" data-method='POST'>
                <i class="glyphicon glyphicon-plus"></i> import sms file
            </a>
           <a class="btn btn-primary pull-right" style="margin-top: -10px;margin-bottom: 5px" href="{!! route('bulkSms.create') !!}">Add New</a>
        </h1>
    </section>
    <div class="content">
        <div class="clearfix"></div>

        @include('flash::message')

        <div class="clearfix"></div>
        <div class="box box-primary">
            <div class="box-body">
                    @include('bulk_sms.table')
            </div>
        </div>
        <div class="text-center">
        
        </div>
    </div>
@endsection
@section('scripts')
    @include('bulk_sms.modal')
@endsection

