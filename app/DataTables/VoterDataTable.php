<?php

namespace App\DataTables;

use App\Models\Voter;
use Form;
use Yajra\Datatables\Services\DataTable;

class VoterDataTable extends DataTable
{

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function ajax()
    {
        return $this->datatables
            ->eloquent($this->query())
            ->addColumn('action', 'voters.datatables_actions')
            ->make(true);
    }

    /**
     * Get the query object to be processed by datatables.
     *
     * @return \Illuminate\Database\Query\Builder|\Illuminate\Database\Eloquent\Builder
     */
    public function query()
    {
        $voters = Voter::query();

        return $this->applyScopes($voters);
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
            'name' => ['name' => 'name', 'data' => 'name'],
            'gender' => ['name' => 'gender', 'data' => 'gender'],
            'nrc_id' => ['name' => 'nrc_id', 'data' => 'nrc_id'],
            'father' => ['name' => 'father', 'data' => 'father'],
            'mother' => ['name' => 'mother', 'data' => 'mother'],
            'address' => ['name' => 'address', 'data' => 'address'],
            'dob' => ['name' => 'dob', 'data' => 'dob']
        ];
    }

    /**
     * Get filename for export.
     *
     * @return string
     */
    protected function filename()
    {
        return 'voters';
    }
}
