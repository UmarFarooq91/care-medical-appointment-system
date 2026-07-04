<?php

include 'includes/connection.php';


if(isset($_POST['finddoctor-page-show-filter-doctor']) && $_POST['finddoctor-page-show-filter-doctor'] == "filter doctor"){

    $search = $_POST['search'];

    $city = $_POST['city'];

    $specialist = $_POST['specialist'];

    $query=" SELECT * FROM users_table INNER JOIN doctors_table ON users_table.UserID=doctors_table.UserID WHERE Role='Doctor' AND Status='Active' ";

    if($search!=""){

        $query.=" AND Name LIKE '%$search%' ";

    }

    if($city!=""){

        $query.=" AND CityID='$city' ";

    }

    if($specialist!=""){

        $query.=" AND Specialist='$specialist' ";

    }

    $result=mysqli_query($connection,$query);

    while($doctor=mysqli_fetch_assoc($result)){

        $doctorId = $doctor['DoctorID'];
        $name = $doctor['Name'];
        $qualification = $doctor['Qualification'];
        $experience = $doctor['Experience'];
        $specialist = $doctor['Specialist'];
        $image = base64_encode($doctor['ProfileImage']);
 
        $availability = mysqli_query($connection," SELECT * FROM availability_table WHERE DoctorID = '$doctorId' AND AvailabilityStatus = 'Available' LIMIT 1");

        $available = mysqli_num_rows($availability) > 0;

        ?>

        <div class="col-12 col-md-6 col-lg-4">

            <div class="card h-100">

                <div class="card-body text-center">

                    <img src="data:image/jpeg;base64,<?php echo $image;?>" class="doctor-img">

                    <h5 class="card-title"><?php echo $name;?></h5>

                    <p class="text-muted"><?php echo $specialist;?></p>

                    <?php

                    if($available){

                        ?>

                        <button class="btn available-btn">Available</button>
                        <button type="button" class="btn btn-secondary book-btn">Book Appointment</button>

                        <?php

                    }

                    else{

                        ?>

                        <button class="btn available-btn">Not Available</button>

                        <button class="btn btn-secondary book-btn" disabled>Book Appointment</button>

                        <?php

                    }

                    ?>

                </div>

            </div>

        </div>

        <?php

    }

    exit;

}


if(isset($_POST['fetch_doctor_profile_detials'])){

    $doctorId = (int)$_POST['fetch_doctor_profile_detials'];

    $query = mysqli_query($connection,"
        SELECT *
        FROM doctors_table
        INNER JOIN users_table
        ON doctors_table.UserID = users_table.UserID
        WHERE DoctorID = '$doctorId'
        LIMIT 1
    ");

    if($doctor = mysqli_fetch_assoc($query)){

        $image = base64_encode($doctor['ProfileImage']);
        $name = $doctor['Name'];
        $email = $doctor['Email'];
        $specialist = $doctor['Specialist'];
        $bio = $doctor['Bio'];
        $qualification = $doctor['Qualification'];
        $experience = $doctor['Experience'];
        $phone = $doctor['Phone'];

        // Availability
        $availability = mysqli_query($connection,"
            SELECT *
            FROM availability_table
            WHERE DoctorID='$doctorId'
            AND AvailabilityStatus='Available'
            ORDER BY FIELD(Day,'Mon','Tue','Wed','Thu','Fri','Sat','Sun')
        ");

        $statusText = (mysqli_num_rows($availability) > 0)
            ? "Available for Appointments"
            : "Busy";

        ?>

        <div class="row">

            <!-- Left -->
            <div class="col-lg-4 text-center border-end">

                <img src="data:image/jpeg;base64,<?php echo $image; ?>"
                    class="img-fluid rounded-circle shadow mb-3"
                    style="width:170px;height:170px;object-fit:cover;">

                <h4 class="fw-bold mb-1">
                    <?php echo $name; ?>
                </h4>

                <p class="text-muted fw-semibold mb-3">
                    <?php echo $specialist; ?>
                </p>

                <?php if(mysqli_num_rows($availability)>0){ ?>

                    <span class="badge bg-light text-success border rounded-pill px-3 py-2 fs-6">
                        <?php echo $statusText; ?>
                    </span>

                <?php } else { ?>

                    <span class="badge bg-light text-danger border rounded-pill px-3 py-2 fs-6">
                        <?php echo $statusText; ?>
                    </span>

                <?php } ?>

            </div>

            <!-- Right -->
            <div class="col-lg-8">

                <div class="mb-3">

                    <h6 class="fw-bold">Bio</h6>

                    <p class="text-muted">
                        <?php echo nl2br(htmlspecialchars($bio)); ?>
                    </p>

                </div>

                <hr>

                <p>
                    <strong>Qualification:</strong>
                    <?php echo $qualification; ?>
                </p>

                <p>
                    <strong>Experience:</strong>
                    <?php echo $experience; ?>+ years
                </p>

                <p>
                    <strong>Contact:</strong> 

                    <?php echo $phone; ?> | 

                    <?php echo $email; ?>

                </p>

                <hr>

                <h6 class="fw-bold mb-3">
                    Availability
                </h6>

                <?php

                if(mysqli_num_rows($availability)>0){

                    while($slot=mysqli_fetch_assoc($availability)){

                        ?>

                        <p>

                            <strong>
                                <?php echo $slot['Day']; ?>:
                            </strong>

                            <?php
                                echo date("h:i A",strtotime($slot['StartTime']));
                            ?>

                            -

                            <?php
                                echo date("h:i A",strtotime($slot['EndTime']));
                            ?>

                        </p>

                        <?php

                    }

                }
                else{

                    ?>

                    <p class="text-danger">
                        No availability found.
                    </p>

                    <?php

                }

                ?>

                <div class="mt-4">

                    <a href="auth.php" class="btn btn-secondary px-4">

                        <i class="bi bi-calendar-plus"></i>

                        Book Appointment

                    </a>

                </div>

            </div>

        </div>

        <?php

    }

    exit;
}



?>

<?php require_once 'includes/navbar.php'; ?>
    
<!-- ==================== search doctor ==================== -->
<div class="container finddoctor-page" id="finddoctor-page">

    <div class="row mb-3">
        <div class="col-12 d-flex flex-wrap align-items-center gap-3 finddoctor-page-header">
            <div>
                <h4>Find Your Doctor</h4>
            </div>
        </div>
    </div>  

    <div class="row">

        <div class="col-sm-6 col-md-3 mb-3">
            <input type="text" id="finddoctor-page-searchDoctor" class="form-control" placeholder="Search Doctor" oninput="finddoctor_page_loadDoctors()">
        </div>

        <div class="col-sm-6 col-md-3 mb-3">
            <select id="finddoctor-page-cityFilter" class="form-select" onchange="finddoctor_page_loadDoctors()">
                <option value="">All Cities</option>

                <?php

                    $cities = mysqli_query($connection,"SELECT * FROM cities_table");

                    while($city=mysqli_fetch_assoc($cities)){
                        ?>
                        <option value="<?php echo $city['CityID']; ?>">
                            <?php echo $city['CityName']; ?>
                        </option>
                        <?php
                    }

                ?>

            </select>
        </div>

        <div class="col-sm-6 col-md-3 mb-3">

            <select id="finddoctor-page-specialistFilter" class="form-select" onchange="finddoctor_page_loadDoctors()">

                <option value="">All Specializations</option>

                <?php

                    $specialists = mysqli_query($connection,"SELECT Specialist FROM doctors_table");

                    while($specialist = mysqli_fetch_assoc($specialists)){
                        ?> 
                        <option value="<?php echo $specialist['Specialist']; ?>">
                            <?php echo $specialist['Specialist']; ?>
                        </option>
                        <?php
                    }

                ?>

            </select>

        </div>

    </div>

    <div class="row g-4 doctor-cards" id="finddoctor-doctorCards">

        <?php

            $query = "SELECT * 
                    FROM users_table
                    INNER JOIN doctors_table
                    ON users_table.UserID = doctors_table.UserID
                    WHERE Role='Doctor' AND Status='Active'";

            $result = mysqli_query($connection, $query);

            while ($doctor = mysqli_fetch_assoc($result)) {

                $doctorId      = $doctor['DoctorID'];
                $name          = $doctor['Name'];
                $specialist    = $doctor['Specialist'];

                $image = base64_encode($doctor['ProfileImage']);


                // Doctor Availability
                $availability = mysqli_query($connection, "
                    SELECT *
                    FROM availability_table
                    WHERE DoctorID='$doctorId'
                    AND AvailabilityStatus='Available'
                    LIMIT 1
                ");

                $row = mysqli_fetch_assoc($availability);

                if ($row) {

                    $available = true;

                }
                else {

                    $available = false;

                }

            ?>

            <div class="col-12 col-md-6 col-lg-4">

                <div class="card h-100">

                    <div class="card-body text-center">

                        <img src="data:image/jpeg;base64,<?php echo $image; ?>" class="doctor-img">

                        <h5 class="card-title">
                            <?php echo htmlspecialchars($name); ?>
                        </h5>

                        <p class="text-muted">
                            <?php echo htmlspecialchars($specialist); ?>
                        </p>

                        <?php if ($available) { ?>

                            <button class="btn available-btn">
                                Available
                            </button>

                        <?php } else { ?>

                            <button class="btn busy-btn">
                                Busy
                            </button>

                        <?php } ?>

                        <button
                            type="button"
                            class="btn btn-secondary book-btn"
                            data-bs-toggle="modal"
                            data-bs-target="#doctor_view_profile_modal"
                            onclick='view_doctor_profile_btn_click(<?php echo $doctorId; ?>)'>
                            View Profile
                        </button>

                    </div>

                </div>

            </div>

            <?php

            }

        ?>

    </div>

</div>


<div class="modal fade" id="doctor_view_profile_modal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">

            <!-- Header -->
            <div class="modal-header">
                <h5 class="modal-title fw-bold">
                    Doctor Profile
                </h5>

                <button type="button" class="btn-close"
                    data-bs-dismiss="modal"></button>
            </div>

            <!-- Body -->
            <div class="modal-body" id="profile_detail_modal_body">

            </div>

        </div>
    </div>
</div>


<!-- ==================== search doctor end ==================== -->



<?php require_once 'includes/footer.php'; ?>