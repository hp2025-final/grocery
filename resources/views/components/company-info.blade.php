@props([
    'showPoweredBy' => false,
    'showContactInfo' => true
])

<div class="company-name">{{ config('app.company.name') }}</div>
@if($showContactInfo)
    <div class="company-details">
        <div class="company-address">{{ config('app.company.address') }}</div>
        <div class="company-contacts">
            {{ config('app.company.email') }} | {{ config('app.company.phone') }}
        </div>
    </div>
@endif
@if($showPoweredBy)
    <div class="developer">{{ config('app.company.powered_by') }}</div>
@endif
