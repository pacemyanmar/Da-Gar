<div class="modal fade" id="qModal" tabindex="-1" role="dialog" aria-labelledby="qModalLabel">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="qModalLabel">Question</h4>
      </div>
      
      <div class="modal-body"> 
            <div class="row">           
            {!! Form::open(['url' => '#', 'id' => 'qModalForm']) !!}
            <input name="_method" value="" type="hidden">
            @include('questions.fields')
            {!! Form::close() !!}
            </div>
            <div class="row">
            <div id="fb-editor" class="col-xs-12"></div>
            </div>
      </div>
      <div class="modal-footer">
        <span id="ajaxMesg" class="hidden"></span>
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        {!! Form::button('Save', ['class' => 'btn btn-primary', 'id' => 'saveQuest', 'data-dismiss' => 'modal']) !!}
      </div>
      
    </div>
  </div>
</div>