@extends('layouts.app')

@section('content')
    <section class="content-header">
        <h1>
            Sms Log
        </h1>
   </section>
   <div class="content">
       @include('adminlte-templates::common.errors')
       <div class="box box-primary">
           <div class="box-body">
               <div class="row">
                   {!! Form::model($smsLog, ['route' => ['smsLogs.update', $smsLog->id], 'method' => 'patch']) !!}

                        @include('sms_logs.fields')

                   {!! Form::close() !!}
               </div>
           </div>
       </div>
   </div>
@endsection