@extends('layouts.app')

@section('content')
    <section class="content-header">
        <h1 class="pull-left">Projects Double Entry Response</h1>
    </section>
    <div class="content">
        <div class="clearfix"></div>

        @include('flash::message')

        <div class="clearfix"></div>
        <div class="box box-primary">
            <div class="box-body">
                <fieldset>
                    <legend>Legend:</legend>
                    <p>blank = no form submitted by data clerk.</p>
                    <p>0 (zero) = no conflict between 2 datasets (data clerk and double entry)</p>
                    <p>number > 0 = number of conflict between 2 datasets (data clerk and double entry)</p>
                </fieldset>
            </div>
        </div>
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
