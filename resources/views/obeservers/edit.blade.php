@extends('layouts.app')

@section('content')
    <section class="content-header">
        <h1>
            Obeserver
        </h1>
   </section>
   <div class="content">
       @include('adminlte-templates::common.errors')
       <div class="box box-primary">
           <div class="box-body">
               <div class="row">
                   {!! Form::model($obeserver, ['route' => ['obeservers.update', $obeserver->id], 'method' => 'patch']) !!}

                        @include('obeservers.fields')

                   {!! Form::close() !!}
               </div>
           </div>
       </div>
   </div>
@endsection