@extends('layouts.app')
@php

@endphp
@section('content')
    <section class="content-header">
        <h1 class="pull-left">{{ $project->project}}</h1>
    </section>
    <div class="content">
        <div class="clearfix"></div>

        @include('flash::message')

        <div class="clearfix"></div>
        <div class="box box-primary">
            <div class="box-body">
                {!! Form::open(['route' => ['projects.sample.search', $project->id], 'id' => 'project', 'method' => 'GET']) !!}
                    <div class="col-sm-3 input-group">
                      {!! Form::text('sample',null, ['class' => 'form-control', 'placeholder' => 'Add location code']) !!}
                      <span class="input-group-btn">
                        <button class="btn btn-success" type="submit">Report Incident!</button>
                      </span>
                    </div><!-- /input-group -->

                {!! Form::close() !!}
            </div>
        </div>
        <div class="box box-primary">
            <div class="box-body">
                    @include('projects.survey.sample2db.table')
            </div>
        </div>
    </div>
@endsection
@push('before-body-end')
    <script type="text/javascript">
    </script>
@endpush
