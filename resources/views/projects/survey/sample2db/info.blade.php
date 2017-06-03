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
                        <th>{!! trans('sample.uecnec_code') !!}</th>
                        <th>{!! trans('sample.level1') !!}</th>
                        <th>{!! trans('sample.level3') !!}</th>
                        <th>{!! trans('messages.polling_station') !!}</th>
                        <th>{!! trans('sample.supervisor_name') !!}</th>
                        <th>{!! trans('sample.supervisor_phone') !!}</th>
                        <th>{!! trans('sample.registered_voters') !!}</th>
                        <th>{!! trans('sample.supervisor_field') !!}</th>

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
                        <td>{!! ucwords($sample->ps_code) !!}</td>
                        <td>{!! ucwords($sample->level1) !!}</td>
                        <td>{!! ucwords($sample->level3) !!}</td>
                        <td>{!! ucwords($sample->level6) !!}</td>
                        <td>{!! ucwords($sample->supervisor_name) !!}</td>
                        <td>{!! ucwords($sample->supervisor_mobile) !!}</td>
                        <td>{!! $sample->registered_voters !!}</td>
                        <td>{!! ucwords($sample->supervisor_field) !!}</td>

                        @if(count($project->samples) > 1)
                            <td>
                                <select id="sample"
                                        class="info form-control" {!! (isset($sample->sample))?'disabled="disabled"':'name="sample"' !!}>
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
                    <th>{!! trans('sample.observer_code') !!}</th>
                    <th>{!! trans('sample.observer_name') !!}</th>
                    <th>{!! trans('sample.phone') !!}</th>
                    <th>{!! trans('sample.phone2') !!}</th>
                    <th>{!! trans('sample.observer_field') !!}</th>
                </tr>
            </thead>
            <tbody>
            @foreach($sample->observers as $observer)
                <tr>
                    <td>{!! ucwords($observer->code) !!}</td>
                    <td>{!! ucwords($observer->full_name) !!}</td>
                    <td>{!! ucwords($observer->phone_1) !!}</td>
                    <td>{!! ucwords($observer->phone_2) !!}</td>
                    <td>{!! $observer->observer_field !!}</td>
                </tr>
            @endforeach
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
