-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 17, 2026 at 12:25 AM
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
-- Database: `geared`
--

-- --------------------------------------------------------

--
-- Table structure for table `borrowing`
--

CREATE TABLE `borrowing` (
  `borrow_id` varchar(5) NOT NULL,
  `item_id` varchar(5) NOT NULL,
  `borrower_id` varchar(15) NOT NULL,
  `borrow_date` date NOT NULL,
  `due_date` date DEFAULT NULL,
  `return_date` date DEFAULT NULL,
  `status` varchar(20) DEFAULT 'borrowed',
  `fine_amount` int(11) DEFAULT 0,
  `fine_note` varchar(200) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `borrowing`
--

INSERT INTO `borrowing` (`borrow_id`, `item_id`, `borrower_id`, `borrow_date`, `due_date`, `return_date`, `status`, `fine_amount`, `fine_note`) VALUES
('B17f3', 'I6c08', 'U6a7f9634327af', '2026-08-17', NULL, '2026-08-17', 'returned', 150, 'Put a scratch on the fan body.'),
('B39c2', 'I9997', 'U1658fc', '2026-08-17', NULL, '2026-08-17', 'returned', 0, NULL),
('B6388', 'I6c08', 'U6a7f9634327af', '2026-08-17', NULL, NULL, 'borrowed', 0, NULL),
('B8d5c', 'I9997', 'U1658fc', '2026-08-15', NULL, '2026-08-17', 'returned', 0, NULL),
('Ba811', 'I6c08', 'U6a7f9634327af', '2026-08-17', NULL, NULL, 'borrowed', 0, NULL),
('Bd562', 'I9997', 'U1658fc', '2026-08-15', NULL, '2026-08-17', 'returned', 0, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `borrow_request`
--

CREATE TABLE `borrow_request` (
  `request_id` varchar(5) NOT NULL,
  `item_id` varchar(5) NOT NULL,
  `requester_id` varchar(15) NOT NULL,
  `request_date` date NOT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `status` varchar(20) DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `borrow_request`
--

INSERT INTO `borrow_request` (`request_id`, `item_id`, `requester_id`, `request_date`, `start_date`, `end_date`, `status`) VALUES
('R0a3b', 'I9997', 'U1658fc', '2026-08-17', NULL, NULL, 'borrowed'),
('R2fad', 'I9997', 'U1658fc', '2026-08-15', '2026-08-08', '2026-08-15', 'borrowed'),
('R34bf', 'I6c08', 'U6a7f9634327af', '2026-08-15', NULL, NULL, 'borrowed'),
('R5226', 'I6c08', 'U6a7f9634327af', '2026-08-17', NULL, NULL, 'borrowed'),
('Ra69c', 'I6c08', 'U6a7f9634327af', '2026-08-17', NULL, NULL, 'borrowed'),
('Re2bc', 'I9997', 'U1658fc', '2026-08-15', NULL, NULL, 'borrowed');

-- --------------------------------------------------------

--
-- Table structure for table `image`
--

CREATE TABLE `image` (
  `image_id` varchar(5) NOT NULL,
  `item_id` varchar(5) NOT NULL,
  `image_path` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `image`
--

INSERT INTO `image` (`image_id`, `item_id`, `image_path`) VALUES
('IM9a6', 'I6c08', 'uploads/I6c08_6a806e34574e8.jpeg'),
('IMa37', 'I9997', 'uploads/I9997_6a8073c817df0.jpeg'),
('IMe4f', 'Ifa5c', 'uploads/Ifa5c_6a8237a4c0454.png');

-- --------------------------------------------------------

--
-- Table structure for table `item`
--

CREATE TABLE `item` (
  `item_id` varchar(5) NOT NULL,
  `item_title` varchar(60) DEFAULT NULL,
  `user_id` varchar(15) NOT NULL,
  `price` int(11) DEFAULT NULL,
  `availability` varchar(3) DEFAULT NULL,
  `description` varchar(100) DEFAULT NULL,
  `date_created` date DEFAULT NULL,
  `category` varchar(30) DEFAULT NULL,
  `archived` varchar(3) DEFAULT 'no'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `item`
--

INSERT INTO `item` (`item_id`, `item_title`, `user_id`, `price`, `availability`, `description`, `date_created`, `category`, `archived`) VALUES
('I6c08', 'Rechargeable Fan', 'U1658fc', 30, 'no', 'Battery: 3000mAh Power Rating: 7.5W Input Interface: TYPE-C Condition: Used', '2026-08-15', 'Electronics', 'no'),
('I9997', 'Anker Zolo Power Bank', 'U6a7f9634327af', 50, 'yes', 'Interface: Type-C Power Capacity: 10000mAh Output Power: 30W Built-in Type-C Cable Condition: New', '2026-08-15', 'Electronics', 'no'),
('Ifa5c', 'SanDisk 1TB Pen Drive', 'U66afff', 40, 'yes', 'The SanDisk Extreme Fit USB-C Flash Drive', '2026-08-17', 'Electronics', 'no');

-- --------------------------------------------------------

--
-- Table structure for table `message`
--

CREATE TABLE `message` (
  `message_id` varchar(6) NOT NULL,
  `sender_id` varchar(15) NOT NULL,
  `receiver_id` varchar(15) NOT NULL,
  `item_id` varchar(5) DEFAULT NULL,
  `content` varchar(500) NOT NULL,
  `sent_date` datetime NOT NULL,
  `is_read` varchar(3) DEFAULT 'no'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `message`
--

INSERT INTO `message` (`message_id`, `sender_id`, `receiver_id`, `item_id`, `content`, `sent_date`, `is_read`) VALUES
('M54b3d', 'U6a7f9634327af', 'U1658fc', 'I6c08', '', '2026-08-17 02:49:00', 'yes'),
('M9f2cd', 'U1658fc', 'U6a7f9634327af', 'I9997', '', '2026-08-15 19:50:15', 'yes');

-- --------------------------------------------------------

--
-- Table structure for table `review`
--

CREATE TABLE `review` (
  `review_id` varchar(5) NOT NULL,
  `borrow_id` varchar(5) NOT NULL,
  `item_id` varchar(5) NOT NULL,
  `reviewer_id` varchar(15) NOT NULL,
  `rating` int(11) NOT NULL,
  `comment` varchar(500) DEFAULT NULL,
  `review_date` date DEFAULT NULL
) ;

--
-- Dumping data for table `review`
--

INSERT INTO `review` (`review_id`, `borrow_id`, `item_id`, `reviewer_id`, `rating`, `comment`, `review_date`) VALUES
('RV046', 'B8d5c', 'I9997', 'U1658fc', 5, '', '2026-08-17'),
('RV138', 'Bd562', 'I9997', 'U1658fc', 5, '', '2026-08-17'),
('RV16f', '', 'I9997', 'U1658fc', 5, '', '2026-08-17'),
('RVa5e', 'B17f3', 'I6c08', 'U6a7f9634327af', 5, 'Long lasting charge. Recommended.', '2026-08-17'),
('RVacc', 'B39c2', 'I9997', 'U1658fc', 5, '', '2026-08-17');

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `user_id` varchar(15) NOT NULL,
  `user_name` varchar(20) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `phone_number` varchar(15) DEFAULT NULL,
  `email` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`user_id`, `user_name`, `password`, `phone_number`, `email`) VALUES
('U1658fc', 'Tahmid Alam', '$2y$10$dywIwYXHEZRbEndRaL7TuehR3yuaSvnODJXg4hynh9GitDwsxZxOS', '01629472041', 'tahmidalam@gmail.com'),
('U66afff', 'Maisha', '$2y$10$6iKGt89uUyeVLr6QIyoW/.0mbffe3NQlkyeXYfhNOKLG.WfORGghm', '0128492034133', 'maisha@gmail.com'),
('U6a7f9634327af', 'Tabshir Ali', '$2y$10$0DcnEkvDKXo.1DeSaSM41.R8wm7INhGbUpVgc39lE1IMV5iXFMUai', '01612379721', 'tabshirali@gmail.com');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `borrowing`
--
ALTER TABLE `borrowing`
  ADD PRIMARY KEY (`borrow_id`),
  ADD KEY `item_id` (`item_id`),
  ADD KEY `borrower_id` (`borrower_id`);

--
-- Indexes for table `borrow_request`
--
ALTER TABLE `borrow_request`
  ADD PRIMARY KEY (`request_id`),
  ADD KEY `item_id` (`item_id`),
  ADD KEY `requester_id` (`requester_id`);

--
-- Indexes for table `image`
--
ALTER TABLE `image`
  ADD PRIMARY KEY (`image_id`),
  ADD KEY `item_id` (`item_id`);

--
-- Indexes for table `item`
--
ALTER TABLE `item`
  ADD PRIMARY KEY (`item_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `message`
--
ALTER TABLE `message`
  ADD PRIMARY KEY (`message_id`);

--
-- Indexes for table `review`
--
ALTER TABLE `review`
  ADD PRIMARY KEY (`review_id`),
  ADD KEY `item_id` (`item_id`),
  ADD KEY `reviewer_id` (`reviewer_id`),
  ADD KEY `borrow_id` (`borrow_id`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`user_id`);

--
-- Constraints for dumped tables
--

--
-- Constraints for table `borrowing`
--
ALTER TABLE `borrowing`
  ADD CONSTRAINT `borrowing_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `item` (`item_id`),
  ADD CONSTRAINT `borrowing_ibfk_2` FOREIGN KEY (`borrower_id`) REFERENCES `user` (`user_id`);

--
-- Constraints for table `borrow_request`
--
ALTER TABLE `borrow_request`
  ADD CONSTRAINT `borrow_request_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `item` (`item_id`),
  ADD CONSTRAINT `borrow_request_ibfk_2` FOREIGN KEY (`requester_id`) REFERENCES `user` (`user_id`);

--
-- Constraints for table `image`
--
ALTER TABLE `image`
  ADD CONSTRAINT `image_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `item` (`item_id`);

--
-- Constraints for table `item`
--
ALTER TABLE `item`
  ADD CONSTRAINT `item_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`);

--
-- Constraints for table `review`
--
ALTER TABLE `review`
  ADD CONSTRAINT `review_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `item` (`item_id`),
  ADD CONSTRAINT `review_ibfk_2` FOREIGN KEY (`reviewer_id`) REFERENCES `user` (`user_id`),
  ADD CONSTRAINT `review_ibfk_borrow` FOREIGN KEY (`borrow_id`) REFERENCES `borrowing` (`borrow_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
