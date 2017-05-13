@extends('layouts.app')

@section('content')
    <section class="content-header">
        <h1>
            Observer
        </h1>
   </section>
   <div class="content">
       @include('adminlte-templates::common.errors')
       <div class="box box-primary">
           <div class="box-body">
               <div class="row">
                   {!! Form::model($observer, ['route' => ['observers.update', $observer->id], 'method' => 'patch']) !!}

                        @include('observers.fields')

                   {!! Form::close() !!}
               </div>
           </div>
       </div>
   </div>
@endsection