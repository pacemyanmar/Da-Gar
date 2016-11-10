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
            api-url="{!! route('voters.search') !!}"
            :fields="columns"
            :append-params="moreParams"
            :item-actions="itemActions"
            :load-on-start=false
            pagination-info-no-data-template="No data to display"
        ></vuetable>
    </div>
</div>


@push('vue-scripts')
<script type='text/javascript'>

new Vue({
            el: '#app',       
            data: {
                moreParams: [],
                searchFor: '',
                columns: [
                    'name',
                    {
                        name: 'dob',
                        sortField: 'dob',
                        title: 'Date of birth',
                        callback: 'formatDate|D/MM/Y'
                    },
                    {
                        name:'nrc_id',
                        title: 'NRC ID'
                    },
                    'father',
                    'mother',
                    'address',
                    '__actions'
                ],
                itemActions: [
                    { name: 'select-item', label: '', icon: 'fa fa-check', class: 'btn btn-sm btn-info', extra: {'title': 'Select', 'data-toggle':"tooltip", 'data-placement': "left"} }
                ],
            },
            methods: {
                formatDate: function(value, fmt) {
                    if (value == null) return ''
                    fmt = (typeof fmt == 'undefined') ? 'D MMM YYYY' : fmt
                    return moment(value, 'YYYY-MM-DD').format(fmt)
                },
                setFilter: function() {
                    this.moreParams = [
                        'type=nrc_id&query=' + this.searchFor
                    ]
                    this.$nextTick(function() {
                        this.$broadcast('vuetable:refresh')
                    })
                    $('.vuetable-pagination-info').hide();
                },
                resetFilter: function() {
                    this.searchFor = ''
                    this.setFilter()
                },
            },
            events: {
                'vuetable:action': function(action, data) {
                    if (action == 'select-item') {
                        this.moreParams = [
                            'type=nrc_id&query=' + data.nrc_id
                        ]
                        this.$nextTick(function() {
                            this.$broadcast('vuetable:refresh')
                        })
                        this.searchFor = data.nrc_id
                        $('#search').val(data.nrc_id);
                    } 
                },
                'vuetable:load-error': function(response) {
                    $('.vuetable-pagination-info').show();
                    if (response.status == 400) {
                        this.$broadcast('vuetable:set-options', {
                            paginationInfoNoDataTemplate: response.data.message
                        })
                    } else {
                        this.$broadcast('vuetable:set-options', {
                            paginationInfoNoDataTemplate: response.data.message
                        })
                        console.log(response.data.message)
                    }
                    $('.vuetable tbody tr').remove()
                }
            }
        })
</script>
@endpush