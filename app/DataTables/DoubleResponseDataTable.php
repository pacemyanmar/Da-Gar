<?php

namespace App\DataTables;

use App\Models\Sample;
use App\Traits\SurveyQueryTrait;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Yajra\DataTables\Services\DataTable;

class DoubleResponseDataTable extends DataTable
{
    use SurveyQueryTrait;

    protected $project;

    private $section;


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
        return datatables()
            ->eloquent($this->query())
            ->addIndexColumn()
            ->addColumn('project_id', $this->project->id)
            ->addColumn('double', 'double')
            ->addColumn('action', 'projects.sample_datatables_actions')
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
        $section_status_query = [];
        $project_sample_db = $project->dbname.'_samples';
        foreach($this->project->sections as $section) {
            $inputs = $section->inputs->pluck('inputid','inputid')->toArray();

            array_walk($inputs, function(&$input, $key){
                $input = "SUM(".$input.")";
            });
            $section_status_query[$section->id] = implode("+", array_values($inputs)). ' AS section'.$section->sort;
            unset($inputs);
        }

        $select_columns = implode(',', array_values($section_status_query));

        $sample->select('samples.id as samples_id', $project_sample_db.'.id', 'samples.form_id', DB::raw($select_columns));

        $sample->leftjoin($project_sample_db, function ($join) use ($project_sample_db) {
            $join->on('samples.sample_data_id', $project_sample_db.'.id');
        });

        foreach ($project->sections as $k => $section) {
            $viewName = $this->dbname . '_s' . $section->sort.'_view';
            $sample->leftjoin($viewName, function ($join) use ($viewName) {
                $join->on('samples.id', '=', $viewName . '.sample_id');
            });
        }
            $sample->where('samples.project_id', $project->id)
                ->groupBy('samples.id')
                ->groupBy($project_sample_db.'.id')
                ->groupBy('samples.form_id');
            $sample->orderBy($project_sample_db.'.id', 'asc');
            $sample->orderBy('samples.form_id', 'asc');

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
            ->addAction(['width' => '40px', 'title' => trans('messages.action')])
            ->columns($this->getColumns())
            ->ajax([
                'type' => 'POST',
                'headers' => [
                    'X-CSRF-TOKEN' => csrf_token(),
                ],
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
            //'searching' => false,
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
                //'print',
                //'reset',
                //'reload',
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
                            this.api().columns(['1']).every(function () {
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
            'drawCallback' => "function(){
                $(\"td:contains('OK')\").addClass('greenBg');
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
            }",
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
        $project_sample_db = $project->dbname.'_samples';

        $columns['action'] = ['title' => '', 'orderable' => false, 'searchable' => false, 'width' => '5px', 'order' => [[1, 'asc']]];

        $columns['id_code'] = [
            'name' => $project_sample_db.'.id',
            'data' => 'id',
            'title' => trans('samples.id'),
            'orderable' => false,
            'visible' => true,
            'width' => '80px'
        ];
        $columns['form_id'] = [
            'name' => 'samples.form_id',
            'data' => 'form_id',
            'title' => trans('samples.form_id'),
            'orderable' => false,
            'visible' => true,
            'width' => '80px'
        ];
        foreach($project->sections as $section) {
            $columns['section'.$section->sort] = [
                'name' => 'section'.$section->sort,
                'data' => 'section'.$section->sort,
                'title' => 'R'.($section->sort + 1),
                'orderable' => false,
                'visible' => true,
                'width' => '20px',
                "render" => function () {
                    return "function ( data, type, full, meta ) {
                                    if(type == 'display') {
                                        if(data == 0) {
                                            cell = '<i class=\"glyphicon glyphicon-ok text-success\"></i>';
                                        } else if (data === null) {
                                            cell = '<i title=\"Both Missing\" class=\"glyphicon glyphicon-floppy-remove text-danger text-lg\"></i>';                                        
                                        }else {
                                            cell = data + ' <i class=\"glyphicon glyphicon-remove text-danger\"></i>';
                                        }
                                        
                                      return cell;
                                      
                                    } else {
                                      return data;
                                    }
                                  }";
                }
            ];
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
