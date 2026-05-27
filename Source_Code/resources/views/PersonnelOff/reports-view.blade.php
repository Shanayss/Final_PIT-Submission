@extends('layouts.app')

@section('content')

<link rel="stylesheet" href="{{ asset('css/PersonnelOff/qualifications.css') }}">

<div class="staff-module-header">
    <h2>{{ $title }}</h2>
    <p>Generated personnel officer report</p>
</div>

<div class="table-card">

    <div class="table-tools qualification-tools">
        <a href="{{ route('personnel.reports') }}" class="sm-btn-add">
            <i class="fas fa-arrow-left"></i>
            Back to Reports
        </a>

        <button type="button" onclick="window.print()" class="sm-btn-add">
            <i class="fas fa-print"></i>
            Print Report
        </button>
    </div>

    <div class="table-responsive">
        <table class="modern-table">
            <thead>
                <tr>
                    @foreach($headers as $header)
                        <th>{{ $header }}</th>
                    @endforeach
                </tr>
            </thead>

            <tbody>
                @forelse($rows as $row)
                    <tr>
                        @foreach((array) $row as $cell)
                            <td>{{ $cell }}</td>
                        @endforeach
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ count($headers) }}" class="empty-state">
                            No report data found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

</div>

@endsection
