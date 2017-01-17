<?php

namespace App\DataTables;

use App\Models\Sample;
use Illuminate\Support\Facades\DB;
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

        $sectionColumns = [];
        foreach ($project->sections as $k => $section) {
            $sectionColumns[] = 'section' . ($k + 1) . 'status';
        }

        // modify column name to use in sql query TABLE.COLUMN format
        array_walk($sectionColumns, function (&$column, $index) use ($childTable) {
            $columnStr = 'SUM(IF(' . $childTable . '.' . $column . ' = 0, 1, 0)) AS ' . $column . '_missing';
            $columnStr .= ', SUM(IF(' . $childTable . '.' . $column . ' = 1, 1, 0)) AS ' . $column . '_complete';
            $columnStr .= ', SUM(IF(' . $childTable . '.' . $column . ' = 2, 1, 0)) AS ' . $column . '_incomplete';
            $columnStr .= ', SUM(IF(' . $childTable . '.' . $column . ' = 3, 1, 0)) AS ' . $column . '_error';
            $column = $columnStr;
        });

        $sectionColumnsStr = implode(',', $sectionColumns);

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
            $query->select('state', DB::raw('GROUP_CONCAT(DISTINCT user.name) as user_name', 'GROUP_CONCAT(DISTINCT update_user.name) as update_user', 'GROUP_CONCAT(DISTINCT qc_user.name) as qc_user'), DB::raw($sectionColumnsStr));
            $query->leftjoin('sample_datas', function ($join) {
                $join->on('samples.sample_data_id', 'sample_datas.id');
            });

            $query->leftjoin($childTable, function ($join) use ($childTable) {
                $join->on('samples.id', '=', $childTable . '.sample_id');
            });
        }
        $query->groupBy('sample_datas.state');

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
            ->ajax(['type' => 'POST', 'data' => '{"_method":"GET"}'])
            ->parameters($this->getBuilderParameters());
    }

    /**
     * Get default builder parameters.
     *
     * @return array
     */
    protected function getBuilderParameters()
    {
        return [
            'scrollX' => true,
            'buttons' => [
                'create',
                'export',
                'print',
                'reset',
                'reload',
            ],
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

        $columns = [
            //'idcode' => ['data' => 'idcode', 'name' => 'idcode', 'title' => 'ID Code'],
            'state',
            //'user_name' => ['data' => 'user_name', 'name' => 'user.name', 'defaultContent' => 'N/A'],
            //'update_user' => ['data' => 'update_user', 'name' => 'update_user.name', 'defaultContent' => 'N/A'],
        ];

        $sectionColumns = [];
        foreach ($project->sections as $k => $section) {
            $section_key = ($k + 1);
            $section_id = 'section' . $section_key . 'status';
            $sectionname = $section['sectionname'];
            $sectionname = "<span data-toggle='tooltip' data-placement='top' title='$sectionname' data-container='body'> <i class='fa fa-info-circle'></i>Sect$section_key  </span>";

            $complete_img = "<img src='" . asset('images/complete.png') . "'>";
            $incomplete_img = "<img src='" . asset('images/incomplete.png') . "'>";
            $missing_img = "<img src='" . asset('images/missing.png') . "'>";
            $error_img = "<img src='" . asset('images/error.png') . "'>";

            $columns[$section_id . '_complete'] = ['data' => $section_id . '_complete', 'name' => $section_id . '_complete', 'defaultContent' => 'N/A', 'title' => $sectionname . $complete_img, 'searchable' => false];
            $columns[$section_id . '_incomplete'] = ['data' => $section_id . '_incomplete', 'name' => $section_id . '_incomplete', 'defaultContent' => 'N/A', 'title' => $sectionname . $incomplete_img, 'searchable' => false];
            $columns[$section_id . '_missing'] = ['data' => $section_id . '_missing', 'name' => $section_id . '_missing', 'defaultContent' => 'N/A', 'title' => $sectionname . $missing_img, 'searchable' => false];
            $columns[$section_id . '_error'] = ['data' => $section_id . '_error', 'name' => $section_id . '_error', 'defaultContent' => 'N/A', 'title' => $sectionname . $error_img, 'searchable' => false];
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
        return 'sampleresponsedatatables_' . time();
    }
}
