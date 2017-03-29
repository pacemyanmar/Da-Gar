@extends('layouts.app')

@section('content')
    <section class="content-header">
        <h1>
            Incident Report Information
        </h1>
    </section>
    <div class="content">
        @include('adminlte-templates::common.errors')


                <div class="panel panel-primary">
    <div class="panel-heading">
        <div class="panel-title">
            Information | Validation
        </div>
    </div>
    <div class="panel-body">
                                <div class="table-responsive">
            <table class="table table-striped table-bordered table-hover">
                <thead>
                    <tr>
                        @if($project->type == 'sample2db')
                        @if($project->index_columns)
                            @foreach($project->index_columns as $column => $columnName)
                                <th>{!! trans('messages.'.snake_case(strtolower($columnName))) !!}</th>
                            @endforeach
                        @else
                            @foreach($sample->fillable as $column)
                                <th>{!! ucwords($column) !!}</th>
                            @endforeach
                        @endif
                        @else
                            <th>{!! trans('messages.idcode') !!}</th>
                            <th>{!! trans('messages.state') !!}</th>
                            <th>{!! trans('messages.township') !!}</th>
                            <th>{!! trans('messages.polling_station') !!}</th>
                        @endif
                        @if(count($project->samples) > 1)
                        <th>{!! trans('messages.sample') !!}</th>
                        @endif
                        @if($project->copies > 1)
                        <th>{!! trans('messages.form_id') !!}</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        @if($project->type == 'sample2db')
                            @if($project->index_columns)
                                @foreach($project->index_columns as $column => $columnName)
                                    @if($column == 'form_id')
                                    <td>
                                        @if(isset($form))
                                            <p>{!! $form !!}</p>
                                        @else
                                        {!! ucwords($sample->{$column}) !!}
                                        @endif
                                    </td>
                                    @else
                                    <td>{!! ucwords($sample->{$column}) !!}</td>
                                    @endif
                                @endforeach
                            @else
                                @foreach($sample->fillable as $column)
                                    <td>{!! ucwords($sample->{$column}) !!}</td>
                                @endforeach
                            @endif
                        @else
                            <td>{!! ucwords($sample->idcode) !!}</td>
                            <td>{!! ucwords($sample->state) !!}</td>
                            <td>{!! ucwords($sample->township) !!}</td>
                            <td>{!! ucwords($sample->village) !!}</td>
                        @endif
                        @if(count($project->samples) > 1)
                        <td>
                            <select id="sample" class="info form-control" {!! (isset($sample->sample))?'disabled="disabled"':'name="sample"' !!}>
                            @foreach($project->samples as $name => $sample_value)
                                <option value="{!! $sample_value !!}" {!! (isset($sample->sample) && $sample->sample == $sample_value)?' selected="selected"':'' !!}>{!! $name !!}</option>
                            @endforeach
                            </select>

                        </td>
                        @endif
                        @if(isset($sample->sample))
                                <input type="hidden" name="sample" value="{!! $sample->sample !!}">
                        @else
                                <input type="hidden" name="sample" value="1">
                        @endif
                        @if($project->copies > 1)
                            <td>
                                @if(isset($form))
                                    <input name="copies" id="copies" type=hidden value="{!! $form !!}">
                                    <p>{!! $form !!}</p>
                                @else
                                <select name="copies" id="copies" class="info form-control">
                                @for($i=1; $i <= $project->copies; $i++)
                                    <option value="{!! $i !!}">{!! $i !!}</option>
                                @endfor
                                </select>
                                @endif
                            </td>
                        @else
                            <input class="info" type="hidden" id="copies" name="copies" value="1">
                        @endif
                    </tr>
                </tbody>
            </table>
            <table class="table table-striped table-bordered table-hover">
            <thead>
                <tr>
                    <th>{!! trans('messages.observer_code') !!}</th>
                    <th>{!! trans('messages.name') !!}</th>
                    <th>{!! trans('messages.phone') !!}</th>
                    <th>{!! trans('messages.phone2') !!}</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>{!! ucwords($sample->code) !!}</td>
                    <td>{!! ucwords($sample->name) !!}</td>
                    <td>{!! ucwords($sample->mobile) !!}</td>
                    <td>{!! ucwords($sample->line_phone) !!}</td>
                </tr>
                <tr>
                    <td>{!! ucwords($sample->code2) !!}</td>
                    <td>{!! ucwords($sample->name2) !!}</td>
                    <td>{!! ucwords($sample->mobile2) !!}</td>
                    <td>{!! ucwords($sample->line_phone2) !!}</td>
                </tr>
            </tbody>

            </table>
            </div>
            <div class="col-sm-12">
                <a href="{!! route('projects.incident.create', [$project->id, $sample->id, $project->dblink, $form_id ]) !!}" class="btn btn-primary">Report Incident</a>
                <a href="{!! route('projects.surveys.index', $project->id) !!}" class="btn btn-default">Cancel</a>
            </div>
                </div>
            </div>
    </div>
    </div>

@endsection
