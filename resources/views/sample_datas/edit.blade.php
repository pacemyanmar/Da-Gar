@extends('layouts.app')

@section('content')
    <section class="content-header">
        <h1>
            Sample Data
        </h1>
   </section>
   <div class="content">
       @include('adminlte-templates::common.errors')
       <div class="box box-primary">
           <div class="box-body">
               <div class="row">
                   {!! Form::model($sampleData, ['route' => ['sampleDatas.update', $sampleData->id], 'method' => 'patch']) !!}

                        @include('sample_datas.fields')

                   {!! Form::close() !!}
               </div>
           </div>
       </div>
   </div>
@endsection