<?php

namespace App\DataTables;

use App\Models\Project;
use App\Models\Sample;
use App\Models\SurveyResult;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;
use Yajra\Datatables\Services\DataTable;

class SurveyResultDataTable extends DataTable
{
    protected $project;

    protected $tableColumns;

    protected $tableBaseColumns;

    protected $tableSectionColumns;

    protected $sampleType;

    protected $joinMethod;
    /**
     * Project Setter
     * @param  App\Models\Project $project [Project Models from route]
     * @return $this ( App\DataTables\SurveyResultDataTable )
     */
    public function forProject(Project $project)
    {
        $this->project = $project;
        return $this;
    }

    /**
     * Columns Setter
     * @param array $columns [array of columns to use by datatables]
     * @return $this ( App\DataTables\SurveyResultDataTable )
     */
    public function setColumns($columns)
    {
        $this->tableColumns = $columns;
        return $this;
    }

    /**
     * Columns Setter
     * @param array $columns [array of columns to use by datatables]
     * @return $this ( App\DataTables\SurveyResultDataTable )
     */
    public function setBaseColumns($columns)
    {
        $this->tableBaseColumns = $columns;
        return $this;
    }

    /**
     * Columns Setter
     * @param array $columns [array of columns to use by datatables]
     * @return $this ( App\DataTables\SurveyResultDataTable )
     */
    public function setSectionColumns($columns)
    {
        $this->tableSectionColumns = $columns;
        return $this;
    }

    /**
     * Survey type setter (country|region)
     * @param string $surveyType [country|region]
     * @return $this ( App\DataTables\SurveyResultDataTable )
     */
    public function setSampleType($sampleType)
    {
        $this->sampleType = $sampleType;
        return $this;
    }

    public function setJoinMethod($join)
    {
        $this->joinMethod = $join;
        return $this;
    }

    /**
     * Display ajax response.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function ajax()
    {
        $orderTable = str_plural($this->project->dblink);
        $orderBy = (isset($this->orderBy)) ? $orderTable . '.' . $this->orderBy : $orderTable . '.id';
        $order = (isset($this->order)) ? $this->order : 'asc';

        $table = $this->datatables
            ->eloquent($this->query());
        $table->addColumn('project_id', $this->project->id);

        $table->addColumn('action', 'projects.sample_datatables_actions');

        $table->orderColumn($orderBy, DB::raw('LENGTH(' . $orderBy . ')') . " $1");

        return $table->make(true);
    }

    /**
     * Get the query object to be processed by dataTables.
     *
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder|\Illuminate\Support\Collection
     */
    public function query()
    {
        $auth = Auth::user();
        // create table name
        $table = str_plural($this->project->dblink);
        $orderBy = (isset($this->orderBy)) ? $table . '.' . $this->orderBy : $table . '.id';
        $order = (isset($this->order)) ? $this->order : 'asc';

        // dblink
        $type = $this->project->dblink;

        $joinMethod = (isset($this->joinMethod)) ? $this->joinMethod : 'join';

        // get dblink table base columns
        $tableColumnsArray = array_keys($this->tableBaseColumns);
        // modify column name to use in sql query TABLE.COLUMN format
        array_walk($tableColumnsArray, function (&$column, $index) use ($table) {
            switch ($column) {
                case 'form_id':
                    $column = 'samples.' . $column;
                    break;

                case 'user_id':
                    $column = 'user.name as username';
                    break;

                default:
                    $column = 'sample_datas.' . $column;
                    break;
            }

        });
        // concat all columns with comma
        $dbLinkTableColumns = implode(', ', $tableColumnsArray);

        $project = $this->project;
        $childTable = $project->dbname;
        $sectionColumns = [];
        foreach ($project->sections as $k => $section) {
            $sectionColumns[] = 'section' . ($k + 1) . 'status';
        }

        $input_columns = '';
        // get all inputs for a project form by name key index
        $unique_inputs = $this->project->inputs->pluck('inputid')->unique();

        $unique_inputs = $unique_inputs->toArray();
        $columnsFromResults = array_merge($sectionColumns, $unique_inputs);
        // modify column name to use in sql query TABLE.COLUMN format
        array_walk($columnsFromResults, function (&$column, $index) use ($childTable) {
            $column = $childTable . '.' . $column;
        });

        $input_columns = implode(',', $columnsFromResults);

        $defaultColumns = "samples.id, samples.form_id, sample_datas.idcode, sample_datas.*";
        if ($table == 'enumerators') {

        }
        //$count = sizeof($unique_inputs);
        // run query
        $query = Sample::query();
        $query->leftjoin('users as user', function ($join) {
            $join->on('user.id', 'samples.user_id');
        });
        $query->leftjoin('users as update_user', function ($join) {
            $join->on('update_user.id', 'samples.update_user_id');
        });
        $query->leftjoin('users as qc_user', function ($join) {
            $join->on('qc_user.id', 'samples.qc_user_id');
        });
        if ($this->project->status != 'new') {
            $query->select(DB::raw($defaultColumns), DB::raw($dbLinkTableColumns), DB::raw($input_columns));
            // join with samplable database (voters, enumerators)
            $query->leftjoin('sample_datas', function ($join) {
                $join->on('samples.sample_data_id', 'sample_datas.id');
            });
            // join with result database
            if ($auth->role->role_name == 'doublechecker') {
                $query->join($childTable, function ($join) use ($childTable) {
                    $join->on('samples.id', '=', $childTable . '.sample_id');
                });
            } else {
                $query->{$joinMethod}($childTable, function ($join) use ($childTable) {
                    $join->on('samples.id', '=', $childTable . '.sample_id');
                });
            }
        }

        $filterColumns = Request::get('columns', []);

        foreach ($filterColumns as $index => $column) {
            if (array_key_exists($filterColumns[$index]['name'], $this->tableSectionColumns) && $filterColumns[$index]['search']['value'] != '') {

                $columnName = $filterColumns[$index]['name'];
                $value = $filterColumns[$index]['search']['value'];

                if ($value) {
                    $query->where($columnName, '=', $value);
                } else {
                    $query->where($columnName, '=', $value)->orWhereNull($columnName);
                }
            }
        }

        return $this->applyScopes($query);
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
        $table = $this->builder()
            ->setTableAttributes($tableAttributes)
            ->addAction(['width' => '80px', 'title' => trans('messages.action')])
            ->columns($this->getColumns())
            ->ajax(['type' => 'POST', 'data' => '{"_method":"GET"}']);

        //$table->addAction(['width' => '80px']);

        return $table->parameters($this->getBuilderParameters());
    }

    /**
     * Get columns.
     *
     * @return array
     */
    protected function getColumns()
    {
        if (!empty($this->tableColumns) && is_array($this->tableColumns)) {
            return $this->tableColumns;
        } else {
            return [
                'inputid' => ['name' => 'inputid', 'data' => 'inputid', 'title' => 'No.'],
                'samplable_id' => ['name' => 'samplable_id', 'data' => 'samplable_id', 'title' => 'ID', 'defaultContent' => ''],
                'value' => ['name' => 'value', 'data' => 'value', 'title' => 'Value', 'defaultContent' => ''],
            ];
        }
    }

    /**
     * Get filename for export.
     *
     * @return string
     */
    protected function filename()
    {
        return $this->project->project . time();
    }

    /**
     * Get default builder parameters.
     *
     * @return array
     */
    protected function getBuilderParameters()
    {
        $columnName = array_keys($this->tableColumns);
        $textColumns = ['idcode', 'name', 'nrc_id', 'form_id', 'mobile'];

        $textColumns = array_intersect_key($this->tableColumns, array_flip($textColumns));

        $columnName = array_flip($columnName);
        $textColsArr = [];
        foreach ($textColumns as $key => $value) {
            $textColsArr[] = $columnName[$key] + 1;
        }

        $selectColumns = ['village', 'village_tract', 'township', 'district', 'state'];

        $selectColumns = array_intersect_key($this->tableColumns, array_flip($selectColumns));
        $selectColsArr = [];
        foreach ($selectColumns as $key => $value) {
            $selectColsArr[] = $columnName[$key] + 1;
        }

        $statusColumns = array_intersect_key($this->tableColumns, $this->tableSectionColumns);
        $statusColsArr = [];
        foreach ($statusColumns as $key => $value) {
            $statusColsArr[] = $columnName[$key] + 1;
        }

        $textCols = implode(',', $textColsArr);
        $selectCols = implode(',', $selectColsArr);
        $statusCols = implode(',', $statusColsArr);

        return [
            'dom' => 'Brtip',
            'ordering' => false,
            'authWidth' => false,
            //'sServerMethod' => 'POST',
            'scrollX' => true,
            'fixedColumns' => true,
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
                //'reset',
                'reload',
                [
                    'extend' => 'collection',
                    'text' => '<i class="fa fa-download"></i> ' . trans('messages.export'),
                    'buttons' => [
                        'exportPostCsv',
                        'exportPostExcel',
                        'exportPostPdf',
                    ],
                ],
                'colvis',
            ],
            'initComplete' => "function () {
                            this.api().columns([$textCols,'.result']).every(function () {
                                var column = this;
                                var br = document.createElement(\"br\");
                                var input = document.createElement(\"input\");
                                input.className = 'form-control input-sm';
                                input.style.width = '80px';
                                $(br).appendTo($(column.header()));
                                $(input).appendTo($(column.header()))
                                .on('change', function () {
                                    column.search($(this).val(), false, false, true).draw();
                                });
                            });
                            this.api().columns([$statusCols]).every( function () {
                              var column = this;
                              var select = $('<select style=\"width:80px !important\"><option value=\"\">-</option><option value=\"0\">Missing</option><option value=\"1\">Complete</option><option value=\"2\">incomplete</option><option value=\"3\">Error</option></select>')
                              .appendTo( $(column.header()) )
                              .on( 'change', function () {
                              var val = $.fn.dataTable.util.escapeRegex(
                                          $(this).val()
                                          );

                                  column
                                  .search( val ? val : '', false, false )
                                  .draw();
                              } );
                              select.addClass('form-control input-sm');
                              } );
                        }",
        ];
    }
}
