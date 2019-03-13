@extends('layouts.app')

@section('content')
    <section class="content-header">
        <h1>
            Bulk Sms
        </h1>
   </section>
   <div class="content">
       @include('adminlte-templates::common.errors')
       <div class="box box-primary">
           <div class="box-body">
               <div class="row">
                   {!! Form::model($bulkSms, ['route' => ['bulkSms.update', $bulkSms->phone], 'method' => 'patch']) !!}

                        @include('bulk_sms.fields')

                   {!! Form::close() !!}
               </div>
           </div>
       </div>
   </div>
@endsection