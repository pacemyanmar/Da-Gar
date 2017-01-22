<?php

namespace App\DataTables;

use App\Models\Sample;
use Illuminate\Support\Facades\DB;
use Yajra\Datatables\Services\DataTable;

class DoubleResponseDataTable extends DataTable
{
    private $project;

    private $section;

    public function setProject($project)
    {
        $this->project = $project;
        return $this;
    }

    public function setSection($section)
    {
        $this->section = $section;
        return $this;
    }
    /**
     * Display ajax response.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function ajax()
    {
        return $this->datatables
            ->eloquent($this->query())
            ->addIndexColumn()
        //->addColumn('action', 'path.to.action.view')
            ->make(true, true);
    }

    /**
     * Get the query object to be processed by dataTables.
     *
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder|\Illuminate\Support\Collection
     */
    public function query()
    {
        $project = $this->project;
        $section = $this->section;
        $sample = Sample::query();
        $project_inputs = $project->load(['inputs' => function ($query) use ($section) {
            return $query->where('survey_inputs.section', $section);
        }])->inputs;
        $origin_db = $project->dbname;
        $double_db = $project->dbname . '_double';
        $columns = [];
        foreach ($project_inputs as $input) {
            $column = $input->inputid;
            $columnName = preg_replace('/s[0-9]+/', '', $column, 1);
            $oriCol = 'ori_' . $columnName;
            $douCol = 'dou_' . $columnName;
            $columns[] = $origin_db . '.' . $column . ' AS ' . $oriCol . ',' . $double_db . '.' . $column . ' AS ' . $douCol . ', IF(' . $origin_db . '.' . $column . ' = ' . $double_db . '.' . $column . ', TRUE, FALSE) AS ' . $columnName;
        }

        $select_columns = implode(',', $columns);
        $sample->select('samples.id as samples_id', 'sample_datas.idcode', 'samples.form_id', DB::raw($select_columns));

        $sample->leftjoin('sample_datas', function ($join) {
            $join->on('samples.sample_data_id', 'sample_datas.id');
        });

        $sample->leftjoin($project->dbname, function ($join) use ($project) {
            $join->on('samples.id', '=', $project->dbname . '.sample_id');
        })
            ->leftjoin($project->dbname . '_double', function ($join) use ($project) {
                $join->on('samples.id', '=', $project->dbname . '_double.sample_id');
            })
            ->where('project_id', $project->id);

        return $this->applyScopes($sample);
    }

    /**
     * Optional method if you want to use html builder.
     *
     * @return \Yajra\Datatables\Html\Builder
     */
    public function html()
    {
        $tableAttributes = [
            'class' => 'table table-striped table-bordered',
        ];
        return $this->builder()
            ->setTableAttributes($tableAttributes)
            ->columns($this->getColumns())
            ->ajax([
                'type' => 'POST',
                'headers' => [
                    'X-CSRF-TOKEN' => csrf_token(),
                ],

                'data' => '{"_method":"GET"}',
            ])
        //->addAction(['width' => '80px'])
            ->parameters($this->getBuilderParameters());
    }

    protected function getBuilderParameters()
    {

        $rowCallBack = "function( row, data, index ) {";

        $project = $this->project;
        $section = $this->section;
        $project_inputs = $project->load(['inputs' => function ($query) use ($section) {
            return $query->where('survey_inputs.section', $section);
        }])->inputs;

        foreach ($project_inputs as $input) {
            $column = $input->inputid;
            $columnName = preg_replace('/s[0-9]+/', '', $column, 1);
            $ori = 'ori_' . $columnName;
            $dou = 'dou_' . $columnName;
            $rowCallBack .= "
                            if( data.$ori == data.$dou ) {
                                $('.$columnName').css('background-color', 'green');
                            }else{
                                $('.$columnName').css('background-color', 'red');
                            }";
        }
        $rowCallBack .= "}";

        return [
            'dom' => 'Brtip',
            'ordering' => false,
            'searching' => false,
            //'autoWidth' => false,
            //'sServerMethod' => 'POST',
            'scrollX' => true,
            //'fixedColumns' => true,
            'language' => [
                "decimal" => trans('messages.decimal'),
                "emptyTable" => trans('messages.emptyTable'),
                "info" => trans('messages.info'),
                "infoEmpty" => trans('messages.infoEmpty'),
                "infoFiltered" => trans('messages.infoFiltered'),
                "infoPostFix" => trans('messages.infoPostFix'),
                "thousands" => trans('messages.thousands'),
                "lengthMenu" => trans('messages.lengthMenu'),
                "loadingRecords" => trans('messages.loadingRecords'),
                "processing" => trans('messages.processing'),
                "search" => trans('messages.search'),
                "zeroRecords" => trans('messages.zeroRecords'),
                "paginate" => [
                    "first" => trans('messages.paginate.first'),
                    "last" => trans('messages.paginate.last'),
                    "next" => trans('messages.paginate.next'),
                    "previous" => trans('messages.paginate.previous'),
                ],
                "aria" => [
                    "sortAscending" => trans('messages.aria.sortAscending'),
                    "sortDescending" => trans('messages.aria.sortDescending'),
                ],
                "buttons" => [
                    'print' => trans('messages.print'),
                    'reset' => trans('messages.reset'),
                    'reload' => trans('messages.reload'),
                    'export' => trans('messages.export'),
                    'colvis' => trans('messages.colvis'),
                ],
            ],
            'buttons' => [
                'print',
                'reset',
                'reload',
                [
                    'extend' => 'collection',
                    'text' => '<i class="fa fa-download"></i> ' . trans('messages.export'),
                    'buttons' => [
                        'csv',
                        'excel',
                        'pdf',
                    ],
                ],
                'colvis',
            ],
            'initComplete' => "function () {
                            this.api().columns(['0,1']).every(function () {
                                var column = this;
                                var br = document.createElement(\"br\");
                                var input = document.createElement(\"input\");
                                input.className = 'form-control input-sm';
                                input.style.width = '90%';
                                $(br).appendTo($(column.header()));
                                $(input).appendTo($(column.header()))
                                .on('change', function () {
                                    column.search($(this).val(), false, false, true).draw();
                                });
                            });
                        }",
            'drawCallback' => function () {return "function(){
                $('.usethis').on('click', function(e){
                        if(!confirm('" . trans('messages.are_you_sure') . "')) return;
                        var request = $.ajax({
                                type: 'GET',
                                url: $(this).data('url'),
                                data: $(this).serialize(),
                                success: function (data) {
                                    alert('OK. Data updated!');
                                }
                            });
                        request.fail(function( jqXHR, textStatus ) {
                            alert(jqXHR.responseJSON.message);
                        });
                        e.preventDefault();
                        LaravelDataTables['dataTableBuilder'].ajax.reload();
                    });
            }";},
            // /'createdRow' => function () use ($rowCallBack) {return $rowCallBack;},
        ];

    }

    /**
     * Get columns.
     *
     * @return array
     */
    protected function getColumns()
    {
        $project = $this->project;
        $section = $this->section;
        $project_inputs = $project->load(['inputs' => function ($query) use ($section) {
            return $query->where('survey_inputs.section', $section);
        }])->inputs;
        $origin_db = $project->dbname;
        $double_db = $project->dbname . '_double';
        $columns = ['idcode', 'form_id'];
        $columns['samples_id'] = ['data' => 'samples_id', 'name' => 'samples_id', 'visible' => false, 'orderable' => false];
        $visibality = true;
        $k = 0;
        $baseUrl = url("projects/$project->id");
        foreach ($project_inputs as $input) {

            if ($k > 30) {
                $visibality = false;
            }
            if ($k > 90) {
                break;
            }
            $column = $input->inputid;
            $columnName = preg_replace('/s[0-9]+/', '', $column, 1);

            $ori_render = "function (data, type, full, meta) {
                            if(type === 'display') {
                                if(full.$columnName) {
                                    return '<span class=\'label label-success\'>'+data+'</span>';
                                } else {
                                    return '<span class=\'label label-danger\'>'+data+'</span><button class=\'btn btn-xs btn-warning usethis\' type=\'button\' data-url=\'$baseUrl/useorigin/'+full.samples_id+'/$column\'>Use this</button>';
                                }
                            }
                            return data;
                        }";
            $dou_render = "function (data, type, full, meta) {
                            if(type === 'display') {
                                if(full.$columnName) {
                                    return '<span class=\'label label-success\'>'+data+'</span>';
                                } else {
                                    return '<span class=\'label label-danger\'>'+data+'</span><button class=\'btn btn-xs btn-warning usethis\' type=\'button\' data-url=\'$baseUrl/usedouble/'+full.samples_id+'/$column\'>Use this</button>';
                                }
                            }
                            return data;
                        }";
            $columns['ori_' . $columnName] = [
                'data' => 'ori_' . $columnName,
                'name' => 'ori_' . $columnName,
                'title' => title_case('(1) ' . $columnName),
                'defaultContent' => 'N', 'searchable' => false,
                'visible' => $visibality,
                'className' => trim($columnName),
                'render' => function () use ($ori_render) {
                    return $ori_render;
                },
            ];
            $columns['dou_' . $columnName] = [
                'data' => 'dou_' . $columnName,
                'name' => 'dou_' . $columnName,
                'title' => title_case('(2) ' . $columnName),
                'defaultContent' => 'N',
                'searchable' => false,
                'visible' => $visibality,
                'className' => trim($columnName),
                'render' => function () use ($dou_render) {
                    return $dou_render;
                },
            ];
            $k++;
        }
        return $columns;
    }

    /**
     * Get filename for export.
     *
     * @return string
     */
    protected function filename()
    {
        return 'doubleresponsedatatables_' . time();
    }
}
