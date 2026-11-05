-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Nov 04, 2026 at 09:42 PM
-- Server version: 8.0.46
-- PHP Version: 8.3.28

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `commoncents`
--

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

DROP TABLE IF EXISTS `cart`;
CREATE TABLE IF NOT EXISTS `cart` (
  `cartID` int NOT NULL AUTO_INCREMENT,
  `userID` int NOT NULL,
  PRIMARY KEY (`cartID`),
  UNIQUE KEY `userID` (`userID`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `cart`
--

INSERT INTO `cart` (`cartID`, `userID`) VALUES
(2, 2);

-- --------------------------------------------------------

--
-- Table structure for table `cartitem`
--

DROP TABLE IF EXISTS `cartitem`;
CREATE TABLE IF NOT EXISTS `cartitem` (
  `cartItemID` int NOT NULL AUTO_INCREMENT,
  `quantity` int NOT NULL,
  `cartID` int NOT NULL,
  `listingID` int NOT NULL,
  PRIMARY KEY (`cartItemID`),
  KEY `fk_cartitem_cart` (`cartID`),
  KEY `fk_cartitem_listing` (`listingID`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `category`
--

DROP TABLE IF EXISTS `category`;
CREATE TABLE IF NOT EXISTS `category` (
  `categoryID` int NOT NULL AUTO_INCREMENT,
  `categoryName` varchar(50) NOT NULL,
  PRIMARY KEY (`categoryID`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `category`
--

INSERT INTO `category` (`categoryID`, `categoryName`) VALUES
(5, 'Electronics'),
(6, 'Textbooks & Study Materials'),
(7, 'Clothing'),
(8, 'Living Essentials'),
(9, 'Sports & Fitness');

-- --------------------------------------------------------

--
-- Table structure for table `listing`
--

DROP TABLE IF EXISTS `listing`;
CREATE TABLE IF NOT EXISTS `listing` (
  `listingID` int NOT NULL AUTO_INCREMENT,
  `title` varchar(100) NOT NULL,
  `description` text,
  `price` decimal(10,2) NOT NULL,
  `type` enum('New','Used') NOT NULL,
  `imageURL` varchar(255) DEFAULT NULL,
  `datePosted` datetime NOT NULL,
  `status` enum('Available','Reserved','Sold') NOT NULL,
  `userID` int NOT NULL,
  `categoryID` int NOT NULL,
  PRIMARY KEY (`listingID`),
  KEY `fk_listing_user` (`userID`),
  KEY `fk_listing_category` (`categoryID`)
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `listing`
--

INSERT INTO `listing` (`listingID`, `title`, `description`, `price`, `type`, `imageURL`, `datePosted`, `status`, `userID`, `categoryID`) VALUES
(1, 'Scientific Calculator', 'Fully functional calculator, ideal for mathematics and accounting students.', 180.00, 'Used', '../uploads/369088e1e1834a24f241d5a4.jpg', '2026-11-04 18:05:31', 'Available', 1, 5),
(2, 'Bluetooth Headphones', 'Wireless headphones suitable for studying, online classes and listening to music.', 250.00, 'New', '../uploads/3f26eaed302364a7b9046772.jpg', '2026-11-04 18:06:52', 'Available', 1, 5),
(3, 'Power Bank 10,000mAh', 'Portable charger for phones and USB devices while on campus.', 200.00, 'New', '../uploads/70c5343f433a72b6463e701f.jpg', '2026-11-04 18:08:15', 'Available', 1, 5),
(4, 'Wireless Mouse', 'Compact wireless mouse with USB receiver for laptops and computers.', 90.00, 'Used', '../uploads/10e3dcccb34fba88e7550cfb.jpg', '2026-11-04 18:09:50', 'Available', 1, 5),
(5, 'Java Programming Textbook', 'Programming textbook covering Java fundamentals and object-oriented programming.', 220.00, 'Used', '../uploads/d67136917d06d1e484058bd2.jpg', '2026-11-04 19:45:45', 'Available', 1, 6),
(6, 'Mathematics Textbook', 'Study guide with mathematical formulas, explanations and practice exercises.', 180.00, 'Used', '../uploads/51bf70ee31e2111c209e3995.jpg', '2026-11-04 19:46:57', 'Available', 1, 6),
(7, 'Highlighter Set', 'Pack of six colourful highlighters for notes and exam preparation.', 45.00, 'New', '../uploads/b3d2d13921bcd63e21be4e40.jpg', '2026-11-04 19:49:12', 'Available', 1, 6),
(8, 'A4 Notebook', '200-page lined notebook for lectures, assignments and studying.', 35.00, 'New', '../uploads/1408b121964b3d349ee8a075.jpg', '2026-11-04 19:51:08', 'Available', 1, 6),
(9, 'Mathematical Instrument Set', 'Complete geometry set with ruler, compass, protractor and set squares.', 60.00, 'New', '../uploads/6398b4b0ffaa479a986266f0.jpg', '2026-11-04 19:52:05', 'Available', 1, 6),
(10, 'Oversized Hoodie', 'Comfortable black oversized hoodie, size medium, in good condition.', 180.00, 'Used', '../uploads/1d974caaa8188045cc76843c.jpg', '2026-11-04 19:59:25', 'Available', 1, 7),
(11, 'White Sneakers', 'Casual white sneakers, UK size 5, lightly worn and cleaned.', 250.00, 'Used', '../uploads/124dca30c2432ab03820fc48.jpg', '2026-11-04 20:00:15', 'Available', 1, 7),
(12, 'Denim Jacket', 'Classic blue denim jacket, size small, suitable for everyday outfits.', 200.00, 'Used', '../uploads/56725a81454acbd1e849d17c.jpg', '2026-11-04 20:01:38', 'Available', 1, 7),
(13, 'Canvas Tote Bag', 'Reusable tote bag for carrying books, stationery and daily essentials.', 80.00, 'New', '../uploads/82b402e9c583d7ea67442c51.jpg', '2026-11-04 20:02:23', 'Available', 1, 7),
(14, 'Chiffon Hijab', 'Lightweight brown chiffon hijab, breathable and suitable for everyday wear.', 70.00, 'New', '../uploads/197e52e7064864eee214cdb0.jpg', '2026-11-04 20:04:17', 'Available', 1, 7),
(15, 'LED Study Lamp', 'Adjustable desk lamp with bright lighting for late-night studying.', 120.00, 'Used', '../uploads/1b1fe413120085ee3e2ea0ad.jpg', '2026-11-04 20:14:16', 'Available', 1, 8),
(16, 'Electric Kettle', '1.7-litre electric kettle, fully working and ideal for student accommodation.', 120.00, 'Used', '../uploads/554d1598d1c70ee0bf204c08.jpg', '2026-11-04 20:15:51', 'Available', 1, 8),
(17, 'Fleece Blanket', 'Soft, warm single-size blanket for a bedroom or student residence.', 100.00, 'New', '../uploads/0338bea2801b8e0901bef104.jpg', '2026-11-04 20:18:47', 'Available', 1, 8),
(18, 'Desk Organiser', 'Compact organiser for stationery, small accessories and study supplies.', 85.00, 'New', '../uploads/38b372a5296920eaabf1e057.jpg', '2026-11-04 20:19:48', 'Available', 1, 8),
(19, 'Mini Desk Fan', 'Portable USB-powered fan suitable for small desks and bedrooms.', 95.00, 'New', '../uploads/8304676b32ac2de72cbdb554.jpg', '2026-11-04 20:20:45', 'Available', 1, 8),
(20, 'Yoga Mat', 'Non-slip exercise mat suitable for yoga, stretching and home workouts.', 120.00, 'Used', '../uploads/dabce43fca771b6d1b51ffc7.jpg', '2026-11-04 20:27:29', 'Available', 1, 9),
(21, 'Dumbbell Set', 'Pair of 2kg dumbbells for strength training and home exercise.', 150.00, 'Used', '../uploads/13600be495f943b90e14c0f8.jpg', '2026-11-04 20:28:47', 'Available', 1, 9),
(22, 'Skipping Rope', 'Adjustable skipping rope suitable for cardio and fitness workouts.', 80.00, 'New', '../uploads/6ecf4da2380bf75b52ca04a7.jpg', '2026-11-04 20:30:19', 'Sold', 1, 9),
(23, 'Soccer Ball', 'Size 5 football in good condition for casual games and training.', 100.00, 'Used', '../uploads/31bdb74bd6ff96549bd4d857.jpg', '2026-11-04 20:31:18', 'Available', 1, 9),
(24, 'Gym Duffel Bag', 'Spacious sports bag with handles and a shoulder strap for gym essentials.', 160.00, 'New', '../uploads/e23389f366f1eeda30353454.jpg', '2026-11-04 20:32:33', 'Available', 1, 9);

-- --------------------------------------------------------

--
-- Table structure for table `notification`
--

DROP TABLE IF EXISTS `notification`;
CREATE TABLE IF NOT EXISTS `notification` (
  `notificationID` int NOT NULL AUTO_INCREMENT,
  `message` text NOT NULL,
  `notificationDate` datetime NOT NULL,
  `userID` int NOT NULL,
  PRIMARY KEY (`notificationID`),
  KEY `fk_notification_user` (`userID`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `notification`
--

INSERT INTO `notification` (`notificationID`, `message`, `notificationDate`, `userID`) VALUES
(1, 'A new order (#ORD-2) includes your listing: Skipping Rope.', '2026-11-04 21:36:25', 1),
(2, 'Your order (#ORD-2) was placed successfully. Payment is pending confirmation.', '2026-11-04 21:36:25', 2);

-- --------------------------------------------------------

--
-- Table structure for table `order`
--

DROP TABLE IF EXISTS `order`;
CREATE TABLE IF NOT EXISTS `order` (
  `orderID` int NOT NULL AUTO_INCREMENT,
  `orderDate` datetime NOT NULL,
  `status` enum('Pending','Paid','Completed','Cancelled') NOT NULL,
  `deliveryAddress` varchar(255) DEFAULT NULL,
  `userID` int NOT NULL,
  PRIMARY KEY (`orderID`),
  KEY `fk_order_user` (`userID`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `order`
--

INSERT INTO `order` (`orderID`, `orderDate`, `status`, `deliveryAddress`, `userID`) VALUES
(2, '2026-11-04 21:36:25', 'Pending', 'Hanover Street, District 6', 2);

-- --------------------------------------------------------

--
-- Table structure for table `orderitem`
--

DROP TABLE IF EXISTS `orderitem`;
CREATE TABLE IF NOT EXISTS `orderitem` (
  `orderItemID` int NOT NULL AUTO_INCREMENT,
  `quantity` int NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `orderID` int NOT NULL,
  `listingID` int NOT NULL,
  PRIMARY KEY (`orderItemID`),
  KEY `fk_orderitem_order` (`orderID`),
  KEY `fk_orderitem_listing` (`listingID`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `orderitem`
--

INSERT INTO `orderitem` (`orderItemID`, `quantity`, `price`, `orderID`, `listingID`) VALUES
(2, 1, 80.00, 2, 22);

-- --------------------------------------------------------

--
-- Table structure for table `payment`
--

DROP TABLE IF EXISTS `payment`;
CREATE TABLE IF NOT EXISTS `payment` (
  `paymentID` int NOT NULL AUTO_INCREMENT,
  `amount` decimal(10,2) NOT NULL,
  `paymentMethod` enum('Card','EFT','PayFast','Cash') NOT NULL,
  `paymentStatus` enum('Pending','Paid','Failed') NOT NULL,
  `paymentDate` datetime NOT NULL,
  `orderID` int NOT NULL,
  PRIMARY KEY (`paymentID`),
  UNIQUE KEY `orderID` (`orderID`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `payment`
--

INSERT INTO `payment` (`paymentID`, `amount`, `paymentMethod`, `paymentStatus`, `paymentDate`, `orderID`) VALUES
(3, 80.00, 'Cash', 'Pending', '2026-11-04 21:36:25', 2);

-- --------------------------------------------------------

--
-- Table structure for table `review`
--

DROP TABLE IF EXISTS `review`;
CREATE TABLE IF NOT EXISTS `review` (
  `reviewID` int NOT NULL AUTO_INCREMENT,
  `rating` tinyint NOT NULL,
  `comment` text,
  `reviewDate` datetime NOT NULL,
  `userID` int NOT NULL,
  `listingID` int NOT NULL,
  PRIMARY KEY (`reviewID`),
  KEY `fk_review_user` (`userID`),
  KEY `fk_review_listing` (`listingID`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

DROP TABLE IF EXISTS `user`;
CREATE TABLE IF NOT EXISTS `user` (
  `userID` int NOT NULL AUTO_INCREMENT,
  `firstName` varchar(50) NOT NULL,
  `lastName` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('User','Vendor') NOT NULL DEFAULT 'User',
  PRIMARY KEY (`userID`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`userID`, `firstName`, `lastName`, `email`, `password`, `role`) VALUES
(1, 'Safiya', 'Elmi', 'safiyamelmi19@gmail.com', '$2y$10$YR4uYzfomjyYYMePgXKlgukdmis0RvkuC/VsYdFAVXsf71nATitiS', 'User'),
(2, 'Lenny', 'Jackson', 'lenjacks17@gmail.com', '$2y$10$Pg.JKNIcrDcuclJQN0QD.uBR/6fFfJhL9MlNZ9y3q.TY/F.jDB596', 'User');

--
-- Constraints for dumped tables
--

--
-- Constraints for table `cart`
--
ALTER TABLE `cart`
  ADD CONSTRAINT `fk_cart_user` FOREIGN KEY (`userID`) REFERENCES `user` (`userID`);

--
-- Constraints for table `cartitem`
--
ALTER TABLE `cartitem`
  ADD CONSTRAINT `fk_cartitem_cart` FOREIGN KEY (`cartID`) REFERENCES `cart` (`cartID`),
  ADD CONSTRAINT `fk_cartitem_listing` FOREIGN KEY (`listingID`) REFERENCES `listing` (`listingID`);

--
-- Constraints for table `listing`
--
ALTER TABLE `listing`
  ADD CONSTRAINT `fk_listing_category` FOREIGN KEY (`categoryID`) REFERENCES `category` (`categoryID`),
  ADD CONSTRAINT `fk_listing_user` FOREIGN KEY (`userID`) REFERENCES `user` (`userID`);

--
-- Constraints for table `notification`
--
ALTER TABLE `notification`
  ADD CONSTRAINT `fk_notification_user` FOREIGN KEY (`userID`) REFERENCES `user` (`userID`);

--
-- Constraints for table `order`
--
ALTER TABLE `order`
  ADD CONSTRAINT `fk_order_user` FOREIGN KEY (`userID`) REFERENCES `user` (`userID`);

--
-- Constraints for table `orderitem`
--
ALTER TABLE `orderitem`
  ADD CONSTRAINT `fk_orderitem_listing` FOREIGN KEY (`listingID`) REFERENCES `listing` (`listingID`),
  ADD CONSTRAINT `fk_orderitem_order` FOREIGN KEY (`orderID`) REFERENCES `order` (`orderID`);

--
-- Constraints for table `payment`
--
ALTER TABLE `payment`
  ADD CONSTRAINT `fk_payment_order` FOREIGN KEY (`orderID`) REFERENCES `order` (`orderID`);

--
-- Constraints for table `review`
--
ALTER TABLE `review`
  ADD CONSTRAINT `fk_review_listing` FOREIGN KEY (`listingID`) REFERENCES `listing` (`listingID`),
  ADD CONSTRAINT `fk_review_user` FOREIGN KEY (`userID`) REFERENCES `user` (`userID`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
