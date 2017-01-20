@extends('layouts.app')
@push('before-head-end')
<script type="text/javascript">
window.url="{!! route('projects.surveys.save', ['project' => $project->id, 'sample' => $sample->id]) !!}"
</script>
@endpush
@push('after-body-start')
<a class="btn btn-primary pull-right btn-float btn-float-up save" style="display:inline;margin-right:15px;" href="#" data-id="survey-form"> Save All</a>
           <a class="pull-right btn-float btn-float-bottom btn-float-to-up" style="display:inline;font-size: 40px;" href="#"><i class="fa fa-arrow-circle-up"></i></a>
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
           <a class="btn btn-primary pull-right save" style="display:inline;margin-top: -10px;margin-bottom: 5" href="#" data-id="survey-form"> Save All</a>
        </h1>
    </section>
    <div class="content">
        <div class="clearfix"></div>
        @include('flash::message')

        <div class="clearfix"></div>

        @include('projects.survey.info_table')

        <div id="survey-form">
        @foreach($project->sections as $section_key => $section)
        @php
            //section as css class name
            $sectionClass = str_slug($section['sectionname'], $separator = "-")
        @endphp
        <div class="panel panel-primary" id="{!! $sectionClass !!}">
            <div class="panel-heading">
                <div class="panel-title">
                    {!! $section['sectionname'] !!} <small> {!! (!empty($section['descriptions']))?" | ".$section['descriptions']:"" !!}</small>
                </div>
            </div>
            <div class="panel-body">

                @include('projects.show_fields')

                <h1 class="pull-right">
                   <a class="btn btn-sm btn-info pull-right save" data-id="{!! $sectionClass !!}" style="display:inline;margin-top: -10px;margin-bottom: 5" href="#"> Save this section</a>
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
<style type="text/css">
.invalid {
    border: 1px solid red;
}
.hf-warning {
  color:red;
}
</style>
<script type='text/javascript'>
    (function($) {
        $('form').reset();

        $('.save').click(function(event){
            event.preventDefault();

            var id = $(this).data('id');

            $('#'+id).find(":input").filter(function(){ return !this.value; }).attr("disabled", "disabled");
            var info_data = $('.info').serializeArray();

            var section_data = $('#'+id+' :input').serializeArray();

            section_data.push({name: 'samplable_type', value: $('#sample').val()});

            var ajaxData = $.merge(info_data, section_data);

            sendAjax(url,ajaxData);

            $('#'+id).find(":input").filter(function(){ return !this.value; }).removeAttr("disabled");

        });



        $(':input').on('keyup change',function(){
            var input = $(this)[0];
            var parent = $(this).parent();
            var validity = input.checkValidity();
            //console.log(validity);
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

@if(Auth::user()->role->role_name == 'doublechecker')

@push('document-ready')
$(":input").on('change keyup', function(e){
    var cssid = $(this).attr('id');
    var cssclass = $(this).data('class');
    if($(this).val() != $(this).data('origin')) {
        $('.'+cssclass).addClass('hide');
        $('.'+cssid).removeClass('hide');
        //console.log('data not match ' + cssid + cssclass);
    } else {
        $('.'+cssclass).addClass('hide');
    }
    e.preventDefault();
});
@endpush
@endif
