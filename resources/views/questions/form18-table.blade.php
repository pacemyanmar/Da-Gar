@php
$parties = explode(',', $project->parties); //to remove later
@endphp
<div class="row">
<div class="col-sm-8">
<table class="table table-bordered table-responsive" style="vertical-align:middle">
	<tr valign="bottom">
		<th rowspan="2" height="12">
			<p>Serial</p>
		</th>
		<th rowspan="2">
			<p style="margin-bottom: 0in"><br/>

			</p>
			<p>Candidate</p>
		</th>
		<th rowspan="2">
			<p>Party</p>
		</th>
		<th colspan="2">
			<p>Votes Cast</p>
		</th>
	</tr>
	<tr valign="bottom">
		<th>
			<p>In numbers</p>
		</th>
		<th>
			<p>In words</p>
		</th>
	</tr>
	@foreach($parties as $party)
	<tr valign="top">
		<td>
		</td>
		<td>

		</td>
		<td>
			<p>{!! $party !!}</p>
		</td>
		<td>
			{!! Form::number("result[ballot][".$party."][station]", (isset($results))?Kanaung\Facades\Converter::convert($results->{$party.'_station'},'unicode','zawgyi'):null, ['class' => 'form-control input-sm']) !!}
		</td>
		<td>
		</td>
	</tr>
	@endforeach
	<tr>
		<td colspan="7" width="100%" height="89" valign="top">
			<p>Witnesses</p>
		</td>
	</tr>
</table>
</div>
<div class="col-sm-4">
<table class="table table-bordered table-responsive">
<tr><th colspan="2">Remarks</th></tr>

<tr>
<td>
	<p>1 - Ballots issued on e-day</p>
</td>
<td class="col-sm-5">
	{!! Form::number("result[ballot_remark][rem1]", (isset($results))?Kanaung\Facades\Converter::convert($results->rem1,'unicode','zawgyi'):null, ['class' => 'form-control input-sm']) !!}
</td>
<tr>
<tr>
<td>
	<p>2 - Ballots received for advanced voting</p>
</td>
<td class="col-sm-5">
	{!! Form::number("result[ballot_remark][rem2]", (isset($results))?Kanaung\Facades\Converter::convert($results->rem2,'unicode','zawgyi'):null, ['class' => 'form-control input-sm']) !!}
</td>
<tr>
<tr>
<td>
	<p>3 - Valid</p>
</td>
<td class="col-sm-5">
	{!! Form::number("result[ballot_remark][rem3]", (isset($results))?Kanaung\Facades\Converter::convert($results->rem3,'unicode','zawgyi'):null, ['class' => 'form-control input-sm']) !!}
</td>
<tr>
<tr>
<td>
	<p>4 - Invalid</p>
</td>
<td class="col-sm-5">
	{!! Form::number("result[ballot_remark][rem4]", (isset($results))?Kanaung\Facades\Converter::convert($results->rem4,'unicode','zawgyi'):null, ['class' => 'form-control input-sm']) !!}
</td>
<tr>
<tr>
<td>
	<p>5 - Missing</p>
</td>
<td class="col-sm-5">
	{!! Form::number("result[ballot_remark][rem5]", (isset($results))?Kanaung\Facades\Converter::convert($results->rem5,'unicode','zawgyi'):null, ['class' => 'form-control input-sm']) !!}
</td>
<tr>

</table>
</div>
</div>
