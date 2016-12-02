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

    protected $surveyType;
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
     * Survey type setter (voter|location|enumerator)
     * @param string $surveyType [voter|location|enumerator]
     * @return $this ( App\DataTables\SurveyResultDataTable )
     */
    public function setSurveyType($surveyType)
    {
        $this->surveyType = $surveyType;
        return $this;
    }

    /**
     * Display ajax response.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function ajax()
    {
        $table = $this->datatables
            ->eloquent($this->query());
        if (empty($this->surveyType)) {
            $table->addColumn('action', 'projects.sample_datatables_actions');
        }

        return $table->make(true);
    }

    /**
     * Get the query object to be processed by dataTables.
     *
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder|\Illuminate\Support\Collection
     */
    public function query()
    {
        // $this->surveyType = 'voter|location|enumerator'
        if (!empty($this->surveyType) && class_exists('App\Models\\' . ucfirst($this->surveyType))) {

            $input_columns = '';
            foreach ($this->project->inputs as $k => $input) {
                $input_columns .= "MAX(IF(survey_results.inputid = '$input->inputid', survey_results.value, NULL)) AS " . camel_case($input->inputid) . ",";
            }
            //dd($input_columns);
            $class = 'App\Models\\' . ucfirst($this->surveyType);
            $table = str_plural($this->surveyType);
            $type = $this->surveyType;
            $query = $class::query()
                ->select(
                    DB::raw($input_columns . 'GROUP_CONCAT(DISTINCT(samplable_id)) AS id, GROUP_CONCAT(DISTINCT(name)) AS name')
                )
                ->join('survey_results', function ($join) use ($table, $type) {
                    $join->on('survey_results.samplable_id', '=', $table . '.id')->where('survey_results.samplable_type', '=', $type);
                })->groupBy('voters.id');
        } else {
            $query = SurveyResult::query();
            $query->where('project_id', $this->project->id)->with('project');
        }
        //dd($query->get());

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
        if (empty($this->surveyType)) {
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
        return 'surveyresultdatatables_' . time();
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
            'scrollX' => false,
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
                //'colvis'
            ],
        ];
    }
}
