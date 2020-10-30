@extends('layouts.app')
@php
    $colors = [
    "#3366CC","#DC3912","#FF9900","#a300e0",
    "#109618","#990099", "#ffe200","#00e0d8",
    "#00edff","#ff00fc","#91ff8e","#ff7225",
    "#aa8bff","#ff00e1","#bc6f51","#fff",
    "#ddd","#aaa","#888","#444","#111",
    "#ddd","#aaa","#888","#444","#111",
    "#ddd","#aaa","#888","#444","#111",
    "#ddd","#aaa","#888","#444","#111",
    "#ddd","#aaa","#888","#444","#111",
    "#ddd","#aaa","#888","#444","#111",
    "#ddd","#aaa","#888","#444","#111",
    "#ddd","#aaa","#888","#444","#111",
    "#ddd","#aaa","#888","#444","#111",
    "#ddd","#aaa","#888","#444","#111",
    "#ddd","#aaa","#888","#444","#111",
    "#ddd","#aaa","#888","#444","#111",
    "#ddd","#aaa","#888","#444","#111",

    ];
    $hasradio = false;
@endphp
@section('content')
    <section class="content-header">
        <h1>
            {!! Form::label('name', $project->project) !!}
        </h1>
    </section>
    <div class="content">
        @if(!$project->sections->isEmpty())
            @foreach($project->sections as $section_key => $section)
                @php
                    //section as css class name
                    $sectionClass = str_slug($section['sectionname'], $separator = "-");
                    $editing = true;
                    $section_table = 'pj_s' . $section->sort;
                @endphp
                <div class="panel panel-primary">
                    <div class="panel-heading">
                        <div class="panel-title">
                            {!! $section->sectionname !!}
                            <small> {!! (!empty($section->descriptions))?" | ".$section->descriptions:"" !!}</small>
                        </div>
                    </div>
                    <div class="panel-body">
                        <table class="table table-responsive" id="questions-table">
                            <thead>
                            <th class="col-xs-1">{!! trans('messages.no_') !!}</th>
                            <th class="col-xs-11">{!! trans('messages.question') !!}</th>
                            </thead>
                            <tbody data-section="{!! $section->id !!}">
                            @foreach($section->questions as $question)
                                @if(!$question->surveyInputs->whereIn('type',['radio','checkbox'])->isEmpty())
                                    @if(!in_array($question->layout,['household']))
                                    <tr id="sort-{!! $question->id !!}">
                                        <td class="col-xs-1" id="{!! $question->css_id !!}">
                                            <label>{!! $question->qnum !!}</label>

                                            @if($question->report)
                                                <span class="badge">{!! trans('messages.in_report') !!}</span>
                                            @endif
                                            @if($question->double_entry)
                                                <span class="badge">{!! trans('messages.double_entry') !!}</span>
                                            @endif
                                        </td>
                                        <td class="col-xs-11">
                                            <div class="row"><label>{!! $question->question !!}</label></div>
                                            <div class="row">
                                                @php
                                                    /**
                                                     * count answers
                                                     * set array of css class based on column count
                                                     */
                                                    $surveyInputs = $question->surveyInputs;
                                                    // reindex collection array
                                                    //$surveyInputs = array_values($surveyInputs);
                                                @endphp
                                                @if($question->layout == 'ballot')
                                                    Ballot table
                                                @else
                                                    @push('d3-js')
                                                        var d3{!! $question->id !!}Data=[];
                                                    @endpush
                                                    <div class="col-sm-3">
                                                        <ul class="list-group">
                                                            @foreach ($surveyInputs as $k => $element)
                                                                @if($element->value)
                                                                    <li class="list-group-item"><span class="badge"
                                                                                                      style="background-color: {{ $colors[$k] }};  min-height:10px;">&nbsp</span>
                                                                        ({{$element->value}}) {{ $element->label }}
                                                                        <a href="{{ route('projects.surveys.index', $project->id) }}/?column={{ $element->inputid }}&value={{ $element->value }}&sect={{ $section_table }}">
                                                                            @if( $element->type == 'radio' )
                                                                                @if($results->{strtolower($question->qnum).'_reported'} && is_numeric($element->value))
                                                                                    ( {{ $results->{$element->inputid.'_'.$element->value} }}
                                                                                    - {{ number_format(($results->{$element->inputid.'_'.$element->value} * 100)/ ($results->{strtolower($question->qnum).'_reported'})??1, 2, '.', '') }}
                                                                                    % )

                                                                                    @push('d3-js')
                                                                                        var data{!! $element->inputid.'_'.$element->value !!} = {
                                                                                            label:"({!! $element->value !!})",
                                                                                            color:"{!! $colors[$k] !!}",
                                                                                            value: {{ number_format(($results->{$element->inputid.'_'.$element->value} * 100)/ ($results->{strtolower($question->qnum).'_reported'})??1, 2, '.', '') }}
                                                                                        }
                                                                                        d3{!! $question->id !!}Data.push(data{!! $element->inputid.'_'.$element->value !!});
                                                                                    @endpush
                                                                                @else
                                                                                    0 %
                                                                                @endif
                                                                            @else
                                                                                @if($results->{strtolower($question->qnum).'_reported'} && is_numeric($element->value))
                                                                                    ( {{ $results->{$element->inputid.'_'.$element->value} }} )

                                                                                    @push('d3-js')
                                                                                        var data{!! $element->inputid.'_'.$element->value !!} = {
                                                                                            label:"({!! $element->value !!})",
                                                                                            color:"{!! $colors[$k] !!}",
                                                                                            value: {{ number_format($results->{$element->inputid.'_'.$element->value}, 2, '.', '') }}
                                                                                        }
                                                                                        d3{!! $question->id !!}Data.push(data{!! $element->inputid.'_'.$element->value !!});
                                                                                    @endpush
                                                                                @else
                                                                                    0 %
                                                                                @endif
                                                                            @endif
                                                                        </a>
                                                                    </li>
                                                                    @if( $element->type == 'radio')
                                                                        @php
                                                                            ${$question->qnum.'hasradio'} = $question->qnum;
                                                                        @endphp
                                                                    @endif
                                                                @endif
                                                            @endforeach
                                                            @if(isset(${$question->qnum.'hasradio'}))
                                                                    <li class="list-group-item">
                                                                        Missing from to be reported<a
                                                                                href="{{ route('projects.surveys.index', $project->id) }}/?column={{ $element->inputid }}&sect={{ $section_table }}&value=NULL">
                                                                            ( {{ $results->{'q'.strtolower($question->qnum).'_none'} }}
                                                                            - {{ number_format(($results->{'q'.strtolower($question->qnum).'_none'} * 100)/ ($results->total)??1, 2, '.', '') }}
                                                                            % ) </a>

                                                                        {{--@push('d3-js')--}}
                                                                        {{--var data{!! 'q'.$question->qnum.'_none' !!} = {label:"Missing", color:"#000",value: {{ number_format(($results->{'q'.$question->qnum.'_none'} * 100)/ $results->total, 2, '.', '') }} }--}}
                                                                        {{--d3{!! $question->id !!}Data.push(data{!! 'q'.$question->qnum.'_none' !!});--}}
                                                                        {{--@endpush--}}
                                                                    </li>
                                                                    <li class="list-group-item">
                                                                        Total reported  - ( {{ number_format($results->total - $results->{'q'.strtolower($question->qnum).'_none'}, 2, '.', '') }} )
                                                                    </li>
                                                                    <li class="list-group-item">
                                                                        Total to be reported  - ( {{ number_format($results->total, 2, '.', '') }} )
                                                                    </li>
                                                            @endif
                                                        </ul>
                                                    </div>
                                                    <div class="col-sm-9" id="d3-{!! $question->id !!}">

                                                        @push('d3-js')
                                                            @if(isset(${$question->qnum.'hasradio'}))
                                                                var d3{!! $question->id !!}svg = d3.select("#d3-{!! $question->id !!}").append("svg").attr("width",700).attr("height",300);
                                                                d3{!! $question->id !!}svg.append("g").attr("id","d3{!! $question->id !!}Donut");

                                                                Donut3D.draw("d3{!! $question->id !!}Donut",d3{!! $question->id !!}Data, 250, 150, 130, 100, 30, 0);
                                                            @else
                                                                D3Bar.draw("#d3-{!! $question->id !!}",d3{!! $question->id !!}Data);
                                                            @endif
                                                        @endpush

                                                    </div>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endif
                                @endif
                            @endforeach
                            </tbody>
                        </table>

                    </div>
                </div>
            @endforeach
        @endif
    </div>
@endsection

@section('scripts')
    <script type='text/javascript'>
        !function () {
            var Donut3D = {};

            function pieTop(d, rx, ry, ir) {
                if (d.endAngle - d.startAngle == 0) return "M 0 0";
                var sx = rx * Math.cos(d.startAngle),
                    sy = ry * Math.sin(d.startAngle),
                    ex = rx * Math.cos(d.endAngle),
                    ey = ry * Math.sin(d.endAngle);
                var ret = [];
                ret.push("M", sx, (sy)?sy:0.0000001, "A", rx, ry, "0", (d.endAngle - d.startAngle > Math.PI ? 1 : 0), "1", ex, ey, "L", ir * ex, ir * ey);
                ret.push("A", ir * rx, ir * ry, "0", (d.endAngle - d.startAngle > Math.PI ? 1 : 0), "0", ir * sx, ir * sy, "z");
                return ret.join(" ");
            }

            function pieOuter(d, rx, ry, h) {
                var startAngle = (d.startAngle > Math.PI ? Math.PI : d.startAngle);
                var endAngle = (d.endAngle > Math.PI ? Math.PI : d.endAngle);
                var sx = rx * Math.cos(startAngle),
                    sy = ry * Math.sin(startAngle),
                    ex = rx * Math.cos(endAngle),
                    ey = ry * Math.sin(endAngle);
                var ret = [];
                ret.push("M", sx, h + sy, "A", rx, ry, "0 0 1", ex, h + ey, "L", ex, ey, "A", rx, ry, "0 0 0", sx, sy, "z");
                return ret.join(" ");
            }

            function pieInner(d, rx, ry, h, ir) {
                var startAngle = (d.startAngle < Math.PI ? Math.PI : d.startAngle);
                var endAngle = (d.endAngle < Math.PI ? Math.PI : d.endAngle);
                var sx = ir * rx * Math.cos(startAngle),
                    sy = ir * ry * Math.sin(startAngle),
                    ex = ir * rx * Math.cos(endAngle),
                    ey = ir * ry * Math.sin(endAngle);
                var ret = [];
                ret.push("M", sx, sy, "A", ir * rx, ir * ry, "0 0 1", ex, ey, "L", ex, h + ey, "A", ir * rx, ir * ry, "0 0 0", sx, h + sy, "z");
                return ret.join(" ");
            }

            function getPercent(d) {
                return (d.endAngle - d.startAngle > 0.2 ?
                    Math.round(100000 * (d.endAngle - d.startAngle) / (Math.PI * 2)) / 1000 + '%' : '');
            }

            Donut3D.transition = function (id, data, rx, ry, h, ir) {
                function arcTweenInner(a) {
                    var i = d3.interpolate(this._current, a);
                    this._current = i(0);
                    return function (t) {
                        return pieInner(i(t), rx + 0.5, ry + 0.5, h, ir);
                    };
                }

                function arcTweenTop(a) {
                    var i = d3.interpolate(this._current, a);
                    this._current = i(0);
                    return function (t) {
                        return pieTop(i(t), rx, ry, ir);
                    };
                }

                function arcTweenOuter(a) {
                    var i = d3.interpolate(this._current, a);
                    this._current = i(0);
                    return function (t) {
                        return pieOuter(i(t), rx - .5, ry - .5, h);
                    };
                }

                function textTweenX(a) {
                    var i = d3.interpolate(this._current, a);
                    this._current = i(0);
                    return function (t) {
                        return 0.6 * rx * Math.cos(0.5 * (i(t).startAngle + i(t).endAngle));
                    };
                }

                function textTweenY(a) {
                    var i = d3.interpolate(this._current, a);
                    this._current = i(0);
                    return function (t) {
                        return 0.6 * rx * Math.sin(0.5 * (i(t).startAngle + i(t).endAngle));
                    };
                }

                var _data = d3.layout.pie().sort(null).value(function (d) {
                    return d.value;
                })(data);
                d3.select("#" + id).selectAll(".innerSlice").data(_data)
                    .transition().duration(750).attrTween("d", arcTweenInner);
                d3.select("#" + id).selectAll(".topSlice").data(_data)
                    .transition().duration(750).attrTween("d", arcTweenTop);
                d3.select("#" + id).selectAll(".outerSlice").data(_data)
                    .transition().duration(750).attrTween("d", arcTweenOuter);
                d3.select("#" + id).selectAll(".percent").data(_data).transition().duration(750)
                    .attrTween("x", textTweenX).attrTween("y", textTweenY).text(getPercent);
            }
            Donut3D.draw = function (id, data, x /*center x*/, y/*center y*/,
                                     rx/*radius x*/, ry/*radius y*/, h/*height*/, ir/*inner radius*/) {
                var _data = d3.layout.pie().sort(null).value(function (d) {
                    return d.value;
                })(data);
                var slices = d3.select("#" + id).append("g").attr("transform", "translate(" + x + "," + y + ")")
                    .attr("class", "slices");
                slices.selectAll(".innerSlice").data(_data).enter().append("path").attr("class", "innerSlice")
                    .style("fill", function (d) {
                        return d3.hsl(d.data.color).darker(0.7);
                    })
                    .attr("d", function (d) {
                        return pieInner(d, rx + 0.5, ry + 0.5, h, ir);
                    })
                    .each(function (d) {
                        this._current = d;
                    });
                slices.selectAll(".topSlice").data(_data).enter().append("path").attr("class", "topSlice")
                    .style("fill", function (d) {
                        return d.data.color;
                    })
                    .style("stroke", function (d) {
                        return d.data.color;
                    })
                    .attr("d", function (d) {
                        return pieTop(d, rx, ry, ir);
                    })
                    .each(function (d) {
                        this._current = d;
                    });
                slices.selectAll(".outerSlice").data(_data).enter().append("path").attr("class", "outerSlice")
                    .style("fill", function (d) {
                        return d3.hsl(d.data.color).darker(0.7);
                    })
                    .attr("d", function (d) {
                        return pieOuter(d, rx - .5, ry - .5, h);
                    })
                    .each(function (d) {
                        this._current = d;
                    });
                slices.selectAll(".percent").data(_data).enter().append("text").attr("class", "percent")
                    .attr("x", function (d) {
                        return 0.6 * rx * Math.cos(0.5 * (d.startAngle + d.endAngle));
                    })
                    .attr("y", function (d) {
                        return 0.6 * ry * Math.sin(0.5 * (d.startAngle + d.endAngle));
                    })
                    .text(getPercent).each(function (d) {
                    this._current = d;
                });
            }
            this.Donut3D = Donut3D;
            var margin = {top: 40, right: 20, bottom: 30, left: 40},
                width = 700 - margin.left - margin.right,
                height = 300 - margin.top - margin.bottom;
            var formatPercent = d3.format(".00%");
            var x = d3.scale.ordinal()
                .rangeRoundBands([0, width], .1);
            var y = d3.scale.linear()
                .range([height, 0]);
            var xAxis = d3.svg.axis()
                .scale(x)
                .orient("bottom");
            var yAxis = d3.svg.axis()
                .scale(y)
                .orient("left")
                .ticks(10);
            var D3Bar = {};
            // load the data
            D3Bar.draw = function (id, data) {
                // add the SVG element
                var svg = d3.select(id).append("svg")
                    .attr("width", width + margin.left + margin.right)
                    .attr("height", height + margin.top + margin.bottom)
                    .append("g")
                    .attr("transform",
                        "translate(" + margin.left + "," + margin.top + ")");
                x.domain(data.map(function (d) {
                    return d.label;
                }));
                y.domain([0, d3.max(data, function (d) {
                    return d.value;
                })]);
                svg.append("g")
                    .attr("class", "x axis")
                    .attr("transform", "translate(0," + height + ")")
                    .call(xAxis);
                svg.append("g")
                    .attr("class", "y axis")
                    .call(yAxis)
                    .append("text")
                    .attr("transform", "rotate(-90)")
                    .attr("y", 6)
                    .attr("dy", ".71em")
                    .style("text-anchor", "end")
                    .text("Frequency");
                svg.selectAll(".bar")
                    .data(data)
                    .enter().append("rect")
                    .attr("class", "bar")
                    .attr("x", function (d) {
                        return x(d.label);
                    })
                    .attr("width", x.rangeBand())
                    .attr("y", function (d) {
                        return y(d.value);
                    })
                    .attr("height", function (d) {
                        return height - y(d.value);
                    })
                    .style("fill", function (d) {
                        return d.color
                    });
                svg.selectAll(".bartext")
                    .data(data)
                    .enter()
                    .append("text")
                    .attr("class", "bartext")
                    .attr("text-anchor", "middle")
                    .attr("fill", "white")
                    .attr("x", function (d, i) {
                        return x(d.label) + x.rangeBand() / 2;
                    })
                    .attr("y", function (d, i) {
                        return y(d.value) + 20;
                    })
                    .text(function (d) {
                        return d.value
                    });
            }
            this.D3Bar = D3Bar;
        }();
    </script>
@endsection
@push('before-head-end')
    <style type="text/css">
        .axis {
            font: 10px sans-serif;
        }

        .axis path,
        .axis line {
            fill: none;
            stroke: #000;
            shape-rendering: crispEdges;
        }
    </style>
@endpush
