<div class="modal fade user-import flat" tabindex="-1" role="dialog" aria-labelledby="userImport">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">User Import From CSV</h4>
            </div>
            <div class="modal-body">
                {!! Form::open(['route' => 'users.store','files' => true]) !!}
                {{ csrf_field() }}

                <input type="file" name="usercsv">
                <hr>
                <p>Default User Role is <b>Data Entry Clerk</b></p>
                <ul class="list-inline">
                    <li class="list-inline-item"><b>Header Format:</b></li>
                    <li class="list-inline-item">code,</li>
                    <li class="list-inline-item">name,</li>
                    <li class="list-inline-item">username,</li>
                    <li class="list-inline-item">email,</li>
                    <li class="list-inline-item">password</li>
                </ul>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default flat" data-dismiss="modal">Close</button>
                {!! Form::submit('Import CSV', ['class' => 'btn btn-primary','name'=> 'submit']) !!}
            </div>
            {!! Form::close() !!}
        </div>
    </div>
</div>