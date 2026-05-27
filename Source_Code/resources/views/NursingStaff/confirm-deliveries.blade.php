@extends('layouts.app')

@section('content')
<div class="nurse-page">
    <div class="nurse-page-header">
        <h1>Confirm Deliveries</h1>
    </div>

    @include('NursingStaff.partials.flash')

    <section class="nurse-table-card">
        <table class="nurse-table">
            <thead>
                <tr><th>Requisition</th><th>Ward</th><th>Requested By</th><th>Date Ordered</th><th>Status</th><th>Action</th></tr>
            </thead>
            <tbody>
                @forelse($requisitions as $req)
                    <tr>
                        <td>REQ-{{ $req->requisition_number }}</td>
                        <td>{{ $req->ward_name }}</td>
                        <td>{{ trim(($req->first_name ?? '') . ' ' . ($req->last_name ?? '')) ?: 'N/A' }}</td>
                        <td>{{ $req->date_ordered }}</td>
                        <td><span class="nurse-status-pill is-due">{{ $req->status }}</span></td>
                        <td>
                            <form method="POST" action="{{ route('nurse.supplies.confirm.store') }}">
                                @csrf
                                <input type="hidden" name="requisition_number" value="{{ $req->requisition_number }}">
                                <button class="nurse-small-button" type="submit">Confirm</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6">No pending deliveries found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </section>
</div>
@endsection
