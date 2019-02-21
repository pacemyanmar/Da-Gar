@extends('layouts.app')

@section('content')
    <section class="content-header">
        <h1>
            Location Meta
        </h1>
   </section>
   <div class="content">
       @include('adminlte-templates::common.errors')
       <div class="clearfix"></div>

       @include('flash::message')

       <div class="clearfix"></div>
       <div class="box box-primary">
           <div class="box-body">
               <div class="row">
                   {!! Form::open(['route' => ['location-metas.edit-structure', $project->id], 'method' => 'patch']) !!}

                        @include('location_metas.fields-structure')

                   {!! Form::close() !!}
               </div>
           </div>
       </div>
   </div>
@endsection