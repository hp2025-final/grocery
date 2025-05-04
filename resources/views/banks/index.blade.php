@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Bank Accounts</h2>
    <a href="{{ route('banks.create') }}" class="btn btn-success mb-3">Add New Bank</a>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Branch</th>
                <th>Account Number</th>
                <th>Account Title</th>
                <th>IBAN</th>
                <th>SWIFT Code</th>
                <th>Opening Balance</th>
                <th>Notes</th>
            </tr>
        </thead>
        <tbody>
            @foreach($banks as $bank)
                <tr>
                    <td>{{ $bank->id }}</td>
                    <td>{{ $bank->name }}</td>
                    <td>{{ $bank->branch }}</td>
                    <td>{{ $bank->account_number }}</td>
                    <td>{{ $bank->account_title }}</td>
                    <td>{{ $bank->iban }}</td>
                    <td>{{ $bank->swift_code }}</td>
                    <td>{{ $bank->opening_balance }}</td>
                    <td>{{ $bank->notes }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
