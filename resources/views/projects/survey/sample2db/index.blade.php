@extends('layouts.app')
@php
    $columns = $dataTable->getColumns()->filter(function ($value, $key) {
        if($value->visible === null || $value->visible === true)
            return true;
        if($value->visible === false)
            return false;
    });
    $columns = $columns->pluck('name','name')->toArray();
    $columnName = array_keys($columns);
        $textColumns = ['idcode', 'name', 'nrc_id', 'form_id'];

        $textColumns = array_intersect_key($columns, array_flip($textColumns));

        $columnName = array_flip($columnName);
        $textColsArr = [];
        foreach ($textColumns as $key => $value) {
            $textColsArr[] = $columnName[$key];
        }

        $selectColumns = ['village', 'village_tract', 'township', 'district', 'state'];

        $selectColumns = array_intersect_key($columns, array_flip($selectColumns));
        $selectColsArr = [];
        foreach ($selectColumns as $key => $value) {
            $selectColsArr[] = $columnName[$key];
        }

        $statusColumns = [''];

        $statusColumns = array_intersect_key($columns, array_flip($statusColumns));
        $statusColsArr = [];
        foreach ($statusColumns as $key => $value) {
            $statusColsArr[] = $columnName[$key];
        }

        $textCols = implode(',', $textColsArr);
        $selectCols = implode(',', $selectColsArr);
        $statusCols = implode(',', $statusColsArr);

       // dd($project->samplesData->groupBy('state')->keys());

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
                    @include('projects.survey.'.$project->type.'.table')
            </div>
        </div>
    </div>
@endsection
@push('before-body-end')
    <script type="text/javascript">

    </script>
@endpush
