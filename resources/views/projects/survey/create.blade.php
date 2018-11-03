@extends('layouts.app')
@push('before-head-end')
    <script type="text/javascript">
        window.url = "{!! route('projects.surveys.save', ['project' => $project->id, 'sample' => $sample->id]) !!}"
    </script>
@endpush
@push('after-body-start')
    <!--a class="btn btn-primary pull-right btn-float btn-float-up save" style="display:inline;margin-right:15px;" href="#" data-id="survey-form">{{ trans('messages.saveall') }}</a>
           <a class="pull-right btn-float btn-float-bottom btn-float-to-up" style="display:inline;font-size: 40px;" href="#"><i class="fa fa-arrow-circle-up"></i></a-->
@endpush
@section('content')
    <form autocomplete="off">
        <section class="content-header">

            @if($project->status != 'published')
                <div class="alert alert-warning">
                    Project modified. Rebuild to show new changes in this form.
                </div>
            @endif
            <h1 class="pull-left">{!! Form::label('name', $project->project) !!}</h1>

        <h1 class="pull-right">
            <a class="btn btn-info pull-right btn-float-show btn-float-up" style="display:block;margin-top: -10px;margin-bottom: 5px;" href="{!! route('projects.surveys.index', $project->id) !!}">
                <i class="fa fa-reply text-warning" id="tolist-icon"></i>
                {{ trans('messages.back') }}</a>
            <a class="pull-right btn-float btn-float-bottom btn-float-to-up" style="display:inline;font-size: 40px;" href="#"><i class="fa fa-arrow-circle-up"></i></a>
        </h1>
        </section>
        <div class="content">
            <div class="clearfix"></div>
            @include('flash::message')

            <div class="clearfix"></div>

            @include('projects.survey.info_table')

            <div class="panel panel-primary">
                <div class="panel-heading">
                    <div class="panel-title">
                        Sections List
                    </div>
                </div>
                <div class="panel-body">
                    <div class="btn-toolbar">
                    @foreach($project->sections as $section_key => $section)
                            @php
                                //section as css class name
                                $sectionClass = 'section'.$section->sort;
                                $section_num = $section->sort;

                                if( isset($results) && !empty($results['section'.$section->sort]) ) {
                                    $section_status = $results['section'.$section->sort]->{'section'.$section->sort.'status'};
                                    if( $section_status == 0) {
                                        $section_status = 'danger';
                                        $icon = 'remove';
                                    } else if($section_status  == 1) {
                                        $section_status = 'success';
                                        $icon = 'ok';
                                    } else if($section_status  == 2) {
                                        $section_status = 'warning';
                                        $icon = 'ban-circle';
                                    } else if($section_status  == 3) {
                                        $section_status = 'info';
                                        $icon = 'alert';
                                    } else {
                                        $section_status = 'danger';
                                        $icon = 'remove';
                                    }
                                } else {
                                    $section_status = 'primary';
                                    $icon = 'question';
                                }

                            @endphp
                        <a style="margin-bottom: 3px" href="#section{!! $section->sort !!}" id="btn-section{!! $section->sort !!}" class="btn btn-{{ $section_status }} btn-sm" role="button">
                            {!! $section->sectionname !!}
                        </a>
                    @endforeach
                    </div>
                </div>
            </div>


            <div id="survey-form">
                @foreach($project->sections as $section_key => $section)
                    @php
                        //section as css class name
                        $sectionClass = 'section'.$section->sort;
                        $section_num = $section->sort;
                        $collapse = 'in';

                        if( isset($results) && !empty($results['section'.$section->sort]) ) {
                            $section_status = $results['section'.$section->sort]->{'section'.$section->sort.'status'};
                            if( $section_status == 0) {
                                $section_status = 'danger';
                                $icon = 'remove';
                            } else if($section_status  == 1) {
                                $section_status = 'success';
                                $icon = 'ok';
                                $collapse = (config('sms.collapse'))?'':'in';
                            } else if($section_status  == 2) {
                                $section_status = 'warning';
                                $icon = 'ban-circle';
                            } else if($section_status  == 3) {
                                $section_status = 'info';
                                $icon = 'alert';
                            } else {
                                $section_status = 'danger';
                                $icon = 'remove';
                            }
                        } else {
                            $section_status = 'primary';
                            $icon = 'question';
                        }

                    @endphp
                    <div class="panel panel-{{ $section_status }}" id="{!! $sectionClass !!}">
                        <div class="panel-heading"  data-toggle="collapse" data-target="#{!! $sectionClass !!}-body">
                            <div class="panel-title">
                                {!! $section->sectionname !!}
                                <small> {!! (!empty($section->descriptions))?" | ".$section->descriptions:"" !!}</small>

                                @if( isset($results) )
                                    <span class="pull-right">
                        <span class="badge">
                            <span  id="icon-{!! $sectionClass !!}" class="glyphicon glyphicon-{{ $icon }}"></span>
                        </span>
                        </span>
                                @else
                                    <span class="pull-right">
                        <span class="badge">
                            <span class="glyphicon glyphicon-remove text-danger"></span>
                        </span>
                        </span>
                                @endif
                            </div>
                        </div>
                        <div class="panel-body collapse {{ $collapse }}" id="{!! $sectionClass !!}-body">

                            @if($section->layout == 'form16')
                                @include('questions.form16-table')
                            @elseif($section->layout == 'form18')
                                @include('questions.form18-table')
                            @else
                                @include('projects.show_fields')
                            @endif

                            <h1 class="pull-right">
                                <a class="btn btn-sm btn-info pull-right save" data-section="{!! $section->id !!}"
                                   data-id="{!! $sectionClass !!}"
                                   style="display:inline;margin-top: -10px;margin-bottom: 5"
                                   href="#"> {{ trans('messages.savesection') }}</a>
                            </h1>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </form>
@endsection

<!-- copy from https://getflywheel.com/layout/add-sticky-back-top-button-website/ -->
@section('css')
    <style>


    </style>
@endsection

@push('before-body-end')
    <!-- Modal -->
    <div class="modal fade" id="alert" role="dialog">
        <div class="modal-dialog modal-sm">

            <!-- Modal content-->
            <div class="modal-content">

                <div class="modal-body">
                    <p id="submitted">Error in form submission.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
            </div>

        </div>
    </div>
    <style type="text/css">

        .verticaltext {
            position:fixed;
            top: 0px;
            right: -20px;
            -webkit-transform:rotate(-90deg) translateX(-100%);
        }

        .verticalreverse {
            -webkit-transform:rotate(-270deg) translateX(0%);
        }

        .btn-float-show {

            margin: 0;

            z-index: 100;

            display: inline;

            text-decoration: none;

        }

        .zawgyi {
            font-family: "Zawgyi-One" !important;
        }

        .invalid {
            border: 1px solid red;
        }

        .hf-warning {
            color: red;
        }

        .modal {
            text-align: center;
            padding: 0 !important;
        }

        .modal:before {
            content: '';
            display: inline-block;
            height: 100%;
            vertical-align: middle;
            margin-right: -4px;
        }

        .modal-dialog {
            display: inline-block;
            text-align: left;
            vertical-align: middle;
        }
    </style>
    <script type='text/javascript'>
        (function ($) {

            $('.time1').datetimepicker({
                datepicker:false,
                format:'H:i'
            });

            $('.time2').datetimepicker({
                datepicker:false,
                format:'h:i A'
            });

            $('.datetime').datetimepicker();

            $('.date').datetimepicker({
                timepicker:false,
                format:'d-m-Y'
            });

            $('.year').datetimepicker({
                timepicker:false,
                format:'Y'
            });

            $('.month').datetimepicker({
                timepicker:false,
                format:'m-Y'
            });

            var other_text = $('.other').closest("div.form-group").find("input[type='text'].othertext");
            if($('.other').is(':checked')){
                other_text.focus().addClass('has-error').prop('disabled', false).prop('required', true);
            } else {
                other_text.removeClass('has-error').prop('disabled', true).prop('required', false);
            }

            $('.other').on('change click',function(){
                var other_text = $(this).closest("div.form-group").find("input[type='text'].othertext");
                if($(this).is(':checked')){
                    other_text.focus().addClass('has-error').prop('disabled', false).prop('required', true);
                } else {
                    other_text.removeClass('has-error').prop('disabled', true).prop('required', false);
                }
            });

            $('.skippable').on('change click',function(){
                if($(this).is(':checked')){
                    var toskip = $(this).data('skip');
                    var goto = $(this).data('goto');
                    if(toskip) {
                        $(toskip).prop("disabled", true);
                    }
                    if(goto) {
                        $("body, html").animate({
                            scrollTop: $(goto).offset().top
                        }, 600);
                    }

                } else {
                    var toskip = $(this).data('skip');

                    if(toskip) {
                        $(toskip).prop("disabled", false);
                    }
                }
            });
            $.each($('.skippable'), function(i, elm){
                if(elm.checked){

                    if(elm.dataset.skip) {
                        $(elm.dataset.skip).prop("disabled", true);
                    }

                }
            });


            $('input:radio').on('change', function(e){
                if(!$(this).data('skip')) {
                    var siblings = $(this).closest('tr').find('input');
                    $.each(siblings, function(i, elm){
                        $(elm.dataset.skip).prop("disabled", false);
                    });
                } else {
                    $($(this).data('skip')).prop("disabled", true);
                }
            });


            //$( ".date" ).datetimepicker();

            if ($("input.none").is(':checked')) {
                $("input.none:checked").closest('tr').find('input').prop("disabled", true);
                $("input.none").prop("disabled", false);
            }

            $("input.none").change(function (e) {
                if ($(this).is(':checked')) {
                    $(this).closest('tr').find('input').prop("disabled", true);
                    $(this).prop("disabled", false);
                } else {
                    $(this).closest('tr').find('input').prop("disabled", false);
                }
            });

            $('.save').click(function (event) {
                event.preventDefault();
                $('.loading').removeClass("hidden");

                var id = $(this).data('id');

                var section_id = $(this).data('section');

                //$('#'+id).find(":input").filter(function(){ return !this.value; }).attr("disabled", "disabled");
                var info_data = $('.info').serializeArray();

                var section_data = $('#' + id + ' :input').serializeArray();

                section_data.push(
                    {
                        name: 'samplable_type',
                        value: $('#sample').val()
                    },
                    {
                        name: 'section_id',
                        value: section_id
                    }
                    );
                @if(isset($double))
                    section_data.push(
                        {
                            name: 'double',
                            value: true
                        }
                    );
                @endif

                var ajaxData = $.merge(info_data, section_data);

                request = sendAjax(url, ajaxData)

                //console.log(request);

                request.done(function (msg) {
                    $.each(msg.data.status, function(id, status){
                        $("#"+id).removeClass (function (index, className) {
                            return (className.match (/\bpanel\S+/g) || []).join(' ');
                        });

                        $("#btn-"+id).removeClass (function (index, className) {
                            return (className.match (/\bbtn\S+/g) || []).join(' ');
                        });

                        $("#icon-"+id).removeClass (function (index, className) {
                            return (className.match (/\bglyphicon\S+/g) || []).join(' ');
                        });

                        if(!status) {
                            $("#"+id).addClass('panel panel-danger');
                            $("#btn-"+id).addClass('btn btn-danger btn-sm');
                            $("#icon-"+id).addClass('glyphicon glyphicon-remove');
                        }

                        if(status === 1) {
                            $("#"+id).addClass('panel panel-success');
                            $("#btn-"+id).addClass('btn btn-success btn-sm');
                            $("#icon-"+id).addClass('glyphicon glyphicon-ok');
                        }

                        if(status === 2) {
                            $("#"+id).addClass('panel panel-warning');
                            $("#btn-"+id).addClass('btn btn-warning btn-sm');
                            $("#icon-"+id).addClass('glyphicon glyphicon-ban-circle');
                        }

                        if(status === 3) {
                            $("#"+id).addClass('panel panel-info');
                            $("#btn-"+id).addClass('btn btn-info btn-sm');
                            $("#icon-"+id).addClass('glyphicon glyphicon-alert');
                        }
                    });

                    $('#submitted').html(msg.message);

                    $('#alert').modal('show');
                });

                request.fail(function (jqXHR, textStatus) {
                    $.LoadingOverlay("hide");

                    if (typeof jqXHR.responseJSON !== 'undefined') {
                        $('#submitted').html(jqXHR.responseJSON.message);
                    } else {
                        $('#submitted').html('Error in form submission!');
                    }

                    $('#alert').modal('show');




                });


                $('#alert').on('hidden.bs.modal', function () {
                    formSubmitted = true;
                    if (id == 'survey-form') {
                        //window.location.href = "{{ route('projects.surveys.index', $project->id) }}";
                    } else {
                        //window.location.reload();
                    }
                })

                request.always(function () {

                });

                $('#' + id).find(":input").filter(function () {
                    return !this.value;
                }).removeAttr("disabled");

            });

            $("input[type=radio]").click(function () {
                // Get the storedValue
                var previousValue = $(this).data('selected');
                // if previousValue = true then
                //     Step 1: toggle radio button check mark.
                //     Step 2: save data-StoredValue as false to indicate radio button is unchecked.
                if (previousValue) {
                    $(this).prop('checked', !previousValue);
                    $(this).data('selected', !previousValue);
                }
                // If previousValue is other than true
                //    Step 1: save data-StoredValue as true to for currently checked radio button.
                //    Step 2: save data-StoredValue as false for all non-checked radio buttons.
                else {
                    $(this).data('selected', true);
                    $("input[type=radio]:not(:checked)").data("selected", false);
                }
            });


            $(':input').on('click keyup change', function () {
                var input = $(this)[0];
                var parent = $(this).parent();
                var validity = input.checkValidity();
                //console.log(validity);
                if (validity) {
                    $(this).removeClass('invalid');
                } else {
                    $(this).addClass('invalid');
                }
                input.reportValidity();

                {{--@if(Auth::user()->role->role_name == 'doublechecker')--}}

                    {{--var cssid = $(this).attr('id');--}}
                    {{--var cssclass = $(this).data('class');--}}
                    {{--var type = $(this).attr('type');--}}
                    {{--var ischecked= $(this).is(':checked');--}}

                    {{--if( (type == 'checkbox' && !ischecked && $(this).val() == $(this).data('origin') )--}}
                        {{--|| (type == 'checkbox' && !ischecked && $(this).val() != $(this).data('origin'))--}}
                        {{--|| (type == 'checkbox' && ischecked && $(this).val() != $(this).data('origin'))--}}
                        {{--|| ($(this).val() != $(this).data('origin'))--}}
                    {{--) {--}}
                        {{--$('.'+cssclass).addClass('hide')--}}
                        {{--$('.'+cssid).removeClass('hide').addClass('label-danger');--}}
                        {{--$('.'+cssid+ ' > i').removeClass('fa-check').addClass('fa-close');--}}
                    {{--} else {--}}
                        {{--//$('.'+cssclass).removeClass('hide').addClass('label-success');--}}
                        {{--$('.'+cssclass).addClass('hide')--}}
                        {{--$('.'+cssid).removeClass('hide').addClass('label-success');--}}
                        {{--$('.'+cssid+ ' > i').removeClass('fa-close').addClass('fa-check');--}}
                    {{--}--}}

                {{--@endif--}}
            });

            var offset = 150;

            var duration = 300;

            jQuery(window).scroll(function () {

                if (jQuery(this).scrollTop() > offset) {

                    jQuery('.btn-float-show').addClass('verticaltext').fadeIn(duration);
                    jQuery('#tolist-icon').addClass('verticalreverse');

                } else {
                    jQuery('.btn-float-show').removeClass('verticaltext');
                    jQuery('#tolist-icon').removeClass('verticalreverse');
                }

            });

        })(jQuery);
    </script>
@endpush
