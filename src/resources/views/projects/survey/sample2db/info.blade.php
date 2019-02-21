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
                        @foreach($sample_structure as $id => $label)
                            <th>{!! $label !!}</th>
                        @endforeach
                        @if($project->samples > 1)
                            <th>{!! trans('messages.sample') !!}</th>
                        @endif
                        @if($project->copies > 1)
                            <th>{!! trans('messages.form_id') !!}</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        @foreach($sample_structure as $id => $label)
                            <td>{!! $sample->{$id} !!}</td>
                        @endforeach
                        @if($project->samples > 1)
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
            </div>
            <div class="col-sm-12">
                <a href="{!! route('projects.incident.create', [$project->id, $sample->id, $form_id ]) !!}" class="btn btn-primary">Report Incident</a>
                <a href="{!! route('projects.surveys.index', $project->id) !!}" class="btn btn-default">Cancel</a>
            </div>
                </div>
            </div>
    </div>
    </div>

@endsection
