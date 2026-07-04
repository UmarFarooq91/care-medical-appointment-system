<?php include 'includes/connection.php'; ?>

<?php require_once 'includes/navbar.php'; ?>


<!-- ================= Contact Section ================= -->

<section class="contact-section py-5">

    <div class="container">

        <div class="row g-5">

            <!-- ================= Left Column ================= -->

            <div class="col-lg-6">

                <h2 class="section-title mb-4">
                    Contact Us
                </h2>

                <form method="post">

                    <div class="mb-3">
                        <label class="form-label">Name</label>
                        <input type="text" name="user-name" class="form-control">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="user-email" class="form-control">
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Message</label>
                        <textarea class="form-control" name="user-message" rows="4" ></textarea>
                    </div>

                    <button type="submit" name="send-message-btn" class="btn btn-secondary px-4 py-2">
                        Send Message
                    </button>

                </form>

            </div>


            <!-- ================= Right Column ================= -->

            <div class="col-lg-6">

                <h2 class="info-section-title mb-4">
                    Our Information
                </h2>

                <div class="contact-info mb-4">

                    <p>
                        <i class="bi bi-geo-alt-fill"></i>
                        123 Healthcare Avenue, New York, NY 10001
                    </p>

                    <p>
                        <i class="bi bi-telephone-fill"></i>
                        +1 (555) 123-4567
                    </p>

                    <p>
                        <i class="bi bi-envelope-fill"></i>
                        info@caremedical.com
                    </p>

                </div>

                <!-- Map -->

                <div class="map-section mb-3">

                    <iframe
                        src="https://www.google.com/maps?q=New+York&output=embed"
                        width="100%"
                        height="280"
                        style="border:0;"
                        loading="lazy">
                    </iframe>

                </div>

                <!-- FAQ -->

                <h4 class="mb-3">
                    FAQ
                </h4>

                <div class="accordion" id="faqAccordion">

                    <!-- FAQ 1 -->

                    <div class="accordion-item">

                        <h2 class="accordion-header">

                            <button class="accordion-button"
                                data-bs-toggle="collapse"
                                data-bs-target="#faq1">

                                How do I book an appointment?

                            </button>

                        </h2>

                        <div id="faq1"
                            class="accordion-collapse collapse show"
                            data-bs-parent="#faqAccordion">

                            <div class="accordion-body">

                                Search for a doctor, select a date and time,
                                and confirm your booking.

                            </div>

                        </div>

                    </div>

                    <!-- FAQ 2 -->

                    <div class="accordion-item mt-2">

                        <h2 class="accordion-header">

                            <button class="accordion-button collapsed"
                                data-bs-toggle="collapse"
                                data-bs-target="#faq2">

                                Is telemedicine available?

                            </button>

                        </h2>

                        <div id="faq2"
                            class="accordion-collapse collapse"
                            data-bs-parent="#faqAccordion">

                            <div class="accordion-body">

                                Yes, we offer telemedicine consultations.

                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>

</section>



<?php require_once 'includes/footer.php'; ?>


<?php

if (isset($_POST['send-message-btn'])) {

    $name = $_POST['user-name'];
    $email = $_POST['user-email'];
    $message = $_POST['user-message'];

    if (empty($name) || empty($email) || empty($message)) {

        echo "
        <script>

            Swal.fire({
                icon: 'warning',
                title: 'Incomplete Form',
                text: 'Please fill in all required fields before sending your message.',
                confirmButtonColor: '#f0ad4e'
            });

        </script>";

    }

    else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        echo "
        <script>

            Swal.fire({
                icon: 'error',
                title: 'Invalid Email',
                text: 'Please enter a valid email address.',
                confirmButtonColor: '#dc3545'
            });

        </script>";

    }
    
    else {

        $insertQuery = "INSERT INTO contact_messages_table (Name, Email, Message) VALUES ('$name','$email','$message')";

        $result = mysqli_query($connection, $insertQuery);

        if($result){
            echo "
            <script>

                Swal.fire({
                    icon: 'success',
                    title: 'Message Sent!',
                    text: 'Your message has been sent successfully.',
                    confirmButtonColor: '#198754'
                }).then(function(){

                    window.location.href='index.php';

                });

            </script>";
        }
        else{
            echo "
            <script>

                Swal.fire({
                    icon: 'error',
                    title: 'Failed!',
                    text: 'Unable to send your message. Please try again.',
                    confirmButtonColor: '#dc3545'
                });

            </script>";
        }

    }


}

?>

