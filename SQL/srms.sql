-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 22, 2024 at 06:21 AM
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
-- Database: `srms`
--

-- --------------------------------------------------------

--
-- Table structure for table `admincreds`
--

CREATE TABLE `admincreds` (
  `id` int(11) NOT NULL,
  `user_id` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admincreds`
--

INSERT INTO `admincreds` (`id`, `user_id`, `password`) VALUES
(1, 'varunjoshi222@nhitm.ac.in', 'admin@123');

-- --------------------------------------------------------

--
-- Table structure for table `batch`
--

CREATE TABLE `batch` (
  `id` int(11) NOT NULL,
  `Batch` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `batch`
--

INSERT INTO `batch` (`id`, `Batch`) VALUES
(1, 'SE'),
(2, 'TE');

-- --------------------------------------------------------

--
-- Table structure for table `department`
--

CREATE TABLE `department` (
  `id` int(11) NOT NULL,
  `Department` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `department`
--

INSERT INTO `department` (`id`, `Department`) VALUES
(1, 'CSD'),
(2, 'AIDS'),
(3, 'MTRX'),
(4, 'COMPS'),
(5, 'CIVIL'),
(6, 'MECH');

-- --------------------------------------------------------

--
-- Table structure for table `results`
--

CREATE TABLE `results` (
  `id` int(11) NOT NULL,
  `ien_number` varchar(20) DEFAULT NULL,
  `subject` varchar(100) DEFAULT NULL,
  `TW_marks` int(11) DEFAULT NULL,
  `PR_marks` int(11) DEFAULT NULL,
  `ESE_marks` int(11) DEFAULT NULL,
  `year` varchar(10) DEFAULT NULL,
  `batch` varchar(10) DEFAULT NULL,
  `semester` varchar(10) DEFAULT NULL,
  `department` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `results`
--

INSERT INTO `results` (`id`, `ien_number`, `subject`, `TW_marks`, `PR_marks`, `ESE_marks`, `year`, `batch`, `semester`, `department`) VALUES
(1, '12222007', 'Software_Engineering', 20, 20, 70, '2024', 'TE', 'Sem 5', 'COMPS'),
(2, '12222007', 'Theoretical_Computer_Science', 25, NULL, 78, '2024', 'TE', 'Sem 5', 'COMPS'),
(3, '12222005', 'Software_Engineering', 25, 25, 70, '2024', 'TE', 'Sem 5', 'COMPS'),
(4, '12222005', 'Theoretical_Computer_Science', 20, NULL, 70, '2024', 'TE', 'Sem 5', 'COMPS'),
(9, '12222007', 'Software_Engineering_Lab', NULL, 25, 70, '2024', 'TE', 'Sem 5', 'COMPS');

-- --------------------------------------------------------

--
-- Table structure for table `semester`
--

CREATE TABLE `semester` (
  `id` int(11) NOT NULL,
  `Semester` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `semester`
--

INSERT INTO `semester` (`id`, `Semester`) VALUES
(1, 'Sem 3'),
(2, 'Sem 4'),
(3, 'Sem 5'),
(4, 'Sem 6');

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `id` int(11) NOT NULL,
  `ien_number` int(11) NOT NULL,
  `first_name` varchar(255) NOT NULL,
  `last_name` varchar(255) NOT NULL,
  `date_of_birth` date NOT NULL,
  `gender` enum('Male','Female','Other') NOT NULL,
  `email` varchar(255) NOT NULL,
  `address` varchar(255) NOT NULL,
  `student_number` varchar(20) NOT NULL,
  `year` int(11) NOT NULL,
  `department` varchar(255) NOT NULL,
  `batch` varchar(255) NOT NULL,
  `semester` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`id`, `ien_number`, `first_name`, `last_name`, `date_of_birth`, `gender`, `email`, `address`, `student_number`, `year`, `department`, `batch`, `semester`) VALUES
(1, 12222007, 'Varun', 'Joshi', '2004-10-31', 'Male', 'varunjoshi311004@gmail.com', 'D-306 Shree Chamunda Garden 90 Ft Rd ,Thakurli (E.)', '7715881457', 2024, 'COMPS', 'TE', 'Sem 5'),
(2, 12222004, 'Jay', 'Makwana', '2004-06-22', 'Male', 'varunjoshi311004@gmail.com', 'as,dhahsbakjshkasasd', '9831654755', 2024, 'COMPS', 'SE', 'Sem 3'),
(3, 12222005, 'Vedant', 'Navthale', '2004-11-15', 'Male', 'vedantnavthale222@nhitm.ac.in', 'asdkabsifbkjas.,dkoashd', '9565687546', 2024, 'COMPS', 'TE', 'Sem 5'),
(10, 12222006, 'Rohit', 'Joshi', '2005-07-13', 'Male', 'rohitjoshi222@nhitm.ac.in', 'asdlkjasn.,ms.nd', '9546853541', 2025, 'AIDS', 'TE', 'Sem 5');

-- --------------------------------------------------------

--
-- Table structure for table `subjects`
--

CREATE TABLE `subjects` (
  `id` int(11) NOT NULL,
  `Subject` varchar(255) NOT NULL,
  `tw_checked` varchar(255) NOT NULL,
  `pr_checked` varchar(255) NOT NULL,
  `ese_checked` varchar(255) NOT NULL,
  `Batch` varchar(255) NOT NULL,
  `Semester` varchar(255) NOT NULL,
  `Department` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `subjects`
--

INSERT INTO `subjects` (`id`, `Subject`, `tw_checked`, `pr_checked`, `ese_checked`, `Batch`, `Semester`, `Department`) VALUES
(1, 'Software Engineering', '1', '1', '1', 'TE', 'Sem 5', 'COMPS'),
(2, 'Theoretical Computer Science', '1', '0', '1', 'TE', 'Sem 5', 'COMPS'),
(3, 'Software Engineering Lab', '0', '1', '1', 'TE', 'Sem 5', 'COMPS'),
(4, 'Computer Network', '', '', '', 'TE', 'Sem 5', 'COMPS'),
(5, 'Internet Programming', '', '', '', 'TE', 'Sem 5', 'COMPS'),
(6, 'Data Warehousing & Mining', '', '', '', 'TE', 'Sem 5', 'COMPS'),
(7, 'Professional Comm. & Ethics II', '', '', '', 'TE', 'Sem 5', 'COMPS'),
(8, 'Computer Graphics', '', '', '', 'SE', 'Sem 3', 'COMPS'),
(9, 'Data Structure', '', '', '', 'SE', 'Sem 3', 'AIDS'),
(10, 'Data Structure', '', '', '', 'SE', 'Sem 3', 'COMPS'),
(11, 'Engineering Mathematics - III', '', '', '', 'SE', 'Sem 3', 'COMPS'),
(12, 'Discrete Structures and Graph Theory', '', '', '', 'SE', 'Sem 3', 'COMPS'),
(13, 'Digital Logic and Computer Architecture', '', '', '', 'SE', 'Sem 3', 'COMPS'),
(14, 'Data Structure Lab', '', '', '', 'SE', 'Sem 3', 'COMPS'),
(15, 'Digital Logic and Computer Architecture Lab', '', '', '', 'SE', 'Sem 3', 'COMPS'),
(16, 'Computer Graphics Lab', '', '', '', 'SE', 'Sem 3', 'COMPS'),
(17, 'Skill base Lab course Object Oriented Programming with Java', '', '', '', 'SE', 'Sem 3', 'COMPS'),
(18, 'Mini Project - 1A', '', '', '', 'SE', 'Sem 3', 'COMPS'),
(19, 'Analysis of Algorithm', '', '', '', 'SE', 'Sem 4', 'COMPS'),
(20, 'Engineering Mathematics - IV', '', '', '', 'SE', 'Sem 4', 'COMPS'),
(21, 'Database Management System', '', '', '', 'SE', 'Sem 4', 'COMPS'),
(22, 'Operating System', '', '', '', 'SE', 'Sem 4', 'COMPS'),
(23, 'Microprocessor', '', '', '', 'SE', 'Sem 4', 'COMPS'),
(24, 'Analysis of Algorithm Lab', '', '', '', 'SE', 'Sem 4', 'COMPS'),
(25, 'Database Management System Lab', '', '', '', 'SE', 'Sem 4', 'COMPS'),
(26, 'Operating System Lab', '', '', '', 'SE', 'Sem 4', 'COMPS'),
(27, 'Microprocessor Lab', '', '', '', 'SE', 'Sem 4', 'COMPS'),
(28, 'Skill Base Lab Course Python Programming', '', '', '', 'SE', 'Sem 4', 'COMPS'),
(29, 'Mini Project - 1B', '', '', '', 'SE', 'Sem 4', 'COMPS'),
(30, 'Computer Network Lab', '', '', '', 'TE', 'Sem 5', 'COMPS'),
(31, 'Data Warehousing & Mining Lab', '', '', '', 'TE', 'Sem 5', 'COMPS'),
(32, 'Mini Project : 2A', '', '', '', 'TE', 'Sem 5', 'COMPS'),
(33, 'System Programming & Compiler Construction', '', '', '', 'TE', 'Sem 6', 'COMPS'),
(34, 'Cryptography & System Security', '', '', '', 'TE', 'Sem 6', 'COMPS'),
(35, 'Mobile Computing', '', '', '', 'TE', 'Sem 6', 'COMPS'),
(36, 'Artificial Intelligence', '', '', '', 'TE', 'Sem 6', 'COMPS'),
(37, 'Quantitative Analysis', '', '', '', 'TE', 'Sem 6', 'COMPS'),
(38, 'Internet of Things', '', '', '', 'TE', 'Sem 6', 'COMPS'),
(39, 'System Programming & Compiler Construction Lab', '', '', '', 'TE', 'Sem 6', 'COMPS'),
(40, 'Cryptography & System Security Lab ', '', '', '', 'TE', 'Sem 6', 'COMPS'),
(41, 'Mobile Computing Lab', '', '', '', 'TE', 'Sem 6', 'COMPS'),
(42, 'Artificial Intelligence Lab', '', '', '', 'TE', 'Sem 6', 'COMPS'),
(43, 'Skill base Lab Course: Cloud Computing', '', '', '', 'TE', 'Sem 6', 'COMPS'),
(52, 'Mini Project : 2B', '1', '1', '0', 'TE', 'Sem 6', 'COMPS');

-- --------------------------------------------------------

--
-- Table structure for table `year`
--

CREATE TABLE `year` (
  `id` int(11) NOT NULL,
  `Year` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `year`
--

INSERT INTO `year` (`id`, `Year`) VALUES
(1, 2020),
(2, 2021),
(3, 2022),
(4, 2023),
(5, 2024),
(6, 2025);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admincreds`
--
ALTER TABLE `admincreds`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `batch`
--
ALTER TABLE `batch`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `department`
--
ALTER TABLE `department`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `results`
--
ALTER TABLE `results`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_student_subject` (`ien_number`,`subject`,`year`,`semester`,`batch`,`department`);

--
-- Indexes for table `semester`
--
ALTER TABLE `semester`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `subjects`
--
ALTER TABLE `subjects`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `year`
--
ALTER TABLE `year`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admincreds`
--
ALTER TABLE `admincreds`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `batch`
--
ALTER TABLE `batch`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `department`
--
ALTER TABLE `department`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `results`
--
ALTER TABLE `results`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `semester`
--
ALTER TABLE `semester`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `subjects`
--
ALTER TABLE `subjects`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=53;

--
-- AUTO_INCREMENT for table `year`
--
ALTER TABLE `year`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
