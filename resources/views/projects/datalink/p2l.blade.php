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
        templates: {
          edit: function(createElement, row) {
            return createElement('a', {
                attrs:{
                    'href': '{!! route('voters.index') !!}/'+row.id
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