<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Laravel</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <!-- Scripts -->
    <script>
        window.Laravel = {!!json_encode([
    'csrfToken' => csrf_token(),
]) !!}
    </script>

</head>
<body>


<div class="row">
    <div id="app" class="col-xs-12">
        <line-chart :chart-data="datacollection"></line-chart>
    </div>
</div>

<script src="{{ mix('/js/manifest.js') }}"></script>
<script src="{{ mix('/js/vendor.js') }}"></script>


<!-- app script -->
<script src="{{ mix('/js/app.js') }}"></script>
<script>
    var app = new Vue({
        el: '#app',
        components: {LineChart},
        data: function() {
            return {
                datacollection: null,
                reports: null,
                date: null
            }
        },
        created: function(){
            this.fillData();
        },
        mounted () {
            this.fillData()
        },
        methods: {
            fillData() {
                axios.get('/api/v1/smscount')
                    .then(response => {
                       // console.log(response.data.data)
                        let results = response.data.data
                        let dateresult = results.map(a => a.time)
                        let report_count = results.map(a => a.count)

                        this.reports = report_count

                        this.date = dateresult
                        this.datacollection = {
                            labels: this.date,
                            datasets: [
                                {
                                    label: 'Reports Count',
                                    backgroundColor: '#f87979',
                                    data: this.reports
                                }
                            ]
                        }
                    })
                    .catch(error => {
                        console.log(error)
                    })

                console.log(this.datacollection);
            }
        }
    });
</script>
</body>
</html>
