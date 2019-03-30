@extends('layouts.app')

@section('content')
    <section class="content-header" xmlns:v-bind="http://www.w3.org/1999/xhtml"
             xmlns:v-on="http://www.w3.org/1999/xhtml">
        <h1 class="pull-left">Report Monitoring</h1>
    </section>
    <div class="content">
        <div class="clearfix"></div>

        @include('flash::message')

        <div class="clearfix"></div>
            @foreach($questions as $question)
                <div class="panel panel-primary">
                    <div class="panel-heading">
                        <div class="panel-title">
                            {{ $question->qnum }}
                            <small>{{ $question->question }}</small>
                        </div>
                    </div>
                    <div class="panel-body">
                        <table class="table table-responsive" id="questions-table">
                            <thead>
                            @foreach($question->surveyInputs as $option)
                                <th class="col-xs-1">({{ $option->value }}) {{ $option->label }}</th>
                            @endforeach
                            </thead>
                            <tbody>
                            <tr id="{!! strtolower($question->qnum) !!}" >
                            @foreach($question->surveyInputs as $option)
                                <td id="option{{ $option->id }}">
                                    <div  v-for="code in {!! $option->id !!}" :key="code.id">
                                        <a target="_blank" v-bind:href="url(code.sid)" v-if="code" :class="{ 'text-danger pull-right': code.followup === 'new', 'pull-left': code.followup !== 'new' }"  @click="code.followup = 'done'">
                                           @{{ code.scode }} &nbsp;</a>
                                    </div>
                                </td>
                            @endforeach
                            </tr>
                            </tbody>
                        </table>
                    </div>

                    </div>
            @endforeach
    </div>
@endsection
@push('before-body-end')
    <script>
     @foreach($questions as $question)

        new Vue({
            el: '#{!! strtolower($question->qnum) !!}',
            data: {
            @foreach($question->surveyInputs as $k => $option)
                @if($k),@endif{{$option->id}}: []
            @endforeach
            },
            created: function() {
                axios.get('/api/v1/projects/6/incidents').then(response => {
                    @foreach($question->surveyInputs as $k => $option)
                    if (typeof response.data.data.{{ $option->id }} !== 'undefined') {
                        this.{{ $option->id }} = response.data.data.{{ $option->id }}
                    }
                    @endforeach
                    return true;

                });
                window.Echo.channel('reported').listen('ReportedEvent', e => {
                    @foreach($question->surveyInputs as $k => $option)
                    var {{ $option->id }}match = 0;
                    this.{{ $option->id }}.forEach(function(item, index){
                        if(typeof e.data.{{ $option->id }} !== 'undefined') {
                            if (item.id === e.data.{{ $option->id }}.id) {
                                this.$set(this.{{ $option->id }}, index, e.data.{{ $option->id }} );
                                {{ $option->id }}match++;
                            }
                        }
                    }.bind(this));
                    console.log(this.$data);
                    if({{ $option->id }}match === 0 && typeof e.data.{{ $option->id }} !== 'undefined') {
                        this.{{ $option->id }}.push(e.data.{{ $option->id }});
                    }

                    @endforeach
                });
            },
            methods: {
                url: function(code) {
                    return "{!! url('/projects/'.$question->project->id.'/surveys') !!}/" + code + "/create";
                }
            }

        });
     @endforeach



    </script>
@endpush
