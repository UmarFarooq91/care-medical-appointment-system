<?php 

include 'includes/connection.php';

session_start();

// Check if user is logged in
if ( !isset($_SESSION['UserID']) || !isset($_SESSION['Role']) || !isset($_SESSION['Status']) ) {

    header("Location: auth.php");
    exit;

}

// Session values
$sessionId = $_SESSION['UserID'];
$sessionRole = $_SESSION['Role'];
$sessionStatus = $_SESSION['Status'];

// Logout
if (isset($_POST['logout-btn'])) {

    $_SESSION = [];

    session_destroy();

    header("Location: auth.php");
    exit;

}


// admin dashboard logic

if(isset($_POST['city_name'])){
    $cityName = $_POST['city_name'];

    if(!empty($cityName))
    {
        $query = "INSERT INTO cities_table (CityName) VALUES ('$cityName')";
        $result = mysqli_query($connection, $query);

        if($result){
            echo "success";
            exit;
        } else {
            echo "DB error";
            exit;
        }
    }
}

if(isset($_POST['delete_city_id'])){
    $cityId = $_POST['delete_city_id'];

    if(!empty($cityId))
    {
        // STEP 1: check dependencies
        $checkQuery = mysqli_query(
            $connection,
            "SELECT COUNT(*) AS totalDoctors FROM doctors_table WHERE CityID = '$cityId'"
        );

        $checkResult = mysqli_fetch_assoc($checkQuery);

        if($checkResult['totalDoctors'] > 0)
        {
            echo "error_has_doctors";
            exit;
        }

        // STEP 2: safe delete
        $deleteQuery = mysqli_query(
            $connection,
            "DELETE FROM cities_table WHERE CityID = '$cityId'"
        );

        if($deleteQuery){
            echo "success";
            exit;
        } else {
            echo "db_error";
            exit;
        }
    }
}

if(isset($_POST['requestFetch'])){

    $request = $_POST['requestFetch'];

    if(!empty($request) && $request == "fetch cities")
    {
        $selectQuery = mysqli_query($connection, "SELECT * FROM cities_table");

        while ($city = mysqli_fetch_assoc($selectQuery)) {
            $cityId = $city['CityID'];
            $cityName = $city['CityName'];
            echo '<option value="'.$city['CityID'].'">'.$city['CityName'].'</option>';

        }

        exit;

    }
}

if(isset($_POST['saveDoctorRequest'])){
    if($_POST['saveDoctorRequest'] == "save doctor"){

        // START TRANSACTION
        mysqli_begin_transaction($connection);

        try {

            if(
                empty($_POST['add-doctors-modal-name-field']) ||
                empty($_POST['add-doctors-modal-email-field']) ||
                empty($_POST['add-doctors-modal-password-field']) ||
                empty($_POST['add-doctors-modal-status-field']) ||
                empty($_POST['add-doctors-modal-specialist-field']) ||
                empty($_POST['add-doctors-modal-qualification-field']) ||
                empty($_POST['add-doctors-modal-experience-field']) ||
                empty($_POST['add-doctors-modal-phone-field']) ||
                empty($_POST['add-doctors-modal-city-field']) ||
                empty($_POST['add-doctors-modal-bio-field']) ||
                empty($_FILES['add-doctors-modal-photo-field']['tmp_name'])
            )
            {
                throw new Exception("error: All fields are required");  
                exit;
            }

            // 1. USER INSERT
            $name = mysqli_real_escape_string($connection, $_POST['add-doctors-modal-name-field']);
            $email = mysqli_real_escape_string($connection, $_POST['add-doctors-modal-email-field']);

            $password = password_hash(
                $_POST['add-doctors-modal-password-field'],
                PASSWORD_BCRYPT
            );

            $role = "Doctor";
            $status = $_POST['add-doctors-modal-status-field'];

            $insertUser = "INSERT INTO users_table
            (Name, Email, Password, Role, Status)
            VALUES
            ('$name','$email','$password','$role','$status')";

            $result1 = mysqli_query($connection, $insertUser);

            if(!$result1){
                throw new Exception("User insert failed");
            }

            $userID = mysqli_insert_id($connection);

            // 1 MB Maximum Image Size
            $maxImageSize = 1 * 1024 * 1024; // 1MB

            if ($_FILES['add-doctors-modal-photo-field']['size'] > $maxImageSize) {
                throw new Exception("Image size must not exceed 1 MB.");
            }

            // 2. IMAGE
            $image = file_get_contents($_FILES['add-doctors-modal-photo-field']['tmp_name']);
            $imageBytes = addslashes($image);


            // 3. DOCTOR INSERT
            $specialist = mysqli_real_escape_string($connection, $_POST['add-doctors-modal-specialist-field']);
            $qualification = mysqli_real_escape_string($connection, $_POST['add-doctors-modal-qualification-field']);
            $experience = mysqli_real_escape_string($connection, $_POST['add-doctors-modal-experience-field']);
            $phone = mysqli_real_escape_string($connection, $_POST['add-doctors-modal-phone-field']);
            $city = mysqli_real_escape_string($connection, $_POST['add-doctors-modal-city-field']);
            $bio = mysqli_real_escape_string($connection, $_POST['add-doctors-modal-bio-field']);

            $insertDoctor = "INSERT INTO doctors_table
            (UserID, Specialist, Qualification, Experience, Phone, CityID, Bio, ProfileImage)
            VALUES
            ('$userID','$specialist','$qualification','$experience','$phone','$city','$bio','$imageBytes')";

            $result2 = mysqli_query($connection, $insertDoctor);

            if(!$result2){
                throw new Exception("Doctor insert failed");
            }

            // ✅ ALL OK → COMMIT
            mysqli_commit($connection);

            echo "success";
            exit;

        }
        catch(Exception $e){
            // ❌ ERROR → ROLLBACK (UNDO EVERYTHING)
            mysqli_rollback($connection);

            echo "error: " . $e->getMessage();
            exit;
        }
    }
}

if(isset($_POST['delete_doctor_id'])){
    $doctorId = $_POST['delete_doctor_id'];

    if(!empty($doctorId))
    {
        // STEP 1: check dependencies
        // $checkQuery = mysqli_query(
        //     $connection,
        //     "SELECT COUNT(*) AS totalDoctors FROM doctors_table WHERE CityID = '$cityId'"
        // );

        // $checkResult = mysqli_fetch_assoc($checkQuery);

        // if($checkResult['totalDoctors'] > 0)
        // {
        //     echo "error_has_doctors";
        //     exit;
        // }

        // STEP 2: safe delete

        $selectQuery = mysqli_query($connection, "SELECT UserID FROM doctors_table WHERE DoctorID = '$doctorId'");

        $row = mysqli_fetch_assoc($selectQuery);

        $userId = $row['UserID'];

        $deleteQuery1 = mysqli_query($connection, "DELETE FROM users_table WHERE UserID = '$userId'");

        $deleteQuery2 = mysqli_query($connection, "DELETE FROM doctors_table WHERE DoctorID = '$doctorId'");

        $deleteQuery3 = mysqli_query($connection, "DELETE FROM appointments_table WHERE DoctorID = '$doctorId'");

        $deleteQuery4 = mysqli_query($connection, "DELETE FROM availability_table WHERE DoctorID = '$doctorId'");

        if($deleteQuery1 && $deleteQuery2 && $deleteQuery3 && $deleteQuery4){
            echo "success";
            exit;
        } else {
            echo "db_error";
            exit;
        }
    }
}

if(isset($_POST['fetch_doctor_status_city'])){

    $userId = $_POST['fetch_doctor_status_city'];

    if(!empty($userId))
    {

        $citiesHtml = "";

        $selectQuery = mysqli_query($connection, "SELECT * FROM cities_table");

        while ($city = mysqli_fetch_assoc($selectQuery)) {
            $cityId = $city['CityID'];
            $cityName = $city['CityName'];
            $citiesHtml .= '<option value="'.$cityId.'">'.$cityName.'</option>';

        }

        $selectStatus = mysqli_query($connection, "SELECT Status FROM users_table WHERE UserID = '$userId'");

        $row1 = mysqli_fetch_assoc($selectStatus);

        $selectIds = mysqli_query($connection, "SELECT CityID, DoctorID FROM doctors_table WHERE UserID = '$userId'");

        $row2 = mysqli_fetch_assoc($selectIds);



        echo json_encode([ "status" => $row1['Status'], "cities" => $citiesHtml, "cityId" => $row2['CityID'], "doctorId" => $row2['DoctorID'], "userId" => $userId ]);

        exit;

    }
}

if(isset($_POST['edit_doctor_id']) && isset($_POST['edit_user_id']) && isset($_POST['edit_user_status']) && isset($_POST['edit_doctor_city'])){

    $doctorId = $_POST['edit_doctor_id'];
    $userId = $_POST['edit_user_id'];

    $status = $_POST['edit_user_status'];
    $city = $_POST['edit_doctor_city'];

    if(!empty($doctorId) && !empty($userId) && !empty($status) && !empty($city))
    {
        $updateQuery1 = mysqli_query($connection, "UPDATE users_table SET Status = '$status' WHERE UserID = '$userId'");

        $updateQuery2 = mysqli_query($connection, "UPDATE doctors_table SET CityID = '$city' WHERE DoctorID = '$doctorId'");

        if ($updateQuery1 && $updateQuery2) {
            echo "Success";
            exit;
        }else{
            echo "DB error";
            exit;
        } 
    }
}

if(isset($_POST['fetch_patient_status'])){

    $userId = $_POST['fetch_patient_status'];

    if(!empty($userId))
    {

        $selectStatus = mysqli_query($connection, "SELECT Status FROM users_table WHERE UserID = '$userId'");
        $users = mysqli_fetch_assoc($selectStatus);

        echo json_encode([ "status" => $users['Status'], "userId" => $userId ]);
        exit;

    }
}

if(isset($_POST['edit_patient_user_id']) && isset($_POST['edit_patient_user_status'])){

    $userId = $_POST['edit_patient_user_id'];

    $status = $_POST['edit_patient_user_status'];

    if(!empty($userId) && !empty($status))
    {
        $updateQuery = mysqli_query($connection, "UPDATE users_table SET Status = '$status' WHERE UserID = '$userId'");

        if ($updateQuery) {
            echo "Success";
            exit;
        }else{
            echo "DB error";
            exit;
        } 
    }
}

if( isset($_POST['delete_patient_id']) && isset($_POST['delete_user_id']) ){
    $patientId = $_POST['delete_patient_id'];
    $userId = $_POST['delete_user_id'];

    if(!empty($patientId) && !empty($userId) )
    {

        $deleteQuery1 = mysqli_query($connection, "DELETE FROM users_table WHERE UserID = '$userId'");

        $deleteQuery2 = mysqli_query($connection, "DELETE FROM patients_table WHERE PatientID = '$patientId'");

        $deleteQuery3 = mysqli_query($connection, "DELETE FROM appointments_table WHERE PatientID = '$patientId'");

        if($deleteQuery1 && $deleteQuery2 && $deleteQuery3){
            echo "success";
            exit;
        } else {
            echo "db_error";
            exit;
        }
    }
}

if(isset($_POST['saveDiseasesRequest']) && $_POST['saveDiseasesRequest'] == "save disease"){

        // START TRANSACTION
        mysqli_begin_transaction($connection);

        try {

            if(
                empty($_POST['add-diseases-modal-name-field']) ||
                empty($_POST['add-diseases-modal-symptoms-field']) ||
                empty($_POST['add-diseases-modal-prevention-field']) ||
                empty($_POST['add-diseases-modal-cure-field'])
            )
            {
                throw new Exception("error: All fields are required");
                exit;
            }

            // 1. USER INSERT
            $name = mysqli_real_escape_string($connection, $_POST['add-diseases-modal-name-field']);
            $symptoms = mysqli_real_escape_string($connection, $_POST['add-diseases-modal-symptoms-field']);
            $prevention = mysqli_real_escape_string($connection, $_POST['add-diseases-modal-prevention-field']);
            $cure = mysqli_real_escape_string($connection, $_POST['add-diseases-modal-cure-field']);

            $insertDisease = "INSERT INTO diseases_table (DiseaseName, Symptoms, Prevention, Cure) 
                            VALUES ('$name','$symptoms','$prevention','$cure')";

            $result = mysqli_query($connection, $insertDisease);

            if(!$result){
                throw new Exception("User insert failed");
            }

            // ✅ ALL OK → COMMIT
            mysqli_commit($connection);

            echo "success";
            exit;

        }
        catch(Exception $e)
        {
            // ❌ ERROR → ROLLBACK (UNDO EVERYTHING)
            mysqli_rollback($connection);

            echo "Error: " . $e->getMessage();
            exit;
        }
}

if(isset($_POST['fetch_disease'])){

    $diseaseId = $_POST['fetch_disease'];

    if(!empty($diseaseId))
    {

        $selectQuery = mysqli_query($connection, "SELECT * FROM diseases_table WHERE DiseaseID = '$diseaseId'");

        $disease = mysqli_fetch_assoc($selectQuery);

        $name = $disease['DiseaseName'];
        $symptoms = $disease['Symptoms'];
        $prevention = $disease['Prevention'];
        $cure = $disease['Cure'];


        echo json_encode([ "name" => $name, "symptoms" => $symptoms, "prevention" => $prevention, "cure" => $cure]);

        exit;

    }
}

if(isset($_POST['editDiseaseRequest']) && $_POST['editDiseaseRequest'] == "edit disease"){

        // START TRANSACTION
        mysqli_begin_transaction($connection);

        try {

            if(
                empty($_POST['edit-diseases-modal-name-field']) ||
                empty($_POST['edit-diseases-modal-symptoms-field']) ||
                empty($_POST['edit-diseases-modal-prevention-field']) ||
                empty($_POST['edit-diseases-modal-cure-field'])
            )
            {
                throw new Exception("error: All fields are required");
                exit;
            }

            // 1. USER INSERT

            $diseaseId = $_POST['editDiseaseId'];

            $name = mysqli_real_escape_string($connection, $_POST['edit-diseases-modal-name-field']);
            $symptoms = mysqli_real_escape_string($connection, $_POST['edit-diseases-modal-symptoms-field']);
            $prevention = mysqli_real_escape_string($connection, $_POST['edit-diseases-modal-prevention-field']);
            $cure = mysqli_real_escape_string($connection, $_POST['edit-diseases-modal-cure-field']);
            
            $updateQuery = mysqli_query($connection, "UPDATE diseases_table SET DiseaseName = '$name', Symptoms = '$symptoms', Prevention = '$prevention', Cure = '$cure'  WHERE DiseaseID = '$diseaseId'");

            if(!$updateQuery){
                throw new Exception("Disease Update Failed");
            }

            // ✅ ALL OK → COMMIT
            mysqli_commit($connection);

            echo "success";
            exit;

        }
        catch(Exception $e)
        {
            // ❌ ERROR → ROLLBACK (UNDO EVERYTHING)
            mysqli_rollback($connection);

            echo "Error: " . $e->getMessage();
            exit;
        }
}

if( isset($_POST['delete_disease_id']) ){

    $diseaseId = $_POST['delete_disease_id'];

    if(!empty($diseaseId)){

        $deleteQuery = mysqli_query($connection, "DELETE FROM diseases_table WHERE DiseaseID = '$diseaseId'");

        if($deleteQuery){
            echo "success";
            exit;
        } else {
            echo "db_error";
            exit;
        }
    }
}

if(isset($_POST['saveNewsRequest']) && $_POST['saveNewsRequest'] == "save news"){

        // START TRANSACTION
        mysqli_begin_transaction($connection);

        try {

            if(
                empty($_POST['add-news-modal-title-field']) ||
                empty($_POST['add-news-modal-description-field']) ||
                empty($_POST['add-news-modal-date-field']) ||
                empty($_POST['add-news-modal-status-field'])
            )
            {
                throw new Exception("error: All fields are required");
                exit;
            }

            // 1. USER INSERT
            $title = mysqli_real_escape_string($connection, $_POST['add-news-modal-title-field']);
            $description = mysqli_real_escape_string($connection, $_POST['add-news-modal-description-field']);
            $date = mysqli_real_escape_string($connection, $_POST['add-news-modal-date-field']);
            $status = mysqli_real_escape_string($connection, $_POST['add-news-modal-status-field']);

            $insertNews = "INSERT INTO medicalnews_table (Title, Description, Date, Status) 
                            VALUES ('$title','$description','$date','$status')";

            $result = mysqli_query($connection, $insertNews);

            if(!$result){
                throw new Exception("User insert failed");
            }

            // ✅ ALL OK → COMMIT
            mysqli_commit($connection);

            echo "success";
            exit;

        }
        catch(Exception $e)
        {
            // ❌ ERROR → ROLLBACK (UNDO EVERYTHING)
            mysqli_rollback($connection);

            echo "Error: " . $e->getMessage();
            exit;
        }
}

if(isset($_POST['fetch_news'])){

    $newsId = $_POST['fetch_news'];

    if(!empty($newsId))
    {

        $selectQuery = mysqli_query($connection, "SELECT * FROM medicalnews_table WHERE NewsID = '$newsId'");

        $news = mysqli_fetch_assoc($selectQuery);

        $title = $news['Title'];
        $description = $news['Description'];
        $date = $news['Date'];
        $status = $news['Status'];


        echo json_encode([ "title" => $title, "description" => $description, "date" => $date, "status" => $status]);

        exit;

    }
}

if(isset($_POST['editNewsRequest']) && $_POST['editNewsRequest'] == "edit news"){

        // START TRANSACTION
        mysqli_begin_transaction($connection);

        try {

            if(
                empty($_POST['edit-news-modal-title-field']) ||
                empty($_POST['edit-news-modal-description-field']) ||
                empty($_POST['edit-news-modal-date-field']) ||
                empty($_POST['edit-news-modal-status-field'])
            )
            {
                throw new Exception("error: All fields are required");
                exit;
            }

            // 1. USER INSERT

            $newsId = $_POST['editNewsId'];

            $title = mysqli_real_escape_string($connection, $_POST['edit-news-modal-title-field']);
            $description = mysqli_real_escape_string($connection, $_POST['edit-news-modal-description-field']);
            $date = mysqli_real_escape_string($connection, $_POST['edit-news-modal-date-field']);
            $status = mysqli_real_escape_string($connection, $_POST['edit-news-modal-status-field']);
            
            $updateQuery = mysqli_query($connection, "UPDATE medicalnews_table SET Title = '$title', Description = '$description', Date = '$date', Status = '$status'  WHERE NewsID = '$newsId'");

            if(!$updateQuery){
                throw new Exception("Disease Update Failed");
            }

            // ✅ ALL OK → COMMIT
            mysqli_commit($connection);

            echo "success";
            exit;

        }
        catch(Exception $e)
        {
            // ❌ ERROR → ROLLBACK (UNDO EVERYTHING)
            mysqli_rollback($connection);

            echo "Error: " . $e->getMessage();
            exit;
        }
}

if( isset($_POST['delete_news_id']) ){

    $newsId = $_POST['delete_news_id'];

    if(!empty($newsId)){

        $deleteQuery = mysqli_query($connection, "DELETE FROM medicalnews_table WHERE NewsID = '$newsId'");

        if($deleteQuery){
            echo "success";
            exit;
        } else {
            echo "db_error";
            exit;
        }
    }
}




// doctor dashboard logic

if(isset($_POST['updateDoctorDetails']) && $_POST['updateDoctorDetails'] == "doctor update"){

    // START TRANSACTION
    mysqli_begin_transaction($connection);

    try {

        // 1. USER INSERT

        $userId = $_POST['id'];
        $name = mysqli_real_escape_string($connection, $_POST['name']);
        $email = mysqli_real_escape_string($connection, $_POST['email']);
        $specialist = mysqli_real_escape_string($connection, $_POST['specialist']);
        $qualification = mysqli_real_escape_string($connection, $_POST['qualification']);
        $experience = mysqli_real_escape_string($connection, $_POST['experience']);
        $phone = mysqli_real_escape_string($connection, $_POST['phone']);
        $bio = mysqli_real_escape_string($connection, $_POST['bio']);
        $plainPassword = mysqli_real_escape_string($connection, $_POST['password']);

        $checkEmail = mysqli_query($connection, "SELECT UserID FROM users_table WHERE Email='$email' AND UserID != '$userId' ");

        if(mysqli_num_rows($checkEmail) > 0){

            mysqli_rollback($connection);

            echo json_encode(["status" => "email exists"]);

            exit;

        }
        else{

            if(!empty($_FILES['image']['tmp_name'])){

                $image = file_get_contents($_FILES['image']['tmp_name']);
                $imageBytes = addslashes($image);

                if (empty($plainPassword)) {
                    $updateUser = mysqli_query($connection, "UPDATE users_table SET Name = '$name', Email = '$email' WHERE UserID = '$userId'");

                    $updateDoctor = mysqli_query($connection, "UPDATE doctors_table SET Specialist = '$specialist', Qualification = '$qualification', Experience = '$experience', Phone = '$phone', Bio = '$bio', ProfileImage = '$imageBytes' WHERE UserID = '$userId'");

                    // ✅ ALL OK → COMMIT

                    if($updateUser && $updateDoctor)
                    {
                        mysqli_commit($connection);

                        echo json_encode(["status" => "success"]);
                        exit;
                    }
                    else{
                        mysqli_rollback($connection);
                        echo json_encode(["status"=>"error"]);
                        exit;
                    }

                }

                if (!empty($plainPassword)) {

                    $password = password_hash( mysqli_real_escape_string($connection, $_POST['password']), PASSWORD_BCRYPT );

                    $updateUser = mysqli_query($connection, "UPDATE users_table SET Name = '$name', Email = '$email', Password = '$password' WHERE UserID = '$userId'");

                    $updateDoctor = mysqli_query($connection, "UPDATE doctors_table SET Specialist = '$specialist', Qualification = '$qualification', Experience = '$experience', Phone = '$phone', Bio = '$bio', ProfileImage = '$imageBytes' WHERE UserID = '$userId'");

                    // ✅ ALL OK → COMMIT
                    if($updateUser && $updateDoctor)
                    {
                        mysqli_commit($connection);

                        echo json_encode(["status" => "success"]);
                        exit;
                    }
                    else{
                        mysqli_rollback($connection);
                        echo json_encode(["status"=>"error"]);
                        exit;
                    }

                }
            }
            else{

                if (empty($plainPassword)) {
                    $updateUser = mysqli_query($connection, "UPDATE users_table SET Name = '$name', Email = '$email' WHERE UserID = '$userId'");

                    $updateDoctor = mysqli_query($connection, "UPDATE doctors_table SET Specialist = '$specialist', Qualification = '$qualification', Experience = '$experience', Phone = '$phone', Bio = '$bio' WHERE UserID = '$userId'");

                    // ✅ ALL OK → COMMIT
                    if($updateUser && $updateDoctor)
                    {
                        mysqli_commit($connection);

                        echo json_encode(["status" => "success"]);
                        exit;
                    }
                    else{
                        mysqli_rollback($connection);
                        echo json_encode(["status"=>"error"]);
                        exit;
                    }

                }

                if (!empty($plainPassword)) {

                    $password = password_hash( mysqli_real_escape_string($connection, $_POST['password']), PASSWORD_BCRYPT );

                    $updateUser = mysqli_query($connection, "UPDATE users_table SET Name = '$name', Email = '$email', Password = '$password' WHERE UserID = '$userId'");

                    $updateDoctor = mysqli_query($connection, "UPDATE doctors_table SET Specialist = '$specialist', Qualification = '$qualification', Experience = '$experience', Phone = '$phone', Bio = '$bio' WHERE UserID = '$userId'");

                    // ✅ ALL OK → COMMIT
                    if($updateUser && $updateDoctor)
                    {
                        mysqli_commit($connection);

                        echo json_encode(["status" => "success"]);
                        exit;
                    }
                    else{
                        mysqli_rollback($connection);
                        echo json_encode(["status"=>"error"]);
                        exit;
                    }

                }
            }

        }

    }
    catch(Exception $e)
    {
        // ❌ ERROR → ROLLBACK (UNDO EVERYTHING)
        mysqli_rollback($connection);

        echo json_encode(["status"=>"error"]);
        exit;
    }

}


if(isset($_POST['saveAvailability']) && $_POST['saveAvailability'] == "save availability"){

    $userId = $_POST['userId'];
    $day = $_POST['day'];
    $status = $_POST['status'];

    $selectId = mysqli_query($connection, "SELECT DoctorID FROM doctors_table WHERE UserID = '$userId'");

    $row = mysqli_fetch_assoc($selectId);

    $doctorId = $row['DoctorID'];

    // Pehle us day ki purani availability delete kar do
    mysqli_query($connection, "DELETE FROM availability_table WHERE DoctorID = '$doctorId' AND Day = '$day'");

    // Doctor not available
    if($status == "Not Available"){
        mysqli_query($connection,
        "INSERT INTO availability_table (DoctorID, Day, AvailabilityStatus) VALUES ('$doctorId', '$day', 'Not Available')");

        echo json_encode(["status"=>"success"]);
        exit;
    }

    // Slot 1
    if(isset($_POST['slot1Start']) && isset($_POST['slot1End'])){
        mysqli_query($connection,
        "INSERT INTO availability_table (DoctorID, Day, StartTime, EndTime, AvailabilityStatus) VALUES
        ('$doctorId', '$day', '{$_POST['slot1Start']}', '{$_POST['slot1End']}', 'Available')");
    }

    // Slot 2
    if(isset($_POST['slot2Start']) && isset($_POST['slot2End'])){
        mysqli_query($connection, "INSERT INTO availability_table (DoctorID, Day, StartTime, EndTime, AvailabilityStatus)
        VALUES
        ( '$doctorId', '$day', '{$_POST['slot2Start']}', '{$_POST['slot2End']}', 'Available')");
    }

    // Slot 3
    if(isset($_POST['slot3Start']) && isset($_POST['slot3End'])){
        mysqli_query($connection, "INSERT INTO availability_table (DoctorID, Day, StartTime, EndTime, AvailabilityStatus)
        VALUES
        ( '$doctorId', '$day', '{$_POST['slot3Start']}', '{$_POST['slot3End']}', 'Available' )");
    }

    echo json_encode(["status"=>"success"]);
    exit;

}

if(isset($_POST['show-filter-doctor']) && $_POST['show-filter-doctor'] == "filter doctor"){

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
                        <button type="button" class="btn btn-secondary book-btn"
                        onclick='patient_page_book_appointment_btn_click(
                        <?php echo json_encode($doctorId); ?>,
                        <?php echo json_encode($name); ?>,
                        <?php echo json_encode($qualification); ?>,
                        <?php echo json_encode($experience); ?>,
                        <?php echo json_encode($specialist); ?>,
                        <?php echo json_encode($image); ?>
                        )'>
                        Book Appointment</button>

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

if (isset($_POST['fetch_doctor_available_slots'])) {
    
    $doctorId = $_POST['fetch_doctor_available_slots'];

    $days = ['Mon','Tue','Wed','Thu','Fri','Sat','Sun'];

    foreach($days as $day){

        $selectSlots = mysqli_query($connection," SELECT * FROM availability_table WHERE DoctorID = '$doctorId' AND Day = '$day' AND AvailabilityStatus = 'Available' ");

        // Agar is day koi available slot nahi hai to is day ko skip kar do
        if(mysqli_num_rows($selectSlots) == 0){
            continue;
        }

        echo '<div class="available-time mb-4 mt-4">';
        echo '<div class="slot-title">'.$day.'</div>';

        while($slot = mysqli_fetch_assoc($selectSlots)){

            $start = date("h:i A", strtotime($slot['StartTime']));
            $end = date("h:i A", strtotime($slot['EndTime']));

            echo '<button type="button" class="btn slot" onclick="selectSlot(this,'.$slot['AvailabilityID'].', \''.$day.'\', \''.$start.'\', \''.$end.'\')">'.$start.' - '.$end.'</button>';

        }

        echo '</div>';
    }

    exit;
}

if(isset($_POST['save-appointment']) && $_POST['save-appointment'] == "save appointment"){

    $availabilityId = $_POST['availability'];
    $doctorId = $_POST['doctor'];
    $userId = $_POST['user'];

    $selectpatientid = mysqli_query($connection, "SELECT PatientID FROM patients_table WHERE UserID = '$userId' ");

    $row = mysqli_fetch_assoc($selectpatientid);

    $patientId = $row['PatientID'];


    $selectQuery = mysqli_query($connection, "SELECT * FROM appointments_table WHERE DoctorID = '$doctorId' AND PatientID = '$patientId' AND AvailabilityID = '$availabilityId' AND Status != 'Cancelled'");

    if(mysqli_num_rows($selectQuery) == 0){

        $insertQuery = mysqli_query($connection, "INSERT INTO appointments_table (DoctorID, PatientID, AvailabilityID, Status) VALUES ('$doctorId', '$patientId', '$availabilityId', 'Pending')");

        if($insertQuery){

            $appointmentId = mysqli_insert_id($connection);

            $appointmentNumber = "APT-" . str_pad($appointmentId, 7, "0", STR_PAD_LEFT);

            mysqli_query($connection, "UPDATE appointments_table SET AppointmentNumber = '$appointmentNumber' WHERE AppointmentID = '$appointmentId'");

            echo json_encode(["status"=>"success"]);

            exit;

        }
        else{
            echo json_encode(["status"=>"error"]);
            exit;
        }

    }

    echo json_encode(["status"=>"already_exists"]);

    exit;
}

if(isset($_POST['fetch_appointment_status'])){
    $appointmentId = $_POST['fetch_appointment_status'];


    $selectStatus = mysqli_query($connection, "SELECT Status FROM appointments_table WHERE AppointmentID = '$appointmentId'");
    $appointment = mysqli_fetch_assoc($selectStatus);

    echo json_encode([ "status" => $appointment['Status'], "message" => "Success" ]);
    exit;

}

if(isset($_POST['edit_appointmentid']) && isset($_POST['edit_appointment_status'])){

    $appointmentId = $_POST['edit_appointmentid'];

    $status = $_POST['edit_appointment_status'];

    if(!empty($appointmentId) && !empty($status))
    {
        $updateQuery = mysqli_query($connection, "UPDATE appointments_table SET Status = '$status' WHERE AppointmentID = '$appointmentId'");

        if ($updateQuery) {
            echo "Success";
            exit;
        }else{
            echo "DB error";
            exit;
        } 
    }
}


// patient dashboard logic

if(isset($_POST['patient_cancel_appointment'])){

    $appointmentId = $_POST['patient_cancel_appointment'];

    $updateQuery = mysqli_query($connection, "UPDATE appointments_table SET Status = 'Cancelled' WHERE AppointmentID = '$appointmentId'");

    if ($updateQuery) {

        echo json_encode(["status"=>"success"]);
        exit;

    }
    else{

        echo json_encode(["status"=>"error"]);
        exit;

    }

    exit;
}


?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    <link rel="stylesheet" href="css/style.css">

    <title>Document</title>

</head>
<body class="dashboard-body">

    <form method="post">

        <input type="hidden" id="session-user-id" value="<?php echo $sessionId; ?>">
        <input type="hidden" id="session-user-role" value="<?php echo $sessionRole; ?>">
        <input type="hidden" id="session-user-status" value="<?php echo $sessionStatus; ?>">
        <!-- ==================== DASHBOARD SIDEBAR ==================== -->
        <aside class="sidebar" id="sidebar">


            <!-- admin header -->
            <div class="sidebar-header d-none" id="admin-sidebar-header">
                <span class="icon">+</span>
                <span class="sidebar-brand">Care Admin</span>
                <button type="button" class="sidebar-in-toggle" onclick="toggleSidebar()">
                    <i class="bi bi-chevron-bar-left"></i>
                </button>
            </div>
            
            <!-- doctor header -->
            <div class="sidebar-header d-none" id="doctor-sidebar-header">
                <span class="icon">+</span>
                <span class="sidebar-brand">Doctor Panel</span>
                <button type="button" class="sidebar-in-toggle" onclick="toggleSidebar()">
                    <i class="bi bi-chevron-bar-left"></i>
                </button>
            </div>
            
                <!-- patient header -->
            <div class="sidebar-header d-none" id="patient-sidebar-header">
                <span class="icon">+</span>
                <span class="sidebar-brand">Patient Panel</span>
                <button type="button" class="sidebar-in-toggle" onclick="toggleSidebar()">
                    <i class="bi bi-chevron-bar-left"></i>
                </button>
            </div>


            <!-- Navigation -->
            <nav class="sidebar-nav">

                <!-- admin list -->
                <ul class="list-unstyled  d-none" id="admin-sidebar-list">
                    <li class="nav-item">
                        <button type="button" onclick="admin_page_dashboard_btn_click()" id="admin-page-dashboard-btn" class="nav-link active">
                            <i class="bi bi-grid-fill"></i>
                            <span class="nav-text">Dashboard</span>
                        </button>
                    </li>
                    <li class="nav-item">
                        <button type="button" onclick="admin_page_cities_btn_click()" id="admin-page-cities-btn" class="nav-link">
                            <i class="bi bi-building"></i>
                            <span class="nav-text">Cities</span>
                        </button>
                    </li>
                    <li class="nav-item">
                        <button type="button" onclick="admin_page_doctors_btn_click()" id="admin-page-doctors-btn" class="nav-link">
                            <i class="bi bi-person-badge"></i>
                            <span class="nav-text">Doctors</span>
                        </button>
                    </li>
                    <li class="nav-item">
                        <button type="button" onclick="admin_page_patients_btn_click()" id="admin-page-patients-btn" class="nav-link">
                            <i class="bi bi-people-fill"></i>
                            <span class="nav-text">Patients</span>
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" type="button" onclick="admin_page_appointments_btn_click()" id="admin-page-appointments-btn">
                            <i class="bi bi-calendar-check"></i>
                            <span class="nav-text">Appointments</span>
                        </button>
                    </li>
                    <li class="nav-item">
                        <button type="button" class="nav-link" id="admin-page-diseases-btn" onclick="admin_page_diseases_btn_click()">
                            <i class="bi bi-virus"></i>
                            <span class="nav-text">Diseases</span>
                        </button>
                    </li>
                    <li class="nav-item">
                        <button type="button" class="nav-link" onclick="admin_page_news_btn_click()" id="admin-page-news-btn">
                            <i class="bi bi-newspaper"></i>
                            <span class="nav-text">News</span>
                        </button>
                    </li>
                    <li class="nav-item">
                        <button type="button" class="nav-link" onclick="admin_page_contact_btn_click()" id="admin-page-contact-btn">
                            <i class="bi bi-envelope-plus"></i>
                            <span class="nav-text">Contact Messages</span>
                        </button>
                    </li>
                    <li class="nav-item">
                        <button type="submit" name="logout-btn" class="nav-link">
                            <i class="bi bi-box-arrow-right"></i>
                            <span class="nav-text">Logout</span>
                        </button>
                    </li>
                    <!-- <li class="nav-item">
                        <button class="nav-link">
                            <i class="bi bi-file-earmark-text"></i>
                            <span class="nav-text">Content</span>
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link">
                            <i class="bi bi-gear-fill"></i>
                            <span class="nav-text">Settings</span>
                        </button>
                    </li> -->

                </ul>

                <!-- doctor list -->
                <ul class="list-unstyled d-none" id="doctor-sidebar-list">
                    <li class="nav-item">
                        <button type="button" onclick="doctor_page_dashboard_btn_click()" id="doctor-page-dashboard-btn" class="nav-link active">
                            <i class="bi bi-grid-fill"></i>
                            <span class="nav-text">Dashboard</span>
                        </button>
                    </li>
                    <li class="nav-item">
                        <button type="button" onclick="doctor_page_profile_btn_click()" id="doctor-page-profile-btn" class="nav-link">
                            <i class="bi bi-person-circle"></i>
                            <span class="nav-text">Profile</span>
                        </button>
                    </li>
                    <li class="nav-item">
                        <button type="button" onclick="doctor_page_edit_profile_btn_click()" id="doctor-page-edit-profile-btn" class="nav-link">
                            <i class="bi bi-pencil-square"></i>
                            <span class="nav-text">Edit Profile</span>
                        </button>
                    </li>
                    <li class="nav-item">
                        <button type="button" onclick="doctor_page_availbility_btn_click()" id="doctor-page-availbility-btn" class="nav-link">
                            <i class="bi bi-clock"></i>
                            <span class="nav-text">Availability</span>
                        </button>
                    </li>
                    <li class="nav-item">
                        <button type="button" onclick="doctor_page_appointment_btn_click()" id="doctor-page-appointment-btn" class="nav-link">
                            <i class="bi bi-calendar-check"></i>
                            <span class="nav-text">Appointments</span>
                        </button>
                    </li>
                    <li class="nav-item">
                        <button type="submit" name="logout-btn" class="nav-link">
                            <i class="bi bi-box-arrow-right"></i>
                            <span class="nav-text">Logout</span>
                        </button>
                    </li>

                </ul>

                <!-- patient list -->
                <ul class="list-unstyled d-none" id="patient-sidebar-list">
                    <li class="nav-item">
                        <button type="button" onclick="patient_page_dashboard_btn_click()" id="patient-page-dashboard-btn" class="nav-link active">
                            <i class="bi bi-grid-fill"></i>
                            <span class="nav-text">Dashboard</span>
                        </button>
                    </li>
                    <li class="nav-item">
                        <button type="button" onclick="patient_page_search_dcotor_btn_click()" id="patient-page-search-doctor-btn" class="nav-link">
                            <i class="bi bi-search"></i>
                            <span class="nav-text">Search Doctor</span>
                        </button>
                    </li>
                    <li class="nav-item">
                        <button type="button" onclick="patient_page_my_appointment_btn_click()" id="patient-page-my-appointment-btn" class="nav-link">
                            <i class="bi bi-calendar-check"></i>
                            <span class="nav-text">My Appointments</span>
                        </button>
                    </li>
                    <li class="nav-item">
                        <button type="button" onclick="patient_page_profile_btn_click()" id="patient-page-profile-btn" class="nav-link">
                            <i class="bi bi-person-circle"></i>
                            <span class="nav-text">Profile</span>
                        </button>
                    </li>
                    <li class="nav-item">
                        <button type="submit" name="logout-btn" class="nav-link">
                            <i class="bi bi-box-arrow-right"></i>
                            <span class="nav-text">Logout</span>
                        </button>
                    </li>

                </ul>

            </nav>

            <!-- Sidebar Footer -->
            <div class="sidebar-footer">
                <span href="#" class="nav-link">
                    <span class="nav-text">© 2026 CARE Medical</span>
                    
                </span>
            </div>
        </aside>
        <!-- ==================== SIDEBAR ==================== -->


        <!-- ==================== ADMIN ==================== -->
        <main class="main-content" id="mainContent">
            <!-- ==================== DASHBOARD ==================== -->
            <div class="container-fluid admin-page-dashboard" id="admin-page-dashboard">

                <div class="row mb-3">
                    <div class="col-12 d-flex flex-wrap align-items-center gap-3 admin-page-dashboard-header">
                        <button type="button" class="sidebar-toggle" onclick="toggleSidebar()">
                            <i class="bi bi-chevron-bar-left"></i>
                        </button>
                        <div>
                            <h4>Admin Dashboard</h4>
                            <p>Welcome back, Administrator!</p>
                        </div>
                    </div>
                </div>

                <div class="row top-cards">

                    <?php
                    
                        if ($sessionRole == "Admin") {

                            $totalQuery1 = mysqli_query($connection, "SELECT COUNT(*) AS Total FROM doctors_table");
                            $totalDoctors = mysqli_fetch_assoc($totalQuery1)['Total'];

                            $totalQuery2 = mysqli_query($connection, "SELECT COUNT(*) AS Total FROM patients_table");
                            $totalPatients = mysqli_fetch_assoc($totalQuery2)['Total'];

                            $totalQuery3 = mysqli_query($connection, "SELECT COUNT(*) AS Total FROM appointments_table");
                            $totalAppointments = mysqli_fetch_assoc($totalQuery3)['Total'];

                            $totalQuery4 = mysqli_query($connection, "SELECT COUNT(*) AS Total FROM cities_table");
                            $totalCities = mysqli_fetch_assoc($totalQuery4)['Total'];

                        }

                    ?>

                    <div class="col-12 col-sm-6 col-lg-3 mb-4">
                        <div class="card">
                            <div class="card-body">
                                <i class="bi bi-person-badge" style="color: #2563EB; background-color: #EFF6FF;"></i>
                                <h5 class="card-title"><?php echo $totalDoctors; ?></h5> 
                                <p class="card-text">Total Doctors</p>    
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-sm-6 col-lg-3 mb-4">
                        <div class="card">
                            <div class="card-body">
                                <i class="bi bi-people-fill" style="color: #0D9488; background-color: #F0FDFA;"></i>
                                <h5 class="card-title"><?php echo $totalPatients; ?></h5> 
                                <p class="card-text">Total Patients</p>    
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-sm-6 col-lg-3 mb-4">
                        <div class="card">
                            <div class="card-body">
                                <i class="bi bi-calendar-check" style="color: #16A34A; background-color: #F0FDF4;"></i>
                                <h5 class="card-title"><?php echo $totalAppointments; ?></h5> 
                                <p class="card-text">Appointments</p>    
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-sm-6 col-lg-3 mb-4">
                        <div class="card">
                            <div class="card-body">
                                <i class="bi bi-building" style="color: #D97706; background-color: #FFFBEB;"></i>
                                <h5 class="card-title"><?php echo $totalCities; ?></h5> 
                                <p class="card-text">Cities</p>    
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12 col-lg-8 mb-4">
                        <div class="card recent-appointments-card">
                            <div class="card-body">
                                <h5 class="card-title">Recent Appointments</h5>
                                <div class="table-responsive">      
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th scope="col">Patient</th>
                                                <th scope="col">Doctor</th>
                                                <th scope="col">Day & Time</th>
                                                <th scope="col">Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>

                                            <?php
                    
                                                if ($sessionRole == "Admin") {
                                                    $selectAppointments = mysqli_query($connection, "SELECT * FROM appointments_table WHERE Status IN ('Pending', 'Confirmed') ORDER BY AppointmentID DESC LIMIT 8");
                                                    if (mysqli_num_rows($selectAppointments) > 0) {
                                                        while ($appointment = mysqli_fetch_assoc($selectAppointments)) {

                                                        $patientId = $appointment['PatientID'];
                                                        $doctorId = $appointment['DoctorID'];
                                                        $availabilityId = $appointment['AvailabilityID'];
                                                        $status = $appointment['Status'];

                                                        $selectPatients = mysqli_query($connection, "SELECT UserID FROM patients_table WHERE PatientID = '$patientId'");

                                                        $row = mysqli_fetch_assoc($selectPatients);

                                                        $userId1 = $row['UserID'];


                                                        $selectUsers = mysqli_query($connection, "SELECT Name FROM users_table WHERE UserID = '$userId1'");

                                                        $row2 = mysqli_fetch_assoc($selectUsers);

                                                        $patientName = $row2['Name'];


                                                        $selectDoctors = mysqli_query($connection, "SELECT UserID FROM doctors_table WHERE DoctorID = '$doctorId'");

                                                        $row3 = mysqli_fetch_assoc($selectDoctors);

                                                            $userId2 = $row3['UserID'];


                                                        $selectUsers2 = mysqli_query($connection, "SELECT Name FROM users_table WHERE UserID = '$userId2'");

                                                        $row4 = mysqli_fetch_assoc($selectUsers2);

                                                            $doctorName = $row4['Name'];

                                                        
                                                        $selectAvailability = mysqli_query($connection, "SELECT * FROM availability_table WHERE AvailabilityID = '$availabilityId'");

                                                        $row5 = mysqli_fetch_assoc($selectAvailability);

                                                            $day = $row5['Day'];
                                                            $start = date("h:i A", strtotime($row5['StartTime']));
                                                            $end = date("h:i A", strtotime($row5['EndTime']));


                                                        ?>

                                                            <tr>
                                                                <td><?php echo $patientName; ?></td>
                                                                <td><?php echo $doctorName; ?></td>
                                                                <td><?php echo $day; ?>, <?php echo $start; ?> - <?php echo $end; ?></td>
                                                                <td><span class="badge <?= ($status == "Confirmed") ? "bg-success" : "bg-warning"; ?>"><?php echo $status; ?></span></td>
                                                            </tr>

                                                            <?php


                                                        } 
                                                    }
                                                    else {

                                                        ?>

                                                        <tr>
                                                            <td colspan="4" class="text-center text-muted py-4">
                                                                No recent appointments found.
                                                            </td>
                                                        </tr>

                                                        <?php

                                                    }


                                                }

                                            ?>

                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-lg-4">
                        <div class="card activity-timeline-card">
                            <div class="card-body">

                                <?php 
                                
                                $totalQuery = mysqli_query($connection, "SELECT COUNT(*) AS Total FROM appointments_table WHERE Status='Confirmed'");
                                $confirmedAppointments = mysqli_fetch_assoc($totalQuery)['Total'];

                                $totalQuery2 = mysqli_query($connection, "SELECT COUNT(*) AS Total FROM appointments_table WHERE Status='Pending'");
                                $pendingAppointments = mysqli_fetch_assoc($totalQuery2)['Total'];

                                $totalQuery3 = mysqli_query($connection, "SELECT COUNT(*) AS Total FROM appointments_table WHERE Status='Cancelled'");
                                $cancelAppointments = mysqli_fetch_assoc($totalQuery3)['Total'];

                                $totalQuery4 = mysqli_query($connection, "SELECT COUNT(*) AS Total FROM doctors_table;");
                                $totalDoctors = mysqli_fetch_assoc($totalQuery4)['Total'];

                                
                                ?>

                                <h5 class="card-title">Appointment Overview</h5>
                                <div class="activity-timeline">
                                    <h5 class="card-title ">Confirmed Appointments</h5>
                                    <p class="card-text m-0"><?php echo $confirmedAppointments; ?></p>
                                    <small>Total appointments successfully confirmed.</small>
                                </div>
                                <div class="activity-timeline">
                                    <h5 class="card-title">Pending Appointments</h5>
                                    <p class="card-text m-0"><?php echo $pendingAppointments; ?></p>
                                    <small>Waiting for doctor approval.</small>
                                </div>
                                <div class="activity-timeline">
                                    <h5 class="card-title">Cancelled Appointments</h5>
                                    <p class="card-text m-0"><?php echo $cancelAppointments; ?></p>
                                    <small>Appointments cancelled by patients</small>
                                </div>
                                <div class="activity-timeline">
                                    <h5 class="card-title">Total Doctors</h5>
                                    <p class="card-text m-0"><?php echo $totalDoctors; ?></p>
                                    <small>Registered doctors in the system.</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
            <!-- ==================== DASHBOARD END ==================== -->


            <!-- ==================== CITIES ==================== -->
            <div class="container-fluid admin-page-cities" id="admin-page-cities">

                <div class="row">
                    <div class="col-12 admin-page-cities-header">
                        <button type="button" class="sidebar-toggle" onclick="toggleSidebar()">
                            <i class="bi bi-chevron-bar-left"></i>
                        </button>
                        <h4>Manage Cities</h4>
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#add_cities_modal">+ Add City</button>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <div class="card cities-card">
                            <div class="card-body">
                                <div class="table-responsive cities-table">      
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th scope="col">#</th>
                                                <th scope="col">City Name</th>
                                                <th scope="col">Doctors</th>
                                                <th scope="col">Actions</th >
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php 
                                                if ($sessionRole == "Admin") {
                                                    $count = 1;
                                                    $fetchCities = mysqli_query($connection, "SELECT * FROM cities_table");
                                                    if (mysqli_num_rows($fetchCities) > 0) {
                                                        while ($city = mysqli_fetch_assoc($fetchCities)) {
                                                            $cityId = $city['CityID'];

                                                            $doctorsCountQuery = mysqli_query($connection, "SELECT COUNT(*) AS totalDoctors FROM doctors_table WHERE CityID = '$cityId'");

                                                            $doctorcount = mysqli_fetch_assoc($doctorsCountQuery);
                                                            ?>
                                                            <tr>
                                                                <td><?php echo $count++; ?></td>
                                                                <td><?php echo $city['CityName'] ?></td>
                                                                <td><?php echo $doctorcount['totalDoctors'] ?? 0 ?></td>
                                                                <td><button type="button" onclick="deleteCity(<?php echo $cityId; ?>)" class="btn btn-outline-danger"><i class="bi bi-trash"></i></button></td>
                                                            </tr>
                                                            <?php
                                                        }
                                                    }
                                                    else {

                                                        ?>

                                                        <tr>
                                                            <td colspan="10" class="text-center text-muted py-4">
                                                                No cities found.
                                                            </td>
                                                        </tr>

                                                        <?php

                                                    }
                                                }
                                            ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Modal -->
            <div class="modal fade" id="add_cities_modal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h1 class="modal-title fs-5" id="exampleModalLabel">Add City</h1>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                        <label class="form-label">City Name</label>
                            <input type="text" class="form-control" id="city-name">
                        </div>
                        <div class="modal-footer">
                            <button type="button" onclick="admin_page_save_cities_btn_click()" class="btn btn-secondary">Save</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ==================== CITIES END ==================== -->
            <!-- ==================== DOCTORS ==================== -->
            <div class="container-fluid admin-page-doctors" id="admin-page-doctors">

                <div class="row">
                    <div class="col-12 admin-page-doctors-header">
                        <button type="button" class="sidebar-toggle" onclick="toggleSidebar()">
                            <i class="bi bi-chevron-bar-left"></i>
                        </button>
                        <h4>Manage Doctors</h4>
                        <button class="btn-primary" type="button" onclick="admin_page_add_doctors_btn_click()" data-bs-toggle="modal" data-bs-target="#add_doctors_modal">+ Add Doctor</button>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <div class="card doctors-card">
                            <div class="card-body">
                                <div class="table-responsive doctors-table">      
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th scope="col">DOCTOR ID</th>
                                                <th scope="col">PHOTO</th>
                                                <th scope="col">NAME</th>
                                                <th scope="col">SPECIALIST</th >
                                                <th scope="col">QUALIFICATION</th>
                                                <th scope="col">EXPERIENCE</th>
                                                <th scope="col">CITY</th>
                                                <th scope="col">PHONE</th >
                                                <th scope="col">STATUS</th >
                                                <th scope="col">ACTIONS</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php 
                                                if ($sessionRole == "Admin") {
                                                    $selectDoctors = mysqli_query($connection, "SELECT * FROM doctors_table");
                                                    if (mysqli_num_rows($selectDoctors) > 0) {
                                                        while ($doctor = mysqli_fetch_assoc($selectDoctors)) {
                                                            $doctorId = $doctor['DoctorID'];
                                                            $userId = $doctor['UserID'];
                                                            $specialist = $doctor['Specialist'];
                                                            $qualification = $doctor['Qualification'];
                                                            $experience = $doctor['Experience'];
                                                            $phone = $doctor['Phone'];
                                                            $cityId = $doctor['CityID'];
                                                            $bio = $doctor['Bio'];
                                                            $profileImage = '<img src="data:image/jpeg;base64,' . base64_encode($doctor['ProfileImage']) . '" style="width:40px; height:40px; object-fit:cover; border-radius:50px;" />';

                                                            $selectUsers = mysqli_query($connection, "SELECT * FROM users_table WHERE UserID = '$userId'");
                                                            $users = mysqli_fetch_assoc($selectUsers);

                                                            $name = $users['Name'];
                                                            $email = $users['Email'];
                                                            $status = $users['Status'];

                                                            $selectCities = mysqli_query($connection, "SELECT * FROM cities_table WHERE CityID = '$cityId'");
                                                            $cities = mysqli_fetch_assoc($selectCities);

                                                            $cityName = $cities['CityName'];

                                                            ?>
                                                            <tr>
                                                                <td class="text-center align-middle"><?php echo $doctorId; ?></td>
                                                                <td class="text-center align-middle"><?php echo $profileImage; ?></td>
                                                                <td class="text-center align-middle"><?php echo $name; ?></td>
                                                                <td class="text-center align-middle"><?php echo $specialist; ?></td>
                                                                <td class="text-center align-middle"><?php echo $qualification; ?></td>
                                                                <td class="text-center align-middle"><?php echo $experience. '+ years'; ?></td>
                                                                <td class="text-center align-middle"><?php echo $cityName; ?></td>
                                                                <td class="text-center align-middle"><?php echo $phone; ?></td>
                                                                <td class="text-center align-middle"><?php echo $status; ?></td>
                                                                <td class="text-center align-middle"><button type="button" data-bs-toggle="modal" data-bs-target="#edit_doctors_modal" onclick="admin_page_edit_doctors_btn_click(<?php echo $userId; ?>)" class="btn btn-outline-secondary btn-sm"><i class="bi bi-pencil"></i></button>&nbsp;<button type="button" onclick="deleteDoctor(<?php echo $doctorId; ?>)" class="btn btn-outline-danger btn-sm"><i class="bi bi-trash"></i></button></td>
                                                            </tr>
                                                            <?php
                                                        }
                                                    }
                                                    else {

                                                        ?>

                                                        <tr>
                                                            <td colspan="10" class="text-center text-muted py-4">
                                                                No doctors found.
                                                            </td>
                                                        </tr>

                                                        <?php

                                                    }
                                                }
                                            ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <!--ADD DOCTORS MODAL -->
            <div class="modal fade" id="add_doctors_modal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h1 class="modal-title fs-5" id="exampleModalLabel">Add Doctor</h1>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row">
                                <div class="col"><label class="form-label">Name</label><input type="text" id="add-doctors-modal-name-field" class="form-control" ></div>
                                <div class="col"><label class="form-label">Specialist</label><input type="text" id="add-doctors-modal-specialist-field" class="form-control" ></div>
                            </div>
                            <div class="row">
                                <div class="col"><label class="form-label">Qualification</label><input type="text" id="add-doctors-modal-qualification-field" class="form-control" ></div>
                                <div class="col"><label class="form-label">Experience</label><input type="number" id="add-doctors-modal-experience-field" class="form-control" ></div>
                            </div>
                            <div class="row">
                                <div class="col"><label class="form-label">Bio</label><input type="text" id="add-doctors-modal-bio-field" class="form-control" ></div>
                                <div class="col"><label class="form-label">Photo</label><input type="file" id="add-doctors-modal-photo-field" class="form-control" ></div>
                            </div>
                            <div class="row">
                                <div class="col"><label class="form-label">Email</label><input type="email" id="add-doctors-modal-email-field" class="form-control" ></div>
                                <div class="col"><label class="form-label">Phone</label><input type="number" id="add-doctors-modal-phone-field" placeholder="03xxxxxxxxx" class="form-control" ></div>
                            </div>
                            <div class="row">
                                <div class="col">
                                    <label class="form-label">Password</label>
                                    <div class="input-group">
                                        <input type="password" id="add-doctors-modal-password-field" class="form-control" >
                                        <button type="button" class="input-group-text" onclick="
                                        document.getElementById('add-doctors-modal-password-field').type = document.getElementById('add-doctors-modal-password-field').type === 'password' ? 'text' : 'password';
                                        this.innerHTML = document.getElementById('add-doctors-modal-password-field').type === 'password'
                                        ? '<i class=\'bi bi-eye\'></i>'
                                        : '<i class=\'bi bi-eye-slash\'></i>';
                                        ">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="col"><label class="form-label">City</label><select class="form-select" id="add-doctors-modal-city-field" aria-label="Default select example"></select></div>
                            </div>
                            <div class="row">
                                <div class="col"><label class="form-label">Role</label><input type="text" id="add-doctors-modal-role-field" disabled value="Doctor" class="form-control" ></div>
                                <div class="col"><label class="form-label">Status</label><select class="form-select" id="add-doctors-modal-status-field" aria-label="Default select example"><option value="Active">Active</option><option value="Inactive">Inactive</option></select></div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" onclick="admin_page_save_doctors_btn_click()" class="btn btn-secondary">Save</button>
                        </div>
                    </div>
                </div>
            </div>

            <!--EDIT DOCTORS MODAL -->
            <div class="modal fade" id="edit_doctors_modal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h1 class="modal-title fs-5" id="exampleModalLabel">Edit Doctor</h1>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">City</label>
                                <select class="form-select" id="edit-doctors-modal-city-field" aria-label="Default select example"></select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Status</label>
                                <select class="form-select" id="edit-doctors-modal-status-field" aria-label="Default select example">
                                    <option value="Active">Active</option>
                                    <option value="Inactive">Inactive</option>
                                </select>
                            </div>
                            <input type="hidden" id="edit-doctor-id" >
                            <input type="hidden" id="edit-user-id" >
                        </div>
                        <div class="modal-footer">
                            <button type="button" onclick="editDoctor()" class="btn btn-secondary">Save</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ==================== DOCTORS END ==================== -->


            <!-- ==================== PATIENTS ==================== -->
            <div class="container-fluid admin-page-patients" id="admin-page-patients">

                <div class="row">
                    <div class="col-12 admin-page-patients-header">
                        <button type="button" class="sidebar-toggle" onclick="toggleSidebar()">
                            <i class="bi bi-chevron-bar-left"></i>
                        </button>
                        <h4>Manage Patients</h4>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <div class="card patients-card">
                            <div class="card-body">
                                <div class="table-responsive patients-table">      
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th scope="col">P-ID</th>
                                                <th scope="col">NAME</th>
                                                <th scope="col">EMAIL</th>
                                                <th scope="col">PHONE</th>
                                                <th scope="col">ADDRESS</th>
                                                <th scope="col">BIRTH</th>
                                                <th scope="col">GENDER</th>
                                                <th scope="col">REGISTERED</th>
                                                <th scope="col">Status</th>
                                                <th scope="col">ACTIONS</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php 
                                                if ($sessionRole == "Admin") {
                                                    $selectPatients = mysqli_query($connection, "SELECT * FROM patients_table");
                                                    if (mysqli_num_rows($selectPatients) > 0) {
                                                        while ($patient = mysqli_fetch_assoc($selectPatients)) {
                                                            $patientId = $patient['PatientID'];
                                                            $userId = $patient['UserID'];
                                                            $address = $patient['Address'];
                                                            $phone = $patient['Phone'];
                                                            $birth = $patient['DateOfBirth'];
                                                            $gender = $patient['Gender'];
                                                            $registered = date("d M Y h:i A", strtotime($patient['CreatedAt']));

                                                            $selectUsers = mysqli_query($connection, "SELECT * FROM users_table WHERE UserID = '$userId'");
                                                            $users = mysqli_fetch_assoc($selectUsers);

                                                            $name = $users['Name'];
                                                            $email = $users['Email'];
                                                            $status = $users['Status'];

                                                            ?>
                                                            <tr>
                                                                <td class="text-center align-middle"><?php echo $patientId; ?></td>
                                                                <td class="text-center align-middle"><?php echo $name; ?></td>
                                                                <td class="text-center align-middle"><?php echo $email; ?></td>
                                                                <td class="text-center align-middle"><?php echo $phone; ?></td>
                                                                <td class="text-center align-middle"><?php echo $address; ?></td>
                                                                <td class="text-center align-middle"><?php echo $birth; ?></td>
                                                                <td class="text-center align-middle"><?php echo $gender; ?></td>
                                                                <td class="text-center align-middle"><?php echo $registered; ?></td>
                                                                <td class="text-center align-middle"><?php echo $status; ?></td>
                                                                <td class="text-center align-middle"><button type="button" data-bs-toggle="modal" data-bs-target="#edit_patients_modal" onclick="admin_page_edit_patients_btn_click(<?php echo $userId; ?>)" class="btn btn-outline-secondary btn-sm"><i class="bi bi-pencil"></i></button> <button type="button" onclick="deletePatient(<?php echo $patientId;?>, <?php echo $userId; ?>)" class="btn btn-outline-danger btn-sm"><i class="bi bi-trash"></i></button></td>
                                                            </tr>
                                                            <?php
                                                        }
                                                    }
                                                    else {

                                                        ?>

                                                        <tr>
                                                            <td colspan="10" class="text-center text-muted py-4">
                                                                No patients found.
                                                            </td>
                                                        </tr>

                                                        <?php

                                                    }
                                                }
                                            ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <!--EDIT PATIENTS MODAL -->
            <div class="modal fade" id="edit_patients_modal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h1 class="modal-title fs-5" id="exampleModalLabel">Edit Patient</h1>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">Status</label>
                                <select class="form-select" id="edit-patients-modal-status-field" aria-label="Default select example">
                                    <option value="Active">Active</option>
                                    <option value="Inactive">Inactive</option>
                                </select>
                            </div>
                            <input type="hidden" id="edit-patient-user-id" >
                        </div>
                        <div class="modal-footer">
                            <button type="button" onclick="editpatient()" class="btn btn-secondary">Save</button>
                        </div>
                    </div>
                </div>
            </div>
            <!-- ==================== PATIENTS END ==================== -->


            <!-- ==================== APPOINTMENTS ==================== -->
            <div class="container-fluid admin-page-appointments" id="admin-page-appointments">

                <div class="row">
                    <div class="col-12 admin-page-appointments-header">
                        <button type="button" class="sidebar-toggle" onclick="toggleSidebar()">
                            <i class="bi bi-chevron-bar-left"></i>
                        </button>
                        <h4>All Appointments</h4>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <div class="card appointments-card">
                            <div class="card-body">
                                <div class="table-responsive appointments-table">      
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th scope="col">#</th>
                                                <th scope="col">PATIENT</th>
                                                <th scope="col">DOCTOR</th>
                                                <th scope="col">Day</th>
                                                <th scope="col">TIME</th>
                                                <th scope="col">STATUS</th>
                                            </tr>
                                        </thead>
                                        <tbody>

                                            <?php
                    
                                                if ($sessionRole == "Admin") {
                                                    $selectAppointments = mysqli_query($connection, "SELECT * FROM appointments_table ORDER BY AppointmentID DESC ");
                                                    
                                                    $count = 1;

                                                    if (mysqli_num_rows($selectAppointments) > 0) {
                                                        while ($appointment = mysqli_fetch_assoc($selectAppointments)) {

                                                        $patientId = $appointment['PatientID'];
                                                        $doctorId = $appointment['DoctorID'];
                                                        $availabilityId = $appointment['AvailabilityID'];
                                                        $status = $appointment['Status'];

                                                        $selectPatients = mysqli_query($connection, "SELECT UserID FROM patients_table WHERE PatientID = '$patientId'");

                                                        $row = mysqli_fetch_assoc($selectPatients);

                                                            $userId1 = $row['UserID'];


                                                        $selectUsers = mysqli_query($connection, "SELECT Name FROM users_table WHERE UserID = '$userId1'");

                                                        $row2 = mysqli_fetch_assoc($selectUsers);

                                                            $patientName = $row2['Name'];


                                                        $selectDoctors = mysqli_query($connection, "SELECT UserID FROM doctors_table WHERE DoctorID = '$doctorId'");

                                                        $row3 = mysqli_fetch_assoc($selectDoctors);

                                                            $userId2 = $row3['UserID'];


                                                        $selectUsers2 = mysqli_query($connection, "SELECT Name FROM users_table WHERE UserID = '$userId2'");

                                                        $row4 = mysqli_fetch_assoc($selectUsers2);

                                                            $doctorName = $row4['Name'];

                                                        
                                                        $selectAvailability = mysqli_query($connection, "SELECT * FROM availability_table WHERE AvailabilityID = '$availabilityId'");

                                                        $row5 = mysqli_fetch_assoc($selectAvailability);

                                                            $day = $row5['Day'];
                                                            $start = date("h:i A", strtotime($row5['StartTime']));
                                                            $end = date("h:i A", strtotime($row5['EndTime']));

                                                            $statusClass = "";

                                                            switch ($status) {
                                                                case "Pending":
                                                                    $statusClass = "bg-warning text-dark";
                                                                    break;

                                                                case "Confirmed":
                                                                    $statusClass = "bg-success";
                                                                    break;

                                                                case "Completed":
                                                                    $statusClass = "bg-secondary";
                                                                    break;

                                                                case "Cancelled":
                                                                    $statusClass = "bg-danger";
                                                                    break;

                                                                case "Rejected":
                                                                    $statusClass = "bg-danger";
                                                                    break;

                                                                default:
                                                                    $statusClass = "bg-primary";
                                                                    break;
                                                            }


                                                        ?>

                                                            <tr>
                                                                <td><?php echo $count++; ?></td>
                                                                <td><?php echo $patientName; ?></td>
                                                                <td><?php echo $doctorName; ?></td>
                                                                <td><?php echo $day; ?></td>
                                                                <td><?php echo $start; ?> - <?php echo $end; ?></td>
                                                                <td><span class="badge <?php echo $statusClass; ?>"><?php echo $status; ?></span></td>
                                                            </tr>

                                                            <?php


                                                        }
                                                    }
                                                    else {

                                                        ?>

                                                        <tr>
                                                            <td colspan="6" class="text-center text-muted py-4">
                                                                No appointments found.
                                                            </td>
                                                        </tr>

                                                        <?php

                                                    } 


                                                }

                                            ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
            <!-- ==================== APPOINTMENTS END ==================== -->


            <!-- ==================== DISEASES ==================== -->
            <div class="container-fluid admin-page-diseases" id="admin-page-diseases">

                <div class="row">
                    <div class="col-12 admin-page-diseases-header">
                        <button type="button" class="sidebar-toggle" onclick="toggleSidebar()">
                            <i class="bi bi-chevron-bar-left"></i>
                        </button>
                        <h4>Manage Diseases</h4>
                        <button class="btn-primary" type="button" data-bs-toggle="modal" data-bs-target="#add_diseases_modal">+ Add Diseases</button>
                    </div>
                </div>

                <div class="row mt-4">
                    <?php 
                        if ($sessionRole == "Admin") {
                            $fetchDiseases = mysqli_query($connection, "SELECT * FROM diseases_table");

                            while ($disease = mysqli_fetch_assoc($fetchDiseases)) {

                            $diseaseId = $disease['DiseaseID'];
                            $name = $disease['DiseaseName'];
                            $symptoms = $disease['Symptoms'];
                            $prevention = $disease['Prevention'];
                            $cure = $disease['Cure'];

                            ?>

                            <div class="col-md-4 mb-3">
                                <div class="card diseases-card">
                                    <div class="card-body">
                                        <h5 class="card-title"><?php echo $name ?></h5>
                                        <p class="card-text"><b>Symptoms:</b> <?php echo $symptoms ?></p>
                                        <p class="card-text"><b>Prevention:</b> <?php echo $prevention ?></p>
                                        <p class="card-text"><b>Cure:</b> <?php echo $cure ?></p>
                                        <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#edit_diseases_modal" onclick= "admin_page_edit_disease_btn_click(<?php echo $diseaseId ?>)"><i class="bi bi-pencil"></i></button> <button onclick="deleteDisease(<?php echo $diseaseId; ?>)" type="button" class="btn btn-outline-danger btn-sm"><i class="bi bi-trash"></i></button>
                                    </div>
                                </div>
                            </div>
                            <?php
                            }    
                        }               
                    ?>
                </div>

            </div>

            <!-- ADD DISEASES MODAL -->
            <div class="modal fade" id="add_diseases_modal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h1 class="modal-title fs-5" id="exampleModalLabel">Add Disease</h1>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">Disease Name</label>
                                <input type="text" id="add-diseases-modal-name-field" maxlength="50" placeholder="Max 50 characters" class="form-control" >
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Symptoms</label>
                                <textarea class="form-control" id="add-diseases-modal-symptoms-field" maxlength="200" placeholder="Max 200 characters" ></textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Prevention</label>
                                <textarea id="add-diseases-modal-prevention-field" class="form-control" maxlength="200" placeholder="Max 200 characters" ></textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Cure</label>
                                <textarea id="add-diseases-modal-cure-field" class="form-control" maxlength="200" placeholder="Max 200 characters" ></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" onclick="admin_page_save_disease_btn_click()" class="btn btn-secondary">Save</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- EDIT DISEASES MODAL -->
            <div class="modal fade" id="edit_diseases_modal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h1 class="modal-title fs-5" id="exampleModalLabel">Edit Doctor</h1>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">Disease Name</label>
                                <input type="text" id="edit-diseases-modal-name-field" maxlength="50" placeholder="Max 50 characters" class="form-control" >
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Symptoms</label>
                                <textarea class="form-control" id="edit-diseases-modal-symptoms-field" maxlength="200" placeholder="Max 200 characters" ></textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Prevention</label>
                                <textarea id="edit-diseases-modal-prevention-field" class="form-control" maxlength="200" placeholder="Max 200 characters" ></textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Cure</label>
                                <textarea id="edit-diseases-modal-cure-field" class="form-control" maxlength="200" placeholder="Max 200 characters" ></textarea>
                            </div>
                            <input type="hidden" id="edit-disease-id" >
                        </div>
                        <div class="modal-footer">
                            <button type="button" onclick="editDisease()" class="btn btn-secondary">Save</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ==================== DISEASES END ==================== -->


            <!-- ==================== NEWS ==================== -->
            <div class="container-fluid admin-page-news" id="admin-page-news">

                <div class="row">
                    <div class="col-12 admin-page-news-header">
                        <button type="button" class="sidebar-toggle" onclick="toggleSidebar()">
                            <i class="bi bi-chevron-bar-left"></i>
                        </button>
                        <h4>Medical News</h4>
                        <button type="button" class="btn-primary"  data-bs-toggle="modal" data-bs-target="#add_news_modal">+ Add News</button>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <div class="card news-card">
                            <div class="card-body">
                                <div class="table-responsive news-table">      
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th scope="col">#</th>
                                                <th scope="col">Title</th>
                                                <th scope="col">Description</th>
                                                <th scope="col">Date</th>
                                                <th scope="col">Status</th>
                                                <th scope="col">ACTIONS</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php 

                                                if ($sessionRole == "Admin") {
                                                    $count = 1;
                                                    $selectNews = mysqli_query($connection, "SELECT * FROM medicalnews_table");
                                                    if (mysqli_num_rows($selectNews) > 0) {
                                                        while ($news = mysqli_fetch_assoc($selectNews)) {
                                                            $newsId = $news['NewsID'];    
                                                            $title = $news['Title'];
                                                            $description = $news['Description'];
                                                            $date = $news['Date'];
                                                            $status = $news['Status'];
                                                            ?>
                                                            <tr>
                                                                <td><?php echo $count++; ?></td>
                                                                <td class="align-middle"><?php echo $title; ?></td>
                                                                <td class="align-middle"><?php echo $description; ?></td>
                                                                <td class="align-middle"><?php echo $date; ?></td>
                                                                <td class="align-middle"><?php echo $status; ?></td>
                                                                <td class="align-middle"><button type="button" data-bs-toggle="modal" data-bs-target="#edit_news_modal" onclick="admin_page_edit_news_btn_click(<?php echo $newsId; ?>)" class="btn btn-outline-secondary btn-sm"><i class="bi bi-pencil"></i></button>&nbsp;<button type="button" onclick="deleteNews(<?php echo $newsId; ?>)" class="btn btn-outline-danger btn-sm"><i class="bi bi-trash"></i></button></td>
                                                            </tr>
                                                            <?php
                                                        }
                                                    }
                                                    else {

                                                        ?>

                                                        <tr>
                                                            <td colspan="6" class="text-center text-muted py-4">
                                                                No news found.
                                                            </td>
                                                        </tr>

                                                        <?php

                                                    }
                                                }
                                            ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>    
            
            <!-- ADD NEWS MODAL -->
            <div class="modal fade" id="add_news_modal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h1 class="modal-title fs-5" id="exampleModalLabel">Add News</h1>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">Title</label>
                                <input type="text" id="add-news-modal-title-field" maxlength="100" placeholder="Max 100 characters" class="form-control" >
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Description</label>
                                <textarea class="form-control" id="add-news-modal-description-field" maxlength="500" placeholder="Max 500 characters" ></textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Date</label>
                                <input type="date" id="add-news-modal-date-field" class="form-control" >
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Status</label>
                                <select class="form-select" id="add-news-modal-status-field" aria-label="Default select example">
                                    <option value="Active">Active</option>
                                    <option value="Inactive">Inactive</option>
                                </select>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" onclick="admin_page_save_news_btn_click()" class="btn btn-secondary">Save</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- EDIT NEWS MODAL -->
            <div class="modal fade" id="edit_news_modal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h1 class="modal-title fs-5" id="exampleModalLabel">Edit News</h1>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">Title</label>
                                <input type="text" id="edit-news-modal-title-field" maxlength="100" placeholder="Max 100 characters" class="form-control" >
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Description</label>
                                <textarea class="form-control" id="edit-news-modal-description-field" maxlength="500" placeholder="Max 500 characters" ></textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Date</label>
                                <input type="date" id="edit-news-modal-date-field" class="form-control" >
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Status</label>
                                <select class="form-select" id="edit-news-modal-status-field" aria-label="Default select example">
                                    <option value="Active">Active</option>
                                    <option value="Inactive">Inactive</option>
                                </select>
                            </div>
                            <input type="hidden" id="edit-news-id" >
                        </div>
                        <div class="modal-footer">
                            <button type="button" onclick="editNews()" class="btn btn-secondary">Save</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ==================== NEWS END ==================== -->

            <!-- ==================== CONTENT ==================== -->
            <div class="container-fluid admin-page-content">

                <div class="row">
                    <div class="col-12 admin-page-content-header">
                        <h4>Website Content management</h4>
                    </div>
                </div>

                <div class="row mt-4">
                    <div class="col-md-4">
                        <div class="card content-card text-center">
                            <div class="card-body">
                                <i class="bi bi-image"></i>
                                <h5 class="card-title">Banner management</h5>
                                <p class="card-text">Edit homepage banner</p>
                                <a href="#" class="btn btn-primary">Manage</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card content-card text-center">
                            <div class="card-body">
                                <i class="bi bi-image"></i>
                                <h5 class="card-title">Banner management</h5>
                                <p class="card-text">Edit homepage banner</p>
                                <a href="#" class="btn btn-primary">Manage</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card content-card text-center">
                            <div class="card-body">
                                <i class="bi bi-image"></i>
                                <h5 class="card-title">Banner management</h5>
                                <p class="card-text">Edit homepage banner</p>
                                <a href="#" class="btn btn-primary">Manage</a>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
            <!-- ==================== CONTENT END ==================== -->

            <!-- ==================== contact messages ==================== -->
            <div class="container-fluid admin-page-contact-messages" id="admin-page-contact-messages">

                <div class="row">
                    <div class="col-12 admin-page-contact-messages-header">
                        <button type="button" class="sidebar-toggle" onclick="toggleSidebar()">
                            <i class="bi bi-chevron-bar-left"></i>
                        </button>
                        <h4>Contact Messages</h4>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <div class="card contact-messages-card">
                            <div class="card-body">
                                <div class="table-responsive contact-messages-table">      
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th scope="col">#</th>
                                                <th scope="col">Name</th>
                                                <th scope="col">Email</th>
                                                <th scope="col">Message</th>
                                                <th scope="col">Sended At</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php 

                                                if ($sessionRole == "Admin") {

                                                    $count = 1;
                                                    $fetchMessages = mysqli_query($connection, "SELECT * FROM contact_messages_table");
                                                    if (mysqli_num_rows($fetchMessages) > 0) {
                                                        while ($message = mysqli_fetch_assoc($fetchMessages)) {
                                                            $name = $message['Name'];
                                                            $email = $message['Email'];
                                                            $user_message = $message['Message'];
                                                            $createdat = date("d M Y, h:i A", strtotime($message['CreatedAt']));                                                        ?>
                                                            <tr>
                                                                <td><?php echo $count++; ?></td>
                                                                <td><?php echo $name; ?></td>
                                                                <td><?php echo $email; ?></td>
                                                                <td><?php echo $user_message; ?></td>
                                                                <td><?php echo $createdat; ?></td>
                                                            </tr>
                                                            <?php
                                                        }
                                                    }
                                                    else {

                                                        ?>

                                                        <tr>
                                                            <td colspan="10" class="text-center text-muted py-4">
                                                                No messages found.
                                                            </td>
                                                        </tr>

                                                        <?php

                                                    }

                                                }

                                            ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <!-- ==================== ADMIN END ==================== -->








            <!-- ==================== DOCTOR ==================== -->
            <!-- ==================== DASHBOARD ==================== -->

            <div class="container-fluid doctor-page-dashboard" id="doctor-page-dashboard">

                <div class="row mb-3">
                    <div class="col-12 d-flex flex-wrap align-items-center gap-3 doctor-page-dashboard-header">
                        <button type="button" class="sidebar-toggle" onclick="toggleSidebar()">
                            <i class="bi bi-chevron-bar-left"></i>
                        </button>

                        <?php

                            $userName = "";

                            if ($sessionRole == "Doctor") {

                                $userQuery = mysqli_query($connection, "
                                    SELECT Name
                                    FROM users_table
                                    WHERE UserID = '$sessionId'
                                ");

                                if (mysqli_num_rows($userQuery) > 0) {

                                    $user = mysqli_fetch_assoc($userQuery);
                                    $userName = $user['Name'];

                                }

                            }
                        ?>

                        <div>
                            <h4><?php echo $sessionRole; ?> Dashboard</h4>
                            <p>
                                Welcome back, <strong><?php echo ucwords(htmlspecialchars($userName)); ?></strong>!
                            </p>
                        </div>
                    </div>
                </div>

                <div class="row top-cards">

                    <?php

                        if ($sessionRole == "Doctor") {

                            // Step 1: UserID se DoctorID nikalo
                            $doctorQuery = mysqli_query($connection, " SELECT DoctorID FROM doctors_table WHERE UserID = '$sessionId' ");

                            $doctor = mysqli_fetch_assoc($doctorQuery);

                            $doctorId = $doctor['DoctorID'];

                            // Current Day
                            $currentDay = date("D");

                            // Today's Appointments
                            $query1 = mysqli_query($connection, " SELECT COUNT(*) AS Total FROM appointments_table a INNER JOIN availability_table av ON a.AvailabilityID = av.AvailabilityID WHERE a.DoctorID = '$doctorId' AND av.Day = '$currentDay' AND a.Status IN ('Confirmed', 'Pending') ");

                            $todayAppointments = mysqli_fetch_assoc($query1)['Total'];

                            // Upcoming Appointments
                            $query2 = mysqli_query($connection, " SELECT COUNT(*) AS Total FROM appointments_table a INNER JOIN availability_table av ON a.AvailabilityID = av.AvailabilityID WHERE a.DoctorID = '$doctorId' AND av.Day != '$currentDay' AND a.Status IN ('Confirmed', 'Pending') ");

                            $upcomingAppointments = mysqli_fetch_assoc($query2)['Total'];

                            // Available Days
                            $query3 = mysqli_query($connection, " SELECT COUNT(DISTINCT Day) AS Total FROM availability_table WHERE DoctorID = '$doctorId' AND AvailabilityStatus = 'Available' ");

                            $availableDays = mysqli_fetch_assoc($query3)['Total'];

                            $query4 = mysqli_query($connection, " SELECT COUNT(*) AS Total FROM appointments_table WHERE DoctorID = '$doctorId' AND Status = 'Completed' ");

                            $completedConsultations = mysqli_fetch_assoc($query4)['Total'];

                        }

                    ?>

                    <div class="col-12 col-sm-6 col-lg-3 mb-4">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo $todayAppointments; ?></h5> 
                                <p class="card-text">Today's Appointments</p>    
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-sm-6 col-lg-3 mb-4">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo $upcomingAppointments; ?></h5> 
                                <p class="card-text">Upcomming</p>    
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-sm-6 col-lg-3 mb-4">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo $availableDays; ?></h5> 
                                <p class="card-text">Available Days/Week</p>    
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-sm-6 col-lg-3 mb-4">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo $completedConsultations; ?></h5> 
                                <p class="card-text">Completed Consultations</p>    
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12 col-lg-12 mb-4">
                        <div class="card today-schedule-card">
                            <div class="card-body">
                                <h5 class="card-title">Today's Appointment List</h5>
                                <div class="table-responsive">      
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th scope="col">Appointment No</th>
                                                <th scope="col">Patient</th>
                                                <th scope="col">Day</th>
                                                <th scope="col">Time</th>
                                                <th scope="col">Status</th>
                                            </tr>
                                        </thead>
                                        
                                        <tbody>

                                            <?php
                                                if ($sessionRole == "Doctor") {
                                                    $currentDay = date("D");

                                                    // Today's appointments of current doctor
                                                    $query = mysqli_query($connection, "
                                                        SELECT a.*, av.Day, av.StartTime, av.EndTime
                                                        FROM appointments_table a
                                                        INNER JOIN availability_table av
                                                            ON a.AvailabilityID = av.AvailabilityID
                                                        WHERE a.DoctorID = '$doctorId'
                                                        AND av.Day = '$currentDay'
                                                        ORDER BY av.StartTime ASC
                                                    ");

                                                    if(mysqli_num_rows($query) > 0){

                                                        while($appointment = mysqli_fetch_assoc($query)){

                                                            $patientId = $appointment['PatientID'];

                                                            // Get UserID
                                                            $patientQuery = mysqli_query($connection, "
                                                                SELECT UserID
                                                                FROM patients_table
                                                                WHERE PatientID = '$patientId'
                                                            ");

                                                            $patient = mysqli_fetch_assoc($patientQuery);
                                                            $userId = $patient['UserID'];

                                                            // Get Patient Name
                                                            $userQuery = mysqli_query($connection, "
                                                                SELECT Name
                                                                FROM users_table
                                                                WHERE UserID = '$userId'
                                                            ");

                                                            $user = mysqli_fetch_assoc($userQuery);
                                                            $patientName = $user['Name'];

                                                            $day = $appointment['Day'];

                                                            $time = date("h:i A", strtotime($appointment['StartTime'])) .
                                                                    " - " .
                                                                    date("h:i A", strtotime($appointment['EndTime']));

                                                            $status = $appointment['Status'];

                                                            $aptno = $appointment['AppointmentNumber'];

                                                            switch($status){

                                                                case "Confirmed":
                                                                    $statusClass = "bg-success";
                                                                    break;

                                                                case "Pending":
                                                                    $statusClass = "bg-warning text-dark";
                                                                    break;

                                                                case "Completed":
                                                                    $statusClass = "bg-secondary";
                                                                    break;

                                                                case "Cancelled":
                                                                case "Rejected":
                                                                    $statusClass = "bg-danger";
                                                                    break;

                                                                default:
                                                                    $statusClass = "bg-primary";
                                                            }

                                                            ?>

                                                            <tr>
                                                                <td><?php echo $aptno; ?></td>
                                                                <td><?php echo $patientName; ?></td>
                                                                <td><?php echo $day; ?></td>
                                                                <td><?php echo $time; ?></td>
                                                                <td>
                                                                    <span class="badge <?php echo $statusClass; ?>">
                                                                        <?php echo $status; ?>
                                                                    </span>
                                                                </td>
                                                            </tr>

                                                            <?php
                                                        }

                                                    }
                                                    else{
                                                        ?>

                                                        <tr>
                                                            <td colspan="5" class="text-center text-muted py-4">
                                                                No appointments scheduled for today.
                                                            </td>
                                                        </tr>

                                                        <?php
                                                    }
                                                }
                                            ?>

                                        </tbody>

                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <!-- ==================== DASHBOARD END ==================== -->

            <!-- ==================== PROFILE ==================== -->

            <div class="container-fluid doctor-page-profile" id="doctor-page-profile">

                <div class="row mb-3">
                    <div class="col-12 d-flex flex-wrap align-items-center gap-3 doctor-page-profile-header">
                        <button type="button" class="sidebar-toggle" onclick="toggleSidebar()">
                            <i class="bi bi-chevron-bar-left"></i>
                        </button>
                        <div>
                            <h4>Profile</h4>
                        </div>
                    </div>
                </div>

                <div class="row top-cards">
                    <div class="col-12 col-sm-8 col-md-7 col-lg-5 mb-4">
                        <div class="card">
                            <div class="card-body">

                                <?php 

                                    if ($sessionRole == "Doctor") {

                                        $selectUser = mysqli_query($connection, "SELECT * FROM users_table WHERE UserID = '$sessionId'");
                                        $user = mysqli_fetch_assoc($selectUser);

                                        $name = $user['Name'];
                                        $email = $user['Email'];

                                        $selectDoctor = mysqli_query($connection, "SELECT * FROM doctors_table WHERE UserID = '$sessionId'");
                                        $doctor = mysqli_fetch_assoc($selectDoctor);

                                        $specialist = $doctor['Specialist'];
                                        $qualification = $doctor['Qualification'];
                                        $experience = $doctor['Experience'];
                                        $phone = $doctor['Phone'];
                                        $bio = $doctor['Bio'];
                                        $image = base64_encode($doctor['ProfileImage']);

                                        ?>

                                        <img class="doctor-image" src="data:image/jpeg;base64,<?php echo $image; ?>" alt="doctor profile image">
                                        <h5 class="card-title"><?php echo $name ?></h5> 
                                        <span class="text-muted"><?php echo $specialist ?></span>
                                        <p class="card-text"><b>Qualification: </b><?php echo $qualification ?></p>
                                        <p class="card-text"><b>Experience: </b><?php echo $experience. '+ years'; ?></p>
                                        <p class="card-text"><b>Phone: </b><?php echo $phone ?></p>
                                        <p class="card-text"><b>Email: </b><?php echo $email ?></p>
                                        <p class="card-text"><b>Bio: </b><?php echo $bio ?></p>

                                <?php

                                    }

                                ?>

                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <!-- ==================== PROFILE END ==================== -->

            <!-- ==================== PROFILE EDIT ==================== -->

            <div class="container-fluid doctor-page-edit-profile" id="doctor-page-edit-profile">

                <div class="row mb-3">
                    <div class="col-12 d-flex flex-wrap align-items-center gap-3 doctor-page-edit-profile-header">
                        <button type="button" class="sidebar-toggle" onclick="toggleSidebar()">
                            <i class="bi bi-chevron-bar-left"></i>
                        </button>
                        <div>
                            <h4>Edit Profile</h4>
                        </div>
                    </div>
                </div>

                <div class="row top-cards">
                    <div class="col-12 col-sm-10 col-md-9 col-lg-8 mb-4">
                        <div class="card">
                            <div class="card-body">

                                <?php 

                                    if ($sessionRole == "Doctor") {

                                        $selectUser = mysqli_query($connection, "SELECT * FROM users_table WHERE UserID = '$sessionId'");
                                        $user = mysqli_fetch_assoc($selectUser);

                                        $name = $user['Name'];
                                        $email = $user['Email'];

                                        $selectDoctor = mysqli_query($connection, "SELECT * FROM doctors_table WHERE UserID = '$sessionId'");
                                        $doctor = mysqli_fetch_assoc($selectDoctor);

                                        $specialist = $doctor['Specialist'];
                                        $qualification = $doctor['Qualification'];
                                        $experience = $doctor['Experience'];
                                        $phone = $doctor['Phone'];
                                        $bio = $doctor['Bio'];
                                        $image = base64_encode($doctor['ProfileImage']);

                                        ?>

                                        <img id="profilePreview" class="doctor-image" src="data:image/jpeg;base64,<?php echo $image; ?>" alt="doctor profile image">
                                        <input type="file" id="profileImageInput" accept="image/*" hidden>

                                        <button type="button" id="uploadImageBtn" class="btn btn-outline-secondary upload-image-btn">Upload Image</button>

                                        <div class="row">
                                            <div class="col-12 col-md-6 mb-3">
                                                <label class="form-label">Name</label>
                                                <input type="text" id="doctor-update-name-field" value="<?php echo $name ?>" class="form-control">
                                            </div>

                                            <div class="col-12 col-md-6 mb-3">
                                                <label class="form-label">Email</label>
                                                <input type="email" id="doctor-update-email-field" value="<?php echo $email ?>" class="form-control">
                                            </div>
                                            <div class="col-12 col-md-6 mb-3">
                                                <label class="form-label">Specialist</label>
                                                <input type="text" id="doctor-update-specialist-field" value="<?php echo $specialist ?>" class="form-control">
                                            </div>

                                            <div class="col-12 col-md-6 mb-3">
                                                <label class="form-label">Qualification</label>
                                                <input type="text" id="doctor-update-qualification-field" value="<?php echo $qualification ?>" class="form-control">
                                            </div>
                                            <div class="col-12 col-md-6 mb-3">
                                                <label class="form-label">Experience</label>
                                                <input type="text" id="doctor-update-experience-field" value="<?php echo $experience ?>" class="form-control">
                                            </div>

                                            <div class="col-12 col-md-6 mb-3">
                                                <label class="form-label">Phone</label>
                                                <input type="number" id="doctor-update-phone-field" value="<?php echo $phone ?>" class="form-control">
                                            </div>
                                            
                                            <div class="col-12 col-md-6 mb-3">
                                                <label class="form-label">Bio</label>
                                                <textarea class="form-control" id="doctor-update-bio-field" maxlength="200" placeholder="Max 200 characters"><?php echo $bio ?></textarea>
                                            </div>

                                            <div class="col-12 col-md-6 mb-3">
                                                <label class="form-label">Password</label>
                                                <div class="input-group">
                                                    <input type="password" id="doctor-update-password-field" class="form-control" style="height:40px;">
                                                    <button type="button" class="input-group-text" style="height:40px;" onclick="
                                                        document.getElementById('doctor-update-password-field').type = document.getElementById('doctor-update-password-field').type === 'password' ? 'text' : 'password';
                                                        this.innerHTML = document.getElementById('doctor-update-password-field').type === 'password' ? '<i class=\'bi bi-eye\'></i>' : '<i class=\'bi bi-eye-slash\'></i>'; "><i class="bi bi-eye"></i>
                                                    </button>
                                                </div>
                                            </div>

                                        </div>  
                                        <button type="button" onclick="update_doctor_details()" class="btn btn-secondary">Save Changes</button>

                                        <?php

                                    }

                                ?>

                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <!-- ==================== PROFILE EDIT END ==================== -->

            <!-- ==================== Availability ==================== -->
            <div class="container-fluid doctor-page-availability" id="doctor-page-availability">

                <div class="row mb-3">
                    <div class="col-12 d-flex flex-wrap align-items-center gap-3 doctor-page-availability-header">
                        <button type="button" class="sidebar-toggle" onclick="toggleSidebar()">
                            <i class="bi bi-chevron-bar-left"></i>
                        </button>
                        <h4>Availability</h4>
                        <button type="button" class="add-slot" data-bs-toggle="modal" data-bs-target="#add_slots_modal">+ Add Slot</button>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12">
                        <div class="card availability-slot-card">
                            <div class="card-body">

                                <h5 class="card-title">Weekly Schedule</h5>

                                <?php

                                    if ($sessionRole == "Doctor") {

                                        $days = ['Mon','Tue','Wed','Thu','Fri','Sat','Sun'];

                                        foreach($days as $day){

                                            echo '<div class="available-time mb-4 mt-4">';
                                            echo '<div class="slot-title">'.$day.'</div>';

                                            $selectSlots = mysqli_query($connection,"
                                                SELECT *
                                                FROM availability_table
                                                WHERE DoctorID = '$doctorId'
                                                AND Day = '$day'
                                            ");

                                            if(mysqli_num_rows($selectSlots) > 0){

                                                while($slot = mysqli_fetch_assoc($selectSlots)){

                                                    if($slot['AvailabilityStatus'] == 'Not Available'){

                                                        echo '<button type="button" class="btn slot">Not Available</button>';
                                                    }
                                                    else{

                                                        $start = date("h:i A", strtotime($slot['StartTime']));
                                                        $end = date("h:i A", strtotime($slot['EndTime']));

                                                        echo '<button type="button" class="btn slot">'
                                                            .$start.' - '.$end.
                                                            '</button>';
                                                    }
                                                }

                                            }
                                            else{

                                                echo '<button type="button" class="btn slot">Not Available</button>';
                                            }

                                            echo '</div>';
                                        }
                                    }
                                ?>

                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <div class="modal fade" id="add_slots_modal" tabindex="-1">
                <div class="modal-dialog modal-dialog-centered modal-lg">
                    <div class="modal-content">

                        <div class="modal-header">
                            <h1 class="modal-title fs-5">Add Availability</h1>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>

                        <div class="modal-body">

                            <div class="mb-3">
                                <label class="form-label">Choose Day</label>
                                <select class="form-select" id="availability-day">
                                    <option value="Mon">Mon</option>
                                    <option value="Tue">Tue</option>
                                    <option value="Wed">Wed</option>
                                    <option value="Thu">Thu</option>
                                    <option value="Fri">Fri</option>
                                    <option value="Sat">Sat</option>
                                    <option value="Sun">Sun</option>
                                </select>
                            </div>

                            <div class="form-check mb-3">
                                <input type="checkbox" id="notAvailableCheckbox" onchange="toggle_availability()">
                                <label class="form-check-label">Not Available</label>
                            </div>

                            <div id="slotContainer">

                                <div class="row">

                                    <label class="form-label">Slot 1</label>
                                    <div class="col-6 mb-3">
                                        <input type="time" id="slot-1-start-time" class="form-control start-time">
                                    </div>
                                    
                                    <div class="col-6 mb-3">
                                        <input type="time" id="slot-1-end-time" class="form-control end-time">
                                    </div>

                                    <label class="form-label">Slot 2</label>
                                    <div class="col-6 mb-3">
                                        <input type="time" id="slot-2-start-time" class="form-control start-time">
                                    </div>

                                    <div class="col-6 mb-3">
                                        <input type="time" id="slot-2-end-time" class="form-control end-time">
                                    </div>

                                    <label class="form-label">Slot 3</label>
                                    <div class="col-6 mb-3">
                                        <input type="time" id="slot-3-start-time" class="form-control start-time">
                                    </div>

                                    <div class="col-6 mb-3">
                                        <input type="time" id="slot-3-end-time" class="form-control end-time">
                                    </div>

                                </div>

                            </div>

                        </div>

                        <div class="modal-footer">

                            <button type="button" onclick="save_available_slots()" class="btn btn-secondary">Save</button>

                        </div>

                    </div>
                </div>
            </div>

            <!-- ==================== Availability END ==================== -->

            <!-- ==================== APPOINTMENT ==================== -->
            <div class="container-fluid doctor-page-appointment" id="doctor-page-appointment">

                <div class="row">
                    <div class="col-12 doctor-page-appointment-header">
                        <button type="button" class="sidebar-toggle" onclick="toggleSidebar()">
                            <i class="bi bi-chevron-bar-left"></i>
                        </button>
                        <h4>My Appointments</h4>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <div class="card appointment-card">
                            <div class="card-body">
                                <div class="table-responsive appointment-table">      
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th scope="col">Appointment No</th>
                                                <th scope="col">PATIENT</th>
                                                <th scope="col">Day</th>
                                                <th scope="col">TIME</th>
                                                <th scope="col">STATUS</th>
                                                <th scope="col">ACTION</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            
                                            <?php
                                                if ($sessionRole == "Doctor") {
                                                    $query = mysqli_query($connection, "
                                                        SELECT a.*, av.Day, av.StartTime, av.EndTime
                                                        FROM appointments_table a
                                                        INNER JOIN availability_table av
                                                            ON a.AvailabilityID = av.AvailabilityID
                                                        WHERE a.DoctorID = '$doctorId'
                                                        ORDER BY av.StartTime ASC
                                                    ");

                                                    if(mysqli_num_rows($query) > 0){

                                                        while($appointment = mysqli_fetch_assoc($query)){

                                                            $patientId = $appointment['PatientID'];

                                                            // Get UserID
                                                            $patientQuery = mysqli_query($connection, "
                                                                SELECT UserID
                                                                FROM patients_table
                                                                WHERE PatientID = '$patientId'
                                                            ");

                                                            $patient = mysqli_fetch_assoc($patientQuery);
                                                            $userId = $patient['UserID'];

                                                            // Get Patient Name
                                                            $userQuery = mysqli_query($connection, "
                                                                SELECT Name
                                                                FROM users_table
                                                                WHERE UserID = '$userId'
                                                            ");

                                                            $user = mysqli_fetch_assoc($userQuery);
                                                            $patientName = $user['Name'];

                                                            $day = $appointment['Day'];

                                                            $time = date("h:i A", strtotime($appointment['StartTime'])) .
                                                                    " - " .
                                                                    date("h:i A", strtotime($appointment['EndTime']));

                                                            $status = $appointment['Status'];

                                                            $appointmentId = $appointment['AppointmentID'];

                                                            $appointmentNo = $appointment['AppointmentNumber'];

                                                            switch($status){

                                                                case "Confirmed":
                                                                    $statusClass = "bg-success";
                                                                    break;

                                                                case "Pending":
                                                                    $statusClass = "bg-warning text-dark";
                                                                    break;

                                                                case "Completed":
                                                                    $statusClass = "bg-secondary";
                                                                    break;

                                                                case "Cancelled":
                                                                case "Rejected":
                                                                    $statusClass = "bg-danger";
                                                                    break;

                                                                default:
                                                                    $statusClass = "bg-primary";
                                                            }

                                                            ?>

                                                            <tr>
                                                                <td class="align-middle"><?php echo $appointmentNo; ?></td>
                                                                <td class="align-middle"><?php echo $patientName; ?></td>
                                                                <td class="align-middle"><?php echo $day; ?></td>
                                                                <td class="align-middle"><?php echo $time; ?></td>
                                                                <td class="align-middle">
                                                                    <span class="badge <?php echo $statusClass; ?>">
                                                                        <?php echo $status; ?>
                                                                    </span>
                                                                </td>
                                                                <td class="align-middle"><button type="button" onclick="editappointment_btn(<?php echo $appointmentId; ?>)" data-bs-toggle="modal" data-bs-target="#edit_appointment_modal" class="btn btn-outline-secondary"><i class="bi bi-pencil"></i></button></td>
                                                            </tr>

                                                            <?php
                                                        }

                                                    }
                                                    else{
                                                        ?>

                                                        <tr>
                                                            <td colspan="6" class="text-center text-muted py-4">
                                                                No appointments scheduled.
                                                            </td>
                                                        </tr>

                                                        <?php
                                                    }
                                                }
                                            ?>
                                                                           
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <!-- EDIT appointment MODAL -->
            <div class="modal fade" id="edit_appointment_modal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h1 class="modal-title fs-5" id="exampleModalLabel">Edit Appointment</h1>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">Status</label>
                                <select class="form-select" id="edit-appointment-modal-status-field" aria-label="Default select example">
                                    <option value="Pending">Pending</option>    
                                    <option value="Confirmed">Confirmed</option>
                                    <option value="Rejected">Rejected</option>
                                    <option value="Completed">Completed</option>
                                </select>
                            </div>
                            <input type="hidden" id="edit-appointment-id" >
                        </div>
                        <div class="modal-footer">
                            <button type="button" onclick="editappointment()" class="btn btn-secondary">Save</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ==================== APPOINTMENT END ==================== -->

            <!-- ==================== DOCTOR END ==================== -->








            <!-- ==================== patient ==================== -->

            <!-- ==================== dashboard ==================== -->
            <div class="container-fluid patient-page-dashboard" id="patient-page-dashboard">

                <div class="row mb-3">
                    <div class="col-12 d-flex flex-wrap align-items-center gap-3 patient-page-dashboard-header">
                        <button type="button" class="sidebar-toggle" onclick="toggleSidebar()">
                            <i class="bi bi-chevron-bar-left"></i>
                        </button>

                        <?php

                            $userName = "";

                            if ($sessionRole == "Patient") {

                                $userQuery = mysqli_query($connection, "
                                    SELECT Name
                                    FROM users_table
                                    WHERE UserID = '$sessionId'
                                ");

                                if (mysqli_num_rows($userQuery) > 0) {

                                    $user = mysqli_fetch_assoc($userQuery);
                                    $userName = $user['Name'];

                                }

                            }
                        ?>

                        <div>
                            <h4><?php echo $sessionRole; ?> Dashboard</h4>
                            <p>
                                Welcome back, <strong><?php echo ucwords(htmlspecialchars($userName)); ?></strong>!
                            </p>
                        </div>
                    </div>
                </div>  

                <div class="row">
                    <div class="col-12 col-lg-8 mb-4">
                        <div class="card upcomming-appointments-card">
                            <div class="card-body">
                                <h5 class="card-title">Upcomming Appointments</h5>

                                <?php
                                    if ($sessionRole == "Patient") {

                                        $selectpatientid = mysqli_query($connection, "SELECT PatientID FROM patients_table WHERE UserID = '$sessionId' ");

                                        $row = mysqli_fetch_assoc($selectpatientid);

                                        $patientId = $row['PatientID'];

                                        $query = mysqli_query($connection, "
                                            SELECT a.*, av.Day, av.StartTime, av.EndTime
                                            FROM appointments_table a
                                            INNER JOIN availability_table av
                                                ON a.AvailabilityID = av.AvailabilityID
                                            WHERE a.PatientID = '$patientId'
                                            AND a.Status IN ('Pending','Confirmed')
                                            ORDER BY a.AppointmentID DESC
                                        ");

                                        if(mysqli_num_rows($query) > 0){

                                            while($appointment = mysqli_fetch_assoc($query)){

                                                $doctorId = $appointment['DoctorID'];

                                                // Get UserID of doctor
                                                $doctorQuery = mysqli_query($connection,"
                                                    SELECT UserID
                                                    FROM doctors_table
                                                    WHERE DoctorID = '$doctorId'
                                                ");

                                                $doctor = mysqli_fetch_assoc($doctorQuery);

                                                // Get Doctor Name
                                                $userQuery = mysqli_query($connection,"
                                                    SELECT Name
                                                    FROM users_table
                                                    WHERE UserID = '".$doctor['UserID']."'
                                                ");

                                                $user = mysqli_fetch_assoc($userQuery);

                                                $doctorName = $user['Name'];

                                                $day = $appointment['Day'];

                                                $time = date("h:i A", strtotime($appointment['StartTime'])) .
                                                        " - " .
                                                        date("h:i A", strtotime($appointment['EndTime']));

                                                $status = $appointment['Status'];

                                                $badge = ($status == "Confirmed")
                                                            ? "bg-success"
                                                            : "bg-warning text-dark";

                                                ?>

                                                <div class="appointments-list">
                                                    <div>
                                                        <p class="card-text mt-0 mb-0"><b><?php echo $doctorName; ?></b></p>
                                                        <p class="card-text"><?php echo $day; ?>: <?php echo $time; ?></p>
                                                    </div>
                                                    <p class="status-badge"><span class="badge <?php echo $badge; ?>"><?php echo $status; ?></span></p>
                                                </div>
                                                <hr>

                                                <?php
                                            }
                                        }
                                        else{
                                            ?>
                                                <p class="text-center text-muted py-4 mb-0">You have no upcoming appointments.</p>
                                            <?php
                                        }
                                    }
                                ?>

                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-lg-4">
                        <div class="card health-care-card">
                            <div class="card-body">
                                <h5 class="card-title">Healthcare Tips</h5>
                                <div class="tips">
                                    <p class="card-text">💧 Drink at least 8 glasses of water daily.</p>
                                    <p class="card-text">🥗 Eat a balanced diet rich in fruits and vegetables.</p>
                                    <p class="card-text">🏃 Exercise regularly to maintain a healthy lifestyle.</p>
                                    <p class="card-text">😴 Get 7–8 hours of sleep every night.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
            <!-- ==================== dashboard END ==================== -->

            <!-- ==================== search doctor ==================== -->
            <div class="container-fluid patient-page-search-doctor" id="patient-page-search-doctor">

                <div class="row mb-3">
                    <div class="col-12 d-flex flex-wrap align-items-center gap-3 patient-page-search-doctor-header">
                        <button type="button" class="sidebar-toggle" onclick="toggleSidebar()">
                            <i class="bi bi-chevron-bar-left"></i>
                        </button>
                        <div>
                            <h4>Search Doctor</h4>
                        </div>
                    </div>
                </div>  

                <div class="row">

                    <div class="col-sm-6 col-md-3 mb-3">
                        <input type="text" id="searchDoctor" class="form-control" placeholder="Search Doctor" oninput="loadDoctors()">
                    </div>

                    <div class="col-sm-6 col-md-3 mb-3">
                        <select id="cityFilter" class="form-select" onchange="loadDoctors()">
                            <option value="">All Cities</option>

                            <?php

                                if ($sessionRole == "Patient") {

                                    $cities = mysqli_query($connection,"SELECT * FROM cities_table");

                                    while($city=mysqli_fetch_assoc($cities)){
                                        ?>
                                        <option value="<?php echo $city['CityID']; ?>">
                                            <?php echo $city['CityName']; ?>
                                        </option>
                                        <?php
                                    }
                                }

                            ?>

                        </select>
                    </div>

                    <div class="col-sm-6 col-md-3 mb-3">

                        <select id="specialistFilter" class="form-select" onchange="loadDoctors()">

                            <option value="">All Specializations</option>

                            <?php

                                if ($sessionRole == "Patient") {

                                    $specialists = mysqli_query($connection,"SELECT Specialist FROM doctors_table");

                                    while($specialist = mysqli_fetch_assoc($specialists)){
                                        ?> 
                                        <option value="<?php echo $specialist['Specialist']; ?>">
                                            <?php echo $specialist['Specialist']; ?>
                                        </option>
                                        <?php
                                    }
                                }

                            ?>

                        </select>

                    </div>

                </div>

                <div class="row g-4 doctor-cards" id="doctorCards">

                </div>


            </div>
            <!-- ==================== search doctor end ==================== -->


            <!-- ==================== book appointment ==================== -->
            <div class="container-fluid patient-page-book-appointment" id="patient-page-book-appointment">


                <div class="row mb-3">
                    <div class="col-12 d-flex flex-wrap align-items-center gap-3 patient-page-book-appointment-header">
                        <button type="button" class="sidebar-toggle" onclick="toggleSidebar()">
                            <i class="bi bi-chevron-bar-left"></i>
                        </button>
                        <div>
                            <h4>Search Doctor</h4>
                        </div>
                    </div>
                </div>  


                <div class="row">
                    <div class="col-12 d-flex progress-bar">
                        <div class="progress-number active" id="part-1">1</div>
                        <div class="progress-line" id="part-1-line"></div>
                        <div class="progress-number" id="part-2">2</div>
                        <div class="progress-line" id="part-2-line"></div>
                        <div class="progress-number" id="part-3">3</div>
                    </div>
                </div>

                
                <div class="row mt-3 g-4 book-appointment-process">


                    <div class="col-12 col-sm-10 col-md-8 col-lg-6" id="book-appointment-select-doctor-part">
                        <h5>Step 1: Select Doctor</h5>
                        <p class="text-muted">Doctor selected: <b id="selected-doctor-Name-top"></b> <span id="selected-doctor-Specialist"></span></p>
                        <div class="card mb-3 ">
                            <div class="card-body">
                                <img src="" id="selected-doctor-img" alt="doctor image" class="doctor-img">
                                <div>
                                    <h5 class="card-title" id="selected-doctor-Name"></h5>
                                    <p class="text-muted mb-0"><span id="selected-doctor-qualification"></span> | <span id="selected-doctor-experience"></span></p>
                                </div>
                            </div>
                        </div>
                        <input type="hidden" id="selected-doctor-id">
                        <button type="button" class="btn btn-secondary book-btn" onclick="book_appointment_select_slot_part_btn_click()"> Next: Select Slot <i class="bi bi-arrow-right"></i></button>
                    </div>


                    <div class="col-12 col-sm-10 col-md-8 col-lg-6 d-none" id="book-appointment-select-slot-part">
                        <h5>Step 2: Select Appointment Slot</h5>

                        <div id="doctorAvailableSlots">

                        </div>

                        <div class="d-flex flex-wrap gap-3 mt-3">
                            <button type="button" class="btn btn-secondary book-btn d-none" id="book-appointment-confirm-part-btn" onclick="book_appointment_confirm_part_btn_click()">Next: Confirm <i class="bi bi-arrow-right"></i></button>
                            <button type="button" class="btn btn-outline-secondary" onclick="book_appointment_select_doctor_part_btn_click()"><i class="bi bi-arrow-left"></i> Back</button>
                        </div>
                        <input type="hidden" id="availabilityId">
                        <input type="hidden" id="selectedDay">
                        <input type="hidden" id="selectedStart">
                        <input type="hidden" id="selectedEnd">
                    </div>


                    <div class="col-12 col-sm-10 col-md-8 col-lg-6 d-none" id="book-appointment-confirm-part">
                        <h5>Step 3: Confirm Appointment</h5>
                        <div class="card mb-3 mt-3 ">
                            <div class="card-body">
                                <div>
                                    <p class=""><b>Doctor: </b><span id="confirm-doctor-selected-name"></span></p>
                                    <p class=""><b>Specialist: </b><span id="confirm-doctor-selected-specialist"></span></p>
                                    <p class=""><b>Appointment Slot: </b><span id="confirm-doctor-selected-slot"></span></p>
                                </div>
                            </div>
                        </div>
                        <div class="d-flex flex-wrap gap-3 mt-3">
                            <button type="button" class="btn btn-secondary book-btn" onclick="save_appointment()"><i class="bi bi-check-circle"></i> Confirm Booking</button>
                            <button type="button" class="btn btn-outline-secondary" onclick="book_appointment_select_slot_part_btn_click()"><i class="bi bi-arrow-left"></i> Back</button>
                        </div>
                    </div>


                </div>


            </div>

            <!-- ==================== book appointment END ==================== -->

            <!-- ==================== my appointment ==================== -->

            
            <div class="container-fluid patient-page-my-appointment" id="patient-page-my-appointment">

                <div class="row mb-3">
                    <div class="col-12 d-flex flex-wrap align-items-center gap-3 patient-page-my-appointment-header">
                        <button type="button" class="sidebar-toggle" onclick="toggleSidebar()">
                            <i class="bi bi-chevron-bar-left"></i>
                        </button>
                        <div>
                            <h4>My Appointments</h4>
                        </div>
                    </div>
                </div>  

                <div class="row mt-3 g-3 my-appointments">

                <?php 
                
                    if ($sessionRole == "Patient") {
                    
                        $selectpatientid = mysqli_query($connection, "SELECT PatientID FROM patients_table WHERE UserID = '$sessionId' ");

                        $row = mysqli_fetch_assoc($selectpatientid);

                        $patientId = $row['PatientID'];

                        $selectAppointments = mysqli_query($connection, "SELECT * FROM appointments_table WHERE PatientID = '$patientId' AND Status != 'Cancelled'");

                        while ($appointment = mysqli_fetch_assoc($selectAppointments)) {

                            $aptId = $appointment['AppointmentID'];
                            $aptNum = $appointment['AppointmentNumber'];
                            $doctorId = $appointment['DoctorID'];
                            $patientId = $appointment['PatientID'];
                            $availabilityId = $appointment['AvailabilityID'];
                            $status = $appointment['Status'];

                            $totalQuery = mysqli_query($connection, "SELECT COUNT(*) AS Total FROM appointments_table WHERE AvailabilityID = '$availabilityId' AND Status IN ('Pending','Confirmed')");
                            $totalAppointments = mysqli_fetch_assoc($totalQuery)['Total'];

                            $beforeQuery = mysqli_query($connection, "SELECT COUNT(*) AS BeforeCount FROM appointments_table WHERE AvailabilityID = '$availabilityId' AND AppointmentID < '$aptId' AND Status IN ('Pending','Confirmed')");
                            $beforeAppointments = mysqli_fetch_assoc($beforeQuery)['BeforeCount'];

                            $yourPosition = $beforeAppointments + 1;

                            $selectDoctorSpecialist = mysqli_query($connection, "SELECT UserID, Specialist FROM doctors_table WHERE DoctorID = '$doctorId'");
                            $doctor = mysqli_fetch_assoc($selectDoctorSpecialist);
                            $userId = $doctor['UserID'];
                            $specialist = $doctor['Specialist'];


                            $selectDoctorName = mysqli_query($connection, "SELECT Name FROM users_table WHERE UserID = '$userId'");
                            $user = mysqli_fetch_assoc($selectDoctorName);
                            $name = $user['Name'];

                            $selectDoctorAvailable = mysqli_query($connection, "SELECT Day, StartTime, EndTime FROM availability_table WHERE AvailabilityID = '$availabilityId'");
                            $available = mysqli_fetch_assoc($selectDoctorAvailable);
                            $day = $available['Day'];
                            $start = date("h:i A", strtotime($available['StartTime']));
                            $end = date("h:i A", strtotime($available['EndTime']));

                            switch($status){

                                case "Confirmed":
                                    $statusClass = "bg-success";
                                    break;

                                case "Pending":
                                    $statusClass = "bg-warning text-dark";
                                    break;

                                case "Completed":
                                    $statusClass = "bg-secondary";
                                    break;

                                case "Cancelled":
                                case "Rejected":
                                    $statusClass = "bg-danger";
                                    break;

                                default:
                                    $statusClass = "bg-primary";
                            }

                            ?>

                            <div class="col-md-6">
                                <div class="card ">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col"><h5 class="card-title"><?php echo $name; ?> <span class="text-muted">(<?php echo $specialist; ?>)</span></h5></div>
                                            <div class="col status">
                                                <span class="badge <?php echo $statusClass; ?>"><?php echo $status; ?></span>
                                            </div>
                                        </div>
                                        <p class="card-text mb-1"><?php echo $day; ?>: <?php echo $start; ?> - <?php echo $end; ?></p>
                                        <p class="card-text mb-1">Appointment No: <?php echo $aptNum; ?></p>
                                        <p class="card-text mb-1">Patients Ahead: <?php echo $beforeAppointments; ?></p>
                                        <p class="card-text mb-1">Your Turn: <?php echo $yourPosition; ?> of <?php echo $totalAppointments; ?></p>
                                        <div class="button-wrapper">
                                            <button type="button" class="btn btn-outline-danger btn-sm patient-cancel-appointment-btn" onclick="patient_cancel_appointment(<?php echo $aptId; ?>)">Cancel Appointment</button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <?php


                        }

                    }

                ?>

                </div>
            
            </div>

            <!-- ==================== my appointment END ==================== -->

            <!-- ==================== my profile ==================== -->

            <div class="container-fluid patient-page-my-profile" id="patient-page-my-profile">

                <div class="row mb-3">
                    <div class="col-12 d-flex flex-wrap align-items-center gap-3 patient-page-my-profile-header">
                        <button type="button" class="sidebar-toggle" onclick="toggleSidebar()">
                            <i class="bi bi-chevron-bar-left"></i>
                        </button>
                        <div>
                            <h4>My Profile</h4>
                        </div>
                    </div>
                </div>  

                <div class="row mt-3 g-3 profile-card">

                    <div class="col-md-5">
                        <div class="card ">
                            <div class="card-body">

                                <?php 
                                
                                    if ($sessionRole == "Patient") {
                                    
                                        $selectPatient = mysqli_query($connection, "SELECT * FROM patients_table WHERE UserID = '$sessionId'");
                                        $patient = mysqli_fetch_assoc($selectPatient);

                                        $userId = $patient['UserID'];
                                        $address = $patient['Address'];
                                        $phone = $patient['Phone'];
                                        $birth = date("d M Y", strtotime($patient['DateOfBirth']));
                                        $gender = $patient['Gender'];
                                        $created = date("d M Y", strtotime($patient['CreatedAt']));

                                        
                                        $selectUser = mysqli_query($connection, "SELECT * FROM users_table WHERE UserID = '$userId'");
                                        $user = mysqli_fetch_assoc($selectUser);

                                        $name = $user['Name'];
                                        $email = $user['Email'];

                                        ?>

                                        <p class="card-text"><b>Name:</b> <?php echo $name; ?></p>
                                        <p class="card-text"><b>Email:</b> <?php echo $email; ?></p>
                                        <p class="card-text"><b>Phone:</b> <?php echo $phone; ?></p>
                                        <p class="card-text"><b>Address:</b> <?php echo $address; ?></p>
                                        <p class="card-text"><b>Date of Birth:</b> <?php echo $birth; ?></p>
                                        <p class="card-text"><b>Gender:</b> <?php echo $gender; ?></p>
                                        <p class="card-text"><b>Member Since:</b> <?php echo $created; ?></p>

                                        <button type="button" class="btn btn-secondary">Edit Profile</button>

                                        <?php


                                    }

                                ?>

                            </div>
                        </div>
                    </div>

                </div>
            
            </div>

            <!-- ==================== my profile END ==================== -->

            <!-- ==================== patient END==================== -->





        </main>

    </form>
    

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="js/script.js"></script>
</body>
</html>