
<?php

include 'includes/connection.php';

if(isset($_POST['userLoginRequest'])) {
    if($_POST['userLoginRequest'] == "user login"){
       
        $email = trim($_POST['signin-email-field']);
        $password = trim($_POST['sigin-password-field']);

        $selectQuery = mysqli_query( $connection, "SELECT UserID, Role, Status, Password FROM users_table WHERE Email='$email'" );

        if(mysqli_num_rows($selectQuery) > 0){
            $user = mysqli_fetch_assoc($selectQuery);

            if(password_verify($password, $user['Password'])){

                session_start();

                $_SESSION['UserID'] = $user['UserID'];
                $_SESSION['Role'] = $user['Role'];
                $_SESSION['Status'] = $user['Status'];

                echo json_encode(["status" => "success"]);
                exit;
            }
            else{
                echo json_encode(["status" => "not match"]);
                exit;
            }
        }
        else{
            echo json_encode(["status" => "not match"]);
            exit;
        }

    }
}

if(isset($_POST['savePatientRequest'])) {
    if($_POST['savePatientRequest'] == "save patient"){
        
        mysqli_begin_transaction($connection);

        try {

            $name = trim($_POST['register-name-field']);
            $email = trim($_POST['register-email-field']);
            $phone = trim($_POST['register-phone-field']);
            $address = trim($_POST['register-address-field']);
            $birth = trim($_POST['register-birth-field']);
            $gender = trim($_POST['register-gender-field']);            
            $password = password_hash(trim($_POST['register-password-field']), PASSWORD_BCRYPT );

            $checkEmail = mysqli_query($connection, "SELECT UserID FROM users_table WHERE Email='$email'");

            if(mysqli_num_rows($checkEmail) > 0){

                mysqli_rollback($connection);

                echo json_encode(["status" => "email exists"]);

                exit;

            }

            // 1. Insert into users table
            $insertUser = mysqli_query($connection,"INSERT INTO users_table (Name, Email, Password, Role) VALUES ('$name', '$email', '$password', 'Patient')");

            if(!$insertUser){
                throw new Exception("User insert failed");
            }

            // 2. Get inserted UserID
            $userID = mysqli_insert_id($connection);

            // 3. Insert into patients table (with FK)
            $insertPatient = mysqli_query($connection, "INSERT INTO patients_table (UserID, Address, Phone, DateOfBirth, Gender)VALUES ('$userID', '$address', '$phone', '$birth', '$gender')");

            if(!$insertPatient){
                throw new Exception("Patient insert failed");
            }

            // 4. Commit transaction
            mysqli_commit($connection);

            session_start();

            $_SESSION['UserID'] = $userID;
            $_SESSION['Role'] = 'Patient';
            $_SESSION['Status'] = 'Active';

            echo json_encode(["status" => "success"]);

            exit;

        }
        catch(Exception $e){
            mysqli_rollback($connection);

            echo json_encode(["status" => "error"]);
            exit;
        }

    }
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
<body>

    <div class="container-fluid authPage">
        <div class="row justify-content-center w-100">

            <div class="login-card col-12 col-sm-9 col-md-7 col-lg-6 col-xl-5" id="login-card">
                <form method="post">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title mb-2">Welcome Back</h5>
                            <h6 class="card-subtitle mb-4 text-muted">Sign in to your account</h6>
                            <div class="mb-3">
                                <label class="form-label">Email address</label>
                                <input type="email" id="signin-email-field" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Password</label>
                                <div class="input-group">
                                    <input type="password" id="signin-password-field" class="form-control" required>
                                    <button type="button" class="input-group-text" onclick="
                                        document.getElementById('signin-password-field').type = document.getElementById('signin-password-field').type === 'password' ? 'text' : 'password';
                                        this.innerHTML = document.getElementById('signin-password-field').type === 'password' ? '<i class=\'bi bi-eye\'></i>' : '<i class=\'bi bi-eye-slash\'></i>'; "><i class="bi bi-eye"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="mb-4 form-check remember-forgot-block">
                                <div>
                                    <input type="checkbox" class="form-check-input" id="exampleCheck1">
                                    <label class="form-check-label" for="exampleCheck1">Remember me</label>
                                </div>
                                <div>
                                    <a href="#" class="card-link">Forgot password?</a>
                                </div>
                            </div>
                            <div class="d-grid mb-3">
                                <button class="btn btn-secondary" type="button" onclick="user_login_btn_click()">Sign In</button>
                            </div>
                            <p class="card-text text-center mb-3">Don't have an account? <a href="#" onclick="showRegisterPage();" class="card-link">Register</a></p>
                            <p class="card-text text-center"><a href="index.php" class="card-link">← Back to Home</a></p>
                        </div>
                    </div>
                </form>
            </div>
            
            <div class="register-card d-none col-12 col-sm-10 col-md-9 col-lg-7 col-xl-6" id="register-card">
                <form method="post">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Patient Registration</h5>
                            <h6 class="card-subtitle mb-5 text-muted">Create your CARE account</h6>

                            <div class="row">
                                <div class="col-12 col-md-6 mb-3">
                                    <label class="form-label">Full Name</label>
                                    <input type="text" id="register-name-field" class="form-control" required>
                                </div>

                                <div class="col-12 col-md-6 mb-3">
                                    <label class="form-label">Email</label>
                                    <input type="email" id="register-email-field" class="form-control" required>
                                </div>

                                <div class="col-12 col-md-6 mb-3">
                                    <label class="form-label">Phone Number</label>
                                    <input type="number" id="register-phone-field" class="form-control" required>
                                </div>

                                <div class="col-12 col-md-6 mb-3">
                                    <label class="form-label">Address</label>
                                    <textarea class="form-control" id="register-address-field" required></textarea>
                                </div>

                                <div class="col-12 col-md-6 mb-3">
                                    <label class="form-label">Date of Birth</label>
                                    <input type="date" id="register-birth-field" class="form-control" required>
                                </div>

                                <div class="col-12 col-md-6 mb-3">
                                    <label class="form-label">Gender</label>
                                    <select class="form-select" id="register-gender-field">
                                        <option>Male</option>
                                        <option>Female</option>
                                        <option>Others</option>
                                    </select>
                                </div>

                                <div class="col-12 col-md-6 mb-3">
                                    <label class="form-label">Password</label>
                                    <div class="input-group">
                                        <input type="password" id="register-password-field" class="form-control" required>
                                        <button type="button" class="input-group-text" onclick="
                                            document.getElementById('register-password-field').type = document.getElementById('register-password-field').type === 'password' ? 'text' : 'password';
                                            this.innerHTML = document.getElementById('register-password-field').type === 'password' ? '<i class=\'bi bi-eye\'></i>' : '<i class=\'bi bi-eye-slash\'></i>'; "><i class="bi bi-eye"></i>
                                        </button>
                                    </div>
                                </div>

                                <div class="col-12 col-md-6 mb-3">
                                    <label class="form-label">Confirm Password</label>
                                    <div class="input-group">
                                        <input type="password" id="register-confirm-password-field" class="form-control" required>
                                        <button type="button" class="input-group-text" onclick="
                                            document.getElementById('register-confirm-password-field').type = document.getElementById('register-confirm-password-field').type === 'password' ? 'text' : 'password';
                                            this.innerHTML = document.getElementById('register-confirm-password-field').type === 'password' ? '<i class=\'bi bi-eye\'></i>' : '<i class=\'bi bi-eye-slash\'></i>'; "><i class="bi bi-eye"></i>
                                        </button>
                                    </div>
                                </div>

                                <div class="d-grid mb-3"><button type="button" class="btn btn-secondary" onclick="patient_register_btn_click()" >Register</button></div>
                                <p class="text-center mb-3">Already have an account? <a href="#" onclick="showLoginPage();" class="card-link">Login</a></p>
                                <p class="text-center"><a href="index.php" class="card-link">← Back to Home</a></p>
                            </div>
                            
                        </div>
                    </div>
                </form>
            </div>

        </div>
    </div>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="js/script.js"></script>

</body>
</html>