<div id="checktable">
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
      columns:['name','father','address', 'edit'],
      options: {
        initFilters: 'GENERIC',
        params: {'all':true},        
        texts:{
          count:'Showing {from} to {to} of {count} records|{count} records|One record',
          filter:'Search for records: ',
          filterPlaceholder:'Search query',
          limit:'Records:',
          noResults:'No matching records',
          page:'Page:', // for dropdown pagination, 
          filterBy: 'Filter by {column}', // Placeholder for search fields when filtering by column
          loading:'Loading...', // First request to server
          defaultOption:'Select {column}' // default option for list filters
        },
        templates: {
          edit: function(createElement, row) {
            return createElement('a', {
                attrs:{
                    'href': '{!! url('projects/'.$project->id.'/surveys'); !!}/'+row.id+'/create/voter'
                }
            }, 'Open');
          }
        }
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