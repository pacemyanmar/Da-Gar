@section('css')
    @include('layouts.datatables_css')
    <style>
    .table-bordered > thead > tr > th, .table-bordered > thead > tr > td, .table-bordered > tbody > tr > th, .table-bordered > tbody > tr > td, .table-bordered > tfoot > tr > th, .table-bordered > tfoot > tr > td {
    border: 1px solid #428bca;


}
</style>
@endsection


<table  class="table table-striped table-bordered table-responsive" id="dataTableBuilder" >

<thead>
<tr>
<th rowspan="2">State</th>
<th rowspan="2">Total Forms</th>
<th rowspan="2">Total</th>
@foreach($project->sectionsDb as $key => $section)
@php
	$skey = $key + 1;
	if($filters['section_num'] && $filters['section_num'] != $skey) {
		continue;
	}
@endphp
<th colspan="4" width="250px">{{$section->sectionname}}</th>

@endforeach
</tr>
<tr>
@foreach($project->sectionsDb as $key => $section)
@php
	$skey = $key + 1;
	if($filters['section_num'] && $filters['section_num'] != $skey) {
		continue;
	}
@endphp
<th>C</th>
<th>I</th>
<th>M</th>
<th>E</th>
@endforeach
</tr>
</thead>
    @if($filters['type'] === 'level1')
    <tfoot>
    <tr>
    <td>State</td>
    <td>Total Forms</td>
    <td>Total</td>
    @foreach($project->sectionsDb as $key => $section)
    @php

        $skey = $key + 1;
        if($filters['section_num'] && $filters['section_num'] != $skey) {
            continue;
        }
    @endphp
    <td></td>
    <td></td>
    <td></td>
    <td></td>
    @endforeach
    </tr>
    </tfoot>
    @endif
</table>

@section('scripts')
    @include('layouts.datatables_js')
    {!! $dataTable->scripts() !!}
@endsection
