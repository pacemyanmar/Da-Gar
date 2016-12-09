<?php

namespace App\DataTables;

use App\Models\SmsLog;
use Form;
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
            ->addColumn('action', 'sms_logs.datatables_actions')
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
            ->addAction(['width' => '10%'])
            ->ajax('')
            ->parameters([
                'dom' => 'Bfrtip',
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
                    'colvis'
                ]
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
            'id' => ['name' => 'id', 'data' => 'id'],
            'service_id' => ['name' => 'service_id', 'data' => 'service_id'],
            'from_number' => ['name' => 'from_number', 'data' => 'from_number'],
            'to_number' => ['name' => 'to_number', 'data' => 'to_number'],
            'name' => ['name' => 'name', 'data' => 'name'],
            'content' => ['name' => 'content', 'data' => 'content'],
            'error_message' => ['name' => 'error_message', 'data' => 'error_message'],
            'search_result' => ['name' => 'search_result', 'data' => 'search_result'],
            'phone' => ['name' => 'phone', 'data' => 'phone']
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
