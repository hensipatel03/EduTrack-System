-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 02, 2026 at 09:21 AM
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
-- Database: `edutrack_system`
--

-- --------------------------------------------------------

--
-- Table structure for table `assignments`
--

CREATE TABLE `assignments` (
  `id` int(10) UNSIGNED NOT NULL,
  `faculty_user_id` int(10) UNSIGNED NOT NULL,
  `subject_id` int(10) UNSIGNED NOT NULL,
  `title` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `file_name` varchar(255) DEFAULT NULL,
  `original_name` varchar(255) DEFAULT NULL,
  `due_date` datetime NOT NULL,
  `max_marks` int(10) UNSIGNED DEFAULT 10,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `status` enum('active','closed') DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `assignments`
--

INSERT INTO `assignments` (`id`, `faculty_user_id`, `subject_id`, `title`, `description`, `file_name`, `original_name`, `due_date`, `max_marks`, `created_at`, `status`) VALUES
(5, 45, 4, 'Unit1 Assignment', 'Covers PHP all topics', 'ass_6979b93e64be91.16078074.pdf', 'PHP unit-1 assignment.pdf', '2026-02-05 12:50:00', 10, '2026-01-28 12:52:38', 'active'),
(6, 45, 4, 'Unit-2 Assignment', 'PHP Database concepts include', 'ass_6979b9dc415d72.54802261.pdf', 'PHP unit-2 assignment.pdf', '2026-02-10 12:54:00', 10, '2026-01-28 12:55:16', 'active'),
(7, 52, 11, 'Advance computer Networks', 'Covers routing, switching, and network design', 'ass_697d88d9c17373.92961973.pdf', 'Assignmnet-NT.pdf', '2026-02-06 10:14:00', 10, '2026-01-31 10:15:13', 'active'),
(8, 52, 11, 'Cyber Security', 'Protect system from digital attacks and threats', 'ass_697d89273cd3e8.09783162.pdf', 'Assignmnet-NT.pdf', '2026-02-11 10:16:00', 10, '2026-01-31 10:16:31', 'active'),
(10, 51, 10, 'WD Unit-1 Assignment', NULL, 'ass_697da91d6920e6.43235803.pdf', 'WD assignment.pdf', '2026-02-06 12:30:00', 10, '2026-01-31 12:32:53', 'active'),
(11, 51, 10, 'WD unit-2 Assignmnet', NULL, 'ass_697da95647f301.02572748.pdf', 'WD assignment.pdf', '2026-03-14 12:33:00', 10, '2026-01-31 12:33:50', 'active'),
(12, 55, 5, 'OS unit-1 assignmnet', NULL, 'ass_697dacf0ca2598.47242744.pdf', 'OS assignment.pdf', '2026-03-20 12:49:00', 10, '2026-01-31 12:49:12', 'active'),
(13, 55, 5, 'OS unit-2 assignmnet', NULL, 'ass_697dad1c3be425.66340829.pdf', 'OS assignment.pdf', '2026-03-19 12:49:00', 10, '2026-01-31 12:49:56', 'active'),
(14, 46, 6, 'DS unit-1 assignment', NULL, 'ass_697dadf4a25ba6.96849064.pdf', 'DS assignmnet.pdf', '2026-02-05 12:52:00', 10, '2026-01-31 12:53:32', 'active'),
(15, 46, 6, 'DS unit-2 assignment', NULL, 'ass_697dae253fbf00.00184095.pdf', 'DS assignmnet.pdf', '2026-02-07 12:54:00', 10, '2026-01-31 12:54:21', 'active'),
(16, 44, 12, 'unit-1 assignment', 'Basic structure and function of cells', 'ass_697db39bcd8e23.49619206.pdf', 'assignmnet.pdf', '2026-02-03 13:17:00', 10, '2026-01-31 13:17:39', 'active'),
(17, 43, 14, 'unit-1 assignment', 'Drug Structure concepts', 'ass_697db461135753.10897840.pdf', 'assignmnet.pdf', '2026-02-02 13:20:00', 10, '2026-01-31 13:20:57', 'active');

-- --------------------------------------------------------

--
-- Table structure for table `assignment_submissions`
--

CREATE TABLE `assignment_submissions` (
  `id` int(10) UNSIGNED NOT NULL,
  `assignment_id` int(10) UNSIGNED NOT NULL,
  `student_user_id` int(10) UNSIGNED NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `original_name` varchar(255) DEFAULT NULL,
  `submitted_at` datetime NOT NULL DEFAULT current_timestamp(),
  `marks_obtained` decimal(5,2) DEFAULT NULL,
  `feedback` text DEFAULT NULL,
  `status` enum('submitted','evaluated','late') DEFAULT 'submitted'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `assignment_submissions`
--

INSERT INTO `assignment_submissions` (`id`, `assignment_id`, `student_user_id`, `file_name`, `original_name`, `submitted_at`, `marks_obtained`, `feedback`, `status`) VALUES
(5, 5, 40, 'sub_6979beed584ed7.96147472.pdf', 'PHP unit-2 assignment.pdf', '2026-01-28 13:16:53', NULL, NULL, 'submitted'),
(7, 6, 40, 'sub_697a0590a063e2.72491776.pdf', 'PHP unit-2 assignment.pdf', '2026-01-28 18:18:16', 10.00, 'Good', 'evaluated'),
(8, 6, 39, 'sub_697cb554eb0a82.36513527.pdf', 'PHP unit-2 assignment.pdf', '2026-01-30 19:12:44', NULL, NULL, 'evaluated'),
(9, 13, 39, 'sub_697db621776b00.41275156.pdf', 'OS assignment.pdf', '2026-01-31 13:28:25', NULL, NULL, 'submitted'),
(10, 12, 39, 'sub_697db6c7c22131.19182001.pdf', 'OS assignment.pdf', '2026-01-31 13:31:11', NULL, NULL, 'submitted');

-- --------------------------------------------------------

--
-- Table structure for table `branches`
--

CREATE TABLE `branches` (
  `id` int(10) UNSIGNED NOT NULL,
  `course_id` int(10) UNSIGNED NOT NULL,
  `name` varchar(150) NOT NULL COMMENT 'e.g., Computer Science & Engineering',
  `code` varchar(50) NOT NULL COMMENT 'e.g., CSE',
  `description` text DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `branches`
--

INSERT INTO `branches` (`id`, `course_id`, `name`, `code`, `description`, `status`, `is_active`, `created_at`, `updated_at`) VALUES
(3, 1, 'DCS', 'D1', NULL, 'active', 1, '2026-01-27 18:44:20', '2026-01-27 18:44:20'),
(6, 5, 'Computer Science Engineering(CSE)', 'CSE1', 'Computer Science Engineering program', 'active', 1, '2026-01-28 11:26:30', '2026-01-28 11:26:30'),
(8, 3, 'AMPIC', 'A1CS', 'Master of Computer Application', 'active', 1, '2026-01-30 19:55:02', '2026-01-30 19:55:02'),
(11, 8, 'AMPIC', 'B1CS', 'Bachelor of Computer Application', 'active', 1, '2026-01-30 20:05:24', '2026-01-30 20:05:24'),
(12, 9, 'Management Studies', 'MS1', NULL, 'active', 1, '2026-01-31 11:15:29', '2026-01-31 11:15:29'),
(13, 10, 'Pharmacy', 'B1P', 'B.Pharm focuses on the study of drugs, medicines, their formulation, production, quality control, and safe use in healthcare.', 'active', 1, '2026-01-31 13:08:02', '2026-01-31 13:08:02');

-- --------------------------------------------------------

--
-- Table structure for table `calendar_events`
--

CREATE TABLE `calendar_events` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED DEFAULT NULL COMMENT 'NULL for global events',
  `title` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `start_date` date NOT NULL,
  `end_date` date DEFAULT NULL,
  `type` enum('assignment','planner','holiday','exam','custom') DEFAULT 'custom',
  `reference_type` varchar(50) DEFAULT NULL COMMENT 'e.g., assignment, topic',
  `reference_id` int(10) UNSIGNED DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `calendar_events`
--

INSERT INTO `calendar_events` (`id`, `user_id`, `title`, `description`, `start_date`, `end_date`, `type`, `reference_type`, `reference_id`, `created_at`) VALUES
(1, NULL, 'Internal Exam', 'Internal Exam', '2026-01-21', '2026-01-30', 'exam', NULL, NULL, '2026-01-27 11:45:30'),
(2, NULL, 'Republic Day', 'Holiday', '2026-01-26', '2026-01-29', 'holiday', NULL, NULL, '2026-01-27 11:46:11'),
(4, NULL, 'External Exam', 'External exam schedule', '2026-02-04', '2026-02-10', 'exam', NULL, NULL, '2026-01-31 12:23:25');

-- --------------------------------------------------------

--
-- Table structure for table `courses`
--

CREATE TABLE `courses` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(150) NOT NULL COMMENT 'e.g., Bachelor of Technology',
  `code` varchar(50) NOT NULL COMMENT 'e.g., B.TECH',
  `description` text DEFAULT NULL,
  `duration_years` int(10) UNSIGNED DEFAULT 4,
  `status` enum('active','inactive') DEFAULT 'active',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `courses`
--

INSERT INTO `courses` (`id`, `name`, `code`, `description`, `duration_years`, `status`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'B.Sc(CA&IT)', 'B1IT', NULL, 4, 'active', 1, '2025-12-02 21:17:40', '2026-01-27 18:42:38'),
(3, 'MCA', 'M1CS', NULL, 4, 'active', 1, '2026-01-27 13:30:58', '2026-01-27 18:44:50'),
(5, 'B. Tech', 'B1CSE', 'Computer Science Engineering(CSE)', 4, 'active', 1, '2026-01-28 11:23:53', '2026-01-28 11:23:53'),
(8, 'BCA', 'B1CS', 'Bachelor of Computer Application course', 4, 'active', 1, '2026-01-30 19:58:32', '2026-01-30 19:58:32'),
(9, 'MBA', 'MBA1', 'Master of Business Administration', 4, 'active', 1, '2026-01-30 20:02:01', '2026-01-30 20:02:01'),
(10, 'B.Pharm', 'B1Pharm', 'Bachelor of Pharmacy (B. Pharm)', 4, 'active', 1, '2026-01-31 13:06:20', '2026-01-31 13:06:20');

-- --------------------------------------------------------

--
-- Table structure for table `doubts`
--

CREATE TABLE `doubts` (
  `id` int(10) UNSIGNED NOT NULL,
  `student_user_id` int(10) UNSIGNED NOT NULL,
  `subject_id` int(10) UNSIGNED NOT NULL,
  `title` varchar(200) NOT NULL,
  `question` text NOT NULL,
  `status` enum('open','resolved','closed') DEFAULT 'open',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `resolved_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `doubts`
--

INSERT INTO `doubts` (`id`, `student_user_id`, `subject_id`, `title`, `question`, `status`, `created_at`, `resolved_at`) VALUES
(3, 40, 4, 'PHP Unit-1', 'What is difference between PHP and HTML?', 'resolved', '2026-01-28 13:14:54', '2026-01-28 13:36:26'),
(5, 39, 4, 'unit-2', 'how to use Function?', 'open', '2026-01-31 13:28:00', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `doubt_replies`
--

CREATE TABLE `doubt_replies` (
  `id` int(10) UNSIGNED NOT NULL,
  `doubt_id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL COMMENT 'Faculty or student reply',
  `message` text NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `doubt_replies`
--

INSERT INTO `doubt_replies` (`id`, `doubt_id`, `user_id`, `message`, `created_at`) VALUES
(1, 3, 45, 'HTML create static web pages, while PHP creates dynamic content', '2026-01-28 13:35:48');

-- --------------------------------------------------------

--
-- Table structure for table `faculty_profiles`
--

CREATE TABLE `faculty_profiles` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `employee_code` varchar(50) DEFAULT NULL,
  `designation` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `faculty_profiles`
--

INSERT INTO `faculty_profiles` (`id`, `user_id`, `employee_code`, `designation`, `created_at`, `updated_at`) VALUES
(19, 43, '1001', NULL, '2026-01-27 13:34:06', '2026-01-27 13:34:06'),
(20, 44, '1002', NULL, '2026-01-27 13:34:42', '2026-01-27 13:34:42'),
(21, 45, '1003', NULL, '2026-01-27 13:35:15', '2026-01-27 13:35:15'),
(22, 46, '1004', NULL, '2026-01-27 13:35:42', '2026-01-27 13:35:42'),
(25, 51, '1006', NULL, '2026-01-31 04:33:32', '2026-01-31 04:33:32'),
(26, 52, '1007', NULL, '2026-01-31 04:35:16', '2026-01-31 04:35:16'),
(27, 55, '1008', NULL, '2026-01-31 07:07:12', '2026-01-31 07:07:12');

-- --------------------------------------------------------

--
-- Table structure for table `faculty_subjects`
--

CREATE TABLE `faculty_subjects` (
  `id` int(10) UNSIGNED NOT NULL,
  `faculty_user_id` int(10) UNSIGNED NOT NULL,
  `subject_id` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `faculty_subjects`
--

INSERT INTO `faculty_subjects` (`id`, `faculty_user_id`, `subject_id`) VALUES
(7, 45, 4),
(8, 46, 6),
(12, 51, 10),
(13, 52, 11),
(14, 55, 5),
(15, 44, 12),
(16, 43, 14);

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(50) NOT NULL COMMENT 'admin, faculty, student',
  `description` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `name`, `description`, `created_at`, `updated_at`) VALUES
(1, 'admin', 'System administrator with full permissions', '2025-12-02 15:35:37', '2025-12-02 15:35:37'),
(2, 'faculty', 'Teaching staff / lecturers', '2025-12-02 15:35:37', '2025-12-02 15:35:37'),
(3, 'student', 'Learner / student', '2025-12-02 15:35:37', '2025-12-02 15:35:37');

-- --------------------------------------------------------

--
-- Table structure for table `semesters`
--

CREATE TABLE `semesters` (
  `id` int(10) UNSIGNED NOT NULL,
  `course_id` int(10) UNSIGNED NOT NULL,
  `branch_id` int(10) UNSIGNED DEFAULT NULL,
  `number` tinyint(3) UNSIGNED NOT NULL COMMENT '1..8',
  `name` varchar(100) DEFAULT NULL,
  `order_no` int(10) UNSIGNED DEFAULT 1,
  `status` enum('active','inactive') DEFAULT 'active',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `semesters`
--

INSERT INTO `semesters` (`id`, `course_id`, `branch_id`, `number`, `name`, `order_no`, `status`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 1, NULL, 1, 'Semester 1', 1, 'active', 1, '2025-12-02 21:18:59', '2026-01-26 18:48:55'),
(2, 1, NULL, 2, 'Semester 2', 1, 'active', 1, '2025-12-02 21:35:21', '2026-01-26 18:48:55'),
(9, 1, NULL, 4, 'Semester 4', 1, 'active', 1, '2026-01-27 18:47:43', '2026-01-27 18:47:43'),
(10, 1, NULL, 5, 'Semester 5', 1, 'active', 1, '2026-01-27 18:47:53', '2026-01-27 18:47:53'),
(11, 1, NULL, 6, 'Semester 6', 1, 'active', 1, '2026-01-27 18:47:57', '2026-01-27 18:47:57'),
(12, 5, NULL, 1, 'Semester 1', 1, 'active', 1, '2026-01-28 11:44:30', '2026-01-28 11:44:30'),
(13, 5, NULL, 2, 'Semester 2', 1, 'active', 1, '2026-01-28 11:45:58', '2026-01-28 11:45:58'),
(16, 8, NULL, 1, 'Semester 1', 1, 'active', 1, '2026-01-30 20:05:47', '2026-01-30 20:05:47'),
(17, 8, NULL, 2, 'Semester 2', 1, 'active', 1, '2026-01-30 20:05:56', '2026-01-30 20:05:56'),
(18, 8, NULL, 3, 'Semester 3', 1, 'active', 1, '2026-01-30 20:06:00', '2026-01-30 20:06:00'),
(19, 8, NULL, 4, 'Semester 4', 1, 'active', 1, '2026-01-30 20:07:58', '2026-01-30 20:07:58'),
(20, 9, NULL, 1, 'Semester 1', 1, 'active', 1, '2026-01-31 11:13:18', '2026-01-31 11:13:18'),
(21, 9, NULL, 2, 'Semester 2', 1, 'active', 1, '2026-01-31 11:13:24', '2026-01-31 11:13:24'),
(22, 3, NULL, 1, 'Semester 1', 1, 'active', 1, '2026-01-31 11:13:40', '2026-01-31 11:13:40'),
(23, 3, NULL, 2, 'Semester 2', 1, 'active', 1, '2026-01-31 11:13:45', '2026-01-31 11:13:45'),
(24, 3, NULL, 3, 'Semester 3', 1, 'active', 1, '2026-01-31 11:14:04', '2026-01-31 11:14:04'),
(25, 3, NULL, 4, 'Semester 4', 1, 'active', 1, '2026-01-31 11:14:06', '2026-01-31 11:14:06'),
(27, 10, NULL, 1, 'Semester 1', 1, 'active', 1, '2026-01-31 13:08:12', '2026-01-31 13:08:12'),
(28, 10, NULL, 2, 'Semester 2', 1, 'active', 1, '2026-01-31 13:08:16', '2026-01-31 13:08:16');

-- --------------------------------------------------------

--
-- Table structure for table `student_profiles`
--

CREATE TABLE `student_profiles` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `enrollment_no` varchar(50) NOT NULL,
  `course_id` int(10) UNSIGNED DEFAULT NULL,
  `branch_id` int(10) UNSIGNED DEFAULT NULL,
  `semester_id` int(10) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `student_profiles`
--

INSERT INTO `student_profiles` (`id`, `user_id`, `enrollment_no`, `course_id`, `branch_id`, `semester_id`, `created_at`, `updated_at`) VALUES
(4, 18, '12347', 1, 3, 10, '2025-12-24 08:13:48', '2026-01-31 06:26:13'),
(18, 37, '12352', 5, 6, 13, '2026-01-27 13:01:57', '2026-01-28 08:23:44'),
(20, 39, '12354', 1, 3, 10, '2026-01-27 13:03:58', '2026-01-31 07:52:24'),
(21, 40, '12355', 1, 3, 10, '2026-01-27 13:04:46', '2026-01-31 06:26:04'),
(25, 42, '1046', 5, 6, 12, '2026-01-27 13:21:56', '2026-01-31 06:25:45'),
(34, 48, '12356', 8, 11, 16, '2026-01-30 13:50:00', '2026-01-31 05:57:36'),
(35, 49, '12357', 9, 12, 20, '2026-01-30 13:51:13', '2026-01-31 05:46:23'),
(36, 50, '12358', 3, 8, 22, '2026-01-30 13:52:05', '2026-01-31 05:47:58'),
(46, 53, '12359', 1, 3, 10, '2026-01-31 06:44:13', '2026-01-31 06:46:35'),
(47, 54, '12360', 8, 11, 16, '2026-01-31 06:44:51', '2026-01-31 06:49:01'),
(52, 56, '12361', 10, 13, 28, '2026-01-31 07:30:39', '2026-01-31 07:49:15'),
(53, 57, '12362', 10, 13, 27, '2026-01-31 07:31:19', '2026-01-31 07:44:10');

-- --------------------------------------------------------

--
-- Table structure for table `student_topic_progress`
--

CREATE TABLE `student_topic_progress` (
  `id` int(10) UNSIGNED NOT NULL,
  `student_user_id` int(10) UNSIGNED NOT NULL,
  `topic_id` int(10) UNSIGNED NOT NULL,
  `target_date` date DEFAULT NULL,
  `status` enum('not_started','in_progress','completed') DEFAULT 'not_started',
  `strength` enum('weak','average','strong') DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `last_updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `student_topic_progress`
--

INSERT INTO `student_topic_progress` (`id`, `student_user_id`, `topic_id`, `target_date`, `status`, `strength`, `notes`, `last_updated_at`) VALUES
(7, 40, 20, '2026-01-29', 'completed', 'strong', NULL, '2026-01-28 18:23:00'),
(8, 40, 21, '2026-01-30', 'completed', 'average', NULL, '2026-01-28 18:23:12'),
(9, 40, 22, '2026-02-07', 'in_progress', 'average', NULL, '2026-01-28 18:23:22'),
(10, 18, 20, '2026-01-15', 'completed', 'strong', NULL, '2026-01-28 18:11:15'),
(11, 18, 21, '2026-01-30', 'completed', 'weak', NULL, '2026-01-28 18:11:05'),
(13, 18, 22, '2026-02-07', 'in_progress', 'average', NULL, '2026-01-28 18:11:26'),
(23, 39, 13, '2026-01-31', 'in_progress', 'strong', NULL, '2026-01-30 19:15:32');

-- --------------------------------------------------------

--
-- Table structure for table `study_materials`
--

CREATE TABLE `study_materials` (
  `id` int(10) UNSIGNED NOT NULL,
  `faculty_user_id` int(10) UNSIGNED NOT NULL,
  `subject_id` int(10) UNSIGNED NOT NULL,
  `title` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `file_name` varchar(255) NOT NULL COMMENT 'Server filename',
  `original_name` varchar(255) DEFAULT NULL,
  `file_type` varchar(50) DEFAULT NULL COMMENT 'MIME type',
  `file_size` int(10) UNSIGNED DEFAULT NULL COMMENT 'Size in bytes',
  `uploaded_at` datetime NOT NULL DEFAULT current_timestamp(),
  `status` enum('visible','hidden') DEFAULT 'visible'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `study_materials`
--

INSERT INTO `study_materials` (`id`, `faculty_user_id`, `subject_id`, `title`, `description`, `file_name`, `original_name`, `file_type`, `file_size`, `uploaded_at`, `status`) VALUES
(7, 45, 4, 'Introduction to PHP', 'Basic of PHP and server-side scripting', 'mat_6979b8020510d3.16501069.pdf', 'PHP Basic.pdf', 'application/pdf', 1581919, '2026-01-28 12:47:22', 'visible'),
(8, 45, 4, 'PHP function', 'Reusable blocks of code for specific tasks', 'mat_6979b88aa54428.68698657.pdf', 'PHP Function.pdf', 'application/pdf', 38073764, '2026-01-28 12:49:38', 'visible'),
(9, 52, 11, 'NT unit-1', NULL, 'mat_697d87f050da61.53990746.pdf', 'NT unit-1.pdf', 'application/pdf', 115578, '2026-01-31 10:11:20', 'visible'),
(10, 52, 11, 'NT unit-2', NULL, 'mat_697d8817e06181.56893378.pdf', 'NT unit-2.pdf', 'application/pdf', 115578, '2026-01-31 10:11:59', 'visible'),
(11, 51, 10, 'WD Unit1', 'All basic concept of HTML/CSS', 'mat_697da7f284b0f1.92825527.pdf', 'WD unit-1.pdf', 'application/pdf', 1581919, '2026-01-31 12:27:54', 'visible'),
(12, 51, 10, 'WD unit-2', 'JavaScript concept are include', 'mat_697da8401e4862.60467224.pdf', 'WD unit-2.pdf', 'application/pdf', 1581919, '2026-01-31 12:29:12', 'visible'),
(13, 55, 5, 'OS unit-1', 'Introduction to Operating System', 'mat_697dac7048cce5.13153688.pdf', 'OS unit-1.pdf', 'application/pdf', 1581919, '2026-01-31 12:47:04', 'visible'),
(14, 55, 5, 'OS unit-2', 'Process Management topic include', 'mat_697daca5e9d3d1.24390089.pdf', 'OS unit-2.pdf', 'application/pdf', 1581919, '2026-01-31 12:47:57', 'visible'),
(15, 46, 6, 'DS unit-1', 'Introduction to Data Structures 📘', 'mat_697dae5fcec594.60565477.pdf', 'DS unit-1.pdf', 'application/pdf', 1581919, '2026-01-31 12:55:19', 'visible'),
(16, 46, 6, 'DS unit-2', 'Algorithm Analysis (Time & Space Complexity)', 'mat_697dae92475403.95092832.pdf', 'DS unit-2.pdf', 'application/pdf', 1581919, '2026-01-31 12:56:10', 'visible'),
(17, 44, 12, 'Cell & Tissues', 'Basic structure and function of cells', 'mat_697db37b1048d3.95517311.pdf', 'unit-1.pdf', 'application/pdf', 1581919, '2026-01-31 13:17:07', 'visible'),
(18, 43, 14, 'unit-1', 'Drug Structure', 'mat_697db441432f84.72424648.pdf', 'unit-1.pdf', 'application/pdf', 1581919, '2026-01-31 13:20:25', 'visible');

-- --------------------------------------------------------

--
-- Table structure for table `subjects`
--

CREATE TABLE `subjects` (
  `id` int(10) UNSIGNED NOT NULL,
  `course_id` int(10) UNSIGNED NOT NULL,
  `branch_id` int(10) UNSIGNED NOT NULL,
  `semester_id` int(10) UNSIGNED NOT NULL,
  `name` varchar(200) NOT NULL,
  `code` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `credits` decimal(4,2) DEFAULT 4.00,
  `status` enum('active','inactive') DEFAULT 'active',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `subjects`
--

INSERT INTO `subjects` (`id`, `course_id`, `branch_id`, `semester_id`, `name`, `code`, `description`, `credits`, `status`, `is_active`, `created_at`, `updated_at`) VALUES
(4, 1, 3, 10, 'PHP', 'U1A1PHP', 'Used to dynamic applications', 10.00, 'active', 1, '2026-01-27 18:59:52', '2026-01-27 18:59:52'),
(5, 1, 3, 10, 'OS', 'U1A4OS', 'Operating System', 10.00, 'active', 1, '2026-01-27 19:03:07', '2026-01-27 19:03:07'),
(6, 5, 6, 12, 'Data Structure & Algorithms', 'B1A1DS', 'DS & Algorithms focuses on the fundamental concepts of organizing, storing and processing data.', 10.00, 'active', 1, '2026-01-28 11:51:01', '2026-01-28 11:51:01'),
(10, 8, 11, 16, 'Web Development', 'U12WD2', 'Website and Web application development', 10.00, 'active', 1, '2026-01-30 20:11:07', '2026-01-30 20:11:07'),
(11, 8, 11, 16, 'Networking & Cyber Security', 'U12CS2', 'Network system and security', 10.00, 'active', 1, '2026-01-30 20:12:15', '2026-01-30 20:12:15'),
(12, 10, 13, 27, 'Human Anatomy & Physiology', 'HA1P', 'Studies the structure and functions of the human body systems to understand how drugs affect normal and diseased conditions.', 10.00, 'active', 1, '2026-01-31 13:11:05', '2026-01-31 13:11:05'),
(14, 10, 13, 28, 'Pharmaceutical Chemistry', 'U11PC1', 'Focuses on the chemical structure, synthesis, properties, and analysis of drugs used for treatment and prevention of diseases.', 10.00, 'active', 1, '2026-01-31 13:12:31', '2026-01-31 13:12:31');

-- --------------------------------------------------------

--
-- Table structure for table `topics`
--

CREATE TABLE `topics` (
  `id` int(10) UNSIGNED NOT NULL,
  `subject_id` int(10) UNSIGNED NOT NULL,
  `title` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `order_no` int(10) UNSIGNED DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `topics`
--

INSERT INTO `topics` (`id`, `subject_id`, `title`, `description`, `order_no`, `created_at`) VALUES
(13, 5, 'Introduction to Operating System 🖥️', 'Basics of operating system, functions, and types.', 1, '2026-01-28 06:27:40'),
(14, 5, 'process Management 🔄', 'Creation, scheduling, and execution of processes.', 2, '2026-01-28 06:28:46'),
(15, 5, 'CPU Scheduling ⏱️', 'Techniques for allocating CPU time to processes.', 3, '2026-01-28 06:31:39'),
(16, 5, 'Deadlocks 🔒', 'Conditions, prevention, and handling of deadlocks.', 4, '2026-01-28 06:32:19'),
(17, 5, 'Memory Management 🧠', 'Allocation and management of primary memory.', 5, '2026-01-28 06:32:56'),
(18, 5, 'Input / Output Management 🔌', 'Handling communication between hardware and OS.', 6, '2026-01-28 06:33:49'),
(19, 5, 'Security & Protection 🛡️', 'Safeguarding system resources and data.', 7, '2026-01-28 06:35:37'),
(20, 4, 'Introduction to PHP 🐘', 'Basic of PHP and server-side Scripting', 1, '2026-01-28 06:59:36'),
(21, 4, 'PHP Syntax & Variables ✍️', 'Rules, variables, and data handing in PHP', 2, '2026-01-28 07:00:22'),
(22, 4, 'Data Types & Operators 🔢', 'Types of data and operators used in PHP', 3, '2026-01-28 07:01:29'),
(23, 4, 'Function 🧩', 'Reusable blocks of code for specific tasks', 4, '2026-01-28 07:02:12'),
(24, 4, 'Array 📦', 'Storing and managing multiple values', 5, '2026-01-28 07:02:45'),
(25, 4, 'Forms Handling 📝', 'Processing user input using GET and POST', 6, '2026-01-28 07:05:32'),
(26, 4, 'Database Connectivity 🗄️', 'Connecting PHP with MYSQL database', 7, '2026-01-28 07:06:22'),
(27, 4, 'Error Handling ⚠️', 'Detecting and managing runtime errors', 8, '2026-01-28 07:07:28'),
(28, 4, 'Object-Oriented PHP 🏗️', 'Classes, objects, and inheritance concepts', 9, '2026-01-28 07:08:19'),
(29, 10, 'HTML & CSS 🌐', 'Builds the structure and design of web pages', 1, '2026-01-30 14:44:19'),
(30, 10, 'JavaScript ⚡', 'Add interactivity and dynamic behavior to website', 2, '2026-01-30 14:45:22'),
(31, 10, 'PHP / ASP.NET', 'Develop server-side logic and dynamic web applications', 4, '2026-01-30 14:47:33'),
(32, 11, 'Advance Computer Networks 🌐', 'Covers routing, switching, and network design.', 1, '2026-01-31 05:50:07'),
(33, 11, 'Cyber Security 🛡️', 'Protects systems from digital attacks and threats.', 2, '2026-01-31 05:50:42'),
(34, 11, 'Cryptography & Network Security 🔑', 'Ensures secure communication.', 3, '2026-01-31 05:51:31'),
(35, 11, 'Ethical Hacking & Forensics 🧠', 'Identifies vulnerabilities and investigates cyber crimes.', 4, '2026-01-31 05:52:47'),
(40, 6, 'Introduction to Data Structures 📘', 'Explains data organization methods for efficient storage and processing.', 1, '2026-01-31 06:01:12'),
(41, 6, 'Algorithm Analysis (Time & Space Complexity) ⏱️', 'Evaluates algorithm efficiency using Big-O notation.', 2, '2026-01-31 06:01:42'),
(42, 6, 'Arrays 📦', 'Arrays \r\nStores elements in a fixed-size sequential structure.', 3, '2026-01-31 06:03:15'),
(43, 6, 'Strings 🔤', 'Strings \r\nHandles character data and string manipulation techniques.', 4, '2026-01-31 06:03:27'),
(44, 6, 'Linked Lists 🔗', 'Linked Lists 🔗\r\nStores data dynamically using nodes (singly, doubly, circular).', 5, '2026-01-31 06:04:00'),
(45, 6, 'Searching Techniques 🔍', 'Includes linear search and binary search for data retrieval.', 6, '2026-01-31 06:05:50'),
(52, 11, 'Cloud & Network Security ☁️🔐', 'Secures cloud-based infrastructures.', 5, '2026-01-31 06:12:53'),
(53, 10, 'DOM Manipulation 🧩', 'Accesses and modifies HTML elements using JavaScript.', 3, '2026-01-31 06:23:17'),
(54, 10, 'Forms & Validation 📝', 'Collects user input and validates data', 5, '2026-01-31 06:23:51');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(10) UNSIGNED NOT NULL,
  `role_id` int(10) UNSIGNED NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL COMMENT 'BCRYPT hashed password',
  `full_name` varchar(150) NOT NULL,
  `first_name` varchar(100) DEFAULT NULL,
  `last_name` varchar(100) DEFAULT NULL,
  `mobile` varchar(20) DEFAULT NULL,
  `gender` enum('male','female','other') DEFAULT NULL,
  `dob` date DEFAULT NULL,
  `address` text DEFAULT NULL,
  `status` enum('active','inactive','pending') DEFAULT 'pending',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `last_login_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `role_id`, `username`, `email`, `password_hash`, `full_name`, `first_name`, `last_name`, `mobile`, `gender`, `dob`, `address`, `status`, `is_active`, `last_login_at`, `created_at`, `updated_at`) VALUES
(1, 1, 'admin', 'admin@college.edu', '$2a$12$MZPtsMTvoH7YjGHXAOiwxudXEhvT9wz1IomRV6d3v/aarv/n45QoO', 'Admin User', 'Admin', 'User', '9990454556', 'male', '2026-01-31', 'Mehsana, Gujarat', 'active', 1, '2026-02-02 13:45:10', '2025-12-02 15:35:37', '2026-02-02 13:45:10'),
(18, 3, '12347', 'jency@gmail.com', '$2y$10$aqpk.lk62JzhiARHiob1RetGFam1Zh1R16zLebVZZVdNlchFEN/AO', 'jency patel', 'jency', 'patel', '9099787862', 'female', '1999-02-01', 'Patan, Gujarat', 'active', 1, '2026-01-28 18:10:35', '2025-12-24 13:43:48', '2026-01-28 18:10:35'),
(37, 3, '12352', 'darsh@gmail.com', '$2y$10$caYWgg9VFMPYbiNypeKr1.Zf2weZYiBnbOEDjA0W8ZbZGmMxPlhGu', 'Darsh Patel', 'Darsh', 'Patel', '9925454870', NULL, NULL, 'Ahmedabad, Gujarat', 'active', 1, NULL, '2026-01-27 18:31:57', '2026-01-31 12:13:00'),
(39, 3, '12354', 'parth@gmail.com', '$2y$10$rZgUt5BTTbEUvp9qxFX39eE8g5nSPZTjndTk2VdswdhQpcXVQTK3u', 'parth prajapati', 'parth', 'prajapati', '8987767654', 'male', NULL, 'Unjha, Gujarat', 'active', 1, '2026-01-31 13:26:20', '2026-01-27 18:33:58', '2026-01-31 13:26:20'),
(40, 3, '12355', 'naiya@gmail.com', '$2y$10$HijIe3Fh4PSahrx70PwlcOwOgo6PGZ.2Qv3D4E/lQyBNO246vGWAi', 'Naiya Patel', 'Naiya', 'Patel', '9099343421', 'female', '2005-01-28', 'Patan, Gujarat', 'active', 1, '2026-02-02 13:42:09', '2026-01-27 18:34:46', '2026-02-02 13:42:09'),
(42, 3, '12349', 'hensi@gmail.com', '$2y$10$XTM141sEo0.3QSw88.1q9OSNoPPK3c2AcpbYN8zAwEQnkrMf63LPS', 'Hensi Patel', 'Hensi', 'Patel', '6352567876', NULL, '2004-12-03', 'Ahmedabad, Gujarat', 'active', 1, NULL, '2026-01-27 18:51:56', '2026-01-28 18:09:02'),
(43, 2, '1001', 'harsha@gmail.com', '$2y$10$qlbBNc4P.0D7iKQlNrnCZOWecx1WWt82egP/pG4.ag/k5n1Ejlwdy', 'Harsha modi', 'Harsha', 'modi', NULL, NULL, NULL, NULL, 'active', 1, '2026-01-31 13:18:00', '2026-01-27 19:04:06', '2026-01-31 13:18:00'),
(44, 2, '1002', 'harsh@gmail.com', '$2y$10$3X9pU/B3oo.O6C1PFCA17..AQOmkbUToqBNsJQ8gxrYaYB.IZ7SNm', 'Harsh Patel', 'Harsh', 'Patel', NULL, NULL, NULL, NULL, 'active', 1, '2026-01-31 12:57:29', '2026-01-27 19:04:42', '2026-01-31 12:57:29'),
(45, 2, '1003', 'nikita@gmail.com', '$2y$10$wOY.rTN/rD3/mtUzvLdxbeHH9hkXAob1XmQFvSuy1YAUHy78qsfSq', 'Nikita Modi', 'Nikita', 'Modi', NULL, 'female', '1994-11-08', 'Ahmedabad, Gujarat', 'active', 1, '2026-02-02 13:48:09', '2026-01-27 19:05:15', '2026-02-02 13:48:09'),
(46, 2, '1004', 'pravina@gmail.com', '$2y$10$p7oturWsGo2f/fXvVqsHAehdHMty9k7CpZU1AF4WbSKKXdGG9M3uG', 'Pravina Patel', 'Pravina', 'Patel', NULL, NULL, NULL, NULL, 'active', 1, '2026-01-31 12:50:43', '2026-01-27 19:05:42', '2026-01-31 12:50:43'),
(48, 3, '12356', 'Preya@gmail.com', '$2y$10$0sgHwfDM4GX1.0cO0Jvi9uhREuPBlHwstuEHNRssk5oVl87eesGVW', 'Preya Patel', 'Preya', 'Patel', '9944545454', 'female', '2005-07-30', 'Unjha, Gujarat', 'active', 1, NULL, '2026-01-30 19:20:00', '2026-01-30 20:30:39'),
(49, 3, '12357', 'hetvi@gmail.com', '$2y$10$wRAIBygmcy5ibHhqjhmD7.IeV9m89daERrK53IcSEPKIP8eNQpc8G', 'Hetvi Panchal', 'Hetvi', 'Panchal', '8989565645', 'female', '2006-01-09', 'Patan, Gujarat', 'active', 1, NULL, '2026-01-30 19:21:13', '2026-01-31 11:16:23'),
(50, 3, '12358', 'het@gmail.com', '$2y$10$jnfXDkPwL2eiz7gYnbJmQOq5YvAN5MhiOQdpRrOjL71dfVnYwgV9S', 'Het Dave', 'Het', 'Dave', '6767454530', 'male', NULL, 'Ahmedabad, Gujarat', 'active', 1, NULL, '2026-01-30 19:22:05', '2026-01-31 11:17:58'),
(51, 2, '1006', 'ravi@gmail.com', '$2y$10$hqUUUwjSpqX.THXToYNQZeqw.Z/4xG6VPQJkKJUt/oVWopQELEiKC', 'Ravi Patel', 'Ravi', 'Patel', NULL, NULL, NULL, NULL, 'active', 1, '2026-01-31 12:24:16', '2026-01-31 10:03:32', '2026-01-31 12:24:16'),
(52, 2, '1007', 'jay@gmail.com', '$2y$10$y/0muUpcR6LrnYIOmBnNPOZhlLeVFTIA3tevBAtxExTRZ9pX7nCsW', 'Jay Dave', 'Jay', 'Dave', NULL, NULL, NULL, NULL, 'active', 1, '2026-01-31 12:20:38', '2026-01-31 10:05:16', '2026-01-31 12:20:38'),
(53, 3, '12359', 'diya@gmail.com', '$2y$10$mPMN0uJkjtdfAD02W.dFqeG2uRm9dKChqFZutE0s2pessIM/nqPh.', 'Diya Patel', 'Diya', 'Patel', '6755567890', NULL, NULL, 'Surat, Gujarat', 'active', 1, NULL, '2026-01-31 12:14:13', '2026-01-31 12:16:35'),
(54, 3, '12360', 'mudra@gmail.com', '$2y$10$goeo2ECec/ijLip89OJRJeBCoKZ/JH8HMQYcUosWSih54T0E6X4RC', 'Mudra Bhanvadiya', 'Mudra', 'Bhanvadiya', '9978678510', 'female', NULL, 'Unjha, Gujarat', 'active', 1, NULL, '2026-01-31 12:14:51', '2026-01-31 12:18:35'),
(55, 2, '1008', 'amit@gmail.com', '$2y$10$s5Bd.5TeJtJE1rm0eGglA.XygNbGqDnJoMJjXVgfgX2gYTVPZ3IDm', 'Amit Prajapati', 'Amit', 'Prajapati', NULL, NULL, NULL, NULL, 'active', 1, '2026-01-31 12:44:17', '2026-01-31 12:37:12', '2026-01-31 12:44:17'),
(56, 3, '12361', 'dolly@gmail.com', '$2y$10$MkP5jihrzZ1xW2XYkX3sE.eR.bYkBjBYOfLI0ED0vWowgBqTL1Vum', 'Dolly Patel', 'Dolly', 'Patel', '9989076658', 'female', '2005-06-30', 'Surat, Gujarat', 'active', 1, NULL, '2026-01-31 13:00:39', '2026-01-31 13:19:15'),
(57, 3, '12362', 'prexa@gmail.com', '$2y$10$E67t062YQ5kRQ1PkA3lfoe9PtckNa94tBIwtKxi2os2PdvW/RLzc2', 'Prexa Suthar', 'Prexa', 'Suthar', '9045454520', 'female', '2004-01-30', 'Patan, Gujarat', 'active', 1, NULL, '2026-01-31 13:01:19', '2026-01-31 13:02:48');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `assignments`
--
ALTER TABLE `assignments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_faculty_user_id` (`faculty_user_id`),
  ADD KEY `idx_subject_id` (`subject_id`),
  ADD KEY `idx_due_date` (`due_date`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `assignment_submissions`
--
ALTER TABLE `assignment_submissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_assign_student` (`assignment_id`,`student_user_id`),
  ADD KEY `idx_assignment_id` (`assignment_id`),
  ADD KEY `idx_student_user_id` (`student_user_id`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `branches`
--
ALTER TABLE `branches`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_branch_course_code` (`course_id`,`code`),
  ADD KEY `idx_course_id` (`course_id`),
  ADD KEY `idx_code` (`code`);

--
-- Indexes for table `calendar_events`
--
ALTER TABLE `calendar_events`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_user_ref` (`user_id`,`reference_type`,`reference_id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_start_date` (`start_date`),
  ADD KEY `idx_type` (`type`);

--
-- Indexes for table `courses`
--
ALTER TABLE `courses`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`),
  ADD KEY `idx_code` (`code`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `doubts`
--
ALTER TABLE `doubts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_student_user_id` (`student_user_id`),
  ADD KEY `idx_subject_id` (`subject_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indexes for table `doubt_replies`
--
ALTER TABLE `doubt_replies`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_doubt_id` (`doubt_id`),
  ADD KEY `idx_user_id` (`user_id`);

--
-- Indexes for table `faculty_profiles`
--
ALTER TABLE `faculty_profiles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`),
  ADD KEY `idx_user_id` (`user_id`);

--
-- Indexes for table `faculty_subjects`
--
ALTER TABLE `faculty_subjects`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_faculty_user_id` (`faculty_user_id`),
  ADD KEY `idx_subject_id` (`subject_id`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `semesters`
--
ALTER TABLE `semesters`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_semester_course_num` (`course_id`,`number`),
  ADD KEY `idx_course_id` (`course_id`),
  ADD KEY `idx_branch_id` (`branch_id`);

--
-- Indexes for table `student_profiles`
--
ALTER TABLE `student_profiles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`),
  ADD UNIQUE KEY `enrollment_no` (`enrollment_no`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_enrollment_no` (`enrollment_no`);

--
-- Indexes for table `student_topic_progress`
--
ALTER TABLE `student_topic_progress`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_student_topic` (`student_user_id`,`topic_id`),
  ADD KEY `idx_student_user_id` (`student_user_id`),
  ADD KEY `idx_topic_id` (`topic_id`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `study_materials`
--
ALTER TABLE `study_materials`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_faculty_user_id` (`faculty_user_id`),
  ADD KEY `idx_subject_id` (`subject_id`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `subjects`
--
ALTER TABLE `subjects`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`),
  ADD KEY `idx_course_id` (`course_id`),
  ADD KEY `idx_branch_id` (`branch_id`),
  ADD KEY `idx_semester_id` (`semester_id`),
  ADD KEY `idx_code` (`code`);

--
-- Indexes for table `topics`
--
ALTER TABLE `topics`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_subject_id` (`subject_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_username` (`username`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_role_id` (`role_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `assignments`
--
ALTER TABLE `assignments`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `assignment_submissions`
--
ALTER TABLE `assignment_submissions`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `branches`
--
ALTER TABLE `branches`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `calendar_events`
--
ALTER TABLE `calendar_events`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `courses`
--
ALTER TABLE `courses`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `doubts`
--
ALTER TABLE `doubts`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `doubt_replies`
--
ALTER TABLE `doubt_replies`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `faculty_profiles`
--
ALTER TABLE `faculty_profiles`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `faculty_subjects`
--
ALTER TABLE `faculty_subjects`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `semesters`
--
ALTER TABLE `semesters`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `student_profiles`
--
ALTER TABLE `student_profiles`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=60;

--
-- AUTO_INCREMENT for table `student_topic_progress`
--
ALTER TABLE `student_topic_progress`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `study_materials`
--
ALTER TABLE `study_materials`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `subjects`
--
ALTER TABLE `subjects`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `topics`
--
ALTER TABLE `topics`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=55;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=58;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `assignments`
--
ALTER TABLE `assignments`
  ADD CONSTRAINT `fk_assign_faculty` FOREIGN KEY (`faculty_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_assign_subject` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `assignment_submissions`
--
ALTER TABLE `assignment_submissions`
  ADD CONSTRAINT `fk_sub_assignment` FOREIGN KEY (`assignment_id`) REFERENCES `assignments` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_sub_student` FOREIGN KEY (`student_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `branches`
--
ALTER TABLE `branches`
  ADD CONSTRAINT `fk_branch_course` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `calendar_events`
--
ALTER TABLE `calendar_events`
  ADD CONSTRAINT `fk_event_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `doubts`
--
ALTER TABLE `doubts`
  ADD CONSTRAINT `fk_doubt_student` FOREIGN KEY (`student_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_doubt_subject` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `doubt_replies`
--
ALTER TABLE `doubt_replies`
  ADD CONSTRAINT `fk_reply_doubt` FOREIGN KEY (`doubt_id`) REFERENCES `doubts` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_reply_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `faculty_profiles`
--
ALTER TABLE `faculty_profiles`
  ADD CONSTRAINT `fk_faculty_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `faculty_subjects`
--
ALTER TABLE `faculty_subjects`
  ADD CONSTRAINT `fk_facsub_faculty` FOREIGN KEY (`faculty_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_facsub_subject` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `semesters`
--
ALTER TABLE `semesters`
  ADD CONSTRAINT `fk_sem_branch` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_sem_course` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `student_profiles`
--
ALTER TABLE `student_profiles`
  ADD CONSTRAINT `fk_student_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `student_topic_progress`
--
ALTER TABLE `student_topic_progress`
  ADD CONSTRAINT `fk_stu_topic_topic` FOREIGN KEY (`topic_id`) REFERENCES `topics` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_stu_topic_user` FOREIGN KEY (`student_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `study_materials`
--
ALTER TABLE `study_materials`
  ADD CONSTRAINT `fk_material_faculty` FOREIGN KEY (`faculty_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_material_subject` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `subjects`
--
ALTER TABLE `subjects`
  ADD CONSTRAINT `fk_subject_branch` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_subject_course` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_subject_semester` FOREIGN KEY (`semester_id`) REFERENCES `semesters` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `topics`
--
ALTER TABLE `topics`
  ADD CONSTRAINT `fk_topic_subject` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `fk_users_role` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
