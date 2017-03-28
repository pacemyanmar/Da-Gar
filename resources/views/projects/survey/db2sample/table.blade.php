@section('css')
    @include('layouts.datatables_css')
    <style>
    .table-bordered > thead > tr > th, .table-bordered > thead > tr > td, .table-bordered > tbody > tr > th, .table-bordered > tbody > tr > td, .table-bordered > tfoot > tr > th, .table-bordered > tfoot > tr > td {
    border: 1px solid #428bca;


}
</style>
@endsection


<table  class="table table-striped table-bordered table-responsive" id="dataTableBuilder">

<thead>
<tr>
<th rowspan="2">State</th>
<th rowspan="2" width="70px">Total Forms</th>
<th rowspan="2" width="70px">Total</th>
@foreach($project->sectionsDb as $section)
<th colspan="4" width="250px">{{$section->sectionname}}</th>

@endforeach
</tr>
<tr>
@foreach($project->sectionsDb as $section)
<th>C</th>
<th>I</th>
<th>M</th>
<th>E</th>
@endforeach
</tr>
</thead>
</table>

@section('scripts')
    @include('layouts.datatables_js')
    {!! $dataTable->scripts() !!}
@endsection
