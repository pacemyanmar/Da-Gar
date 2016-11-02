@extends('layouts.app')
@section('meta')
<meta name="csrf-token" content="{{ csrf_token() }}">
@endsection
@section('content')
<section class="content-header">
  <h1>
    Projects
  </h1>
</section>
<div class="content">
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
  {!! Form::open(['url' => '#', 'id' => 'qaForm', 'class'=>'form-horizontal']) !!}
  @if(is_array($project->sections))
  @foreach($project->sections as $section_key => $section)
  <div class="panel panel-primary">
    <div class="panel-heading">
      <div class="panel-title">
        {!! $section['sectionname'] !!} <small> {!! (!empty($section['descriptions']))?" | ".$section['descriptions']:"" !!}</small>
        <span class="pull-right"><a href="#" class='btn btn-success' data-toggle="modal" data-target="#qModal" data-qurl="@{!! route('api.v1.questions.store') !!}" data-section="{!! $section_key !!}" data-method='POST'><i class="glyphicon glyphicon-plus"></i></a></span>
      </div>          
    </div>
    <div class="panel-body">
      @@include('questions.table')
    </div>
  </div> 
  @endforeach
  @endif
  {!! Form::close() !!}
</div>
@@include('questions.modal')
@endsection

@section('scripts')
<script type='text/javascript'>
  
  var formData = {'fields':''}; 

  $(document).ready(function(){

    $.ajaxSetup({
      headers:
      { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
    });
    

    $('#qModal').on('shown.bs.modal', function(event) {
      var button = $(event.relatedTarget) // Button that triggered the modal
      var formData = button.data('answers')
      var qid = button.data('qid') // Extract info from data-* attributes
      var qnum = button.data('qnum')
      var question = button.data('questions')
      var section = button.data('section')
      var layout = button.data('layout')
      var actionurl = button.data('qurl')
      var method = button.data('method')
      // If necessary, you could initiate an AJAX request here (and then do the updating in a callback).
      // Update the modal's content. We'll use jQuery here, but you could use a data binding library or other methods instead.
      var modal = $(this)
      modal.find( "input[name='qnum']" ).val(qnum)
      modal.find( "input[name='questions']" ).val(question)
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
        'radio-group',
        'text',
        'date',
        'number',
        'textarea'
        ],
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
    
    var htmlStr =   '<tr class="item" style="display: table-row;"><td style="vertical-align: middle">';
    htmlStr +=  '<input type="text" style="width: 100%" required class="form-control sectionname"/>';
    htmlStr +=  '</td>';
    htmlStr +=  '<td style="vertical-align: middle">';
    htmlStr +=  '<input type="text" class="form-control descriptions"/>';
    htmlStr +=  '</td>';
    htmlStr +=  '<td style="vertical-align: middle">';
    htmlStr +=  '<i onclick="removeItem(this)" class="remove fa fa-trash-o" style="cursor: pointer;font-size: 20px;color: red"></i>';
    htmlStr +=  '</td>';
    htmlStr +=  '</tr>';

    $("#btnAdd").on("click", function () {
      var item = $(htmlStr).clone();
      $("#container").append(item);
    });

    $(".editProject").on("click", function () {
      $(".toggle").toggle();
    });

    $("#project").on("submit", function(e) {
      $('.item').each(function (index,value) {
        var sectionname = $(this).find('.sectionname').val();
        $(this).find('.sectionname').attr('name','sections['+index+'][sectionname]');
        $(this).find('.descriptions').attr('name','sections['+index+'][descriptions]');
      });
    });

    // For radio button and checkbox styling
    $('input').iCheck({
            checkboxClass: 'icheckbox_square-blue',
            radioClass: 'iradio_square-blue',
            increaseArea: '10%' // optional
        });

  });

  function removeItem(e) {
    e.parentNode.parentNode.parentNode.removeChild(e.parentNode.parentNode);
  }

</script>
@endsection