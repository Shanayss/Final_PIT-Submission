function patientRegistrationModal() {
    return {
        currentStep: 1,
        title: 'Register New Patient',
        isSubmitting: false,
        registeredPatientNumber: '',
        successMessage: '',
        recentPatients: [],
        expandedPatient: {},
        expandedNextOfKin: null,
        expandedDoctor: null,
        doctors: [],
        errors: {},
        formData: {
            first_name: '',
            last_name: '',
            date_of_birth: '',
            sex: '',
            address: '',
            telephone: '',
            marital_status: '',
            kin_name: '',
            kin_relationship: '',
            kin_address: '',
            kin_telephone: '',
            clinic_number: '',
        },

        init() {
            this.loadDoctors();
        },

        loadDoctors() {
            fetch('/api/local-doctors', {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.doctors) {
                    this.doctors = data.doctors;
                }
            })
            .catch(error => console.error('Error loading doctors:', error));
        },

        validateStep(step) {
            this.errors = {};

            if (step === 1) {
                if (!this.formData.first_name.trim()) {
                    this.errors.first_name = 'First name is required';
                }
                if (!this.formData.last_name.trim()) {
                    this.errors.last_name = 'Last name is required';
                }
            } else if (step === 2) {
                if (this.formData.kin_name && !this.formData.kin_relationship) {
                    this.errors.kin_relationship = 'Relationship is required if next of kin is provided';
                }
            }

            return Object.keys(this.errors).length === 0;
        },

        nextStep() {
            if (!this.validateStep(this.currentStep)) {
                return;
            }
            if (this.currentStep < 3) {
                this.currentStep++;
            }
        },

        prevStep() {
            if (this.currentStep > 1) {
                this.currentStep--;
            }
        },

        submitForm() {
            if (!this.validateStep(3)) {
                return;
            }

            this.isSubmitting = true;

            fetch('/register-patient', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify(this.formData)
            })
            .then(response => response.json())
            .then(data => {
                this.isSubmitting = false;

                if (data.success) {
                    this.registeredPatientNumber = data.patient_number;
                    this.successMessage = data.patient_name + ' (ID: ' + data.patient_number + ')';
                    this.currentStep = 'success';
                    this.loadRecentPatients();
                } else {
                    alert('Error: ' + (data.message || 'Failed to register patient'));
                }
            })
            .catch(error => {
                this.isSubmitting = false;
                console.error('Error:', error);
                alert('Error registering patient: ' + error.message);
            });
        },

        loadRecentPatients() {
            fetch('/register-patient/recent', {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    this.recentPatients = data.patients;
                }
            })
            .catch(error => console.error('Error loading recent patients:', error));
        },

        expandPatient(patient) {
            this.expandedPatient = patient;
            this.currentStep = 'details';
            this.loadPatientDetails(patient.patient_number);
        },

        loadPatientDetails(patientNumber) {
            fetch('/register-patient/' + patientNumber, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    this.expandedPatient = data.patient;
                    this.expandedNextOfKin = data.next_of_kin;
                    this.expandedDoctor = data.local_doctor;
                }
            })
            .catch(error => console.error('Error loading patient details:', error));
        },

        resetForm() {
            this.formData = {
                first_name: '',
                last_name: '',
                date_of_birth: '',
                sex: '',
                address: '',
                telephone: '',
                marital_status: '',
                kin_name: '',
                kin_relationship: '',
                kin_address: '',
                kin_telephone: '',
                clinic_number: '',
            };
            this.errors = {};
            this.currentStep = 1;
        },

        closeModal() {
            const modal = document.getElementById('patient-registration-modal');
            if (modal) {
                modal.style.display = 'none';
            }
            this.resetForm();
        }
    };
}

function openPatientModal() {
    const modal = document.getElementById('patient-registration-modal');
    if (modal) {
        modal.style.display = 'block';
    }
}

// Global functions for sidebar integration
window.openPatientModal = openPatientModal;
