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
            ->addAction(['width' => '10%', 'title' => trans('messages.action')])
            ->ajax('')
            ->parameters([
                'dom' => 'Bfrtip',
                'scrollX' => true,
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
            'idcode' => ['name' => 'idcode', 'data' => 'idcode', 'title' => trans('messages.idcode')],
            'type' => ['name' => 'type', 'data' => 'type', 'title' => trans('messages.type')],
            'name' => ['name' => 'name', 'data' => 'name', 'title' => trans('messages.name')],
            'gender' => ['name' => 'gender', 'data' => 'gender', 'title' => trans('messages.gender')],
            'nrc_id' => ['name' => 'nrc_id', 'data' => 'nrc_id', 'title' => trans('messages.nrc_id')],
            'dob' => ['name' => 'dob', 'data' => 'dob', 'title' => trans('messages.dob')],
            'father' => ['name' => 'father', 'data' => 'father', 'title' => trans('messages.father')],
            'mother' => ['name' => 'mother', 'data' => 'mother', 'title' => trans('messages.mother')],
            'address' => ['name' => 'address', 'data' => 'address', 'title' => trans('messages.address')],
            'village' => ['name' => 'village', 'data' => 'village', 'title' => trans('messages.village')],
            'village_tract' => ['name' => 'village_tract', 'data' => 'village_tract', 'title' => trans('messages.village_tract')],
            'township' => ['name' => 'township', 'data' => 'township', 'title' => trans('messages.township')],
            'district' => ['name' => 'district', 'data' => 'district', 'title' => trans('messages.district')],
            'state' => ['name' => 'state', 'data' => 'state', 'title' => trans('messages.state')],
            //'parent_id' => ['name' => 'parent_id', 'data' => 'parent_id'],
            //'created_at' => ['name' => 'created_at', 'data' => 'created_at'],
            //'updated_at' => ['name' => 'updated_at', 'data' => 'updated_at'],
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
