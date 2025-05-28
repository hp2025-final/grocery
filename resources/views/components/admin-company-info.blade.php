@props([
    'showPoweredBy' => false,
    'showContactInfo' => true,
    'adminSection' => true
])

<div class="company-name">{{ config('company.name') }} - Admin Section</div>
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
@if($adminSection)
    <div class="admin-badge bg-gray-800 text-white px-2 py-1 text-xs rounded-md inline-block mt-2">Administrator Access</div>
@endif
