@php
if(isset($sample)) {
	$parties = explode(',', $sample->data->parties);
} else {
	$parties = explode(',', $project->parties); //to remove later
}
@endphp
<div class="row">
<div class="col-sm-8">
<table class="table table-bordered table-responsive" style="vertical-align:middle">
	<tr valign="bottom">
		<th rowspan="2" height="12">
			<p>{!! trans('ballots.serial') !!}</p>
		</th>
		<th rowspan="2">
			<p style="margin-bottom: 0in"><br/>

			</p>
			<p>{!! trans('ballots.candidate') !!}</p>
		</th>
		<th rowspan="2">
			<p>{!! trans('ballots.party') !!}</p>
		</th>
		<th colspan="2">
			<p>{!! trans('ballots.votes_cast') !!}</p>
		</th>
	</tr>
	<tr valign="bottom">
		<th>
			<p>{!! trans('ballots.in_numbers') !!}</p>
		</th>
		<th>
			<p>{!! trans('ballots.in_words') !!}</p>
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
			<p>{!! trans('ballots.witnesses') !!}</p>
		</td>
	</tr>
</table>
</div>
<div class="col-sm-4">
<table class="table table-bordered table-responsive">
<tr><th colspan="2">{!! trans('ballots.remarks') !!}</th></tr>

<tr>
<td>
	<p>{!! trans('ballots.ballots_issued_on_e_day') !!}</p>
</td>
<td class="col-sm-5">
	{!! Form::number("result[ballot_remark][rem1]", (isset($results))?Kanaung\Facades\Converter::convert($results->rem1,'unicode','zawgyi'):null, ['class' => 'form-control input-sm']) !!}
</td>
<tr>
<tr>
<td>
	<p>{!! trans('ballots.ballots_received_for_advanced_voting') !!}</p>
</td>
<td class="col-sm-5">
	{!! Form::number("result[ballot_remark][rem2]", (isset($results))?Kanaung\Facades\Converter::convert($results->rem2,'unicode','zawgyi'):null, ['class' => 'form-control input-sm']) !!}
</td>
<tr>
<tr>
<td>
	<p>{!! trans('ballots.valid') !!}</p>
</td>
<td class="col-sm-5">
	{!! Form::number("result[ballot_remark][rem3]", (isset($results))?Kanaung\Facades\Converter::convert($results->rem3,'unicode','zawgyi'):null, ['class' => 'form-control input-sm']) !!}
</td>
<tr>
<tr>
<td>
	<p>{!! trans('ballots.invalid') !!}</p>
</td>
<td class="col-sm-5">
	{!! Form::number("result[ballot_remark][rem4]", (isset($results))?Kanaung\Facades\Converter::convert($results->rem4,'unicode','zawgyi'):null, ['class' => 'form-control input-sm']) !!}
</td>
<tr>
<tr>
<td>
	<p>{!! trans('ballots.missing') !!}</p>
</td>
<td class="col-sm-5">
	{!! Form::number("result[ballot_remark][rem5]", (isset($results))?Kanaung\Facades\Converter::convert($results->rem5,'unicode','zawgyi'):null, ['class' => 'form-control input-sm']) !!}
</td>
<tr>

</table>
</div>
</div>
