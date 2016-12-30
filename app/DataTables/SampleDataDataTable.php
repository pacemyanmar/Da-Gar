<?php

namespace App\DataTables;

use App\Models\SampleData;
use Yajra\Datatables\Services\DataTable;

class SampleDataDataTable extends DataTable
{

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function ajax()
    {
        return $this->datatables
            ->eloquent($this->query())
            ->addColumn('action', 'sample_datas.datatables_actions')
            ->make(true);
    }

    /**
     * Get the query object to be processed by datatables.
     *
     * @return \Illuminate\Database\Query\Builder|\Illuminate\Database\Eloquent\Builder
     */
    public function query()
    {
        $sampleDatas = SampleData::query();

        return $this->applyScopes($sampleDatas);
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
            ->addAction(['width' => '10%'])
            ->ajax('')
            ->parameters([
                'dom' => 'Bfrtip',
                'scrollX' => true,
                'buttons' => [
                    'print',
                    'reset',
                    'reload',
                    [
                        'extend' => 'collection',
                        'text' => '<i class="fa fa-download"></i> Export',
                        'buttons' => [
                            'csv',
                            'excel',
                            'pdf',
                        ],
                    ],
                    'colvis',
                ],
            ]);
    }

    /**
     * Get columns.
     *
     * @return array
     */
    private function getColumns()
    {
        return [
            'idcode' => ['name' => 'idcode', 'data' => 'idcode'],
            'type' => ['name' => 'type', 'data' => 'type'],
            'name' => ['name' => 'name', 'data' => 'name'],
            'gender' => ['name' => 'gender', 'data' => 'gender'],
            'nrc_id' => ['name' => 'nrc_id', 'data' => 'nrc_id'],
            'dob' => ['name' => 'dob', 'data' => 'dob'],
            'father' => ['name' => 'father', 'data' => 'father'],
            'mother' => ['name' => 'mother', 'data' => 'mother'],
            'address' => ['name' => 'address', 'data' => 'address'],
            'village' => ['name' => 'village', 'data' => 'village'],
            'village_tract' => ['name' => 'village_tract', 'data' => 'village_tract'],
            'township' => ['name' => 'township', 'data' => 'township'],
            'district' => ['name' => 'district', 'data' => 'district'],
            'state' => ['name' => 'state', 'data' => 'state'],
            'parent_id' => ['name' => 'parent_id', 'data' => 'parent_id'],
            'created_at' => ['name' => 'created_at', 'data' => 'created_at'],
            'updated_at' => ['name' => 'updated_at', 'data' => 'updated_at'],
        ];
    }

    /**
     * Get filename for export.
     *
     * @return string
     */
    protected function filename()
    {
        return 'sampleDatas';
    }
}
