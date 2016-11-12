<div id="checktable">
@if($project->type==="p2l")
@endif
	<div class="form-inline form-group">
        <label>Search:</label>
        <input id="search" class="form-control" @keyup.enter="setFilter" placeholder="Enter NRC ID to Search">
        <button class="btn btn-primary" @click="setFilter">Go</button>
        <button class="btn btn-default" @click="resetFilter">Reset</button>
    </div>

@if($project->type==="l2p")
	voterlist l2p
@endif
	<div class="table-responsive">
          <v-server-table url="{!! route('voters.search') !!}" :columns="columns" :options="options"></v-server-table>
    </div>
</div>


@push('vue-scripts')
<script type='text/javascript'>
options = {}
Vue.use(VueTables.server, [options], false);

new Vue({
    el:"#app",
    data: {
      columns:['name','father','address'],
      options: {
       // see the options API
      }
    },
    methods: {
      formatDate: function(value, fmt) {
          
      },
      setFilter: function() {
          
      },
      resetFilter: function() {
          
      },
    }
});
</script>
@endpush