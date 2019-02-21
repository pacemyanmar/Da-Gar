<?php

namespace App\DataTables;

use App\Models\Observer;
use Form;
use Yajra\DataTables\Services\DataTable;

class ObserverDataTable extends DataTable
{

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function ajax()
    {
        return datatables()
            ->eloquent($this->query())
            //->addColumn('action', 'observers.datatables_actions')
            ->make(true);
    }

    /**
     * Get the query object to be processed by datatables.
     *
     * @return \Illuminate\Database\Query\Builder|\Illuminate\Database\Eloquent\Builder
     */
    public function query()
    {
        $observers = Observer::query();

        return $this->applyScopes($observers);
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
            //->addAction(['width' => '10%'])
            ->ajax('')
            ->parameters([
                'dom' => 'Bfrtip',
                'scrollX' => false,
                'buttons' => [
                    //'print',
                    //'reset',
                    //'reload',
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
            'full_name' => ['name' => 'full_name', 'data' => 'full_name'],
            'code' => ['name' => 'code', 'data' => 'code'],
            //'sample_id' => ['name' => 'sample_id', 'data' => 'sample_id'],
            'national_id' => ['name' => 'national_id', 'data' => 'national_id'],
            'phone_1' => ['name' => 'phone_1', 'data' => 'phone_1'],
            'phone_2' => ['name' => 'phone_2', 'data' => 'phone_2'],
            'address' => ['name' => 'address', 'data' => 'address'],
            'language' => ['name' => 'language', 'data' => 'language'],
            'ethnicity' => ['name' => 'ethnicity', 'data' => 'ethnicity'],
            'occupation' => ['name' => 'occupation', 'data' => 'occupation'],
            'gender' => ['name' => 'gender', 'data' => 'gender'],
            'dob' => ['name' => 'dob', 'data' => 'dob'],
            'education' => ['name' => 'education', 'data' => 'education'],
            //'created_at' => ['name' => 'created_at', 'data' => 'created_at'],
            //'updated_at' => ['name' => 'updated_at', 'data' => 'updated_at']
        ];
    }

    /**
     * Get filename for export.
     *
     * @return string
     */
    protected function filename()
    {
        return 'observers';
    }
}
