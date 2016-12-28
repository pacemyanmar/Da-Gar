@extends('layouts.app')

@section('content')
    <section class="content-header">
        <h1>
            Enumerator
        </h1>
   </section>
   <div class="content">
       @include('adminlte-templates::common.errors')
       <div class="box box-primary">
           <div class="box-body">
               <div class="row">
                   {!! Form::model($enumerator, ['route' => ['enumerators.update', $enumerator->id], 'method' => 'patch']) !!}

                        @include('enumerators.fields')

                   {!! Form::close() !!}
               </div>
           </div>
       </div>
   </div>
@endsection