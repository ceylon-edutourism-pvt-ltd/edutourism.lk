-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 10, 2026 at 04:20 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `edutouri_edutourism_lk`
--

-- --------------------------------------------------------

--
-- Table structure for table `attendance`
--

CREATE TABLE `attendance` (
  `id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `date` date NOT NULL,
  `check_in` time DEFAULT NULL,
  `check_out` time DEFAULT NULL,
  `work_hours` decimal(5,2) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `working_hours` time DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `attendance`
--

-- --------------------------------------------------------

--
-- Table structure for table `breaks`
--

CREATE TABLE `breaks` (
  `id` int(11) NOT NULL,
  `attendance_id` int(11) DEFAULT NULL,
  `break_start` time DEFAULT NULL,
  `break_end` time DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `breaks`
--

-- --------------------------------------------------------

--
-- Table structure for table `employees`
--

CREATE TABLE `employees` (
  `id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `role` enum('super','admin','staff') NOT NULL DEFAULT 'staff',
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `username` varchar(100) NOT NULL DEFAULT 'user2004'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `employees`
--

INSERT INTO `employees` (`id`, `name`, `email`, `password`, `role`, `active`, `username`) VALUES
(4, 'Gayan Rajapaksha', 'gayanraj@outlook.com', '$2y$10$0MFhb.F3f7OrgpKndGC4nOoAzKp2JsPEgR5KkSNzPIZWWSLpGJalm', 'super', 1, 'gayanraj'),
(6, 'Tharusha Gimsara', 'tgimsara@gmail.com', '$2y$10$uh6NlUG2iOGeQygf.MNc9OFZV0mbAggN/3eNWk/.gyZGQyZtE74Vi', 'admin', 1, 'tgimsara');

-- --------------------------------------------------------

--
-- Table structure for table `faqs`
--

CREATE TABLE `faqs` (
  `id` int(11) NOT NULL,
  `question_en` text NOT NULL,
  `question_si` text NOT NULL,
  `answer_en` text NOT NULL,
  `answer_si` text NOT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `display_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `heroes`
--

CREATE TABLE `heroes` (
  `id` int(11) NOT NULL,
  `type` tinyint(4) NOT NULL COMMENT '1=Left Text+Right Media, 2=Center Overlay, 3=Left Media+Right Text',
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `button_text` varchar(100) DEFAULT NULL,
  `button_url` varchar(500) DEFAULT NULL,
  `button_text_2` varchar(100) DEFAULT NULL COMMENT 'Second button for Type 2',
  `button_url_2` varchar(500) DEFAULT NULL COMMENT 'Second button URL for Type 2',
  `media_url` varchar(500) NOT NULL COMMENT 'Video or Image URL',
  `media_type` enum('video','image') NOT NULL DEFAULT 'image',
  `background_image` varchar(500) DEFAULT NULL COMMENT 'Background image for entire hero section (Type 1 & 3)',
  `background_overlay_color` varchar(50) DEFAULT 'rgba(0, 0, 0, 0.5)' COMMENT 'Overlay color for background image',
  `text_bg_image` varchar(500) DEFAULT NULL COMMENT 'Background image for text area only (Type 1 & 3)',
  `text_bg_enabled` tinyint(1) DEFAULT 0 COMMENT 'Enable/disable text area background',
  `text_bg_color` varchar(50) DEFAULT 'rgba(0, 0, 0, 0.7)' COMMENT 'Background color/overlay for text area',
  `is_active` tinyint(1) DEFAULT 1,
  `display_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `heroes`
--



-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `position` varchar(255) DEFAULT NULL,
  `organization` varchar(255) DEFAULT NULL,
  `content_en` text NOT NULL,
  `content_si` text DEFAULT NULL,
  `profile_image` varchar(255) DEFAULT NULL,
  `youtube_link` varchar(500) DEFAULT NULL,
  `rating` int(11) DEFAULT 5,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tours`
--

CREATE TABLE `tours` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL COMMENT 'Tour title (English)',
  `destination` varchar(255) NOT NULL COMMENT 'Destination city/country',
  `description` text NOT NULL COMMENT 'Tour description (English)',
  `start_date` date NOT NULL COMMENT 'Tour start date',
  `end_date` date NOT NULL COMMENT 'Tour end date',
  `duration` int(11) NOT NULL COMMENT 'Duration in days',
  `cover_image` varchar(500) DEFAULT NULL COMMENT 'Cover image path: uploads/tours/covers/filename.jpg',
  `participants` int(11) DEFAULT 0 COMMENT 'Number of participants (for past tours)',
  `price` varchar(100) DEFAULT NULL COMMENT 'Tour price (optional)',
  `year` int(11) NOT NULL COMMENT 'Tour year',
  `tour_status` enum('upcoming','past') NOT NULL DEFAULT 'upcoming' COMMENT 'upcoming or past tour',
  `status` tinyint(4) NOT NULL DEFAULT 1 COMMENT '1=Active (visible), 0=Inactive (hidden)',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Unified tours table - both upcoming and past tours';

--
-- Dumping data for table `tours`
--



-- --------------------------------------------------------

--
-- Table structure for table `tour_media`
--

CREATE TABLE `tour_media` (
  `id` int(11) NOT NULL,
  `tour_id` int(11) NOT NULL COMMENT 'Foreign key to tours table',
  `media_type` enum('image','video') NOT NULL DEFAULT 'image' COMMENT 'image or video',
  `media_url` varchar(500) NOT NULL COMMENT 'Image path: uploads/tours/gallery/filename.jpg OR YouTube URL',
  `caption` text DEFAULT NULL COMMENT 'Media caption/description',
  `display_order` int(11) DEFAULT 0 COMMENT 'Display order (lower = first)',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Media gallery for tours - images and videos';

--
-- Dumping data for table `tour_media`
--



-- --------------------------------------------------------

--
-- Table structure for table `visa_applications`
--

CREATE TABLE `visa_applications` (
  `nic_number` varchar(15) NOT NULL COMMENT 'NIC Number - Primary Key',
  `name_for_certificates` varchar(255) DEFAULT NULL COMMENT 'Name for the Certificates',
  `name_for_tour_id` varchar(100) DEFAULT NULL COMMENT 'Name for the Tour ID (Short Name)',
  `permanent_address` text DEFAULT NULL COMMENT 'Permanent Address',
  `city` varchar(100) DEFAULT NULL COMMENT 'Nearest Main City',
  `postal_code` varchar(10) DEFAULT NULL COMMENT 'Postal Code',
  `province` varchar(100) DEFAULT NULL COMMENT 'Province',
  `surname` varchar(255) DEFAULT NULL COMMENT 'Surname as given in Passport',
  `other_names` varchar(255) DEFAULT NULL COMMENT 'Other Names as given in Passport',
  `date_of_birth` date DEFAULT NULL COMMENT 'Date of Birth as given in Passport',
  `gender` enum('male','female') DEFAULT NULL COMMENT 'Gender as given in Passport',
  `passport_number` varchar(20) DEFAULT NULL COMMENT 'Passport Number',
  `issue_date` date DEFAULT NULL COMMENT 'Passport Issue Date',
  `expiry_date` date DEFAULT NULL COMMENT 'Passport Expiry Date',
  `employment_status` enum('employee','business','freelancer','student') DEFAULT NULL COMMENT 'Employment Status',
  `dependent_status` text DEFAULT NULL,
  `passport_copy` text DEFAULT NULL,
  `photo_id` text DEFAULT NULL,
  `visa_request_letter` text DEFAULT NULL,
  `bank_statements` text DEFAULT NULL,
  `employment_letter` text DEFAULT NULL,
  `epf_confirmation` text DEFAULT NULL,
  `pay_slips` text DEFAULT NULL,
  `business_registration` text DEFAULT NULL,
  `form_pvt_ltd` text DEFAULT NULL,
  `company_statements` text DEFAULT NULL,
  `service_letters` text DEFAULT NULL,
  `student_letter` text DEFAULT NULL,
  `dependent_confirmation` text DEFAULT NULL,
  `dependent_income` text DEFAULT NULL,
  `other_documents` text DEFAULT NULL,
  `application_status` enum('draft','submitted','under_review','approved','rejected','completed') DEFAULT 'draft' COMMENT 'Application Status',
  `submission_date` datetime DEFAULT NULL COMMENT 'Date when application was submitted',
  `review_date` datetime DEFAULT NULL COMMENT 'Date when application was reviewed',
  `completion_date` datetime DEFAULT NULL COMMENT 'Date when application was completed',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() COMMENT 'Record Creation Date',
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp() COMMENT 'Record Last Update Date',
  `created_by` varchar(100) DEFAULT NULL COMMENT 'User who created the record',
  `updated_by` varchar(100) DEFAULT NULL COMMENT 'User who last updated the record',
  `notes` text DEFAULT NULL COMMENT 'Internal notes or remarks',
  `rejection_reason` text DEFAULT NULL COMMENT 'Reason for rejection if applicable',
  `processing_fee` decimal(10,2) DEFAULT NULL COMMENT 'Processing fee amount',
  `payment_status` enum('pending','paid','refunded') DEFAULT 'pending' COMMENT 'Payment Status',
  `payment_date` datetime DEFAULT NULL COMMENT 'Payment completion date',
  `reference_number` varchar(50) DEFAULT NULL COMMENT 'System generated reference number',
  `year` int(11) DEFAULT NULL,
  `tourname` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Visa Application Data Storage Table';

--
-- Dumping data for table `visa_applications`
--

--
-- Triggers `visa_applications`
--
DELIMITER $$
CREATE TRIGGER `generate_reference_number` BEFORE INSERT ON `visa_applications` FOR EACH ROW BEGIN
    IF NEW.reference_number IS NULL THEN
        SET NEW.reference_number = CONCAT('VA', YEAR(NOW()), MONTH(NOW()), DAY(NOW()), HOUR(NOW()), MINUTE(NOW()), SECOND(NOW()));
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `log_new_visa_application` AFTER INSERT ON `visa_applications` FOR EACH ROW BEGIN
    INSERT INTO visa_application_logs (nic_number, action, new_status, description)
    VALUES (
        NEW.nic_number,
        'created',
        NEW.application_status,
        CONCAT('New application created at ', NOW())
    );
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `log_visa_application_changes` AFTER UPDATE ON `visa_applications` FOR EACH ROW BEGIN
    INSERT INTO visa_application_logs (nic_number, action, old_status, new_status, description)
    VALUES (
        NEW.nic_number,
        'updated',
        OLD.application_status,
        NEW.application_status,
        CONCAT('Application updated at ', NOW())
    );
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `visa_application_config`
--

CREATE TABLE `visa_application_config` (
  `id` int(11) NOT NULL,
  `config_key` varchar(100) NOT NULL,
  `config_value` text NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `visa_application_config`
--

INSERT INTO `visa_application_config` (`id`, `config_key`, `config_value`, `description`, `created_at`, `updated_at`) VALUES
(1, 'max_file_size', '5242880', 'Maximum file size in bytes (5MB)', '2025-05-27 05:19:31', '2025-05-27 05:19:31'),
(2, 'allowed_file_types', 'jpg,jpeg,png,pdf', 'Allowed file extensions', '2025-05-27 05:19:31', '2025-05-27 05:19:31'),
(3, 'upload_directory', 'uploads/', 'Directory for file uploads', '2025-05-27 05:19:31', '2025-05-27 05:19:31'),
(4, 'application_fee', '0.00', 'Default application processing fee', '2025-05-27 05:19:31', '2025-05-27 05:19:31'),
(5, 'admin_email', 'admin@example.com', 'Admin email for notifications', '2025-05-27 05:19:31', '2025-05-27 05:19:31'),
(6, 'auto_reference_prefix', 'VA', 'Prefix for auto-generated reference numbers', '2025-05-27 05:19:31', '2025-05-27 05:19:31');

-- --------------------------------------------------------

--
-- Table structure for table `visa_application_logs`
--

CREATE TABLE `visa_application_logs` (
  `id` int(11) NOT NULL,
  `nic_number` varchar(15) NOT NULL,
  `action` varchar(100) NOT NULL COMMENT 'Action performed (created, updated, submitted, etc.)',
  `old_status` varchar(50) DEFAULT NULL COMMENT 'Previous status',
  `new_status` varchar(50) DEFAULT NULL COMMENT 'New status',
  `description` text DEFAULT NULL COMMENT 'Description of the action',
  `performed_by` varchar(100) DEFAULT NULL COMMENT 'User who performed the action',
  `performed_at` timestamp NOT NULL DEFAULT current_timestamp() COMMENT 'When the action was performed',
  `ip_address` varchar(45) DEFAULT NULL COMMENT 'IP address of the user'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Visa Application Activity Log';

--
-- Dumping data for table `visa_application_logs`
--

--
-- Indexes for dumped tables
--

--
-- Indexes for table `attendance`
--
ALTER TABLE `attendance`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_employee_date` (`employee_id`,`date`);

--
-- Indexes for table `breaks`
--
ALTER TABLE `breaks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `attendance_id` (`attendance_id`);

--
-- Indexes for table `employees`
--
ALTER TABLE `employees`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `faqs`
--
ALTER TABLE `faqs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `heroes`
--
ALTER TABLE `heroes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_is_active` (`is_active`),
  ADD KEY `idx_display_order` (`display_order`),
  ADD KEY `idx_type` (`type`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tours`
--
ALTER TABLE `tours`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_tour_status` (`tour_status`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_year` (`year`),
  ADD KEY `idx_dates` (`start_date`,`end_date`);

--
-- Indexes for table `tour_media`
--
ALTER TABLE `tour_media`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_tour_id` (`tour_id`),
  ADD KEY `idx_display_order` (`display_order`);

--
-- Indexes for table `visa_applications`
--
ALTER TABLE `visa_applications`
  ADD KEY `idx_nic_tour` (`nic_number`,`tourname`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `attendance`
--
ALTER TABLE `attendance`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `breaks`
--
ALTER TABLE `breaks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `employees`
--
ALTER TABLE `employees`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `faqs`
--
ALTER TABLE `faqs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `heroes`
--
ALTER TABLE `heroes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `tours`
--
ALTER TABLE `tours`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `tour_media`
--
ALTER TABLE `tour_media`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `attendance`
--
ALTER TABLE `attendance`
  ADD CONSTRAINT `attendance_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `breaks`
--
ALTER TABLE `breaks`
  ADD CONSTRAINT `breaks_ibfk_1` FOREIGN KEY (`attendance_id`) REFERENCES `attendance` (`id`);

--
-- Constraints for table `tour_media`
--
ALTER TABLE `tour_media`
  ADD CONSTRAINT `tour_media_ibfk_1` FOREIGN KEY (`tour_id`) REFERENCES `tours` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
