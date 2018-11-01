@section('css')
    @include('layouts.datatables_css')
    <style>
        .table-bordered > thead > tr > th, .table-bordered > thead > tr > td, .table-bordered > tbody > tr > th, .table-bordered > tbody > tr > td, .table-bordered > tfoot > tr > th, .table-bordered > tfoot > tr > td {
            border: 1px solid #428bca;
        }
    </style>
@endsection

{!! $dataTable->table(['width' => '100%'], config('sms.response_filter')) !!}

@section('scripts')
    @include('layouts.datatables_js')
    {!! $dataTable->scripts() !!}
@endsection
