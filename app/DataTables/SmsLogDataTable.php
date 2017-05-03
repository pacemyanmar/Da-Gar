<?php

namespace App\DataTables;

use App\Models\SmsLog;
use Yajra\Datatables\Services\DataTable;

class SmsLogDataTable extends DataTable
{

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function ajax()
    {
        return $this->datatables
            ->eloquent($this->query())
        //->addColumn('action', 'sms_logs.datatables_actions')
            ->make(true);
    }

    /**
     * Get the query object to be processed by datatables.
     *
     * @return \Illuminate\Database\Query\Builder|\Illuminate\Database\Eloquent\Builder
     */
    public function query()
    {
        $smsLogs = SmsLog::query();
        $smsLogs->orderBy('created_at', 'desc');
        return $this->applyScopes($smsLogs);
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
        //->addAction(['width' => '10%', 'title' => trans('messages.action')])
            ->ajax('')
            ->parameters([
                'dom' => 'Bfrtip',
                'scrollX' => false,
                'processing' => false,
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
            //'id' => ['name' => 'id', 'data' => 'id'],
            //'service_id' => ['name' => 'service_id', 'data' => 'service_id'],
            'from_number' => ['name' => 'from_number', 'data' => 'from_number', 'width' => 150],
            'to_number' => ['name' => 'to_number', 'data' => 'to_number', 'width' => 150],
            'form_code' => ['name' => 'form_code', 'data' => 'form_code', 'width' => 100],
            'content' => ['name' => 'content', 'data' => 'content', "render" => function () {
                return "function ( data, type, full, meta ) {
                                    return data
                                  }, createdCell: function (td, cellData, rowData, row, col) { if(rowData.status == 'error') { $(td).addClass('danger'); } }"; // this is really dirty hack to work createdCell
            }],
            'status_message' => ['name' => 'status_message', 'data' => 'status_message'],
        ];
    }

    /**
     * Get filename for export.
     *
     * @return string
     */
    protected function filename()
    {
        return 'smsLogs';
    }
}
