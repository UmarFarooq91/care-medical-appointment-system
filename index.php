
<?php

include 'includes/connection.php';

?>


<?php require_once 'includes/navbar.php'; ?>

<main class="home-page">

    <div class="container-fluid hero-banner">
        <div class="container">
            <div class="row">
                
                <div class="col-lg-6 col-12 first-col">
                    <h1>Your Health, <span>Our Priority</span></h1>
                    <p class="text-muted">Book appointments with top-rated specialists. Access quality healthcare from the comfort of your home with CARE Medical.</p>
                    <div class="search-wrapper">
                        <input type="text" class="form-control" placeholder="Search doctors, specialists...">
                        <a class="btn" href="finddoctor.php"><i class="bi bi-search"></i> Search Doctor</a>
                    </div>
                    <div class="banner-statistics">
                        <div>
                            <h4>500+</h4>
                            <p>Expert Doctors</p>
                        </div>
                        <div>
                            <h4>50k+</h4>
                            <p>Happy Patients</p>
                        </div>
                        <div>
                            <h4>30+</h4>
                            <p>Cities Covered</p>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6 col-12 second-col">
                    <div class="card">
                        <div class="card-body">
                            <div class="row g-4 text-center">
                                <div class="col first-people">
                                    <div class="first-circle"><div class="second-circle"></div></div>
                                    <div class="first-line"></div>
                                    <div class="second-line"></div>
                                </div>
                                <div class="col second-people">
                                    <div class="first-circle"><div class="second-circle"></div></div>
                                    <div class="first-line"></div>
                                    <div class="second-line"></div>
                                </div>
                                <p><span>Book Appointment</span></p>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    
    <!-- Featured Doctors Section -->

    <section class="featured-doctors py-5">
        <div class="container">

            <!-- Section Heading -->
            <div class="text-center mb-5">
                <h2 class="fw-bold">Featured Doctors</h2>
                <p class="text-muted">Meet our top-rated specialists</p>
            </div>

            <div class="row g-4">

            <?php

                $query = "SELECT *
                    FROM users_table
                    INNER JOIN doctors_table
                    ON users_table.UserID = doctors_table.UserID
                    WHERE Role='Doctor'
                    AND Status='Active'
                    ORDER BY RAND()
                    LIMIT 4";

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


                <div class="col-lg-3 col-md-6">
                    <a href="finddoctor.php" class="doctor-link">
                    <div class="doctor-card text-center p-4 h-100">

                        <img src="data:image/jpeg;base64,<?php echo $image; ?>"
                            alt="Doctor"
                            class="doctor-img mb-3">

                        <h6 class="fw-bold mb-1"><?php echo $name; ?></h6>

                        <p class="text-muted small mb-2">
                            <?php echo $specialist; ?>
                        </p>

                        <!-- <div class="rating mb-3">
                            <i class="bi bi-star-fill text-warning"></i>
                            <span>4.9 (230 reviews)</span>
                        </div> -->

                        <!-- remove rating  -->
                        <?php if ($available) { ?>
                            <span class="badge px-3 py-2" >
                                Available
                            </span>
                        <?php } else { ?>
                            <span class="badge px-3 py-2 busy-badge">
                                Busy
                            </span>
                         <?php } ?>

                    </div>
                    </a>
                </div>

                <?php

                }

            ?>

            </div>

            <!-- More Doctors Button -->
            <div class="text-center mt-5">
                <a href="finddoctor.php" class="btn btn-primary px-4 py-2">
                    More Doctors
                </a>
            </div>

        </div>
    </section>


    <!-- Healthcare Statistics Section -->

    <section class="healthcare-statistics mt-3 py-5">
        <div class="container">

            <!-- Section Heading -->
            <div class="text-center mb-5">
                <h2 class="fw-bold">Healthcare Statistics</h2>
                <p class="text-muted">Meet our top-rated specialists</p>
            </div>

            <div class="row g-4">

                <?php
                
                    $doctorQuery = mysqli_query($connection," SELECT COUNT(*) AS Total FROM doctors_table");

                    $totalDoctors = mysqli_fetch_assoc($doctorQuery)['Total'];


                    $patientQuery = mysqli_query($connection," SELECT COUNT(*) AS Total FROM patients_table");

                    $totalPatients = mysqli_fetch_assoc($patientQuery)['Total'];


                    $appointmentQuery = mysqli_query($connection," SELECT COUNT(*) AS Total FROM appointments_table");

                    $totalAppointment = mysqli_fetch_assoc($appointmentQuery)['Total'];


                    $cityQuery = mysqli_query($connection," SELECT COUNT(*) AS Total FROM cities_table");

                    $totalcities = mysqli_fetch_assoc($cityQuery)['Total'];


                ?>

                <!-- statistics 1 -->
                <div class="col-lg-3 col-md-6">
                    <div class="statistics-card text-center p-4 h-100">

                        <span class="card-icon" style="background-color:#EFF6FF;"><i style="color:#2563EB;" class="bi bi-people-fill"></i></span>

                        <h2 class="mb-2 mt-4"><?php echo $totalDoctors; ?>+</h2>

                        <p class="text-muted small mb-3">Expert Doctors</p>

                    </div>
                </div>

                <!-- statistics 2 -->
                <div class="col-lg-3 col-md-6">
                    <div class="statistics-card text-center p-4 h-100">

                        <span class="card-icon" style="background-color:#F0FDFA;"><i style="color:#0D9488;" class="bi bi-heart-pulse-fill"></i></span>

                        <h2 class="mb-2 mt-4"><?php echo $totalPatients; ?>+</h2>

                        <p class="text-muted small mb-3">Patient Served</p>

                    </div>
                </div>

                <!-- statistics 3 -->
                <div class="col-lg-3 col-md-6">
                    <div class="statistics-card text-center p-4 h-100">

                        <span class="card-icon" style="background-color:#F0FDF4;"><i style="color:#16A34A;" class="bi bi-calendar-check-fill"></i></span>

                        <h2 class="mb-2 mt-4"><?php echo $totalAppointment; ?>+</h2>

                        <p class="text-muted small mb-3">Appointments</p>

                    </div>
                </div>

                <!-- statistics 4 -->
                <div class="col-lg-3 col-md-6">
                    <div class="statistics-card text-center p-4 h-100">

                        <span class="card-icon" style="background-color:#FFFBEB;"><i style="color:#D97706;" class="bi bi-geo-alt-fill"></i></span>

                        <h2 class="mb-2 mt-4"><?php echo $totalcities; ?>+</h2>

                        <p class="text-muted small mb-3">Cities</p>

                    </div>
                </div>


            </div>

        </div>
    </section>


    <!-- ================= Medical News ================= -->

    <section class="medical-news mt-3 py-5">

        <div class="container">

            <div class="row mb-5">

                <div class="col-lg-8 mx-auto text-center">

                    <h2 class="section-title fw-bold">
                        Medical News & Updates
                    </h2>

                    <p class="section-subtitle">
                        Stay informed with the latest healthcare news, medical innovations, and updates from CARE Medical.
                    </p>

                </div>

            </div>

            <div class="row g-4">


                <?php
                
                    $newsQuery = mysqli_query($connection," SELECT * FROM medicalnews_table WHERE Status = 'Active' ORDER BY RAND() LIMIT 3 ");

                    while ($news = mysqli_fetch_assoc($newsQuery)) {
                        $title = $news['Title'];
                        $description = $news['Description'];
                        $date = $news['Date'];
                        ?>

                        <div class="col-lg-4 col-md-6">

                            <div class="news-card h-100">

                                <h4><?php echo $title; ?></h4>

                                <p><?php echo $description; ?></p>

                                <div class="news-date">
                                    <i class="bi bi-calendar-event"></i>
                                    <?php echo $date; ?>
                                </div>

                            </div>

                        </div>

                        <?php
                    }


                ?>

            </div>

        </div>

    </section>


    <!-- ================= Disease Awareness Section ================= -->

    <section class="disease-section mt-5 py-5">

        <div class="container">

            <div class="text-center mb-5">

                <h2 class="section-title fw-bold">
                    Stay Informed About Health Conditions
                </h2>

                <p class="section-subtitle">
                    Learn about common diseases, symptoms, prevention, and treatment options.
                </p>

            </div>

            <div class="row g-4">

                <?php
                
                    $diseaseQuery = mysqli_query($connection," SELECT * FROM diseases_table ORDER BY RAND() LIMIT 4 ");

                    while ($disease = mysqli_fetch_assoc($diseaseQuery)) {
                        $diseaseName = $disease['DiseaseName'];
                        $symptoms = $disease['Symptoms'];
                        $prevention = $disease['Prevention'];
                        $cure = $disease['Cure'];
                        ?>

                        <div class="col-lg-3 col-md-6">

                            <div class="disease-card">

                                <h4><?php echo $diseaseName; ?></h4>

                                <h6>Symptoms</h6>

                                <p><?php echo $symptoms; ?></p>

                                <h6>Prevention</h6>

                                <p><?php echo $prevention; ?></p>

                                <h6>Cure / Treatment</h6>

                                <p><?php echo $cure; ?></p>

                            </div>

                        </div>

                        <?php
                    }

                ?>

            </div>

        </div>

    </section>


    <!-- ================= Get Started CTA  ================= -->

    <section class="cta-section mt-5">

        <div class="container-fluid">

            <div class="cta-box text-center">

                <h2 class="fw-bold">
                    Ready to Book Your Appointment?
                </h2>

                <p>
                    Connect with top doctors in just a few clicks.
                </p>

                <a href="finddoctor.php" class="btn btn-light rounded-pill px-5 py-3">Get Started <i class="bi bi-arrow-right"></i></a>

            </div>

        </div>

    </section>


</main>

<?php require_once 'includes/footer.php'; ?>
