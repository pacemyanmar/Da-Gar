@extends('layouts.app')
@php
    $columns = $dataTable->getColumns()->filter(function ($value, $key) {
        if($value->visible === null || $value->visible === true)
            return true;
        if($value->visible === false)
            return false;
    });
    $columns = $columns->pluck('name','name')->toArray();
    $columnName = array_keys($columns);
        $textColumns = ['idcode', 'name', 'nrc_id', 'form_id'];

        $textColumns = array_intersect_key($columns, array_flip($textColumns));

        $columnName = array_flip($columnName);
        $textColsArr = [];
        foreach ($textColumns as $key => $value) {
            $textColsArr[] = $columnName[$key];
        }

        $selectColumns = ['village', 'village_tract', 'township', 'district', 'state'];

        $selectColumns = array_intersect_key($columns, array_flip($selectColumns));

        $selectColsArr = [];
        foreach ($selectColumns as $key => $value) {
            $selectColsArr[] = $columnName[$key];
        }

        $textCols = implode(',', $textColsArr);
        $selectCols = implode(',', $selectColsArr);

       // dd($project->samplesData->groupBy('state')->keys());

@endphp
@section('content')
    <section class="content-header">
        <a href="{{ route('projects.surveys.index', $project->id) }}" class='btn btn-primary pull-right'>
            <i class="fa fa-refresh" aria-hidden="true"></i> Reset All
        </a>
        <br>
        <h1 class="pull-left">{{ $project->project}}</h1>
    </section>
    <div class="content">
        <div class="clearfix"></div>

        @include('flash::message')

        <div class="clearfix"></div>
        <div class="box box-primary">
            <div class="box-body">
                    @include('projects.table')
            </div>
        </div>
    </div>
@endsection

@push('before-body-end')
    <script type="text/javascript">
        (function($) {
            table = window.LaravelDataTables["dataTableBuilder"];
            window.locations = {!! json_encode($locations) !!};
            console.log(locations);
            table.columns([{!! $selectCols !!}]).every( function ( colIdx ) {
                var column = this;
                var columnName = {!! json_encode(array_flip($columnName)) !!};
                var locations = window.locations;
                //console.log(column);
                // Create the select list and search operation
                //console.log(colIdx);
                var br = document.createElement("br");
                $(br).appendTo($(column.header()));
                var select = $('<select style=\"width:100px !important\" id=\"'+columnName[this.selector.cols]+'\"><option value=\"\"></option></select>')
                    .appendTo(
                        column.header()
                    )
                    .on( 'change', function () {
                        var val = $.fn.dataTable.util.escapeRegex(
                                            $(this).val()
                                        );

                        column
                            .search( val ? '^'+val+'$' : '', true, false )
                            .draw();
                        column.search('');

                    } );

                    select.addClass('form-control input-sm');


                    stateVal = $('#state').val();

                    if(columnName[this.selector.cols] == 'state') {
                        var state = $('#state');
                        var states = locations['allStates'];
                        state.empty();
                        $.each(states,function(k,v){
                            state.append( '<option value=\"'+v+'\">'+v+'</option>' )
                        });
                    }


                    if(columnName[this.selector.cols] == 'district') {
                        var district = $('#district');
                        var districts = locations['allDistricts'];
                        district.empty();
                        $.each(districts,function(k,v){
                            district.append( '<option value=\"'+v+'\">'+v+'</option>' )
                        });
                    }

                    if(columnName[this.selector.cols] == 'township') {
                        var township = $('#township');
                        var townships = locations['allTownships'];
                        township.empty();
                        $.each(townships,function(k,v){
                            township.append( '<option value=\"'+v+'\">'+v+'</option>' )
                        });
                    }

                    if(columnName[this.selector.cols] == 'village_tract') {
                        var village_tract = $('#village_tract');
                        var village_tracts = locations['allVillageTracts'];
                        village_tract.empty();
                        $.each(village_tracts,function(k,v){
                            village_tract.append( '<option value=\"'+v+'\">'+v+'</option>' )
                        });
                    }

                    if(columnName[this.selector.cols] == 'village') {
                        var village = $('#village');
                        var villages = locations['allVillages'];
                        village.empty();
                        $.each(villages,function(k,v){
                            village.append( '<option value=\"'+v+'\">'+v+'</option>' )
                        });
                    }
            } );

            $('#dataTableBuilder').on( 'draw.dt', function () {

                console.log( 'Redraw occurred at: '+new Date().getTime() );
            } );

            $('#state').on('change', function(){
                var stateVal = $(this).val();

                var district = $('#district');
                district.val('');
                district.empty();
                $.each(locations['state'][stateVal]['district'],function(k,v){
                    district.append( '<option value=\"'+v+'\">'+v+'</option>' )
                });

                var township = $('#township');
                township.val('');
                township.empty();
                $.each(locations['state'][stateVal]['township'],function(k,v){
                    township.append( '<option value=\"'+v+'\">'+v+'</option>' )
                });

                var village_tract = $('#village_tract');
                village_tract.val('');
                village_tract.empty();
                $.each(locations['state'][stateVal]['village_tract'],function(k,v){
                    village_tract.append( '<option value=\"'+v+'\">'+v+'</option>' )
                });

                var village = $('#village');
                village.val('');
                village.empty();
                $.each(locations['state'][stateVal]['village'],function(k,v){
                    village.append( '<option value=\"'+v+'\">'+v+'</option>' )
                });
            });

            $('#district').on('change', function(){
                var districtVal = $(this).val();

                var township = $('#township');
                township.val('');
                township.empty();
                $.each(locations['district'][districtVal]['township'],function(k,v){
                    township.append( '<option value=\"'+v+'\">'+v+'</option>' )
                });

                var village_tract = $('#village_tract');
                village_tract.val('');
                village_tract.empty();
                $.each(locations['district'][districtVal]['village_tract'],function(k,v){
                    village_tract.append( '<option value=\"'+v+'\">'+v+'</option>' )
                });

                var village = $('#village');
                village.val('');
                village.empty();
                $.each(locations['district'][districtVal]['village'],function(k,v){
                    village.append( '<option value=\"'+v+'\">'+v+'</option>' )
                });
            });

            $('#township').on('change', function(){
                var townshipVal = $(this).val();

                var village_tract = $('#village_tract');
                village_tract.val('');
                village_tract.empty();
                $.each(locations['township'][townshipVal]['village_tract'],function(k,v){
                    village_tract.append( '<option value=\"'+v+'\">'+v+'</option>' )
                });

                var village = $('#village');
                village.val('');
                village.empty();
                $.each(locations['township'][townshipVal]['village'],function(k,v){
                    village.append( '<option value=\"'+v+'\">'+v+'</option>' )
                });
            });

            $('#village_tract').on('change', function(){
                var village_tractVal = $(this).val();

                var village = $('#village');
                village.val('');
                village.empty();
                $.each(locations['village_tract'][village_tractVal]['village'],function(k,v){
                    village.append( '<option value=\"'+v+'\">'+v+'</option>' )
                });
            });

            console.log($('#state'));
            console.log($('#village'));
        })(jQuery);
    </script>
@endpush
