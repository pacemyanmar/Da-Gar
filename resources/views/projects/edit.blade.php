@extends('layouts.app')
@section('meta')
<meta name="csrf-token" content="{{ csrf_token() }}">
@endsection
@section('content')
<section class="content-header" style="margin-bottom:30px;">
  <h1 class="pull-left">{!! $project->project !!}</h1>
  <div class="pull-right">
    <a href="{!! route('projects.export', [$project->id]) !!}" class="btn btn-info">{!! trans('messages.export_project') !!}</a>
    <a href="{!! route('projects.sort', [$project->id]) !!}" class="btn btn-info">{!! trans('messages.sort_project') !!}</a>
  @if($project->status != 'published')
    {!! Form::open(['route' => ['projects.dbcreate', $project->id], 'method' => 'post', 'class' => 'btn']) !!}
    @if($project->status == 'modified')
        {!! Form::button('<i class="fa fa-list-alt"></i> '.trans('messages.rebuild_form'), [
            'type' => 'submit',
            'class' => 'btn btn-danger',
            'onclick' => 'return confirm("Are you sure?\n This will update live form table for data entry!\nSome serious changes are running.\nPlease do not run this frequently if data entry already live.")'
        ]) !!}
    @else
        {!! Form::button('<i class="fa fa-list-alt"></i> '.trans('messages.build_form'), [
            'type' => 'submit',
            'class' => 'btn btn-danger',
            'onclick' => 'return confirm("Are you sure?\nThis will build actual form table for data entry!")'
        ]) !!}
    @endif

  {!! Form::close() !!}
  @endif
  </div>
</section>

<section>
<div class="content">
<div class="clearfix"></div>
  @include('flash::message')
  @include('adminlte-templates::common.errors')
  <div class="box box-primary">
    <div class="box-body">
      <div class="row">
        <div class="col-xs-12">
          <i class="editProject fa fa-edit pull-right btn btn-primary" style="cursor: pointer;font-size: 20px;"></i>
        </div>
      </div>
      <div class="row">
        {!! Form::model($project, ['route' => ['projects.update', $project->id], 'method' => 'patch', 'id' => 'project']) !!}

        @include('projects.fields')

        {!! Form::close() !!}
      </div>
    </div>
  </div>

  @if(!$project->sectionsDb->isEmpty())
  @foreach($project->sectionsDb as $section_key => $section)
  @php
            //section as css class name
            $sectionClass = str_slug($section['sectionname'], $separator = "-");
            $editing = true;
  @endphp
  <div class="panel panel-primary">
    <div class="panel-heading">
      <div class="panel-title">
        {!! $section->sectionname !!} <small> {!! (!empty($section->descriptions))?" | ".$section->descriptions:"" !!}</small>
        <span class="pull-right"><a href="#" class='btn btn-success' data-toggle="modal" data-target="#qModal" data-qurl="{!! route('questions.store') !!}" data-section="{!! $section->id !!}" data-method='POST'><i class="glyphicon glyphicon-plus"></i></a></span>
      </div>
    </div>
    <div class="panel-body">
      @include('projects.table_questions')
    </div>
  </div>
  @endforeach
  @endif
</div>
</section>
@include('questions.modal')
@endsection
@section('css')
<style type="text/css">
  .toggle {
    display: none;
  }
</style>
@endsection
@section('scripts')
<script type='text/javascript'>

  var formData = {'fields':''};
  var sortURL = '{!! route('questions.sort') !!}';

  $(document).ready(function(){

    $.ajaxSetup({
      headers:
      { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
    });

    $('tbody').sortable({
      cursor: 'move',
      axis: 'y',
      update: function (event, ui) {
        var order = $(this).sortable("serialize");
        var section = $(this).data('section');
        order += '&section=' + section;
        console.log(section);
        console.log(order);

        //send ajax request
        $.ajax({
          url    : sortURL,
          type   : 'POST',
          data   : order,
          success: function (data) {
            console.log(data);
            if(data.success){
              $("#message").html('Sorted');
              $("#message").addClass('text-green');
            }else{
              $("#message").html('Something wrong');
              $("#message").addClass('text-red');
            }
          }

        });
      }
    });

    $('#qModal').on('shown.bs.modal', function(event) {
      var button = $(event.relatedTarget) // Button that triggered the modal
      var formData = button.data('answers')
      var qid = button.data('qid') // Extract info from data-* attributes
      var qnum = button.data('qnum')
      var question = button.data('question')
      var double = button.data('double')
      var optional = button.data('optional')
      var report = button.data('report')
      var sort = button.data('sort')
      var section = button.data('section')
      var layout = button.data('layout')
      var actionurl = button.data('qurl')
      var method = button.data('method')
      // If necessary, you could initiate an AJAX request here (and then do the updating in a callback).
      // Update the modal's content. We'll use jQuery here, but you could use a data binding library or other methods instead.
      var modal = $(this)
      modal.find( "input[name='qnum']" ).val(qnum)
      modal.find( "input[name='question']" ).val(question)
      modal.find( "input[name='double_entry']").prop('checked', double)
      modal.find( "input[name='optional']").prop('checked', optional)
      modal.find( "input[name='report']").prop('checked', report)
      if(sort) {
      modal.find( "input[name='sort']" ).val(sort)
      }
      modal.find( "input[name='section']" ).val(section)
      modal.find( "select[name='layout']" ).val(layout)
      modal.find( "input[name='_method']" ).val(method)
      $('#qModalLabel').text(question);
      var fbEditor = $(document.getElementById('fb-editor'));

      var options = {
        showActionButtons: false, // defaults: true
        editOnAdd: true,
        stickyControls: true,
        dataType: 'json',
        controlOrder: [
        'checkbox',
        'radio',
        'radio-group',
        'text',
        'date',
        'number',
        'textarea'
        ],
        disableFields: ['autocomplete', 'button', 'header', 'checkbox-group', 'file', 'paragraph', 'hidden', 'select'],
        typeUserEvents: {
          text: {
            onadd: function (fld) {
              $('.name-wrap', fld).remove();
              $('.required-wrap', fld).remove();
              $('.access-wrap', fld).remove();
            },
            onclone: function (fld) {
              $('.name-wrap', fld).remove();
              $('.required-wrap', fld).remove();
              $('.access-wrap', fld).remove();
            }
          },
          date: {
            onadd: function (fld) {
              $('.name-wrap', fld).remove();
              $('.required-wrap', fld).remove();
              $('.access-wrap', fld).remove();
            },
            onclone: function (fld) {
              $('.name-wrap', fld).remove();
              $('.required-wrap', fld).remove();
              $('.access-wrap', fld).remove();
            }
          },
          number: {
            onadd: function (fld) {
              $('.name-wrap', fld).remove();
              $('.required-wrap', fld).remove();
              $('.access-wrap', fld).remove();
            },
            onclone: function (fld) {
              $('.name-wrap', fld).remove();
              $('.required-wrap', fld).remove();
              $('.access-wrap', fld).remove();
            }
          },
          checkbox: {
            onadd: function (fld) {
              $('.name-wrap', fld).remove();
              $('.required-wrap', fld).remove();
              $('.access-wrap', fld).remove();
              $('.toggle-wrap', fld).remove();
            },
            onclone: function (fld) {
              $('.name-wrap', fld).remove();
              $('.required-wrap', fld).remove();
              $('.access-wrap', fld).remove();
              $('.toggle-wrap', fld).remove();
            }
          },
          radio: {
            onadd: function (fld) {
              $('.name-wrap', fld).remove();
              $('.required-wrap', fld).remove();
              $('.access-wrap', fld).remove();
              $('.toggle-wrap', fld).remove();
            },
            onclone: function (fld) {
              $('.name-wrap', fld).remove();
              $('.required-wrap', fld).remove();
              $('.access-wrap', fld).remove();
              $('.toggle-wrap', fld).remove();
            }
          },
          'radio-group': {
            onadd: function (fld) {
              $('.name-wrap', fld).remove();
              $('.required-wrap', fld).remove();
              $('.access-wrap', fld).remove();
              $('.toggle-wrap', fld).remove();
            },
            onclone: function (fld) {
              $('.name-wrap', fld).remove();
              $('.required-wrap', fld).remove();
              $('.access-wrap', fld).remove();
              $('.toggle-wrap', fld).remove();
            }
          },
          textarea: {
            onadd: function (fld) {
              $('.name-wrap', fld).remove();
              $('.required-wrap', fld).remove();
              $('.access-wrap', fld).remove();
            },
            onclone: function (fld) {
              $('.name-wrap', fld).remove();
              $('.required-wrap', fld).remove();
              $('.access-wrap', fld).remove();
            }
          },
          template: {
            onadd: function (fld) {
              $('.name-wrap', fld).remove();
              $('.required-wrap', fld).remove();
              $('.access-wrap', fld).remove();
            },
            onclone: function (fld) {
              $('.name-wrap', fld).remove();
              $('.required-wrap', fld).remove();
              $('.access-wrap', fld).remove();
            }
          }
        },
        typeUserAttrs: {
          text: {
            skip: {
              label: 'Skip',
              type: 'text',
              name: 'skip',
              placeholder: 'Space seperated list of Question Number'
            },
            goto: {
              label: 'Go to',
              type: 'text',
              name: 'goto',
              placeholder: 'Single Question Number'
            },
            optional: {
              label: 'Optional',
              type: 'checkbox',
              name: 'optional'
            },
            other: {
              label: 'Show Other Textbox',
              type: 'checkbox',
              name: 'other'
            }
          },
          date: {
            skip: {
              label: 'Skip',
              type: 'text',
              name: 'skip',
              placeholder: 'Space seperated list of Question Number'
            },
            goto: {
              label: 'Go to',
              type: 'text',
              name: 'goto',
              placeholder: 'Single Question Number'
            },
            optional: {
              label: 'Optional',
              type: 'checkbox',
              name: 'optional'
            },
            other: {
              label: 'Show Other Textbox',
              type: 'checkbox',
              name: 'other'
            }
          },
          number: {
            skip: {
              label: 'Skip',
              type: 'text',
              name: 'skip',
              placeholder: 'Space seperated list of Question Number'
            },
            goto: {
              label: 'Go to',
              type: 'text',
              name: 'goto',
              placeholder: 'Single Question Number'
            },
            optional: {
              label: 'Optional',
              type: 'checkbox',
              name: 'optional'
            },
            other: {
              label: 'Show Other Textbox',
              type: 'checkbox',
              name: 'other'
            }
          },
          checkbox: {
            skip: {
              label: 'Skip',
              type: 'text',
              name: 'skip',
              placeholder: 'Space seperated list of Question Number'
            },
            goto: {
              label: 'Go to',
              type: 'text',
              name: 'goto',
              placeholder: 'Single Question Number'
            },
            optional: {
              label: 'Optional',
              type: 'checkbox',
              name: 'optional'
            },
            value: {
              type: 'number',
              placeholder: 'Only number allow'
            },
            other: {
              label: 'Show Other Textbox',
              type: 'checkbox',
              name: 'other'
            }
          },
          radio: {
            skip: {
              label: 'Skip',
              type: 'text',
              name: 'skip',
              placeholder: 'Space seperated list of Question Number'
            },
            goto: {
              label: 'Go to',
              type: 'text',
              name: 'goto',
              placeholder: 'Single Question Number'
            },
            optional: {
              label: 'Optional',
              type: 'checkbox',
              name: 'optional'
            },
            value: {
              type: 'number',
              placeholder: 'Only number allow'
            },
            other: {
              label: 'Show Other Textbox',
              type: 'checkbox',
              name: 'other'
            }
          },
          'radio-group': {
            optional: {
              label: 'Optional',
              type: 'checkbox',
              name: 'optional'
            }
          },
          textarea: {
            optional: {
              label: 'Optional',
              type: 'checkbox',
              name: 'optional'
            }
          }
        },
        defaultFields: formData
      };

      var formBuilder = fbEditor.formBuilder(options).data('formBuilder');

      $('#saveQuest').on('click',function(e){
        e.preventDefault();
        var payload;
        var message;
        payload = formBuilder.formData;
        modal.find('input[name="raw_ans"]').val(payload)
        $.ajax({
          url: actionurl,
          type: 'POST',
          cache: false,
          data: $( "#qModalForm" ).serialize(),
          success: function(data) {
            button.attr('data-answers',data.data.answers);
            $('#ajaxMesg').text(data.message).addClass('text-success').removeClass('hidden').fadeOut(1400);

          },
          error: function(data) {
            if(data.status == '401')
              message = "Your session has expired. You need to log in again!"
            else
              message = data.message

            $('#ajaxMesg').text(message).addClass('text-danger').removeClass('hidden').fadeOut(1400);
          },
          complete: function(){
            window.beforeunload = function(){ return void 0;}
            resetForm($( "#qModalForm" ))
            setTimeout(function(){
              window.location.reload();
            }, 1800);
          }
        });
        return false;
      });

    }).on('hidden.bs.modal', function () {
      $("#fb-editor").empty()
    })


  });


</script>
@endsection


@push('before-body-end')
<style type="text/css">
.invalid {
    border: 1px solid red;
}
.hf-warning {
  color:red;
}
</style>
<script type="text/javascript">
    (function($) {
        $('form.translation').submit(function(e){
                          $.ajax({
                                type: $(this).attr('method'),
                                url: $(this).attr('action'),
                                data: $(this).serialize(),
                                success: function (data) {
                                    alert('OK. Translation saved!');
                                }
                            });
                            e.preventDefault();
                        });
        $(':input').on('keyup change',function(){
            var input = $(this)[0];
            var parent = $(this).parent();
            var validity = input.checkValidity();

            if(validity) {
                $(this).removeClass('invalid');
            } else {
                $(this).addClass('invalid');
            }
            input.reportValidity();
        });
    })(jQuery);
    </script>
@endpush
