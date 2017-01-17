<?php

namespace App\DataTables;

use App\Models\Sample;
use Yajra\Datatables\Services\DataTable;

class SampleResponseDataTable extends DataTable
{
    private $project;

    public function setProject($project)
    {
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
            ->make(true, true);
    }

    /**
     * Get the query object to be processed by dataTables.
     *
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder|\Illuminate\Support\Collection
     */
    public function query()
    {
        $query = Sample::query();
        $project = $this->project;
        $childTable = $project->dbname;

        $query->leftjoin('users as user', function ($join) {
            $join->on('user.id', 'samples.user_id');
        });
        $query->leftjoin('users as update_user', function ($join) {
            $join->on('update_user.id', 'samples.update_user_id');
        });
        $query->leftjoin('users as qc_user', function ($join) {
            $join->on('qc_user.id', 'samples.qc_user_id');
        });

        if ($project->status != 'new') {
            $query->select('idcode', 'user.name as user_name', 'update_user.name as update_user', 'qc_user.name as qc_user');
            $query->leftjoin('sample_datas', function ($join) {
                $join->on('samples.sample_data_id', 'sample_datas.id');
            });

            $query->leftjoin($childTable, function ($join) use ($childTable) {
                $join->on('samples.id', '=', $childTable . '.sample_id');
            });
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
        return $this->builder()
            ->columns($this->getColumns())
            ->ajax('')
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
            'idcode',
            'user_name' => ['data' => 'user_name', 'name' => 'user.name'],
            'update_user' => ['data' => 'update_user', 'name' => 'update_user.name'],
        ];
    }

    /**
     * Get filename for export.
     *
     * @return string
     */
    protected function filename()
    {
        return 'sampleresponsedatatables_' . time();
    }
}
