<div class="modal fade" id="logicModal" tabindex="-1" role="dialog" aria-labelledby="logicModalLabel">
    {!! Form::open(['route' => ['projects.logic', $project->id], 'method' => 'post', 'class' => 'addlogic', 'id' => 'logicModalForm']) !!}
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="logicModalLabel">Logic</h4>
            </div>

            <div class="modal-body">
                <div class="row">

                    @include('projects.logicfields')

                </div>
                <div class="row">
                    <div id="fb-editor" class="col-xs-12"></div>
                </div>
            </div>
            <div class="modal-footer">
                <span id="ajaxMesg" class="hidden"></span>
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                {!! Form::submit('Save', ['class' => 'btn btn-primary', 'id' => 'saveLogics']) !!}
            </div>

        </div>
    </div>
    {!! Form::close() !!}
</div>