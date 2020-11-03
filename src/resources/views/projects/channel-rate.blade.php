@extends('layouts.app')

@section('content')
    <div class="content">
        <div class="clearfix"></div>

        @include('flash::message')

        <div class="clearfix"></div>
        <div class="row">
        <div id="plot"></div>
        <div id="plot2"></div>
        </div>
    </div>

@foreach($project->sections as $section)
    <div id="section{{$section->sort}}"></div>
@endforeach
@endsection
@section('scripts')
<script src="https://cdn.plot.ly/plotly-latest.min.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
<script>
function getFirstData(update) {
        var ajaxurl = "{{ route('project.responses', [$project->id], false) }}";

        var start_time = "{!! $start_time !!}"
        jQuery.ajax({
            type: "get",
            url: ajaxurl,
            dataType : 'JSON',
            cache: "false",
            success: function(response){
                if(response.success) {
                    var ySMS = [0];
                    var yWeb = [0];

                    var sms_channel_time = [start_time];
                    var web_channel_time = [start_time];

                    jQuery.each(response.data, function (index, value) {

                        if(value.channel == 'sms') {
                            sms_channel_time.push(value.sms_time_slice);
                            ySMS.push(value.sms_channel_count);

                        }

                        if(value.channel == 'web') {
                            web_channel_time.push(value.web_time_slice);
                            yWeb.push(value.web_channel_count);
                        }

                    });
                    trace1 = {
                        type: "scatter",
                        mode: "line",
                        name: 'SMS',
                        x: sms_channel_time,
                        y: ySMS,
			            //text: ySMS,
			            marker: {size: ySMS, color: '#17BECF'},
                        //line: {color: '#17BECF'}
                    }

                    trace2 = {
                        type: "scatter",
                        mode: "line",
                        name: 'Web',
                        x: web_channel_time,
                        y: yWeb,
			            //text: yWeb,
			            marker: {size: yWeb, color: '#111111'},
                        //line: {color: '#7F7F7F'}
                    }
                    drawChart(trace1,trace2, update);
                }
            }
        });

    }
    var trace1 = {};
    var trace2 = {};
    getFirstData(false);

    function drawChart(trace1, trace2, update) {
        var data = [trace1,trace2];
        console.log(data);

        var layout = {
            title: "{{ preg_replace('/\s+/', ' ', $project->project) }} response rate by report channel",
            xaxis: {
                autorange: true,
            },
            yaxis: {
                autorange: true,
                range: [0, 50],
                //type: 'linear'
            }
        };
        if(update){
            Plotly.restyle('plot2', 'y', [[value]]);
        } else {
            Plotly.newPlot('plot2', data, layout, {scrollZoom: true, displayModeBar: false});
        }
        
    }

    setInterval(function(){getFirstData(true);}, 3000);





    function drawSectionChart(section_data, section_title, section_id) {
        var data = [section_data];
        console.log(data);

        var layout = {
            title: section_title,
            xaxis: {
                autorange: true,
            },
            yaxis: {
                autorange: true,
                range: [0, 50],
                type: 'linear'
            }
        };

        Plotly.newPlot(section_id, data, layout, {scrollZoom: true,displayModeBar: false});
    }

    function getSectionData(ajaxurl, section_title, section_id) {

        var start_time = "{!! $start_time !!}"

        jQuery.ajax({
            type: "get",
            url: ajaxurl,
            dataType : 'JSON',
            cache: "false",
            success: function(response){
                if(response.success) {
                    var ySMS = [0];

                    var sms_channel_time = [start_time];

                    jQuery.each(response.data, function (index, value) {

                            sms_channel_time.push(value.time);
                            ySMS.push(value.count);

                    });
                    section_data = {
                        type: "scatter",
                        mode: "line",
                        name: 'SMS',
                        x: sms_channel_time,
                        y: ySMS,
			//text: ySMS,
			marker: {size: ySMS, color: '#17BECF'},
                        //line: {color: '#17BECF'}
                    }

                    drawSectionChart(section_data, section_title, section_id);
                }
            }
        });

    }

@foreach($project->sections as $section)
            var section_data{{$section->sort}} = {};

            getSectionData("{{ route('smscount', [$project->id, $section->sort], false) }}", "{{ $section->sectionname }}", "section{{$section->sort}}");

           setInterval(function(){getSectionData("{{ route('smscount', [$project->id, $section->sort], false) }}", "{{ $section->sectionname }}", "section{{$section->sort}}");}, 10000);
@endforeach
    </script>
@endsection
