<div id="checktable">
@if($project->type==="p2l")
@endif
	<div class="form-inline form-group">
        <label>Search:</label>
        <input id="search" v-model="searchFor" class="form-control" @keyup.enter="setFilter" placeholder="Enter NRC ID to Search">
        <button class="btn btn-primary" @click="setFilter">Go</button>
        <button class="btn btn-default" @click="resetFilter">Reset</button>
    </div>

@if($project->type==="l2p")
	voterlist l2p
@endif
	<div class="table-responsive">
        <vuetable
            api-url="{!! route('voterlists.search') !!}"
            :fields="columns"
            :append-params="moreParams"
            :item-actions="itemActions"
            :load-on-start=false
            pagination-info-no-data-template="No data to display"
        ></vuetable>
    </div>
</div>
@section('scripts')
@parent
<script type='text/javascript'>


</script>
@endsection