<?php
$active = "Contact";
include('db.php');
include("functions.php");
include("header.php");
?>

<head>
    <link rel="stylesheet" href="css/contactus.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>

<!-- Contact Section Begin -->
<section class="edu-contact-section">
    <div class="container">
        <div class="row">
            <div class="col-lg-5">
                <div class="edu-contact-info-box">
                    <h2 class="edu-contact-info-title">Contact Us</h2>
                    <p class="edu-contact-subtitle">Reach out to us through any of these platforms</p>
                    
                    <div class="edu-contact-links-wrapper">
                        <div class="edu-contact-item edu-contact-call">
                            <span class="edu-contact-item-label">Call Us</span>
                            <a href="tel:+94777138134" class="edu-contact-button">
                                <i class="fas fa-phone-alt"></i>
                                Call Now
                            </a>
                        </div>
                        <div class="edu-contact-item edu-contact-whatsapp">
                            <span class="edu-contact-item-label">WhatsApp</span>
                            <a href="https://wa.me/+94777138134" class="edu-contact-button">
                                <i class="fab fa-whatsapp"></i>
                                Chat Now
                            </a>
                        </div>
                        <div class="edu-contact-item edu-contact-facebook">
                            <span class="edu-contact-item-label">Facebook</span>
                            <a href="https://www.facebook.com/edutourism.lk/" class="edu-contact-button">
                                <i class="fab fa-facebook-f"></i>
                                See Profile
                            </a>
                        </div>
                        <div class="edu-contact-item edu-contact-instagram">
                            <span class="edu-contact-item-label">Instagram</span>
                            <a href="https://www.instagram.com/edutourism.lk/" class="edu-contact-button">
                                <i class="fab fa-instagram"></i>
                                See Profile
                            </a>
                        </div>
                        <div class="edu-contact-item edu-contact-tiktok">
                            <span class="edu-contact-item-label">TikTok</span>
                            <a href="https://www.tiktok.com/@edutourism.lk" class="edu-contact-button">
                                <i class="fab fa-tiktok"></i>
                                See Profile
                            </a>
                        </div>
                        <div class="edu-contact-item edu-contact-youtube">
                            <span class="edu-contact-item-label">YouTube</span>
                            <a href="https://youtube.com/@edutourismLK" class="edu-contact-button">
                                <i class="fab fa-youtube"></i>
                                See Profile
                            </a>
                        </div>
                        <div class="edu-contact-item edu-contact-linkedin">
                            <span class="edu-contact-item-label">LinkedIn</span>
                            <a href="https://www.linkedin.com/in/edutourism/" class="edu-contact-button">
                                <i class="fab fa-linkedin-in"></i>
                                See Profile
                            </a>
                        </div>
                    </div>
                    
                    <div class="edu-contact-map">
                        <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3960.2717291740805!2d79.92661287448315!3d6.977231617725457!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3ae257c1737d6de5%3A0xa329403f5a6cf7e6!2sCeylon%20Edutourism%20Pvt%20Ltd!5e0!3m2!1sen!2slk!4v1770697589802!5m2!1sen!2slk" width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-7">
                <div class="edu-contact-form-container">
                    <h4 class="edu-contact-form-title">Leave A Message</h4>
                    <p class="edu-contact-form-subtitle">Our staff will call back later and answer your questions.</p>
                    
                    <form action="contact.php" method="post" class="edu-contact-form">
                        <div class="row">
                            <div class="col-lg-6">
                                <div class="edu-form-group">
                                    <input type="text" placeholder="Your name" class="edu-form-control" name="name" required>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="edu-form-group">
                                    <input type="email" placeholder="Your email" class="edu-form-control" name="email" required>
                                </div>
                            </div>
                            <div class="col-lg-12">
                                <div class="edu-form-group">
                                    <input type="text" placeholder="Message Subject" class="edu-form-control" name="subject" required>
                                </div>
                            </div>
                            <div class="col-lg-12">
                                <div class="edu-form-group">
                                    <textarea placeholder="Your message" class="edu-form-control" name="message" rows="5" required></textarea>
                                </div>
                                <button type="submit" class="edu-send-btn" name="submit">
                                    <i class="fas fa-paper-plane"></i> Send message
                                </button>
                            </div>
                        </div>
                    </form>

                    <?php
                    if (isset($_POST['submit'])) {
                        $user_name = $_POST['name'];
                        $user_email = $_POST['email'];
                        $user_subject = $_POST['subject'];
                        $user_msg = $_POST['message'];

                        // Email details
                        $to = 'info@edutourism.lk';
                        $headers = "From: $user_email" . "\r\n" .
                                  "Reply-To: $user_email" . "\r\n" .
                                  "X-Mailer: PHP/" . phpversion();
                        
                        // Send email
                        $mail_sent = mail($to, $user_subject, $user_msg, $headers);
                        
                        // Show success/error message
                        if ($mail_sent) {
                            echo "<div class='edu-alert edu-alert-success'>Your message has been sent successfully!</div>";
                        } else {
                            echo "<div class='edu-alert edu-alert-danger'>Sorry, there was an error sending your message.</div>";
                        }
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>
</section>
<!-- Contact Section End -->

<?php include('footer.php'); ?>