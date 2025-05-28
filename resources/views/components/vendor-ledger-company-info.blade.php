@props([
    'showPoweredBy' => false,
    'showContactInfo' => true
])

<div class="company-name">{{ config('company.name') }}</div>
@if($showContactInfo)
    <div class="company-details">
        <div class="company-contacts">
            {{ config('company.email') }} | {{ config('company.phone') }}
        </div>
    </div>
@endif
@if($showPoweredBy)
    <div class="developer">{{ config('company.powered_by') }}</div>
@endif
