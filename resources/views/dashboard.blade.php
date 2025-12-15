@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="max-w-11xl mx-auto mt-1 px-4 sm:px-6 lg:px-8">
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">

        {{-- Total Parcel --}}
        @include('partials.card', [
            'icon' => '<i class="fa fa-box-open text-white text-4xl"></i>',
            'value' => $data['t_parcel'],
            'label' => 'Total Parcel'
        ])

        {{-- Total Delivered --}}
        @include('partials.card', [
            'icon' => '<i class="fa fa-shipping-fast text-white text-4xl"></i>',
            'value' => $data['t_delivered'],
            'label' => 'Total Delivered'
        ])

        {{-- Total Return --}}
        @include('partials.card', [
            'icon' => '<i class="fa fa-dna text-white text-4xl"></i>',
            'value' => $data['t_return'],
            'label' => 'Total Return'
        ])

        {{-- Total Transit --}}
        @include('partials.card', [
            'icon' => '<i class="fa fa-dolly text-white text-4xl"></i>',
            'value' => $data['t_parcel'] - $data['t_delivered'] - $data['t_return'],
            'label' => 'Total Transit'
        ])

        {{-- Total Sales Amount --}}
        @include('partials.card', [
            'icon' => '<i class="fa fa-hand-holding-usd text-white text-4xl"></i>',
            'value' => $data['t_sale'],
            'label' => 'Total Sales Amount'
        ])

        {{-- Total Delivery Fees Paid --}}
        @include('partials.card', [
            'icon' => '<i class="fa fa-hands-helping text-white text-4xl"></i>',
            'value' => $data['t_delivery_fee'],
            'label' => 'Total Delivery Fees Paid'
        ])

        {{-- Total Vat Amount --}}
        @include('partials.card', [
            'icon' => '<i class="fa fa-dna text-white text-4xl"></i>',
            'value' => $data['t_vat_amount'],
            'label' => 'Total Vat Amount'
        ])

        {{-- Net Profit Amount --}}
        @include('partials.card', [
            'icon' => '<i class="fa fa-hockey-puck text-white text-4xl"></i>',
            'value' => $data['t_liquid_fragile'],
            'label' => 'Net Profit Amount'
        ])

        {{-- Current Balance --}}
        @include('partials.card', [
            'icon' => '<i class="fa fa-credit-card text-white text-4xl"></i>',
            'value' => $data['t_balance_paid'],
            'label' => 'Current Balance'
        ])

        {{-- Opening Balance --}}
        @include('partials.card', [
            'icon' => '<i class="fa fa-donate text-white text-4xl"></i>',
            'value' => $data['t_balance_proc'],
            'label' => 'Opening Balance'
        ])

        {{-- Payment Processing --}}
        @include('partials.card', [
            'icon' => '<i class="fa fa-hourglass-half text-white text-4xl"></i>',
            'value' => $data['t_balance_proc'],
            'label' => 'Payment Processing'
        ])

        {{-- Paid Amount --}}
        @include('partials.card', [
            'icon' => '<i class="fa fa-database text-white text-4xl"></i>',
            'value' => $data['t_balance_paid'],
            'label' => 'Paid Amount'
        ])

        {{-- Total Shops --}}
        @include('partials.card', [
            'icon' => '<i class="fa fa-home text-white text-4xl"></i>',
            'value' => $data['t_shop'],
            'label' => 'Total Shops'
        ])

        {{-- Total Parcel Bank Items --}}
        @include('partials.card', [
            'icon' => '<i class="fa fa-boxes text-white text-4xl"></i>',
            'value' => $data['t_parcel_bank'],
            'label' => 'Total Parcel Bank Items'
        ])

        {{-- Total Payment Request --}}
        @include('partials.card', [
            'icon' => '<i class="fa fa-history text-white text-4xl"></i>',
            'value' => $data['t_request'],
            'label' => 'Total Payment Request'
        ])

    </div>
</div>


@endsection
