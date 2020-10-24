<?php

namespace App\DataTables;

use App\Models\SampleDetails;
use Yajra\DataTables\Services\DataTable;
use Yajra\DataTables\EloquentDataTable;

class SampleDetailsDataTable extends DataTable
{
    private $project;
    public function setProject($project)
    {
        $this->project = $project;
    }
    /**
     * Build DataTable class.
     *
     * @param mixed $query Results from query() method.
     * @return \Yajra\DataTables\DataTableAbstract
     */
    public function dataTable($query)
    {
        $dataTable = new EloquentDataTable($query);

        return $dataTable->addColumn('project_id', $this->project->id)->addColumn('action', 'sample_details.datatables_actions');
    }

    /**
     * Get query source of dataTable.
     *
     * @param \App\Models\Post $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(SampleDetails $model)
    {
        return $model->setTable($this->project->dbname.'_samples')->newQuery();
    }

    /**
     * Optional method if you want to use html builder.
     *
     * @return \Yajra\DataTables\Html\Builder
     */
    public function html()
    {
        return $this->builder()
            ->columns($this->getColumns())
            ->ajax(['type' => 'GET',
                'headers' => [
                    'X-CSRF-TOKEN' => csrf_token(),
                ]
            ])
            ->addAction(['width' => '80px'])
            ->parameters([
                'dom'     => 'Bfrtip',
                'order'   => [[0, 'desc']],
                'buttons' => [
                    'create',
                    'export',
                    'print',
                    'reset',
                    'reload',
                ],
            ]);
    }

    /**
     * Get columns.
     *
     * @return array
     */
    protected function getColumns()
    {
        return $this->project->locationMetas->where('show_index', true)->pluck('field_name')->toArray();
    }

    /**
     * Get filename for export.
     *
     * @return string
     */
    protected function filename()
    {
        return 'sample_detailsdatatable_' . time();
    }
}