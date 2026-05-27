@extends('layouts.app')

@section('content')
<!-- Include Custom Supplier Layout Styling -->
<link rel="stylesheet" href="{{ asset('css/meddirector-suppliers.css') }}">

<div class="meddirector-system-wrapper">
    
    <!-- Main Supplier Card View Canvas Dashboard -->
    <div class="meddirector-supplier-main-card">
        <div class="md-supplier-top-bar">
            <h2 class="md-supplier-title">Suppliers</h2>
            <button class="md-dots-btn">&#8226;&#8226;&#8226;</button>
        </div>

        <!-- Controls Toolbar Filter Bar Deck Layout -->
        <div class="md-supplier-toolbar">
            <div class="md-toolbar-left">
                <div class="md-search-box-wrapper">
                    <span class="md-search-icon">&#128269;</span>
                    <input type="text" id="md-supplier-search" placeholder="Search here..." class="md-search-input">
                </div>
                <button class="md-btn-filter">
                    <span class="md-icon-filter">&#8801;</span> Filter
                </button>
            </div>
            <div class="md-toolbar-right">
                <button id="md-btn-add-supplier" class="md-btn-add-primary">+ Add supplier</button>
            </div>
        </div>

        <!-- Subcard Content Grid Matrix Enclosure Section -->
        <div class="md-supplier-subcard">
            <div class="md-subcard-header" style="margin-bottom: 24px;">
                <h3 class="md-subcard-subtitle">Supplier list</h3>
                <span class="md-subcard-meta-location">Global Registry Log</span>
            </div>

            <!-- Standard Data View Table Registry Grid -->
            <div class="md-table-responsive-container">
                <table class="md-supplier-table">
                    <thead>
                        <tr>
                            <th>Supplier Name</th>
                            <th>Address</th>
                            <th>Telephone</th>
                            <th>Fax</th>
                            <th style="text-align: center;">Action</th>
                        </tr>
                    </thead>
                    <tbody id="md-supplier-table-rows">
                        <!-- Populated dynamically via JavaScript bridge -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<!-- New Supplier Interactive Input Entry Form Modal Popup Panel Container overlay -->
<div id="md-supplier-modal-overlay" class="md-modal-overlay md-hidden">
    <div class="md-modal-window">

        <!-- Modal Banner Heading Component Area -->
        <div class="md-modal-header">
            <div class="md-modal-header-left">
                <span class="md-modal-header-icon">&#128196;</span>
                <h3 class="md-modal-title">New supplier form</h3>
            </div>
            <button id="md-close-modal-btn" class="md-modal-close-x-btn">&times;</button>
        </div>

        <!-- Submission Handshaking Schema Model Endpoint Target Context Form -->
        <form id="md-add-supplier-form" action="{{ route('meddirector.suppliers.store') }}" method="POST" class="md-modal-form-body">

            @csrf
            
            <h4 class="md-form-section-subtitle">Supplier basic details</h4>
            <div class="md-form-row">
                <div class="md-form-field-group">
                    <label class="md-form-label">Supplier name</label>
                    <input type="text" name="supplier_name" class="md-form-input" placeholder="e.g. AstraZeneca Logistics" required>
                </div>
                <div class="md-form-field-group">
                    <label class="md-form-label">Supplier type</label>
                    <select name="supplier_type" class="md-form-select" required>
                        <option value="" disabled selected>Select type...</option>
                        <option value="pharmaceutical">Pharmaceutical</option>
                        <option value="surgical">Surgical</option>
                        <option value="medical-hardware">Medical Hardware</option>
                    </select>
                </div>
            </div>

            <h4 class="md-form-section-subtitle">Contact details info</h4>
            <div class="md-form-row">
                <div class="md-form-field-group">
                    <label class="md-form-label">Telephone</label>
                    <input type="text" name="telephone" class="md-form-input" placeholder="e.g. +63 (2) 844-1029" required>
                </div>
                <div class="md-form-field-group">
                    <label class="md-form-label">Fax (Optional)</label>
                    <input type="text" name="fax" class="md-form-input" placeholder="e.g. +63 (2) 844-1030">
                </div>
            </div>

            <div class="md-form-row single-column">
                <div class="md-form-field-group">
                    <label class="md-form-label">Address</label>
                    <textarea name="address" rows="3" class="md-form-textarea" placeholder="Enter complete warehouse or office registry address route..." required></textarea>
                </div>
            </div>

            <!-- Execution triggers bottom control line button row elements -->
            <div class="md-modal-action-row">
                <button type="button" id="md-cancel-modal-btn" class="md-btn-modal-cancel">Cancel</button>
                <button type="submit" class="md-btn-modal-submit">Save Supplier</button>
            </div>
        </form>

    </div>
</div>

<script>
    // Safe fallback bridge parsing database config down cleanly into window contexts layer objects
    window.suppliersJsonConfig = {!! $suppliersJsonConfig ?? '[]' !!};
</script>
<!-- Include Custom JS Behavioral Asset Logic -->
<script src="{{ asset('js/meddirector-suppliers.js') }}"></script>
@endsection
