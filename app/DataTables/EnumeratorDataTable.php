<?php

namespace App\DataTables;

use App\Models\Enumerator;
use Form;
use Yajra\Datatables\Services\DataTable;

class EnumeratorDataTable extends DataTable
{

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function ajax()
    {
        return $this->datatables
            ->eloquent($this->query())
            ->addColumn('action', 'enumerators.datatables_actions')
            ->make(true);
    }

    /**
     * Get the query object to be processed by datatables.
     *
     * @return \Illuminate\Database\Query\Builder|\Illuminate\Database\Eloquent\Builder
     */
    public function query()
    {
        $enumerators = Enumerator::query();

        return $this->applyScopes($enumerators);
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
            'idcode' => ['name' => 'idcode', 'data' => 'idcode'],
            'name' => ['name' => 'name', 'data' => 'name'],
            'gender' => ['name' => 'gender', 'data' => 'gender'],
            'nrc_id' => ['name' => 'nrc_id', 'data' => 'nrc_id'],
            'dob' => ['name' => 'dob', 'data' => 'dob'],
            'address' => ['name' => 'address', 'data' => 'address']
        ];
    }

    /**
     * Get filename for export.
     *
     * @return string
     */
    protected function filename()
    {
        return 'enumerators';
    }
}
