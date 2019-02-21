@extends('layouts.app')

@section('content')
    <section class="content-header">
        <h1>
            Translation
        </h1>
   </section>
   <div class="content">
       @include('adminlte-templates::common.errors')
       <div class="box box-primary">
           <div class="box-body">
               <div class="row">
                   {!! Form::model($translation, ['route' => ['translations.update', $translation->id], 'method' => 'patch']) !!}

                        @include('translations.fields')

                   {!! Form::close() !!}
               </div>
           </div>
       </div>
   </div>
@endsection