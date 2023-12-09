@extends('layouts.appADMIN')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">{{ __('SCRAPE') }}</div>
                <div class="card-body">
                    @livewire('scrape')
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
