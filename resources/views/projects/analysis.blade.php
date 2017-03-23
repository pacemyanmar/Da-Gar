@extends('layouts.app')
@php
$colors = ["#3366CC","#DC3912","#FF9900","#a300e0","#109618","#990099", "#ffe200","#00e0d8","#00edff","#ff00fc","#91ff8e","#ff7225","#aa8bff","#ff00e1","#bc6f51"];
@endphp
@section('content')
    <section class="content-header">
        <h1>
            {!! Form::label('name', $project->project) !!}
        </h1>
    </section>
    <div class="content">
     @if(!$project->sectionsDb->isEmpty())
      @foreach($project->sectionsDb as $section_key => $section)
      @php
                //section as css class name
                $sectionClass = str_slug($section['sectionname'], $separator = "-");
                $editing = true;
      @endphp
      <div class="panel panel-primary">
        <div class="panel-heading">
          <div class="panel-title">
            {!! $section->sectionname !!} <small> {!! (!empty($section->descriptions))?" | ".$section->descriptions:"" !!}</small>
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
                                    $surveyInputs = $project->inputs->where('question_id', $question->id)->all();
                                    // reindex collection array
                                    $surveyInputs = array_values($surveyInputs);
                                @endphp
                                @if($question->layout == 'ballot')
                                    Ballot table
                                @else
                                @push('d3-js')
                                    var d3{!! $question->id !!}Data=[];
                                @endpush
                                <div class="col-sm-5">
                                <ul class="list-group">
                                @foreach ($surveyInputs as $k => $element)
                                   <li class="list-group-item"> {{ $element->label }} ( {{ number_format(($results->{$element->inputid.'_'.$element->value} * 100)/ $results->total, 2, '.', '') }} % ) <span class="badge" style="background-color: {{ $colors[$k] }};  min-height:10px;">&nbsp</span></li>
                                   @if($results->{$element->inputid.'_'.$element->value})
                                   @push('d3-js')
                                        var data{!! $element->inputid.'_'.$element->value !!} = {label:"{!! $element->label !!}", color:"{!! $colors[$k] !!}", value: {{ number_format(($results->{$element->inputid.'_'.$element->value} * 100)/ $results->total, 2, '.', '') }} }
                                        d3{!! $question->id !!}Data.push(data{!! $element->inputid.'_'.$element->value !!});
                                   @endpush
                                   @endif
                                @endforeach
                                    @push('d3-js')
                                        var data{!! $question->qnum.'_none' !!} = {label:"None", color:"#fff", value: {{ number_format(($results->{'q'.$question->qnum.'_none'} * 100)/ $results->total, 2, '.', '') }} }
                                        d3{!! $question->id !!}Data.push(data{!! $question->qnum.'_none' !!});
                                   @endpush
                                   <li class="list-group-item">
                                   Missing ({{ number_format(($results->{'q'.$question->qnum.'_none'} * 100)/ $results->total, 2, '.', '') }} % )
                                   </li>
                                </ul>
                                </div>
                                <div class="col-sm-7" id="d3-{!! $question->id !!}">
                                    @push('d3-js')

                                    var d3{!! $question->id !!}svg = d3.select("#d3-{!! $question->id !!}").append("svg").attr("width",700).attr("height",300);
                                    d3{!! $question->id !!}svg.append("g").attr("id","d3{!! $question->id !!}Donut");
                                    Donut3D.draw("d3{!! $question->id !!}Donut", d3{!! $question->id !!}Data, 250, 150, 130, 100, 30, 0);
                                    @endpush
                                </div>
                                @endif
                                </div>
                            </td>
                        </tr>
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
!function(){
    var Donut3D={};

    function pieTop(d, rx, ry, ir ){
        if(d.endAngle - d.startAngle == 0 ) return "M 0 0";
        var sx = rx*Math.cos(d.startAngle),
            sy = ry*Math.sin(d.startAngle),
            ex = rx*Math.cos(d.endAngle),
            ey = ry*Math.sin(d.endAngle);

        var ret =[];
        ret.push("M",sx,sy,"A",rx,ry,"0",(d.endAngle-d.startAngle > Math.PI? 1: 0),"1",ex,ey,"L",ir*ex,ir*ey);
        ret.push("A",ir*rx,ir*ry,"0",(d.endAngle-d.startAngle > Math.PI? 1: 0), "0",ir*sx,ir*sy,"z");
        return ret.join(" ");
    }

    function pieOuter(d, rx, ry, h ){
        var startAngle = (d.startAngle > Math.PI ? Math.PI : d.startAngle);
        var endAngle = (d.endAngle > Math.PI ? Math.PI : d.endAngle);

        var sx = rx*Math.cos(startAngle),
            sy = ry*Math.sin(startAngle),
            ex = rx*Math.cos(endAngle),
            ey = ry*Math.sin(endAngle);

            var ret =[];
            ret.push("M",sx,h+sy,"A",rx,ry,"0 0 1",ex,h+ey,"L",ex,ey,"A",rx,ry,"0 0 0",sx,sy,"z");
            return ret.join(" ");
    }

    function pieInner(d, rx, ry, h, ir ){
        var startAngle = (d.startAngle < Math.PI ? Math.PI : d.startAngle);
        var endAngle = (d.endAngle < Math.PI ? Math.PI : d.endAngle);

        var sx = ir*rx*Math.cos(startAngle),
            sy = ir*ry*Math.sin(startAngle),
            ex = ir*rx*Math.cos(endAngle),
            ey = ir*ry*Math.sin(endAngle);

            var ret =[];
            ret.push("M",sx, sy,"A",ir*rx,ir*ry,"0 0 1",ex,ey, "L",ex,h+ey,"A",ir*rx, ir*ry,"0 0 0",sx,h+sy,"z");
            return ret.join(" ");
    }

    function getPercent(d){
        return (d.endAngle-d.startAngle > 0.2 ?
                Math.round(1000*(d.endAngle-d.startAngle)/(Math.PI*2))/10+'%' : '');
    }

    Donut3D.transition = function(id, data, rx, ry, h, ir){
        function arcTweenInner(a) {
          var i = d3.interpolate(this._current, a);
          this._current = i(0);
          return function(t) { return pieInner(i(t), rx+0.5, ry+0.5, h, ir);  };
        }
        function arcTweenTop(a) {
          var i = d3.interpolate(this._current, a);
          this._current = i(0);
          return function(t) { return pieTop(i(t), rx, ry, ir);  };
        }
        function arcTweenOuter(a) {
          var i = d3.interpolate(this._current, a);
          this._current = i(0);
          return function(t) { return pieOuter(i(t), rx-.5, ry-.5, h);  };
        }
        function textTweenX(a) {
          var i = d3.interpolate(this._current, a);
          this._current = i(0);
          return function(t) { return 0.6*rx*Math.cos(0.5*(i(t).startAngle+i(t).endAngle));  };
        }
        function textTweenY(a) {
          var i = d3.interpolate(this._current, a);
          this._current = i(0);
          return function(t) { return 0.6*rx*Math.sin(0.5*(i(t).startAngle+i(t).endAngle));  };
        }

        var _data = d3.layout.pie().sort(null).value(function(d) {return d.value;})(data);

        d3.select("#"+id).selectAll(".innerSlice").data(_data)
            .transition().duration(750).attrTween("d", arcTweenInner);

        d3.select("#"+id).selectAll(".topSlice").data(_data)
            .transition().duration(750).attrTween("d", arcTweenTop);

        d3.select("#"+id).selectAll(".outerSlice").data(_data)
            .transition().duration(750).attrTween("d", arcTweenOuter);

        d3.select("#"+id).selectAll(".percent").data(_data).transition().duration(750)
            .attrTween("x",textTweenX).attrTween("y",textTweenY).text(getPercent);
    }

    Donut3D.draw=function(id, data, x /*center x*/, y/*center y*/,
            rx/*radius x*/, ry/*radius y*/, h/*height*/, ir/*inner radius*/){

        var _data = d3.layout.pie().sort(null).value(function(d) {return d.value;})(data);

        var slices = d3.select("#"+id).append("g").attr("transform", "translate(" + x + "," + y + ")")
            .attr("class", "slices");

        slices.selectAll(".innerSlice").data(_data).enter().append("path").attr("class", "innerSlice")
            .style("fill", function(d) { return d3.hsl(d.data.color).darker(0.7); })
            .attr("d",function(d){ return pieInner(d, rx+0.5,ry+0.5, h, ir);})
            .each(function(d){this._current=d;});

        slices.selectAll(".topSlice").data(_data).enter().append("path").attr("class", "topSlice")
            .style("fill", function(d) { return d.data.color; })
            .style("stroke", function(d) { return d.data.color; })
            .attr("d",function(d){ return pieTop(d, rx, ry, ir);})
            .each(function(d){this._current=d;});

        slices.selectAll(".outerSlice").data(_data).enter().append("path").attr("class", "outerSlice")
            .style("fill", function(d) { return d3.hsl(d.data.color).darker(0.7); })
            .attr("d",function(d){ return pieOuter(d, rx-.5,ry-.5, h);})
            .each(function(d){this._current=d;});

        slices.selectAll(".percent").data(_data).enter().append("text").attr("class", "percent")
            .attr("x",function(d){ return 0.6*rx*Math.cos(0.5*(d.startAngle+d.endAngle));})
            .attr("y",function(d){ return 0.6*ry*Math.sin(0.5*(d.startAngle+d.endAngle));})
            .text(getPercent).each(function(d){this._current=d;});
    }

    this.Donut3D = Donut3D;
}();



</script>
@endsection