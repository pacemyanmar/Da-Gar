@push('before-body-end')
<script type="text/javascript">

    $(document).ready(function(){
        var logicStr =   '<tr class="logic" style="display: table-row;"><td style="vertical-align: middle">';
                logicStr +=  '<input type="text" style="width: 100%" class="form-control leftval"/>';
                logicStr +=  '</td>';
            logicStr +=  '<td style="vertical-align: middle">';
                logicStr +=  '<select class="form-control operator">';
                logicStr += '<option value="muex">Mutual Exclusive</option>';
                logicStr += '</select>';
                logicStr +=  '</td>';
            logicStr +=  '<td style="">';
                logicStr +=  '<input type="text" style="width: 100%" class="form-control rightval"/>';
                logicStr +=  '</td>';
            logicStr +=  '<td style="">';
                logicStr +=  '<select class="form-control scope">';
                logicStr += '<option value="q">In a question</option>';
                logicStr += '</select>';
                logicStr +=  '</td>';
            logicStr +=  '<td style="vertical-align: middle">';
                logicStr +=  '<i onclick="removeItem(this)" class="remove fa fa-trash-o" style="cursor: pointer;font-size: 20px;color: red"></i>';
                logicStr +=  '</td>';
            logicStr +=  '</tr>';

        $("#btnAddLogic").on("click", function () {
        var item = $(logicStr).clone();
        $("#logiccontainer").append(item);
        });

        $("#logicModalForm").find(":input").filter(function(){ return !this.value; }).removeAttr("disabled");
        $("#logicModalForm").on("submit", function(e) {

            $('.logic').each(function (index,value) {
                $(this).find('.leftval').attr('name','logic['+index+'][leftval]');
                $(this).find('.operator').attr('name','logic['+index+'][operator]');
                $(this).find('.rightval').attr('name','logic['+index+'][rightval]');
                $(this).find('.scope').attr('name','logic['+index+'][scope]');
            });

            $(this).find(":input").filter(function(){ return !this.value; }).attr("disabled", "disabled");
        });
    });
</script>
@endpush
<div class="form-group col-sm-12 col-lg-12">
<table class="table table-striped table-bordered" id="logictable">
    <thead class="no-border">
    <tr>
        <th>Left Value</th>
        <th>Operator</th>
        <th>Right Value</th>
        <th>Scope</th>
        <th><i class=" fa fa-plus btn btn-sm btn-success btn-flat btn-green" id="btnAddLogic"></i></th>
    </tr>
    </thead>

    <tbody id="logiccontainer" >

    @foreach($project->logics as $logic)

    <tr class="logic" style="display: table-row;">
        <td style="vertical-align: middle">
            <input value="{!! $logic->leftval !!}" class="form-control leftval" type="text">
        </td>
        <td style="vertical-align: middle">
            {!! Form::select('', ['muex'=>'Mutual Exclusive'], $logic->operator, ['class' => 'form-control operator']) !!}
        </td>
        <td style="vertical-align: middle">
            <input value="{!! $logic->rightval !!}" class="form-control rightval" type="text">
        </td>
        <td style="vertical-align: middle">
            {!! Form::select('', ['q'=>'In a question'], $logic->scope, ['class' => 'form-control scope']) !!}
        </td>
        <td style="vertical-align: middle">
            <i onclick="removeItem(this)" class="remove fa fa-trash-o" style="cursor: pointer;font-size: 20px;color: red;"></i>
        </td>
    </tr>
    @endforeach

    </tbody>
</table>
</div>