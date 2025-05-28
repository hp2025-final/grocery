@props([
    'showPoweredBy' => false
])

<div class="footer-address" style="font-size: 10px;">Address: {{ config('company.address') }}</div>
@if($showPoweredBy)
    <div class="developer">{{ config('company.powered_by') }}</div>
@endif
