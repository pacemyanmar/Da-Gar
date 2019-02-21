@extends('layouts.app')

@section('content')
    <section class="content-header">
        <h1 class="pull-left">Translations</h1>
        <h1 class="pull-right">
            <a class="btn btn-primary pull-right" style="margin-top: -10px;margin-bottom: 5px"
               href="{!! route('translations.create') !!}">Add New</a>
        </h1>
        <div class="pull-right">
            <a href="#" class='btn btn-success' style="margin-top: -10px;margin-bottom: 5px;margin-right:5px;"
               data-toggle="modal"
               data-target="#uploadModal" data-method='POST'>
                <i class="glyphicon glyphicon-plus"></i> Import Translations
            </a>
        </div>
    </section>
    <div class="content">
        <div class="clearfix"></div>

        @include('flash::message')

        <div class="clearfix"></div>
        <div class="box box-primary">
            <div class="box-body">
                @include('translations.table')
            </div>
        </div>
        <div class="text-center">

        </div>
    </div>
    <div class="modal fade" id="uploadModal" tabindex="-1" role="dialog" aria-labelledby="uploadModalLabel">
        {!! Form::open(['route' => ['translations.import'], 'method' => 'post', 'class' => 'import', 'id' => 'uploadModalForm', 'files' => true]) !!}
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="uploadModalLabel">Logic</h4>
                </div>

                <div class="modal-body">
                    <div class="row">
                        <div class="form-group col-sm-12 col-lg-12">

                        <div class="form-group">
                            <span class="btn btn-primary btn-file" id="file">
                                <label for="upload" id="file-label">Browse File</label>
                                {!! Form::file('file', ['aria-describedby' => 'file', 'id'=>'upload']); !!}
                            </span>
                        </div>
                        </div>

                    </div>
                </div>
                <div class="modal-footer">
                    <span id="ajaxMesg" class="hidden"></span>
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    {!! Form::submit('Save', ['class' => 'btn btn-primary', 'id' => 'import']) !!}
                </div>

            </div>
        </div>
        {!! Form::close() !!}
    </div>
@endsection

