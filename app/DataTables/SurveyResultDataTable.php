<?php

namespace App\DataTables;

use App\Models\SurveyResult;
use Yajra\Datatables\Services\DataTable;

class SurveyResultDataTable extends DataTable
{
    protected $project;
    /**
     * Project Setter
     * @param  App\Models\Project $project [Project Models from route]
     * @return App\DataTables\SurveyResultDataTable          [this datatable object]
     */
    public function forProject($project) {
            $this->project = $project;
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
            ->addColumn('action', 'projects.sample_datatables_actions')
            ->make(true);
    }

    /**
     * Get the query object to be processed by dataTables.
     *
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder|\Illuminate\Support\Collection
     */
    public function query()
    {
        $query = SurveyResult::query()->where('project_id',$this->project->id)->with('project');
        return $this->applyScopes($query);
    }

    /**
     * Optional method if you want to use html builder.
     *
     * @return \Yajra\Datatables\Html\Builder
     */
    public function html()
    {
        return $this->builder()
                    ->columns($this->getColumns())
                    ->ajax('')
                    ->addAction(['width' => '80px'])
                    ->parameters($this->getBuilderParameters());
    }

    /**
     * Get columns.
     *
     * @return array
     */
    protected function getColumns()
    {
        return [
            'inputid' => ['name' => 'inputid', 'data' => 'inputid', 'title' => 'No.'],
            'samplable_id' => ['name' => 'samplable_id', 'data' => 'samplable_id', 'title' => 'Sample ID'],
        ];
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
                'dom' => 'rtip',
                'scrollX' => false,
                'buttons' => [
                    'print',
                    'reset',
                    'reload',
                    [
                         'extend'  => 'collection',
                         'text'    => '<i class="fa fa-download"></i> Export',
                         'buttons' => [
                             'csv',
                             'excel',
                             'pdf',
                         ],
                    ],
                    //'colvis'
                ]
            ];
    }
}
