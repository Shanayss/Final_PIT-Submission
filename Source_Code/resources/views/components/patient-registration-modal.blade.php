<div x-cloak>
    <div id="patient-registration-modal" x-data="patientRegistrationModal()" style="display: none;">
        {{-- Modal overlay --}}
        <div class="modal-overlay" @click="closeModal()"></div>

        {{-- Modal content --}}
        <div class="modal modal-lg">
            <div class="modal-header">
                <h2 class="modal-title" x-text="title"></h2>
                <button class="modal-close" @click="closeModal()">
                    <i class="fa-solid fa-times"></i>
                </button>
            </div>

            <div class="modal-body">
                {{-- Step 1: Patient Demographics --}}
                <div x-show="currentStep === 1">
                    <h3 class="modal-sub">Step 1: Patient Information</h3>
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="first_name">First Name <span class="text-danger">*</span></label>
                            <input type="text" id="first_name" x-model="formData.first_name" class="form-control" placeholder="First name">
                            <small class="text-danger" x-show="errors.first_name" x-text="errors.first_name"></small>
                        </div>
                        <div class="form-group">
                            <label for="last_name">Last Name <span class="text-danger">*</span></label>
                            <input type="text" id="last_name" x-model="formData.last_name" class="form-control" placeholder="Last name">
                            <small class="text-danger" x-show="errors.last_name" x-text="errors.last_name"></small>
                        </div>
                        <div class="form-group">
                            <label for="date_of_birth">Date of Birth</label>
                            <input type="date" id="date_of_birth" x-model="formData.date_of_birth" class="form-control">
                            <small class="text-danger" x-show="errors.date_of_birth" x-text="errors.date_of_birth"></small>
                        </div>
                        <div class="form-group">
                            <label for="sex">Gender</label>
                            <select id="sex" x-model="formData.sex" class="form-control">
                                <option value="">Select gender</option>
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="address">Address</label>
                            <input type="text" id="address" x-model="formData.address" class="form-control" placeholder="Address">
                        </div>
                        <div class="form-group">
                            <label for="telephone">Telephone</label>
                            <input type="text" id="telephone" x-model="formData.telephone" class="form-control" placeholder="Phone number">
                        </div>
                        <div class="form-group">
                            <label for="marital_status">Marital Status</label>
                            <select id="marital_status" x-model="formData.marital_status" class="form-control">
                                <option value="">Select</option>
                                <option value="Single">Single</option>
                                <option value="Married">Married</option>
                                <option value="Divorced">Divorced</option>
                                <option value="Widowed">Widowed</option>
                            </select>
                        </div>
                    </div>
                </div>

                {{-- Step 2: Next of Kin --}}
                <div x-show="currentStep === 2">
                    <h3 class="modal-sub">Step 2: Next of Kin (Optional)</h3>
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="kin_name">Full Name</label>
                            <input type="text" id="kin_name" x-model="formData.kin_name" class="form-control" placeholder="Next of kin full name">
                            <small class="text-danger" x-show="errors.kin_name" x-text="errors.kin_name"></small>
                        </div>
                        <div class="form-group">
                            <label for="kin_relationship">Relationship</label>
                            <input type="text" id="kin_relationship" x-model="formData.kin_relationship" class="form-control" placeholder="e.g., Mother, Spouse">
                            <small class="text-danger" x-show="errors.kin_relationship" x-text="errors.kin_relationship"></small>
                        </div>
                        <div class="form-group">
                            <label for="kin_address">Address</label>
                            <input type="text" id="kin_address" x-model="formData.kin_address" class="form-control" placeholder="Address">
                        </div>
                        <div class="form-group">
                            <label for="kin_telephone">Telephone</label>
                            <input type="text" id="kin_telephone" x-model="formData.kin_telephone" class="form-control" placeholder="Phone number">
                        </div>
                    </div>
                </div>

                {{-- Step 3: Local Doctor (Conditional) --}}
                <div x-show="currentStep === 3">
                    <h3 class="modal-sub">Step 3: Referring Doctor (Optional)</h3>
                    <div class="form-grid">
                        <div class="form-group full-width">
                            <label for="clinic_number">Select Doctor</label>
                            <select id="clinic_number" x-model="formData.clinic_number" class="form-control">
                                <option value="">No doctor referred</option>
                                <template x-for="doctor in doctors" :key="doctor.clinic_number">
                                    <option :value="doctor.clinic_number" x-text="doctor.full_name"></option>
                                </template>
                            </select>
                        </div>
                    </div>
                </div>

                {{-- Success State --}}
                <div x-show="currentStep === 'success'">
                    <div style="text-align: center; padding: 20px;">
                        <div style="font-size: 48px; margin-bottom: 15px; color: #4CAF50;">
                            <i class="fa-solid fa-check-circle"></i>
                        </div>
                        <h3 style="color: #333; margin-bottom: 10px;">Patient Registered Successfully</h3>
                        <p style="color: #666; margin-bottom: 20px;">
                            <strong x-text="successMessage"></strong>
                        </p>
                        <p style="color: #999; font-size: 14px;">Patient Number: <strong x-text="registeredPatientNumber"></strong></p>
                    </div>

                    {{-- Recently Registered Patients List --}}
                    <div style="margin-top: 30px; border-top: 1px solid #eee; padding-top: 20px;">
                        <h4 style="margin-bottom: 15px;">Recently Registered Patients</h4>
                        <div x-show="recentPatients.length === 0" style="text-align: center; color: #999; padding: 20px;">
                            No patients registered yet
                        </div>
                        <div class="patients-list">
                            <template x-for="patient in recentPatients" :key="patient.patient_number">
                                <div class="patient-card" @click="expandPatient(patient)">
                                    <div style="display: flex; justify-content: space-between; align-items: center;">
                                        <div>
                                            <p style="margin: 0; font-weight: bold;" x-text="patient.first_name + ' ' + patient.last_name"></p>
                                            <p style="margin: 5px 0 0; font-size: 12px; color: #999;">
                                                <span x-text="patient.patient_number"></span> •
                                                <span x-text="patient.date_of_birth ? patient.date_of_birth : 'DOB unknown'"></span>
                                            </p>
                                        </div>
                                        <div style="text-align: right;">
                                            <i class="fa-solid fa-chevron-right" style="color: #ccc;"></i>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>

                {{-- Expanded Patient Details --}}
                <div x-show="currentStep === 'details'">
                    <button class="btn btn-secondary btn-sm" @click="currentStep = 'success'">← Back to List</button>
                    <div style="margin-top: 20px;">
                        <h3 style="margin-bottom: 15px;" x-text="expandedPatient.first_name + ' ' + expandedPatient.last_name"></h3>

                        <div style="background: #f9f9f9; padding: 15px; border-radius: 5px; margin-bottom: 15px;">
                            <h4 style="margin-top: 0;">Patient Information</h4>
                            <p><strong>Patient Number:</strong> <span x-text="expandedPatient.patient_number"></span></p>
                            <p><strong>Date of Birth:</strong> <span x-text="expandedPatient.date_of_birth || 'Not provided'"></span></p>
                            <p><strong>Gender:</strong> <span x-text="expandedPatient.sex || 'Not provided'"></span></p>
                            <p><strong>Address:</strong> <span x-text="expandedPatient.address || 'Not provided'"></span></p>
                            <p><strong>Phone:</strong> <span x-text="expandedPatient.telephone || 'Not provided'"></span></p>
                        </div>

                        <template x-if="expandedNextOfKin">
                            <div style="background: #f9f9f9; padding: 15px; border-radius: 5px; margin-bottom: 15px;">
                                <h4 style="margin-top: 0;">Next of Kin</h4>
                                <p><strong>Name:</strong> <span x-text="expandedNextOfKin.full_name"></span></p>
                                <p><strong>Relationship:</strong> <span x-text="expandedNextOfKin.relationship"></span></p>
                                <p><strong>Address:</strong> <span x-text="expandedNextOfKin.address || 'Not provided'"></span></p>
                                <p><strong>Phone:</strong> <span x-text="expandedNextOfKin.telephone || 'Not provided'"></span></p>
                            </div>
                        </template>

                        <template x-if="expandedDoctor">
                            <div style="background: #f9f9f9; padding: 15px; border-radius: 5px; margin-bottom: 15px;">
                                <h4 style="margin-top: 0;">Referring Doctor</h4>
                                <p><strong>Name:</strong> <span x-text="expandedDoctor.full_name"></span></p>
                                <p><strong>Specialization:</strong> <span x-text="expandedDoctor.specialization || 'General'"></span></p>
                            </div>
                        </template>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button class="btn btn-secondary" @click="closeModal()" x-show="currentStep !== 'success'">Cancel</button>
                <button class="btn btn-secondary" @click="prevStep()" x-show="currentStep > 1 && currentStep < 'success'">← Previous</button>
                <button class="btn btn-primary" @click="nextStep()" x-show="currentStep < 3" x-text="currentStep === 2 ? 'Next' : 'Next'"></button>
                <button class="btn btn-primary" @click="submitForm()" x-show="currentStep === 3" x-text="isSubmitting ? 'Registering...' : 'Register Patient'" :disabled="isSubmitting"></button>
                <button class="btn btn-primary" @click="resetForm()" x-show="currentStep === 'success'">Register Another</button>
            </div>
        </div>
    </div>
</div>

<style>
.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
    z-index: 9998;
}

.modal {
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background: white;
    border-radius: 8px;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
    z-index: 9999;
    max-width: 600px;
    width: 90%;
    max-height: 90vh;
    display: flex;
    flex-direction: column;
}

.modal-lg {
    max-width: 700px;
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px;
    border-bottom: 1px solid #eee;
}

.modal-title {
    margin: 0;
    font-size: 20px;
    font-weight: 600;
    color: #333;
}

.modal-close {
    background: none;
    border: none;
    font-size: 24px;
    color: #666;
    cursor: pointer;
    padding: 0;
}

.modal-close:hover {
    color: #333;
}

.modal-body {
    flex: 1;
    overflow-y: auto;
    padding: 20px;
}

.modal-sub {
    font-size: 16px;
    font-weight: 600;
    color: #333;
    margin-top: 0;
    margin-bottom: 15px;
}

.modal-footer {
    display: flex;
    gap: 10px;
    justify-content: flex-end;
    padding: 15px 20px;
    border-top: 1px solid #eee;
    background-color: #f9f9f9;
}

.form-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 15px;
    margin-bottom: 20px;
}

.form-group {
    display: flex;
    flex-direction: column;
}

.form-group.full-width {
    grid-column: 1 / -1;
}

.form-group label {
    font-weight: 600;
    margin-bottom: 5px;
    color: #333;
    font-size: 14px;
}

.form-control {
    padding: 8px 12px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 14px;
    font-family: inherit;
}

.form-control:focus {
    outline: none;
    border-color: #007bff;
    box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.1);
}

.text-danger {
    color: #dc3545;
}

.btn {
    padding: 8px 16px;
    border: none;
    border-radius: 4px;
    font-weight: 600;
    cursor: pointer;
    font-size: 14px;
    transition: all 0.2s;
}

.btn-primary {
    background-color: #007bff;
    color: white;
}

.btn-primary:hover:not(:disabled) {
    background-color: #0056b3;
}

.btn-primary:disabled {
    background-color: #ccc;
    cursor: not-allowed;
}

.btn-secondary {
    background-color: #6c757d;
    color: white;
}

.btn-secondary:hover {
    background-color: #545b62;
}

.btn-sm {
    padding: 5px 10px;
    font-size: 12px;
}

.patients-list {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.patient-card {
    background: #f9f9f9;
    border: 1px solid #eee;
    border-radius: 5px;
    padding: 12px;
    cursor: pointer;
    transition: all 0.2s;
}

.patient-card:hover {
    background: #f0f0f0;
    border-color: #ddd;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
}

[x-cloak] {
    display: none !important;
}
</style>
