

// ============================================== DASHBOARD SCRIPT ============================================== //

// ============ SIDE BAR ============ //
const sidebar = document.getElementById("sidebar");

function toggleSidebar() {
    sidebar.classList.toggle("show");
}

// ============ SIDE BAR END ============ //


// ============ ONLOAD FUNCTIONS ============ //


document.addEventListener("DOMContentLoaded", function () {
    roleSelect();
});

function roleSelect() {

    let sessionId = document.getElementById("session-user-id").value;
    let sessionRole = document.getElementById("session-user-role").value;
    let sessionStatus = document.getElementById("session-user-status").value;

    let admin_sidebar_header = document.getElementById("admin-sidebar-header");
    let doctor_sidebar_header = document.getElementById("doctor-sidebar-header");
    let patient_sidebar_header = document.getElementById("patient-sidebar-header");

    let admin_sidebar_list = document.getElementById("admin-sidebar-list");
    let doctor_sidebar_list = document.getElementById("doctor-sidebar-list");
    let patient_sidebar_list = document.getElementById("patient-sidebar-list");


    let admin_page_dashboard = document.getElementById("admin-page-dashboard");
    let doctor_page_dashboard = document.getElementById("doctor-page-dashboard");
    let patient_page_dashboard = document.getElementById("patient-page-dashboard");



    if (sessionStatus === "Active") {
        if (sessionRole === "Admin") {

            admin_sidebar_header.classList.remove("d-none");
            admin_sidebar_list.classList.remove("d-none");

            admin_page_dashboard.style.display='block';

        }
        else if (sessionRole === "Doctor") {

            doctor_sidebar_header.classList.remove("d-none");
            doctor_sidebar_list.classList.remove("d-none");

            doctor_page_dashboard.style.display='block';

        }
        else{

            patient_sidebar_header.classList.remove("d-none");
            patient_sidebar_list.classList.remove("d-none");

            patient_page_dashboard.style.display='block';

        }
    }
}

// ============ ONLOAD FUNCTIONS END ============ //

let admin_page_dashboard = document.getElementById("admin-page-dashboard");
let admin_page_cities = document.getElementById("admin-page-cities");
let admin_page_doctors = document.getElementById("admin-page-doctors");
let admin_page_patients = document.getElementById("admin-page-patients");
let admin_page_appointments = document.getElementById("admin-page-appointments");
let admin_page_diseases = document.getElementById("admin-page-diseases");
let admin_page_news = document.getElementById("admin-page-news");
let admin_page_contact_messages = document.getElementById("admin-page-contact-messages");

let admin_page_dashboard_btn = document.getElementById("admin-page-dashboard-btn");
let admin_page_cities_btn = document.getElementById("admin-page-cities-btn");
let admin_page_doctors_btn = document.getElementById("admin-page-doctors-btn");
let admin_page_patients_btn = document.getElementById("admin-page-patients-btn");
let admin_page_appointments_btn = document.getElementById("admin-page-appointments-btn");
let admin_page_diseases_btn = document.getElementById("admin-page-diseases-btn");
let admin_page_news_btn = document.getElementById("admin-page-news-btn");
let admin_page_contact_btn = document.getElementById("admin-page-contact-btn");

// ============ ADMIN PAGE DASHBOARD ============ //
function admin_page_dashboard_btn_click() {
    admin_page_dashboard.style.display='block';
    admin_page_contact_messages.style.display='none';
    admin_page_news.style.display='none';
    admin_page_diseases.style.display='none';
    admin_page_cities.style.display='none';
    admin_page_doctors.style.display='none';
    admin_page_patients.style.display='none';
    admin_page_appointments.style.display='none';

    admin_page_appointments_btn.className='nav-link';
    admin_page_patients_btn.className='nav-link';
    admin_page_doctors_btn.className='nav-link';
    admin_page_cities_btn.className='nav-link';
    admin_page_diseases_btn.className='nav-link';
    admin_page_news_btn.className='nav-link';
    admin_page_contact_btn.className='nav-link';
    admin_page_dashboard_btn.className='nav-link active';

    $("#admin-page-dashboard").load("dashboard.php #admin-page-dashboard > *");

}

// ============ ADMIN PAGE DASHBOARD END ============ //


// ============ ADMIN PAGE CITIES ============ //
function admin_page_cities_btn_click() {
    admin_page_cities.style.display='block';
    admin_page_contact_messages.style.display='none';
    admin_page_news.style.display='none';
    admin_page_diseases.style.display='none';
    admin_page_doctors.style.display='none';
    admin_page_dashboard.style.display='none';
    admin_page_patients.style.display='none';
    admin_page_appointments.style.display='none';

    admin_page_appointments_btn.className='nav-link';
    admin_page_patients_btn.className='nav-link';
    admin_page_dashboard_btn.className='nav-link';
    admin_page_doctors_btn.className='nav-link';
    admin_page_diseases_btn.className='nav-link';
    admin_page_news_btn.className='nav-link';
    admin_page_contact_btn.className='nav-link';
    admin_page_cities_btn.className='nav-link active';

    $(".cities-table").load("dashboard.php .cities-table > *");

}

function admin_page_save_cities_btn_click(){

    let cityName = $("#city-name").val();    

    $.ajax({
        url: "dashboard.php",
        type: "POST",
        data: { city_name: cityName },
        success: function(response){            

            if(response == "success"){
                $("#add_cities_modal").modal("hide");
                $("#city-name").val("");
                $(".cities-table").load("dashboard.php .cities-table > *");
            } else {
                console.log("post request nahi gai bro");
            }

        }
    });
}

function deleteCity(cityId) {

    Swal.fire({
        title: "Are you sure?",
        text: "You won't be able to recover this city after deletion.",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#dc3545",
        cancelButtonColor: "#6c757d",
        confirmButtonText: "Yes, Delete",
        cancelButtonText: "Cancel"
    }).then((result) => {

        if (result.isConfirmed) {

            $.ajax({
                url: "dashboard.php",
                type: "POST",
                data: { delete_city_id: cityId },

                success: function(response) {

                    response = response.trim();

                    if (response === "success") {

                        $(".cities-table").load("dashboard.php .cities-table > *");

                    }
                    else if (response === "error_has_doctors") {

                        Swal.fire({
                            icon: "warning",
                            title: "Cannot Delete",
                            text: "This city cannot be deleted because doctors are assigned to it. Please reassign or delete the doctors first.",
                            confirmButtonColor: "#f0ad4e"
                        });

                    }
                    else {

                        Swal.fire({
                            icon: "error",
                            title: "Deletion Failed",
                            text: "A system error occurred. Please try again later.",
                            confirmButtonColor: "#dc3545"
                        });

                    }

                },

                error: function() {

                    Swal.fire({
                        icon: "error",
                        title: "Server Error",
                        text: "Unable to connect to the server. Please try again later.",
                        confirmButtonColor: "#dc3545"
                    });

                }

            });

        }

    });

}

// ============ ADMIN PAGE CITIES END ============ //


// ============ ADMIN PAGE DOCTORS ============ //
function admin_page_doctors_btn_click() {
    admin_page_doctors.style.display='block';
    admin_page_contact_messages.style.display='none';
    admin_page_news.style.display='none';
    admin_page_diseases.style.display='none';
    admin_page_cities.style.display='none';
    admin_page_dashboard.style.display='none';
    admin_page_patients.style.display='none';
    admin_page_appointments.style.display='none';

    admin_page_appointments_btn.className='nav-link';
    admin_page_patients_btn.className='nav-link';
    admin_page_dashboard_btn.className='nav-link';
    admin_page_cities_btn.className='nav-link';
    admin_page_diseases_btn.className='nav-link';
    admin_page_news_btn.className='nav-link';
    admin_page_contact_btn.className='nav-link';
    admin_page_doctors_btn.className='nav-link active';

    $("#admin-page-doctors").load("dashboard.php #admin-page-doctors > *");

}

function admin_page_add_doctors_btn_click(){

    let request_fetch = "fetch cities";    

    $.ajax({
        url: "dashboard.php",
        type: "POST",
        data: { requestFetch: request_fetch },
        success: function(response){            
            document.getElementById('add-doctors-modal-city-field').innerHTML = response;
            request_fetch = "";
        },
        error: function () {
            console.log("post request nahi gai bro");
            
        }
        
    });
}

function admin_page_save_doctors_btn_click() {

    let formData = new FormData();

    formData.append("saveDoctorRequest", "save doctor");

    formData.append("add-doctors-modal-name-field",
        $("#add-doctors-modal-name-field").val());

    formData.append("add-doctors-modal-specialist-field",
        $("#add-doctors-modal-specialist-field").val());

    formData.append("add-doctors-modal-qualification-field",
        $("#add-doctors-modal-qualification-field").val());

    formData.append("add-doctors-modal-experience-field",
        $("#add-doctors-modal-experience-field").val());

    formData.append("add-doctors-modal-bio-field",
        $("#add-doctors-modal-bio-field").val());

    formData.append("add-doctors-modal-email-field",
        $("#add-doctors-modal-email-field").val());

    formData.append("add-doctors-modal-phone-field",
        $("#add-doctors-modal-phone-field").val());

    formData.append("add-doctors-modal-password-field",
        $("#add-doctors-modal-password-field").val());

    formData.append("add-doctors-modal-city-field",
        $("#add-doctors-modal-city-field").val());

    formData.append("add-doctors-modal-status-field",
        $("#add-doctors-modal-status-field").val());

    // IMAGE
    formData.append(
        "add-doctors-modal-photo-field",
        $("#add-doctors-modal-photo-field")[0].files[0]
    );

    $.ajax({
        url: "dashboard.php",
        type: "POST",
        data: formData,

        processData: false,
        contentType: false,

        success: function(response) {

            if(response.trim() == "success") {

                $("#add_doctors_modal").modal("hide");
                $(".doctors-table").load("dashboard.php .doctors-table > *");

                // TEXT FIELDS CLEAR
                $("#add-doctors-modal-name-field").val("");
                $("#add-doctors-modal-specialist-field").val("");
                $("#add-doctors-modal-qualification-field").val("");
                $("#add-doctors-modal-experience-field").val("");
                $("#add-doctors-modal-bio-field").val("");
                $("#add-doctors-modal-email-field").val("");
                $("#add-doctors-modal-phone-field").val("");
                $("#add-doctors-modal-password-field").val("");

                // SELECT RESET
                $("#add-doctors-modal-city-field").val("");
                $("#add-doctors-modal-status-field").val("Active");

                // FILE INPUT CLEAR (IMPORTANT)
                $("#add-doctors-modal-photo-field").val("");

            } else {

                Swal.fire({
                    icon: "error",
                    title: "Error",
                    text: response,
                    confirmButtonColor: "#dc3545"
                });

            }
        }
    });
}

function deleteDoctor(doctorId) {

    Swal.fire({
        title: "Are you sure?",
        text: "You won't be able to recover this doctor after deletion.",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#dc3545",
        cancelButtonColor: "#6c757d",
        confirmButtonText: "Yes, Delete",
        cancelButtonText: "Cancel"
    }).then((result) => {

        if (result.isConfirmed) {

            $.ajax({
                url: "dashboard.php",
                type: "POST",
                data: { delete_doctor_id: doctorId },

                success: function(response) {

                    response = response.trim();

                    if (response === "success") {

                        $(".doctors-table").load("dashboard.php .doctors-table > *");

                    }
                    else if (response === "error_has_appointments") {

                        Swal.fire({
                            icon: "warning",
                            title: "Cannot Delete",
                            text: "This doctor cannot be deleted because appointments are assigned to this doctor. Please reassign or delete the appointments first.",
                            confirmButtonColor: "#f0ad4e"
                        });

                    }
                    else {

                        Swal.fire({
                            icon: "error",
                            title: "Deletion Failed",
                            text: "A system error occurred. Please try again later.",
                            confirmButtonColor: "#dc3545"
                        });

                    }

                },

                error: function() {

                    Swal.fire({
                        icon: "error",
                        title: "Server Error",
                        text: "Unable to connect to the server. Please try again later.",
                        confirmButtonColor: "#dc3545"
                    });

                }

            });

        }

    });

}

function admin_page_edit_doctors_btn_click(userId){

    $.ajax({
        url: "dashboard.php",
        type: "POST",
        data: { fetch_doctor_status_city: userId },
        success: function(response){            
            let data = JSON.parse(response);            

            document.getElementById('edit-doctors-modal-city-field').innerHTML = data.cities;
            document.getElementById('edit-doctors-modal-city-field').value = data.cityId;

            document.getElementById('edit-doctors-modal-status-field').value = data.status;

            document.getElementById('edit-doctor-id').value = data.doctorId;
            document.getElementById('edit-user-id').value = data.userId;


        },
        error: function () {
            console.log("post request nahi gai bro");
            
        }
        
    });
}

function editDoctor() {

    let doctorId = document.getElementById("edit-doctor-id").value;
    let userId = document.getElementById("edit-user-id").value;

    let city = document.getElementById("edit-doctors-modal-city-field").value;
    let status = document.getElementById("edit-doctors-modal-status-field").value;

    $.ajax({
        url: "dashboard.php",
        type: "POST",
        data: {
            edit_doctor_id: doctorId,
            edit_user_id: userId,
            edit_user_status: status,
            edit_doctor_city: city
        },

        success: function(response) {

            response = response.trim();

            if (response === "Success") {

                $("#edit_doctors_modal").modal("hide");

                $(".doctors-table").load("dashboard.php .doctors-table > *");

            }
            else {

                Swal.fire({
                    icon: "error",
                    title: "Update Failed",
                    text: "A system error occurred. Please try again later.",
                    confirmButtonColor: "#dc3545"
                });

            }

        },

        error: function() {

            Swal.fire({
                icon: "error",
                title: "Server Error",
                text: "Unable to connect to the server. Please try again later.",
                confirmButtonColor: "#dc3545"
            });

        }

    });

}

// ============ ADMIN PAGE DOCTORS END ============ //


// ============ ADMIN PAGE PATIENTS ============ //
function admin_page_patients_btn_click() {
    admin_page_patients.style.display='block';
    admin_page_contact_messages.style.display='none';
    admin_page_news.style.display='none';
    admin_page_diseases.style.display='none';
    admin_page_doctors.style.display='none';
    admin_page_cities.style.display='none';
    admin_page_dashboard.style.display='none';
    admin_page_appointments.style.display='none';

    admin_page_dashboard_btn.className='nav-link';
    admin_page_cities_btn.className='nav-link';
    admin_page_doctors_btn.className='nav-link';
    admin_page_diseases_btn.className='nav-link';
    admin_page_news_btn.className='nav-link';
    admin_page_appointments_btn.className='nav-link';
    admin_page_contact_btn.className='nav-link';
    admin_page_patients_btn.className='nav-link active';

    $(".patients-table").load("dashboard.php .patients-table > *");

}

function admin_page_edit_patients_btn_click(userId){

    $.ajax({
        url: "dashboard.php",
        type: "POST",
        data: { fetch_patient_status: userId },
        success: function(response){            
            let data = JSON.parse(response); 

            document.getElementById('edit-patients-modal-status-field').value = data.status;

            document.getElementById('edit-patient-user-id').value = data.userId;

        },
        error: function () {
            console.log("post request nahi gai bro");
            
        }
        
    });
}

function editpatient() {

    let userId = document.getElementById("edit-patient-user-id").value;
    let status = document.getElementById("edit-patients-modal-status-field").value;

    $.ajax({
        url: "dashboard.php",
        type: "POST",
        data: {
            edit_patient_user_id: userId,
            edit_patient_user_status: status
        },

        success: function(response) {

            response = response.trim();

            if (response === "Success") {

                $("#edit_patients_modal").modal("hide");

                $(".patients-table").load("dashboard.php .patients-table > *");

            }
            else {

                Swal.fire({
                    icon: "error",
                    title: "Update Failed",
                    text: "A system error occurred. Please try again later.",
                    confirmButtonColor: "#dc3545"
                });

            }

        },

        error: function() {

            Swal.fire({
                icon: "error",
                title: "Server Error",
                text: "Unable to connect to the server. Please try again later.",
                confirmButtonColor: "#dc3545"
            });

        }

    });

}

function deletePatient(patientId, userId) {

    Swal.fire({
        title: "Are you sure?",
        text: "You won't be able to recover this patient after deletion.",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#dc3545",
        cancelButtonColor: "#6c757d",
        confirmButtonText: "Yes, Delete",
        cancelButtonText: "Cancel"
    }).then((result) => {

        if (result.isConfirmed) {

            $.ajax({
                url: "dashboard.php",
                type: "POST",
                data: {
                    delete_patient_id: patientId,
                    delete_user_id: userId
                },
                success: function(response) {

                    response = response.trim();

                    if (response === "success") {

                        Swal.fire({
                            icon: "success",
                            title: "Deleted!",
                            text: "Patient has been deleted successfully.",
                            timer: 1500,
                            showConfirmButton: false,
                            timerProgressBar: true
                        }).then(() => {

                            $(".patients-table").load("dashboard.php .patients-table > *");

                        });
                    } else {

                        Swal.fire({
                            icon: "error",
                            title: "Deletion Failed",
                            text: "System error occurred. Please try again later.",
                            confirmButtonColor: "#dc3545"
                        });

                    }

                },

                error: function() {

                    Swal.fire({
                        icon: "error",
                        title: "Server Error",
                        text: "Unable to connect to the server. Please try again later.",
                        confirmButtonColor: "#dc3545"
                    });

                }

            });

        }

    });

}

// ============ ADMIN PAGE PATIENTS END ============ //



// ============ ADMIN PAGE appointments ============ //
function admin_page_appointments_btn_click() {
    admin_page_appointments.style.display='block';
    admin_page_contact_messages.style.display='none';
    admin_page_patients.style.display='none';
    admin_page_news.style.display='none';
    admin_page_diseases.style.display='none';
    admin_page_doctors.style.display='none';
    admin_page_cities.style.display='none';
    admin_page_dashboard.style.display='none';

    admin_page_dashboard_btn.className='nav-link';
    admin_page_cities_btn.className='nav-link';
    admin_page_doctors_btn.className='nav-link';
    admin_page_diseases_btn.className='nav-link';
    admin_page_news_btn.className='nav-link';
    admin_page_patients_btn.className='nav-link';
    admin_page_contact_btn.className='nav-link';
    admin_page_appointments_btn.className='nav-link active';

    $("#admin-page-appointments").load("dashboard.php #admin-page-appointments > *");

}
// ============ ADMIN PAGE appointments END ============ //



// ============ ADMIN PAGE APPOINTMENTS ============ //

// ============ ADMIN PAGE APPOINTMENTS END============ //


// ============ ADMIN PAGE DISEASES ============ //
function admin_page_diseases_btn_click() {
    admin_page_diseases.style.display='block';
    admin_page_contact_messages.style.display='none';
    admin_page_news.style.display='none';
    admin_page_patients.style.display='none';
    admin_page_doctors.style.display='none';
    admin_page_cities.style.display='none';
    admin_page_appointments.style.display='none';
    admin_page_dashboard.style.display='none';

    admin_page_dashboard_btn.className='nav-link';
    admin_page_cities_btn.className='nav-link';
    admin_page_doctors_btn.className='nav-link';
    admin_page_patients_btn.className='nav-link';
    admin_page_news_btn.className='nav-link';
    admin_page_appointments_btn.className='nav-link';
    admin_page_contact_btn.className='nav-link';
    admin_page_diseases_btn.className='nav-link active';

    $("#admin-page-diseases").load("dashboard.php #admin-page-diseases > *");

}

function admin_page_save_disease_btn_click() {

    let formData = new FormData();

    formData.append("saveDiseasesRequest", "save disease");

    formData.append("add-diseases-modal-name-field",
        $("#add-diseases-modal-name-field").val());

    formData.append("add-diseases-modal-symptoms-field",
        $("#add-diseases-modal-symptoms-field").val());

    formData.append("add-diseases-modal-prevention-field",
        $("#add-diseases-modal-prevention-field").val());

    formData.append("add-diseases-modal-cure-field",
        $("#add-diseases-modal-cure-field").val());

    $.ajax({
        url: "dashboard.php",
        type: "POST",
        data: formData,

        processData: false,
        contentType: false,

        success: function(response) {

            if(response.trim() == "success") {

                $("#add_diseases_modal").modal("hide");
                $("#admin-page-diseases").load("dashboard.php #admin-page-diseases > *");

                // TEXT FIELDS CLEAR
                $("#add-diseases-modal-name-field").val("");
                $("#add-diseases-modal-symptoms-field").val("");
                $("#add-diseases-modal-prevention-field").val("");
                $("#add-diseases-modal-cure-field").val("");

            } else {

                alert(response);

            }
        }
    });
}

function admin_page_edit_disease_btn_click(diseaseId){

    

    $.ajax({
    url: "dashboard.php",
    type: "POST",
    data: { fetch_disease: diseaseId },
    success: function(response){            
        let data = JSON.parse(response);            

        document.getElementById('edit-diseases-modal-name-field').value = data.name;
        document.getElementById('edit-diseases-modal-symptoms-field').value = data.symptoms;
        document.getElementById('edit-diseases-modal-prevention-field').value = data.prevention;
        document.getElementById('edit-diseases-modal-cure-field').value = data.cure;


        document.getElementById('edit-disease-id').value = diseaseId;


    },
    error: function () {
        console.log("post request nahi gai bro");
        
    }
        
    });

}

function editDisease() {

    let diseaseId = document.getElementById("edit-disease-id").value;

    let formData = new FormData();

    formData.append("editDiseaseRequest", "edit disease");
    formData.append("editDiseaseId", diseaseId);

    formData.append("edit-diseases-modal-name-field", $("#edit-diseases-modal-name-field").val());
    formData.append("edit-diseases-modal-symptoms-field", $("#edit-diseases-modal-symptoms-field").val());
    formData.append("edit-diseases-modal-prevention-field", $("#edit-diseases-modal-prevention-field").val());
    formData.append("edit-diseases-modal-cure-field", $("#edit-diseases-modal-cure-field").val());

    $.ajax({
        url: "dashboard.php",
        type: "POST",
        data: formData,
        processData: false,
        contentType: false,

        success: function(response) {

            response = response.trim();

            if (response === "success") {

                $("#edit_diseases_modal").modal("hide");

                $("#admin-page-diseases").load("dashboard.php #admin-page-diseases > *");

                $("#edit-diseases-modal-name-field").val("");
                $("#edit-diseases-modal-symptoms-field").val("");
                $("#edit-diseases-modal-prevention-field").val("");
                $("#edit-diseases-modal-cure-field").val("");

                $("#edit-disease-id").val("");

            }
            else {

                Swal.fire({
                    icon: "error",
                    title: "Update Failed",
                    text: "A system error occurred. Please try again later.",
                    confirmButtonColor: "#dc3545"
                });

            }

        },

        error: function() {

            Swal.fire({
                icon: "error",
                title: "Server Error",
                text: "Unable to connect to the server. Please try again later.",
                confirmButtonColor: "#dc3545"
            });

        }

    });

}

function deleteDisease(diseaseId) {

    Swal.fire({
        title: "Are you sure?",
        text: "You won't be able to recover this disease after deletion.",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#dc3545",
        cancelButtonColor: "#6c757d",
        confirmButtonText: "Yes, Delete",
        cancelButtonText: "Cancel"
    }).then((result) => {

        if (result.isConfirmed) {

            $.ajax({
                url: "dashboard.php",
                type: "POST",
                data: { delete_disease_id: diseaseId },

                success: function(response) {

                    response = response.trim();

                    if (response === "success") {

                        $("#admin-page-diseases").load("dashboard.php #admin-page-diseases > *");

                    } else {

                        Swal.fire({
                            icon: "error",
                            title: "Deletion Failed",
                            text: "A system error occurred. Please try again later.",
                            confirmButtonColor: "#dc3545"
                        });

                    }

                },

                error: function() {

                    Swal.fire({
                        icon: "error",
                        title: "Server Error",
                        text: "Unable to connect to the server. Please try again later.",
                        confirmButtonColor: "#dc3545"
                    });

                }

            });

        }

    });

}

// ============ ADMIN PAGE DISEASES END ============ //


// ============ ADMIN PAGE NEWS ============ //
function admin_page_news_btn_click() {
    admin_page_news.style.display='block';
    admin_page_contact_messages.style.display='none';
    admin_page_diseases.style.display='none';
    admin_page_patients.style.display='none';
    admin_page_doctors.style.display='none';
    admin_page_cities.style.display='none';
    admin_page_dashboard.style.display='none';
    admin_page_appointments.style.display='none';

    admin_page_dashboard_btn.className='nav-link';
    admin_page_cities_btn.className='nav-link';
    admin_page_doctors_btn.className='nav-link';
    admin_page_patients_btn.className='nav-link';
    admin_page_diseases_btn.className='nav-link';
    admin_page_appointments_btn.className='nav-link';
    admin_page_contact_btn.className='nav-link';
    admin_page_news_btn.className='nav-link active';

}

function admin_page_save_news_btn_click() {

    let formData = new FormData();

    formData.append("saveNewsRequest", "save news");

    formData.append("add-news-modal-title-field", $("#add-news-modal-title-field").val());

    formData.append("add-news-modal-description-field", $("#add-news-modal-description-field").val());

    formData.append("add-news-modal-date-field", $("#add-news-modal-date-field").val());

    formData.append("add-news-modal-status-field", $("#add-news-modal-status-field").val());

    $.ajax({
        url: "dashboard.php",
        type: "POST",
        data: formData,

        processData: false,
        contentType: false,

        success: function(response) {

            if(response.trim() == "success") {

                $("#add_news_modal").modal("hide");
                $(".news-table").load("dashboard.php .news-table > *");

                // TEXT FIELDS CLEAR
                $("#add-news-modal-title-field").val("");
                $("#add-news-modal-description-field").val("");
                $("#add-news-modal-date-field").val("");
                $("#add-news-modal-status-field").val("Active");

            } else {

                alert(response);

            }
        }
    });
}

function admin_page_edit_news_btn_click(newsId){

    $.ajax({
    url: "dashboard.php",
    type: "POST",
    data: { fetch_news: newsId },
    success: function(response){            
        let data = JSON.parse(response);            

        document.getElementById('edit-news-modal-title-field').value = data.title;
        document.getElementById('edit-news-modal-description-field').value = data.description;
        document.getElementById('edit-news-modal-date-field').value = data.date;
        document.getElementById('edit-news-modal-status-field').value = data.status;


        document.getElementById('edit-news-id').value = newsId;


    },
    error: function () {
        console.log("post request nahi gai bro");
        
    }
        
    });

}

function editNews() {

    let newsId = document.getElementById("edit-news-id").value;

    let formData = new FormData();

    formData.append("editNewsRequest", "edit news");
    formData.append("editNewsId", newsId);

    formData.append("edit-news-modal-title-field", $("#edit-news-modal-title-field").val());
    formData.append("edit-news-modal-description-field", $("#edit-news-modal-description-field").val());
    formData.append("edit-news-modal-date-field", $("#edit-news-modal-date-field").val());
    formData.append("edit-news-modal-status-field", $("#edit-news-modal-status-field").val());

    $.ajax({
        url: "dashboard.php",
        type: "POST",
        data: formData,
        processData: false,
        contentType: false,

        success: function(response) {

            response = response.trim();

            if (response === "success") {

                $("#edit_news_modal").modal("hide");

                $("#admin-page-news").load("dashboard.php #admin-page-news > *");

                $("#edit-news-modal-title-field").val("");
                $("#edit-news-modal-description-field").val("");
                $("#edit-news-modal-date-field").val("");
                $("#edit-news-modal-status-field").val("");

                $("#edit-news-id").val("");

            }
            else {

                Swal.fire({
                    icon: "error",
                    title: "Update Failed",
                    text: "A system error occurred. Please try again later.",
                    confirmButtonColor: "#dc3545"
                });

            }

        },

        error: function() {

            Swal.fire({
                icon: "error",
                title: "Server Error",
                text: "Unable to connect to the server. Please try again later.",
                confirmButtonColor: "#dc3545"
            });

        }

    });

}

function deleteNews(newsId) {

    Swal.fire({
        title: "Are you sure?",
        text: "You won't be able to recover this news after deletion.",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#dc3545",
        cancelButtonColor: "#6c757d",
        confirmButtonText: "Yes, Delete",
        cancelButtonText: "Cancel"
    }).then((result) => {

        if (result.isConfirmed) {

            $.ajax({
                url: "dashboard.php",
                type: "POST",
                data: { delete_news_id: newsId },

                success: function(response) {

                    response = response.trim();

                    if (response === "success") {

                        $("#admin-page-news").load("dashboard.php #admin-page-news > *");

                    }
                    else {

                        Swal.fire({
                            icon: "error",
                            title: "Deletion Failed",
                            text: "A system error occurred. Please try again later.",
                            confirmButtonColor: "#dc3545"
                        });

                    }

                },

                error: function() {

                    Swal.fire({
                        icon: "error",
                        title: "Server Error",
                        text: "Unable to connect to the server. Please try again later.",
                        confirmButtonColor: "#dc3545"
                    });

                }

            });

        }

    });

}

// ============ ADMIN PAGE NEWS END ============ //

// ============ ADMIN PAGE contact messages ============ //
function admin_page_contact_btn_click() {
    admin_page_contact_messages.style.display='block';
    admin_page_dashboard.style.display='none';
    admin_page_news.style.display='none';
    admin_page_diseases.style.display='none';
    admin_page_cities.style.display='none';
    admin_page_doctors.style.display='none';
    admin_page_patients.style.display='none';
    admin_page_appointments.style.display='none';

    admin_page_appointments_btn.className='nav-link';
    admin_page_patients_btn.className='nav-link';
    admin_page_doctors_btn.className='nav-link';
    admin_page_cities_btn.className='nav-link';
    admin_page_diseases_btn.className='nav-link';
    admin_page_news_btn.className='nav-link';
    admin_page_dashboard_btn.className='nav-link';
    admin_page_contact_btn.className='nav-link active';

    $("#admin-page-contact-messages").load("dashboard.php #admin-page-contact-messages > *");

}

// ============ ADMIN PAGE contact messages END ============ //





// ============ DOCTOR PAGE DASHBOARD ============ //
function doctor_page_dashboard_btn_click() {
    
    let doctor_page_dashboard = document.getElementById("doctor-page-dashboard");
    let doctor_page_profile = document.getElementById("doctor-page-profile");
    let doctor_page_profile_edit = document.getElementById("doctor-page-edit-profile");
    let doctor_page_availbility = document.getElementById("doctor-page-availability");
    let doctor_page_appointment = document.getElementById("doctor-page-appointment");

    let doctor_page_dashboard_btn = document.getElementById("doctor-page-dashboard-btn");
    let doctor_page_profile_btn = document.getElementById("doctor-page-profile-btn");
    let doctor_page_edit_profile_btn = document.getElementById("doctor-page-edit-profile-btn");
    let doctor_page_availbility_btn = document.getElementById("doctor-page-availbility-btn");
    let doctor_page_appointment_btn = document.getElementById("doctor-page-appointment-btn");

    doctor_page_dashboard.style.display='block';
    doctor_page_profile.style.display='none';
    doctor_page_profile_edit.style.display='none';
    doctor_page_availbility.style.display='none';
    doctor_page_appointment.style.display='none';

    doctor_page_dashboard_btn.classList.add("active");
    doctor_page_profile_btn.classList.remove("active");
    doctor_page_edit_profile_btn.classList.remove("active");
    doctor_page_availbility_btn.classList.remove("active");
    doctor_page_appointment_btn.classList.remove("active");

    $("#doctor-page-dashboard").load("dashboard.php #doctor-page-dashboard > *");
    
}
// ============ DOCTOR PAGE DASHBOARD END ============ //

// ============ DOCTOR PAGE PROFILE ============ //
function doctor_page_profile_btn_click() {
    
    let doctor_page_dashboard = document.getElementById("doctor-page-dashboard");
    let doctor_page_profile = document.getElementById("doctor-page-profile");
    let doctor_page_profile_edit = document.getElementById("doctor-page-edit-profile");
    let doctor_page_availbility = document.getElementById("doctor-page-availability");
    let doctor_page_appointment = document.getElementById("doctor-page-appointment");

    let doctor_page_dashboard_btn = document.getElementById("doctor-page-dashboard-btn");
    let doctor_page_profile_btn = document.getElementById("doctor-page-profile-btn");
    let doctor_page_edit_profile_btn = document.getElementById("doctor-page-edit-profile-btn");
    let doctor_page_availbility_btn = document.getElementById("doctor-page-availbility-btn");
    let doctor_page_appointment_btn = document.getElementById("doctor-page-appointment-btn");

    doctor_page_dashboard.style.display='none';
    doctor_page_profile.style.display='block';
    doctor_page_profile_edit.style.display='none';
    doctor_page_availbility.style.display='none';
    doctor_page_appointment.style.display='none';

    doctor_page_dashboard_btn.classList.remove("active");
    doctor_page_profile_btn.classList.add("active");
    doctor_page_edit_profile_btn.classList.remove("active");
    doctor_page_availbility_btn.classList.remove("active");
    doctor_page_appointment_btn.classList.remove("active");

    $("#doctor-page-profile").load("dashboard.php #doctor-page-profile > *");
    
}
// ============ DOCTOR PAGE PROFILE END ============ //

// ============ DOCTOR PAGE EDIT PROFILE ============ //
function doctor_page_edit_profile_btn_click() {
    
    let doctor_page_dashboard = document.getElementById("doctor-page-dashboard");
    let doctor_page_profile = document.getElementById("doctor-page-profile");
    let doctor_page_profile_edit = document.getElementById("doctor-page-edit-profile");
    let doctor_page_availbility = document.getElementById("doctor-page-availability");
    let doctor_page_appointment = document.getElementById("doctor-page-appointment");

    let doctor_page_dashboard_btn = document.getElementById("doctor-page-dashboard-btn");
    let doctor_page_profile_btn = document.getElementById("doctor-page-profile-btn");
    let doctor_page_edit_profile_btn = document.getElementById("doctor-page-edit-profile-btn");
    let doctor_page_availbility_btn = document.getElementById("doctor-page-availbility-btn");
    let doctor_page_appointment_btn = document.getElementById("doctor-page-appointment-btn");

    doctor_page_dashboard.style.display='none';
    doctor_page_profile.style.display='none';
    doctor_page_profile_edit.style.display='block';
    doctor_page_availbility.style.display='none';
    doctor_page_appointment.style.display='none';

    doctor_page_dashboard_btn.classList.remove("active");
    doctor_page_profile_btn.classList.remove("active");
    doctor_page_edit_profile_btn.classList.add("active");
    doctor_page_availbility_btn.classList.remove("active");
    doctor_page_appointment_btn.classList.remove("active");

    $("#doctor-page-edit-profile").load("dashboard.php #doctor-page-edit-profile > *");
    
}

document.getElementById("uploadImageBtn").onclick = function () {
    document.getElementById("profileImageInput").click();
};

document.getElementById("profileImageInput").onchange = function () {
    document.getElementById("profilePreview").src =
        URL.createObjectURL(this.files[0]);
};

function update_doctor_details() {

    let userId = document.getElementById("session-user-id").value;
    let name = document.getElementById("doctor-update-name-field").value;
    let email = document.getElementById("doctor-update-email-field").value;
    let specialist = document.getElementById("doctor-update-specialist-field").value;
    let qualification = document.getElementById("doctor-update-qualification-field").value;
    let experience = document.getElementById("doctor-update-experience-field").value;
    let phone = document.getElementById("doctor-update-phone-field").value;
    let bio = document.getElementById("doctor-update-bio-field").value;
    let password = document.getElementById("doctor-update-password-field").value;
    let image = document.getElementById("profileImageInput").files[0];

    if(name.trim() === "" || email.trim() === "" || specialist.trim() === "" || qualification.trim() === "" || experience.trim() === "" || phone.trim() === "" || bio.trim() === "")
    {

        Swal.fire({
            icon: 'warning',
            title: 'Incomplete Form',
            text: 'All required fields must be filled before proceeding.',
            confirmButtonColor: '#6c757d'
        });

    }
    else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
        Swal.fire({
            icon: 'error',
            title: 'Invalid Email',
            text: 'Please enter a valid email address.',
            confirmButtonColor: '#dc3545'
        }); 
    }
    else{

        let formData = new FormData();

        formData.append("updateDoctorDetails", "doctor update");

        formData.append("id", userId);
        formData.append("name", name);
        formData.append("email", email);
        formData.append("specialist", specialist);
        formData.append("qualification", qualification);
        formData.append("experience", experience);
        formData.append("phone", phone);
        formData.append("bio", bio);
        formData.append("password", password);
        if(image){
            formData.append("image", image);
        }

        $.ajax({
        url: "dashboard.php",
        type: "POST",
        data: formData,

        processData: false,
        contentType: false,

        success: function(response) {            
            
            let res = JSON.parse(response);            

            if(res.status === "success") {

                Swal.fire({
                    icon: 'success',
                    title: 'Profile Updated',
                    text: 'Your profile has been updated successfully.',
                    timer: 1500,
                    showConfirmButton: false,
                    timerProgressBar: true
                }).then(() => {
                    // window.location.href = 'dashboard.php';
                    $("#doctor-page-edit-profile").load("dashboard.php #doctor-page-edit-profile > *");
                    $("#doctor-page-profile").load("dashboard.php #doctor-page-profile > *");
                });
                
            }
            else if (res.status === "email exists") {
                Swal.fire({
                    icon: 'warning',
                    title: 'Email Already Exists',
                    text: 'Another account is already using this email address.',
                    confirmButtonColor: '#ffc107'
                });
            }
            else if (res.status === "error") {
                Swal.fire({
                    icon: 'error',
                    title: 'Update Failed',
                    text: 'Unable to update profile. Please try again.',
                    confirmButtonColor: '#dc3545'
                });
            }
            
        },

        error: function(xhr){
            console.log(xhr.responseText);

            Swal.fire({
                icon: 'error',
                title: 'Server Error',
                text: 'We are unable to process your request at the moment. Please try again later.',
                confirmButtonColor: '#dc3545'
            });
        } 

    });

    }
}

// ============ DOCTOR PAGE EDIT PROFILE END ============ //

// ============ DOCTOR PAGE AVAILBILITY ============ //
function doctor_page_availbility_btn_click() {
    
    let doctor_page_dashboard = document.getElementById("doctor-page-dashboard");
    let doctor_page_profile = document.getElementById("doctor-page-profile");
    let doctor_page_profile_edit = document.getElementById("doctor-page-edit-profile");
    let doctor_page_availbility = document.getElementById("doctor-page-availability");
    let doctor_page_appointment = document.getElementById("doctor-page-appointment");

    let doctor_page_dashboard_btn = document.getElementById("doctor-page-dashboard-btn");
    let doctor_page_profile_btn = document.getElementById("doctor-page-profile-btn");
    let doctor_page_edit_profile_btn = document.getElementById("doctor-page-edit-profile-btn");
    let doctor_page_availbility_btn = document.getElementById("doctor-page-availbility-btn");
    let doctor_page_appointment_btn = document.getElementById("doctor-page-appointment-btn");

    doctor_page_dashboard.style.display='none';
    doctor_page_profile.style.display='none';
    doctor_page_profile_edit.style.display='none';
    doctor_page_availbility.style.display='block';
    doctor_page_appointment.style.display='none';

    doctor_page_dashboard_btn.classList.remove("active");
    doctor_page_profile_btn.classList.remove("active");
    doctor_page_edit_profile_btn.classList.remove("active");
    doctor_page_availbility_btn.classList.add("active");
    doctor_page_appointment_btn.classList.remove("active");

    
}

function toggle_availability() {

    let checked =
        document.getElementById("notAvailableCheckbox").checked;

    let timeInputs =
        document.querySelectorAll("#slotContainer input");

    timeInputs.forEach(input => {
        input.disabled = checked;

        if (checked) input.value = "";
    });

}

function save_available_slots() {

    let userId = document.getElementById("session-user-id").value;

    let day = document.getElementById("availability-day").value;    

    let notAvailable = document.getElementById("notAvailableCheckbox").checked;

    let slot1Start = document.getElementById("slot-1-start-time").value;
    let slot1End = document.getElementById("slot-1-end-time").value;

    let slot2Start = document.getElementById("slot-2-start-time").value;
    let slot2End = document.getElementById("slot-2-end-time").value;

    let slot3Start = document.getElementById("slot-3-start-time").value;
    let slot3End = document.getElementById("slot-3-end-time").value;

    let formData = new FormData();

    formData.append("saveAvailability", "save availability");

    formData.append("userId", userId);
    formData.append("day", day);

    if (!notAvailable && slot1Start === "" && slot1End === "" && slot2Start === "" && slot2End === "" && slot3Start === "" && slot3End === "") {
        Swal.fire({
            icon: 'warning',
            title: 'No Time Slot Selected',
            text: 'Please select at least one time slot.',
            confirmButtonColor: '#6c757d'
        });

        return;
    }
    else{

        if (notAvailable) {
            formData.append("status", "Not Available");
        }

        else{

            formData.append("status", "Available");

            if (slot1Start !== "" || slot1End !== "") {
                if (slot1Start === "") {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Start Time Required',
                        text: 'Please select a start time for Slot 1.',
                        confirmButtonColor: '#6c757d'
                    });

                    return;
                }
                else if (slot1End === "") {
                    Swal.fire({
                    icon: 'warning',
                    title: 'End Time Required',
                    text: 'Please select an end time for Slot 1.',
                    confirmButtonColor: '#6c757d'
                });

                    return;
                }
                else if (slot1Start >= slot1End) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Invalid Time Range',
                        text: 'End time must be greater than the start time for Slot 1.',
                        confirmButtonColor: '#6c757d'
                    });

                    return;
                }
                else{
                    formData.append("slot1Start", slot1Start);
                    formData.append("slot1End", slot1End);
                }
            }

            if (slot2Start !== "" || slot2End !== "") {
                if (slot2Start === "") {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Start Time Required',
                        text: 'Please select a start time for Slot 2.',
                        confirmButtonColor: '#6c757d'
                    });

                    return;
                }
                else if (slot2End === "") {
                    Swal.fire({
                    icon: 'warning',
                    title: 'End Time Required',
                    text: 'Please select an end time for Slot 2.',
                    confirmButtonColor: '#6c757d'
                });

                    return;
                }
                else if (slot2Start >= slot2End) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Invalid Time Range',
                        text: 'End time must be greater than the start time for Slot 2.',
                        confirmButtonColor: '#6c757d'
                    });

                    return;
                }
                else{
                    formData.append("slot2Start", slot2Start);
                    formData.append("slot2End", slot2End);
                }
            }

            if (slot3Start !== "" || slot3End !== "") {
                if (slot3Start === "") {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Start Time Required',
                        text: 'Please select a start time for Slot 3.',
                        confirmButtonColor: '#6c757d'
                    });

                    return;
                }
                else if (slot3End === "") {
                    Swal.fire({
                    icon: 'warning',
                    title: 'End Time Required',
                    text: 'Please select an end time for Slot 3.',
                    confirmButtonColor: '#6c757d'
                });

                    return;
                }
                else if (slot3Start >= slot3End) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Invalid Time Range',
                        text: 'End time must be greater than the start time for Slot 3.',
                        confirmButtonColor: '#6c757d'
                    });

                    return;
                }
                else{
                    formData.append("slot3Start", slot3Start);
                    formData.append("slot3End", slot3End);
                }
            }

        }

    }

    $.ajax({
        url: "dashboard.php",
        type: "POST",
        data: formData,

        processData: false,
        contentType: false,

        success: function(response){

            let res = JSON.parse(response);            

            if(res.status === "success"){

                $("#doctor-page-availability").load("dashboard.php #doctor-page-availability > *");
                $("#add_slots_modal").modal("hide");

                day.value = "Mon";
                notAvailable.checked = false;
                slot1Start.value = "";
                slot1End.value = "";
                slot2Start.value = "";
                slot2End.value = "";
                slot3Start.value = "";
                slot3End.value = "";

            }
            else{

                Swal.fire({
                    icon: "error",
                    title: "Save Failed",
                    text: "An unexpected error occurred while saving your availability. Please try again.",
                    confirmButtonColor: "#dc3545"
                });

            }

        },
        error: function() {

            Swal.fire({
                icon: "error",
                title: "Server Error",
                text: "Unable to connect to the server. Please try again later.",
                confirmButtonColor: "#dc3545"
            });

        }

    });

}

// ============ DOCTOR PAGE AVAILBILITY END ============ //

// ============ DOCTOR PAGE APPOINTMENT ============ //
function doctor_page_appointment_btn_click() {
    
    let doctor_page_dashboard = document.getElementById("doctor-page-dashboard");
    let doctor_page_profile = document.getElementById("doctor-page-profile");
    let doctor_page_profile_edit = document.getElementById("doctor-page-edit-profile");
    let doctor_page_availbility = document.getElementById("doctor-page-availability");
    let doctor_page_appointment = document.getElementById("doctor-page-appointment");

    let doctor_page_dashboard_btn = document.getElementById("doctor-page-dashboard-btn");
    let doctor_page_profile_btn = document.getElementById("doctor-page-profile-btn");
    let doctor_page_edit_profile_btn = document.getElementById("doctor-page-edit-profile-btn");
    let doctor_page_availbility_btn = document.getElementById("doctor-page-availbility-btn");
    let doctor_page_appointment_btn = document.getElementById("doctor-page-appointment-btn");

    doctor_page_dashboard.style.display='none';
    doctor_page_profile.style.display='none';
    doctor_page_profile_edit.style.display='none';
    doctor_page_availbility.style.display='none';
    doctor_page_appointment.style.display='block';

    doctor_page_dashboard_btn.classList.remove("active");
    doctor_page_profile_btn.classList.remove("active");
    doctor_page_edit_profile_btn.classList.remove("active");
    doctor_page_availbility_btn.classList.remove("active");
    doctor_page_appointment_btn.classList.add("active");

    $("#doctor-page-appointment").load("dashboard.php #doctor-page-appointment > *");
    
}

function editappointment_btn(appointmentid) {

    document.getElementById('edit-appointment-id').value = appointmentid;

    $.ajax({
        url: "dashboard.php",
        type: "POST",
        data: { fetch_appointment_status: appointmentid},
        success: function(response) {

            let res = JSON.parse(response);

            if(res.message === "Success") {
                
                document.getElementById('edit-appointment-modal-status-field').value = res.status;

            } 
            else {

                alert("System error occurred. Try again later.");

            }

        }
    });


}

function editappointment() {

    let appointmentid = document.getElementById('edit-appointment-id').value;

    let status = document.getElementById('edit-appointment-modal-status-field').value;

    $.ajax({
        url: "dashboard.php",
        type: "POST",
        data: { edit_appointmentid: appointmentid, edit_appointment_status: status},
        success: function(response) {

            response = response.trim();

            if(response === "Success") {
                
                $("#edit_appointment_modal").modal("hide");
                $(".doctor-page-appointment").load("dashboard.php .doctor-page-appointment > *");

            } 
            else {

                alert("System error occurred. Try again later.");

            }

        }
    });
}

// ============ DOCTOR PAGE APPOINTMENT END ============ //







// ============ PATIENT PAGE DASHBOARD ============ //
function patient_page_dashboard_btn_click() {
    
    let patient_page_dashboard = document.getElementById("patient-page-dashboard");
    let patient_page_search_doctor = document.getElementById("patient-page-search-doctor");
    let patient_page_book_appointment = document.getElementById("patient-page-book-appointment");
    let patient_page_my_appointment = document.getElementById("patient-page-my-appointment");
    let patient_page_profile = document.getElementById("patient-page-my-profile");

    let patient_page_dashboard_btn = document.getElementById("patient-page-dashboard-btn");
    let patient_page_search_doctor_btn = document.getElementById("patient-page-search-doctor-btn");
    let patient_page_my_appointment_btn = document.getElementById("patient-page-my-appointment-btn");
    let patient_page_profile_btn = document.getElementById("patient-page-profile-btn");

    patient_page_dashboard.style.display='block';
    patient_page_search_doctor.style.display='none';
    patient_page_book_appointment.style.display='none';
    patient_page_my_appointment.style.display='none';
    patient_page_profile.style.display='none';

    patient_page_dashboard_btn.classList.add("active");
    patient_page_search_doctor_btn.classList.remove("active");
    patient_page_my_appointment_btn.classList.remove("active");
    patient_page_profile_btn.classList.remove("active");

    $("#patient-page-book-appointment").load("dashboard.php #patient-page-book-appointment > *");
    $("#patient-page-dashboard").load("dashboard.php #patient-page-dashboard > *");
    
}
// ============ PATIENT PAGE DASHBOARD END ============ //

// ============ PATIENT PAGE SEARCH DOCTOR ============ //
function patient_page_search_dcotor_btn_click() {
    
    loadDoctors();

    let patient_page_dashboard = document.getElementById("patient-page-dashboard");
    let patient_page_search_doctor = document.getElementById("patient-page-search-doctor");
    let patient_page_book_appointment = document.getElementById("patient-page-book-appointment");
    let patient_page_my_appointment = document.getElementById("patient-page-my-appointment");
    let patient_page_profile = document.getElementById("patient-page-my-profile");

    let patient_page_dashboard_btn = document.getElementById("patient-page-dashboard-btn");
    let patient_page_search_doctor_btn = document.getElementById("patient-page-search-doctor-btn");
    let patient_page_my_appointment_btn = document.getElementById("patient-page-my-appointment-btn");
    let patient_page_profile_btn = document.getElementById("patient-page-profile-btn");

    patient_page_search_doctor.style.display='block';
    patient_page_dashboard.style.display='none';
    patient_page_book_appointment.style.display='none';
    patient_page_my_appointment.style.display='none';
    patient_page_profile.style.display='none';

    patient_page_search_doctor_btn.classList.add("active");
    patient_page_dashboard_btn.classList.remove("active");
    patient_page_my_appointment_btn.classList.remove("active");
    patient_page_profile_btn.classList.remove("active");

    $("#patient-page-book-appointment").load("dashboard.php #patient-page-book-appointment > *");
    // $("#patient-page-search-doctor").load("dashboard.php #patient-page-search-doctor > *");

}

function loadDoctors(){

    let searchDoctor = document.getElementById("searchDoctor").value;
    let cityFilter = document.getElementById("cityFilter").value;
    let specialistFilter = document.getElementById("specialistFilter").value;    

    let formData = new FormData();

    formData.append("show-filter-doctor", "filter doctor");

    formData.append("search", searchDoctor);
    formData.append("city", cityFilter);
    formData.append("specialist", specialistFilter);

    $.ajax({

        url:"dashboard.php",

        type:"POST",

        data:formData,

        processData: false,
        contentType: false,

        success:function(response){

            $("#doctorCards").html(response);

        }

    });

}

// ============ PATIENT PAGE SEARCH DOCTOR END ============ //

// ============ PATIENT PAGE BOOK APPOINTMENT ============ //
function patient_page_book_appointment_btn_click(doctorId, doctorName, doctorQualification, doctorExperience, doctorSpecialist, doctorImage) {    

    let patient_page_dashboard = document.getElementById("patient-page-dashboard");
    let patient_page_search_doctor = document.getElementById("patient-page-search-doctor");
    let patient_page_book_appointment = document.getElementById("patient-page-book-appointment");
    let patient_page_my_appointment = document.getElementById("patient-page-my-appointment");
    let patient_page_profile = document.getElementById("patient-page-my-profile");

    let patient_page_dashboard_btn = document.getElementById("patient-page-dashboard-btn");
    let patient_page_search_doctor_btn = document.getElementById("patient-page-search-doctor-btn");
    let patient_page_my_appointment_btn = document.getElementById("patient-page-my-appointment-btn");
    let patient_page_profile_btn = document.getElementById("patient-page-profile-btn");

    patient_page_book_appointment.style.display='block';
    patient_page_search_doctor.style.display='none';
    patient_page_dashboard.style.display='none';
    patient_page_my_appointment.style.display='none';
    patient_page_profile.style.display='none';

    patient_page_search_doctor_btn.classList.add("active");
    patient_page_dashboard_btn.classList.remove("active");
    patient_page_my_appointment_btn.classList.remove("active");
    patient_page_profile_btn.classList.remove("active");


    document.getElementById("selected-doctor-id").value = doctorId;

    document.getElementById("selected-doctor-Name-top").textContent = doctorName;

    document.getElementById("selected-doctor-Specialist").textContent = "("+ doctorSpecialist +")";

    document.getElementById("selected-doctor-img").src = "data:image/jpeg;base64,"+doctorImage;

    document.getElementById("selected-doctor-Name").textContent = doctorName;

    document.getElementById("selected-doctor-qualification").textContent = doctorQualification;

    document.getElementById("selected-doctor-experience").textContent = doctorExperience+"+ years";



}

function book_appointment_select_doctor_part_btn_click() {

    let select_doctor = document.getElementById("book-appointment-select-doctor-part");
    let select_slot = document.getElementById("book-appointment-select-slot-part");
    let confirm = document.getElementById("book-appointment-confirm-part");

    select_doctor.classList.remove("d-none");
    select_slot.classList.add("d-none");
    confirm.classList.add("d-none");


    let part1 = document.getElementById("part-1");
    part1.textContent = "1";
    part1.classList.add("active");
    part1.classList.remove("complete");

    let part1line = document.getElementById("part-1-line");
    part1line.classList.remove("complete-line");

    let part2 = document.getElementById("part-2");
    part2.classList.remove("active");


}

function book_appointment_select_slot_part_btn_click() {

    let select_doctor = document.getElementById("book-appointment-select-doctor-part");
    let select_slot = document.getElementById("book-appointment-select-slot-part");
    let confirm = document.getElementById("book-appointment-confirm-part");

    select_doctor.classList.add("d-none");
    select_slot.classList.remove("d-none");
    confirm.classList.add("d-none");

    let part1 = document.getElementById("part-1");
    part1.textContent = "✓";
    part1.classList.remove("active");
    part1.classList.add("complete");

    let part1line = document.getElementById("part-1-line");
    part1line.classList.add("complete-line");
    
    let part2 = document.getElementById("part-2");
    part2.textContent = "2";
    part2.classList.add("active");
    part2.classList.remove("complete");

    let part2line = document.getElementById("part-2-line");
    part2line.classList.remove("complete-line");

    let part3 = document.getElementById("part-3");
    part3.classList.remove("active");





    let slotsBlock = document.getElementById("doctorAvailableSlots").innerHTML;

    if (slotsBlock.trim() === "") {
        
        let doctorId = document.getElementById("selected-doctor-id").value;

        $.ajax({

            url:"dashboard.php",

            type:"POST",

            data:{ fetch_doctor_available_slots: doctorId },

            success:function(response){

                $("#doctorAvailableSlots").html(response);

            }

        });

    }




}

function selectSlot(button, availabilityId, day, startTime, endTime) {

    document.querySelector(".selected-slot")?.classList.remove("selected-slot");

    button.classList.add("selected-slot");

    document.getElementById("availabilityId").value = availabilityId;
    document.getElementById("selectedDay").value = day;
    document.getElementById("selectedStart").value = startTime;
    document.getElementById("selectedEnd").value = endTime;

    let confirm_part_btn = document.getElementById("book-appointment-confirm-part-btn");

    confirm_part_btn.classList.remove("d-none");

}    

function book_appointment_confirm_part_btn_click() {

    let select_doctor = document.getElementById("book-appointment-select-doctor-part");
    let select_slot = document.getElementById("book-appointment-select-slot-part");
    let confirm = document.getElementById("book-appointment-confirm-part");

    select_doctor.classList.add("d-none");
    select_slot.classList.add("d-none");
    confirm.classList.remove("d-none");

    let doctorName = document.getElementById("selected-doctor-Name").textContent;
    let doctorSpecialist = document.getElementById("selected-doctor-Specialist").textContent;
    let startTime = document.getElementById("selectedStart").value;
    let endTime = document.getElementById("selectedEnd").value;
    let day = document.getElementById("selectedDay").value;

    document.getElementById("confirm-doctor-selected-name").textContent = doctorName;
    document.getElementById("confirm-doctor-selected-specialist").textContent = doctorSpecialist.replace(/[()]/g, "");
    document.getElementById("confirm-doctor-selected-slot").textContent = day+": "+startTime+" - "+endTime;

    let part2 = document.getElementById("part-2");
    part2.textContent = "✓";
    part2.classList.remove("active");
    part2.classList.add("complete");

    let part2line = document.getElementById("part-2-line");
    part2line.classList.add("complete-line");

    let part3 = document.getElementById("part-3");
    part3.classList.add("active");


}

function save_appointment() {
    let availability = document.getElementById("availabilityId").value;
    let doctor = document.getElementById("selected-doctor-id").value;
    let user = document.getElementById("session-user-id").value; 

    // console.log(availability, doctor, patient);
    

    let formData = new FormData();

    formData.append("save-appointment", "save appointment");

    formData.append("availability", availability);
    formData.append("doctor", doctor);
    formData.append("user", user);

    $.ajax({

        url:"dashboard.php",

        type:"POST",

        data:formData,

        processData: false,
        contentType: false,

        success:function(response){

            let res = JSON.parse(response);            

            if(res.status === "success"){

                Swal.fire({
                    icon: "success",
                    title: "Appointment Booked",
                    text: "Your appointment request has been submitted successfully.",
                    confirmButtonColor: "#6c757d"
                }).then(() => {

                    $("#patient-page-my-appointment").load("dashboard.php #patient-page-my-appointment > *");
                    
                    patient_page_my_appointment_btn_click();


                });
                

            }
            else if(res.status === "already_exists"){
                Swal.fire({
                    icon: "warning",
                    title: "Appointment Already Exists",
                    text: "You have already booked this appointment.",
                    confirmButtonColor: "#6c757d"
                });
            }
            else{
                Swal.fire({
                    icon: "error",
                    title: "Booking Failed",
                    text: "Unable to book your appointment. Please try again.",
                    confirmButtonColor: "#6c757d"
                });
            }

        }

    });


}

function patient_cancel_appointment(appointmentId) {

    Swal.fire({
        title: "Cancel Appointment?",
        text: "Are you sure you want to cancel this appointment?",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Yes, Cancel",
        cancelButtonText: "No",
        confirmButtonColor: "#dc3545",
        cancelButtonColor: "#6c757d"
    }).then((result) => {

        if(result.isConfirmed){

            $.ajax({

                url: "dashboard.php",

                type: "POST",

                data: {
                    patient_cancel_appointment: appointmentId
                },

                success: function(response){

                    let res = JSON.parse(response);

                    if(res.status === "success"){

                        Swal.fire({
                            icon: "success",
                            title: "Appointment Cancelled",
                            text: "Your appointment has been cancelled successfully.",
                            confirmButtonColor: "#6c757d"
                        }).then(() => {

                            $("#patient-page-my-appointment")
                            .load("dashboard.php #patient-page-my-appointment > *");

                        });

                    }
                    else{

                        Swal.fire({
                            icon: "error",
                            title: "Cancellation Failed",
                            text: "Unable to cancel your appointment. Please try again.",
                            confirmButtonColor: "#6c757d"
                        });

                    }

                }

            });

        }

    });

}

// ============ PATIENT PAGE BOOK APPOINTMENT END ============ //

// ============ PATIENT PAGE MY APPOINTMENT ============ //
function patient_page_my_appointment_btn_click() {
    
    let patient_page_dashboard = document.getElementById("patient-page-dashboard");
    let patient_page_search_doctor = document.getElementById("patient-page-search-doctor");
    let patient_page_book_appointment = document.getElementById("patient-page-book-appointment");
    let patient_page_my_appointment = document.getElementById("patient-page-my-appointment");
    let patient_page_profile = document.getElementById("patient-page-my-profile");

    let patient_page_dashboard_btn = document.getElementById("patient-page-dashboard-btn");
    let patient_page_search_doctor_btn = document.getElementById("patient-page-search-doctor-btn");
    let patient_page_my_appointment_btn = document.getElementById("patient-page-my-appointment-btn");
    let patient_page_profile_btn = document.getElementById("patient-page-profile-btn");

    patient_page_my_appointment.style.display='block';
    patient_page_book_appointment.style.display='none';
    patient_page_search_doctor.style.display='none';
    patient_page_dashboard.style.display='none';
    patient_page_profile.style.display='none';

    patient_page_my_appointment_btn.classList.add("active");
    patient_page_search_doctor_btn.classList.remove("active");
    patient_page_dashboard_btn.classList.remove("active");
    patient_page_profile_btn.classList.remove("active");

    $("#patient-page-book-appointment").load("dashboard.php #patient-page-book-appointment > *");

    $("#patient-page-my-appointment").load("dashboard.php #patient-page-my-appointment > *");

}
// ============ PATIENT PAGE MY APPOINTMENT END ============ //

// ============ PATIENT PAGE PROFILE ============ //
function patient_page_profile_btn_click() { 
    
    let patient_page_dashboard = document.getElementById("patient-page-dashboard");
    let patient_page_search_doctor = document.getElementById("patient-page-search-doctor");
    let patient_page_book_appointment = document.getElementById("patient-page-book-appointment");
    let patient_page_my_appointment = document.getElementById("patient-page-my-appointment");
    let patient_page_profile = document.getElementById("patient-page-my-profile");

    let patient_page_dashboard_btn = document.getElementById("patient-page-dashboard-btn");
    let patient_page_search_doctor_btn = document.getElementById("patient-page-search-doctor-btn");
    let patient_page_my_appointment_btn = document.getElementById("patient-page-my-appointment-btn");
    let patient_page_profile_btn = document.getElementById("patient-page-profile-btn");

    patient_page_profile.style.display='block';
    patient_page_my_appointment.style.display='none';
    patient_page_book_appointment.style.display='none';
    patient_page_search_doctor.style.display='none';
    patient_page_dashboard.style.display='none';

    patient_page_profile_btn.classList.add("active");
    patient_page_my_appointment_btn.classList.remove("active");
    patient_page_search_doctor_btn.classList.remove("active");
    patient_page_dashboard_btn.classList.remove("active");

    $("#patient-page-book-appointment").load("dashboard.php #patient-page-book-appointment > *");

    $("#patient-page-my-profile").load("dashboard.php #patient-page-my-profile > *");

}
// ============ PATIENT PAGE PROFILE END ============ //

// ============================================== DASHBOARD SCRIPT END ============================================== //


// ============================================== AUTH PAGE SCRIPT ============================================== //


// ============ AUTH PAGE LOGIN ============ //
function showLoginPage() {

    let loginCard = document.getElementById("login-card");
    let registerCard = document.getElementById("register-card");

    registerCard.classList.add("d-none");

    loginCard.classList.remove("d-none");
}

function user_login_btn_click() {

    let email = document.getElementById("signin-email-field").value;
    let password = document.getElementById("signin-password-field").value;

    if(email.trim() === "" || password.trim() === "")
    {

        Swal.fire({
            icon: 'warning',
            title: 'Incomplete Form',
            text: 'All required fields must be filled before proceeding.',
            confirmButtonColor: '#6c757d'
        });

    }
    else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
        Swal.fire({
            icon: 'error',
            title: 'Invalid Email',
            text: 'Please enter a valid email address.',
            confirmButtonColor: '#dc3545'
        }); 
    }
    else{

        let formData = new FormData();

        formData.append("userLoginRequest", "user login");

        formData.append("signin-email-field", email);
        formData.append("sigin-password-field", password);

        $.ajax({
        url: "auth.php",
        type: "POST",
        data: formData,

        processData: false,
        contentType: false,

        success: function(response) {            
            
            let res = JSON.parse(response);

            if(res.status === "success") {

                Swal.fire({
                    icon: 'success',
                    title: 'Login Successful',
                    text: 'Welcome back! Redirecting to your dashboard...',
                    timer: 1500,
                    showConfirmButton: false,
                    timerProgressBar: true
                }).then(() => {
                    window.location.href = 'dashboard.php';
                });
                
            }
            else if (res.status === "not match") {
                Swal.fire({
                    icon: 'error',
                    title: 'Authentication Failed',
                    text: 'Invalid email or password. Please try again.',
                    confirmButtonColor: '#dc3545'
                });
            }
        },

        error: function(xhr){
            console.log(xhr.responseText);

            Swal.fire({
                icon: 'error',
                title: 'Server Error',
                text: 'We are unable to process your request at the moment. Please try again later.',
                confirmButtonColor: '#dc3545'
            });
        } 

    });

    }

}

// ============ AUTH PAGE LOGIN ============ //


// ============ AUTH PAGE REGISTER ============ //
function showRegisterPage() {

    let loginCard = document.getElementById("login-card");
    let registerCard = document.getElementById("register-card");

    registerCard.classList.remove("d-none");

    loginCard.classList.add("d-none");

}

function patient_register_btn_click() {

    let name = document.getElementById("register-name-field").value;
    let email = document.getElementById("register-email-field").value;
    let phone = document.getElementById("register-phone-field").value;
    let address = document.getElementById("register-address-field").value;
    let birth = document.getElementById("register-birth-field").value;
    let gender = document.getElementById("register-gender-field").value;
    let password = document.getElementById("register-password-field").value;
    let confirmpassword = document.getElementById("register-confirm-password-field").value;

    if(name.trim() === "" || email.trim() === "" || phone.trim() === "" || address.trim() === "" || birth.trim() === "" || password.trim() === "" || confirmpassword.trim() === "" )
    {

        Swal.fire({
            icon: 'warning',
            title: 'Incomplete Form',
            text: 'All required fields must be filled before proceeding.',
            confirmButtonColor: '#6c757d'
        });

    }
    else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
        Swal.fire({
            icon: 'error',
            title: 'Invalid Email',
            text: 'Please enter a valid email address.',
            confirmButtonColor: '#dc3545'
        }); 
    }
    else if (password !== confirmpassword) {

        Swal.fire({
            icon: 'error',
            title: 'Password Mismatch',
            text: 'Password and Confirm Password do not match. Please correct and try again.',
            confirmButtonColor: '#dc3545'
        });

    }
    else{

        let formData = new FormData();

        formData.append("savePatientRequest", "save patient");

        formData.append("register-name-field", name);
        formData.append("register-email-field", email);
        formData.append("register-phone-field", phone);
        formData.append("register-address-field", address);
        formData.append("register-birth-field", birth);
        formData.append("register-gender-field", gender);
        formData.append("register-password-field", password);

        $.ajax({
        url: "auth.php",
        type: "POST",
        data: formData,

        processData: false,
        contentType: false,

        success: function(response) {            

            let res = JSON.parse(response);

            if(res.status === "success") {

                Swal.fire({
                    icon: 'success',
                    title: 'Account Created',
                    text: 'Your account has been successfully created.',
                    timer: 1500,
                    showConfirmButton: false,
                    timerProgressBar: true
                }).then(() => {
                    window.location.href = 'dashboard.php';
                });
                
            }
            else if (res.status === "email exists") {
                Swal.fire({
                    icon: 'info',
                    title: 'Email Already Registered',
                    text: 'This email is already associated with an account. Please use a different email or login.',
                    confirmButtonColor: '#0d6efd'
                });
            }
            else if (res.status === "error") {
                Swal.fire({
                    icon: 'error',
                    title: 'Registration Failed',
                    text: 'An unexpected error occurred while processing your request. Please try again.',
                    confirmButtonColor: '#dc3545'
                });
            }
        },

        error: function(xhr){
            console.log(xhr.responseText);

            Swal.fire({
                icon: 'error',
                title: 'Server Error',
                text: 'We are unable to process your request at the moment. Please try again later.',
                confirmButtonColor: '#dc3545'
            });
        } 

    });

    }

}

// ============ AUTH PAGE REGISTER ============ //

// ============================================== AUTH PAGE SCRIPT END ============================================== //

// ============================================== find doctor PAGE SCRIPT ============================================== //

function finddoctor_page_loadDoctors(){

    let searchDoctor = document.getElementById("finddoctor-page-searchDoctor").value;
    let cityFilter = document.getElementById("finddoctor-page-cityFilter").value;
    let specialistFilter = document.getElementById("finddoctor-page-specialistFilter").value;    

    let formData = new FormData();

    formData.append("finddoctor-page-show-filter-doctor", "filter doctor");

    formData.append("search", searchDoctor);
    formData.append("city", cityFilter);
    formData.append("specialist", specialistFilter);

    $.ajax({

        url:"finddoctor.php",

        type:"POST",

        data:formData,

        processData: false,
        contentType: false,

        success:function(response){

            $("#finddoctor-doctorCards").html(response);

        }

    });

}

function view_doctor_profile_btn_click(doctorId){

    $.ajax({

        url:"finddoctor.php",

        type:"POST",

        data:{fetch_doctor_profile_detials:doctorId},

        success:function(response){

            $("#profile_detail_modal_body").html(response);

        },
        error:function(){

            $("#profile_detail_modal_body").html("<p class='text-danger'>Unable to load doctor profile.</p>");

        }

    });

}

// ============================================== find doctor page SCRIPT END ============================================== //
