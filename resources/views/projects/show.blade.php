@extends('layouts.app')

@section('content')
    <section class="content-header">
        <h1>
            Project
        </h1>
    </section>
    <div class="content">
        <div class="box box-primary">
            <div class="box-body">
                <div class="row" style="padding-left: 20px">
                    @include('projects.show_fields')
                    <a href="{!! route('projects.index') !!}" class="btn btn-default">Back</a>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
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
        });

</script>
@endsection
