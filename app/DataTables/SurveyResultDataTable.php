<?php

namespace App\DataTables;

use App\Models\Project;
use App\Models\SurveyResult;
use Illuminate\Support\Facades\DB;
use Yajra\Datatables\Services\DataTable;

class SurveyResultDataTable extends DataTable
{
    protected $project;

    protected $tableColumns;

    protected $tableBaseColumns;

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

        $project = $this->project;
        foreach ($project->sections as $k => $section) {
            $sectionColumn = 'section' . ($k + 1) . 'status';
            $table->addColumn($sectionColumn, function ($model) use ($project, $sectionColumn) {
                $model = $model->results($project->dbname)->where('project_id', $project->id);
                return $model->first()[$sectionColumn];
            });
        }

        // get all inputs for a project form by name key index
        $unique_inputs = $project->inputs->pluck('inputid')->unique();
        foreach ($unique_inputs as $input) {
            $table->addColumn($input, function ($model) use ($project, $input) {
                $model = $model->results($project->dbname)->where('project_id', $project->id);
                return $model->first()[$input];
            });
        }

        //$table->addColumn('action', 'projects.sample_datatables_actions');

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
        // $this->project->dblink = 'voter|location|enumerator'
        if (!empty($this->project->dblink) && class_exists('App\Models\\' . ucfirst($this->project->dblink))) {

            $input_columns = '';
            // get all inputs for a project form by name key index
            $unique_inputs = $this->project->inputs->pluck('inputid')->unique();

            $input_columns = implode(',', $unique_inputs->toArray());
            //$count = sizeof($unique_inputs);

            // set dblink model class
            $class = 'App\Models\\' . ucfirst($this->project->dblink);

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
                $column = $table . '.' . $column;
            });
            // concat all columns with comma
            $dbLinkTableColumns = implode(', ', $tableColumnsArray);

            $project_id = $this->project->id;
            // run query
            $query = $class::query();
        } else {
            $query = SurveyResult::query();
            $query->where('project_id', $this->project->id)->with('project');
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
        $table = $this->builder()
            ->columns($this->getColumns())
            ->ajax(['type' => 'POST', 'data' => '{"_method":"GET"}']);
        if (empty($this->project->dblink)) {
            $table->addAction(['width' => '80px']);
        }

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
        return [
            'dom' => 'Brtip',
            //'sServerMethod' => 'POST',
            'scrollX' => true,
            'buttons' => [
                'print',
                'reset',
                'reload',
                [
                    'extend' => 'collection',
                    'text' => '<i class="fa fa-download"></i> Export',
                    'buttons' => [
                        'exportPostCsv',
                        'exportPostExcel',
                        'exportPostPdf',
                    ],
                ],
                'colvis',
            ],
        ];
    }
}
