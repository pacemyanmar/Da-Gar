<?php

namespace App\DataTables;

use App\Models\LogicalCheck;
use Form;
use Yajra\DataTables\Services\DataTable;

class LogicalCheckDataTable extends DataTable
{

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function ajax()
    {
        return datatables()
            ->eloquent($this->query())
            ->addColumn('action', 'logical_checks.datatables_actions')
            ->make(true);
    }

    /**
     * Get the query object to be processed by datatables.
     *
     * @return \Illuminate\Database\Query\Builder|\Illuminate\Database\Eloquent\Builder
     */
    public function query()
    {
        $logicalChecks = LogicalCheck::query();

        return $this->applyScopes($logicalChecks);
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
                    //'print',
                    //'reset',
                    //'reload',
//                    [
//                         'extend'  => 'collection',
//                         'text'    => '<i class="fa fa-download"></i> Export',
//                         'buttons' => [
//                             'csv',
//                             'excel',
//                             'pdf',
//                         ],
//                    ],
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
            'leftval' => ['name' => 'leftval', 'data' => 'leftval'],
            'rightval' => ['name' => 'rightval', 'data' => 'rightval'],
            'operator' => ['name' => 'operator', 'data' => 'operator'],
            'scope' => ['name' => 'scope', 'data' => 'scope']
        ];
    }

    /**
     * Get filename for export.
     *
     * @return string
     */
    protected function filename()
    {
        return 'logicalChecks';
    }
}
