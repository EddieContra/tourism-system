-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 06, 2024 at 12:59 PM
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
-- Database: `tourism`
--

-- --------------------------------------------------------

--
-- Table structure for table `permissions`
--

CREATE TABLE `permissions` (
  `id` int(11) NOT NULL,
  `permission` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `createuser` varchar(255) DEFAULT NULL,
  `deleteuser` varchar(255) DEFAULT NULL,
  `createbid` varchar(255) DEFAULT NULL,
  `updatebid` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `permissions`
--

INSERT INTO `permissions` (`id`, `permission`, `createuser`, `deleteuser`, `createbid`, `updatebid`) VALUES
(1, 'Superuser', NULL, '1', '1', '1'),
(2, 'Admin', '1', NULL, '1', '1'),
(3, 'User', NULL, NULL, '1', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `tbladmin`
--

CREATE TABLE `tbladmin` (
  `ID` int(10) NOT NULL,
  `Staffid` varchar(255) DEFAULT NULL,
  `AdminName` varchar(120) DEFAULT NULL,
  `UserName` varchar(120) DEFAULT NULL,
  `FirstName` varchar(255) DEFAULT NULL,
  `LastName` varchar(255) DEFAULT NULL,
  `MobileNumber` bigint(10) DEFAULT NULL,
  `Email` varchar(200) DEFAULT NULL,
  `Status` int(11) NOT NULL DEFAULT 1,
  `Photo` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL DEFAULT 'avatar15.jpg',
  `Password` varchar(120) DEFAULT NULL,
  `AdminRegdate` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbladmin`
--

INSERT INTO `tbladmin` (`ID`, `Staffid`, `AdminName`, `UserName`, `FirstName`, `LastName`, `MobileNumber`, `Email`, `Status`, `Photo`, `Password`, `AdminRegdate`) VALUES
(2, '10002', 'Admin', 'admin', 'Alfred', 'Ndayanse ', 622899540, 'admin@gmail.com', 1, '', '81dc9bdb52d04dc20036dbd8313ed055', '2021-06-21 10:18:39'),
(9, '10003', 'Admin', 'harry', 'Hamis', 'Mwinjuma', 757537271, 'hamis@gmail.com', 1, 'face27.jpg', '81dc9bdb52d04dc20036dbd8313ed055', '2021-06-21 07:08:48'),
(29, 'U002', 'User', 'morgan', 'Happy', 'Clement', 760546590, 'happy@gmail.com', 1, 'avatar15.jpg', '81dc9bdb52d04dc20036dbd8313ed055', '2021-07-21 14:26:42'),
(31, '1223', 'User', 'Baraka', 'Baraka', 'Simba', 76356262, 'baraka@gmail.com', 1, 'avatar15.jpg', '81dc9bdb52d04dc20036dbd8313ed055', '2024-06-02 15:12:25'),
(32, '2323', 'Admin', 'Ndayanse', 'Alfred', 'Ndayanse', 622899540, 'alfred1@gmail.com', 1, 'avatar15.jpg', 'e82c4b19b8151ddc25d4d93baf7b908f', '2024-06-02 18:43:39');

-- --------------------------------------------------------

--
-- Table structure for table `tblbooking`
--

CREATE TABLE `tblbooking` (
  `BookingId` int(11) NOT NULL,
  `PackageId` int(11) DEFAULT NULL,
  `UserEmail` varchar(100) DEFAULT NULL,
  `FromDate` varchar(100) DEFAULT NULL,
  `ToDate` varchar(100) DEFAULT NULL,
  `Comment` mediumtext DEFAULT NULL,
  `RegDate` timestamp NULL DEFAULT current_timestamp(),
  `status` int(11) DEFAULT NULL,
  `CancelledBy` varchar(5) DEFAULT NULL,
  `UpdationDate` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `tblbooking`
--

INSERT INTO `tblbooking` (`BookingId`, `PackageId`, `UserEmail`, `FromDate`, `ToDate`, `Comment`, `RegDate`, `status`, `CancelledBy`, `UpdationDate`) VALUES
(16, 2, 'mbise@gmail.com', '2024-03-26', '2021-03-31', 'I Really need to visit that place', '2021-03-24 22:48:36', 0, NULL, '2024-06-02 15:13:38'),
(19, 1, 'gadaki1@gmail.com', '2024-06-04', '2024-06-11', 'I need the trip immediately', '2024-06-02 14:12:45', 1, NULL, '2024-06-02 14:13:38'),
(20, 2, 'imma@gmail.com', '2024-06-07', '2024-06-12', 'I need this schedule as soon as possible', '2024-06-06 10:56:02', 1, NULL, '2024-06-06 10:56:26');

-- --------------------------------------------------------

--
-- Table structure for table `tblcompany`
--

CREATE TABLE `tblcompany` (
  `id` int(11) NOT NULL,
  `regno` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `companyname` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `companyemail` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `country` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `companyphone` int(10) NOT NULL,
  `companyaddress` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `companylogo` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL DEFAULT 'avatar15.jpg',
  `status` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL DEFAULT '0',
  `developer` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `creationdate` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tblcompany`
--

INSERT INTO `tblcompany` (`id`, `regno`, `companyname`, `companyemail`, `country`, `companyphone`, `companyaddress`, `companylogo`, `status`, `developer`, `creationdate`) VALUES
(4, '1002', 'Five Star Tours', 'admin@gmail.com', 'Tanzania', 752045268, 'Shangarai', 'church.jpg', '1', 'Gadaki', '2021-02-02 12:17:15');

-- --------------------------------------------------------

--
-- Table structure for table `tbltourpackages`
--

CREATE TABLE `tbltourpackages` (
  `PackageId` int(11) NOT NULL,
  `PackageName` varchar(200) DEFAULT NULL,
  `PackageType` varchar(150) DEFAULT NULL,
  `PackageLocation` varchar(100) DEFAULT NULL,
  `PackagePrice` int(11) DEFAULT NULL,
  `PackageFetures` varchar(255) DEFAULT NULL,
  `PackageDetails` mediumtext DEFAULT NULL,
  `PackageImage` varchar(100) DEFAULT NULL,
  `Creationdate` timestamp NULL DEFAULT current_timestamp(),
  `UpdationDate` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `tbltourpackages`
--

INSERT INTO `tbltourpackages` (`PackageId`, `PackageName`, `PackageType`, `PackageLocation`, `PackagePrice`, `PackageFetures`, `PackageDetails`, `PackageImage`, `Creationdate`, `UpdationDate`) VALUES
(1, 'Tarangire National Park', 'General', 'Manyara, Tanzania', 1800, 'Walking safaris, Maasai cultural experiences, traditional craft workshops, luxurious lodges, gourmet bush dinners, hot air balloon rides,', 'Our Tarangire National Park packages offer an array of unforgettable experiences, including thrilling game drives with expert guides, diverse birdwatching, and scenic views of iconic baobab trees and the wildlife-rich Tarangire River. Guests can enjoy walking safaris, engaging Maasai cultural experiences, and traditional craft workshops. Accommodation options feature luxurious lodges and tented camps with modern amenities, where you can savor gourmet bush dinners under the stars. For adventure seekers, early morning hot air balloon rides provide breathtaking aerial views, while night game drives reveal the park\'s nocturnal wonders. Specialized photography tours, family-friendly activities, spa services, yoga sessions, and eco-friendly practices with conservation projects ensure a comprehensive and enriching safari experience for all.\r\n\r\n\r\n', 'sign-overhead-at-the-main-entrance-to-tarangire-national-park-in-northern-CYD7BH.jpg', '2024-05-13 14:23:44', '2024-06-02 15:15:33'),
(2, 'Ngorongoro Conservation Area', 'Discovery', 'Ngorongoro, Arusha', 2433, 'Volcanic caldera teeming with wildlife, cultural visits to Maasai villages, luxury lodge stays with stunning crater views, guided walking safaris, and opportunities to explore archaeological sites like Olduvai Gorge', 'Our Ngorongoro Conservation Area Discovery Package offers an immersive journey into one of Africa\'s most spectacular landscapes. Delve into the heart of the vast volcanic caldera on exhilarating game drives, where you\'ll encounter a diverse array of wildlife, including the Big Five, against the backdrop of stunning crater views. Immerse yourself in Maasai culture with visits to local villages, gaining insight into traditional customs and lifestyles. Luxuriate in comfortable lodge accommodations overlooking the crater rim, providing the perfect blend of relaxation and adventure. Guided walking safaris offer opportunities to explore the area\'s rich biodiversity up close, while visits to archaeological sites like Olduvai Gorge provide fascinating insights into human evolution. With a seamless combination of thrilling wildlife encounters, cultural immersion, and breathtaking scenery, this package promises an unforgettable safari experience in the Ngorongoro Conservation Area.', 'ngorongoro-e1659983535290.png', '2024-05-16 15:24:26', '2024-06-02 15:15:46'),
(3, 'Serengeti National Park', 'Adventure', 'Experience the iconic wildebeest migration, thrilling game drives, luxury tented camps, guided bush ', 3600, 'Mara and Simiyu Regions', 'Our Serengeti National Park Adventure Package invites you to embark on a journey of unparalleled wildlife encounters and breathtaking landscapes. Experience the awe-inspiring spectacle of the annual wildebeest migration, where millions of animals traverse the plains in search of greener pastures, accompanied by thrilling game drives led by expert guides. Retreat to luxurious tented camps nestled in the heart of the wilderness, offering comfort and serenity amidst nature\'s beauty. Immerse yourself in the sights and sounds of the Serengeti with guided bush walks, allowing for intimate encounters with the flora and fauna of this iconic ecosystem. Take to the skies with mesmerizing hot air balloon safaris, providing a bird\'s eye view of the sweeping plains and majestic wildlife below. Whether you\'re a seasoned safari enthusiast or embarking on your first African adventure, our Serengeti package promises an unforgettable experience of discovery and wonder.', 'maxresdefault.jpg', '2024-04-23 16:00:58', '2024-06-02 15:16:00'),
(4, 'Lake Manyara National Park', 'Exploration', 'Manyara, Tanzania', 1800, 'Exhilarating game drives, luxurious accommodations with stunning views, guided nature walks, birdwatching excursions, and cultural visits to nearby villages, ensuring an unforgettable safari experience.', 'Our Lake Manyara National Park Exploration Package offers a captivating adventure amidst the diverse landscapes and abundant wildlife of this renowned park. Experience exhilarating game drives through lush forests, open grasslands, and along the shores of the lake, where you\'ll have the chance to encounter iconic species such as tree-climbing lions, elephants, giraffes, and flamingos. Stay in luxurious lodges or tented camps with stunning views of the park, providing comfort and tranquility after days of exploration. Immerse yourself in the park\'s natural beauty with guided nature walks, birdwatching excursions, and cultural visits to nearby villages, where you can learn about the traditions and lifestyles of the local communities. Whether you\'re seeking adventure, relaxation, or cultural immersion, our Lake Manyara package promises an unforgettable safari experience in one of Tanzania\'s most captivating destinations.\r\n', 'img_2863-scaled-1-750x450.jpg', '2024-05-29 22:39:37', '2024-06-02 15:16:19'),
(5, 'Arusha National Park', 'Expedition', 'Arusha, Tanzania', 3200, 'Our Mount Meru Expedition Package offers thrilling treks to the summit of Tanzania\'s second-highest peak, with expert guides, breathtaking scenery, and luxurious accommodations providing an unforgettable mountain adventure.', 'Embark on an unforgettable adventure with our Mount Meru Expedition Package, where you\'ll conquer Tanzania\'s second-highest peak alongside experienced guides. This thrilling trekking experience will lead you through lush rainforests, alpine meadows, and rugged volcanic terrain, culminating in breathtaking views from the summit. Relax and rejuvenate in comfortable accommodations at the base of the mountain before and after your ascent, ensuring a well-deserved rest after exhilarating days of hiking. With expertly planned itineraries, delicious meals, and top-notch support staff, our Mount Meru Expedition Package promises an immersive and rewarding journey to the heart of Tanzania\'s natural beauty.', 'co9c9896.jpg', '2024-05-23 22:42:10', '2024-06-02 15:16:50'),
(6, 'Kilimanjaro National park', 'Summit Expedition', 'Kilimanjaro, Tanzania', 5000, 'Our Kilimanjaro National Park Summit Expedition Package offers a life-changing journey to the Roof of Africa, featuring expertly guided treks, breathtaking scenery, comfortable accommodations, and unparalleled views from the summit of Mount Kilimanjaro.  ', 'Embark on the adventure of a lifetime with our Kilimanjaro National Park Summit Expedition Package, where you\'ll journey to the iconic Roof of Africa. Led by experienced guides, you\'ll navigate through diverse ecosystems, from lush rainforests to alpine deserts, before reaching the snowy summit of Mount Kilimanjaro. Along the way, you\'ll enjoy comfortable accommodations at various campsites, delicious meals prepared by our skilled staff, and awe-inspiring views of the surrounding landscape. With carefully planned itineraries and top-notch support, our package ensures a safe, memorable, and rewarding trek to the highest peak in Africa.', 'maxresdefault (1).jpg', '2024-06-01 08:01:08', '2024-06-02 15:17:07');

-- --------------------------------------------------------

--
-- Table structure for table `tblusers`
--

CREATE TABLE `tblusers` (
  `id` int(11) NOT NULL,
  `FullName` varchar(100) DEFAULT NULL,
  `MobileNumber` char(10) DEFAULT NULL,
  `EmailId` varchar(70) DEFAULT NULL,
  `Password` varchar(100) DEFAULT NULL,
  `RegDate` timestamp NULL DEFAULT current_timestamp(),
  `UpdationDate` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `tblusers`
--

INSERT INTO `tblusers` (`id`, `FullName`, `MobileNumber`, `EmailId`, `Password`, `RegDate`, `UpdationDate`) VALUES
(14, 'Imma Jr', '0745225985', 'emma@gmail.com', '81dc9bdb52d04dc20036dbd8313ed055', '2024-05-15 14:00:35', '2024-06-06 10:57:54'),
(16, 'Imma Jnr', '0745225985', 'admin@gmail.com', '81dc9bdb52d04dc20036dbd8313ed055', '2024-04-24 08:34:08', '2024-06-06 10:58:13'),
(17, 'Gaston Kisheo', '0752045268', 'gadaki1@gmail.com', '81dc9bdb52d04dc20036dbd8313ed055', '2024-06-02 13:18:21', NULL),
(18, 'Imma', '0745225985', 'imma@gmail.com', '81dc9bdb52d04dc20036dbd8313ed055', '2024-06-06 10:54:52', NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tbladmin`
--
ALTER TABLE `tbladmin`
  ADD PRIMARY KEY (`ID`);

--
-- Indexes for table `tblbooking`
--
ALTER TABLE `tblbooking`
  ADD PRIMARY KEY (`BookingId`);

--
-- Indexes for table `tblcompany`
--
ALTER TABLE `tblcompany`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tbltourpackages`
--
ALTER TABLE `tbltourpackages`
  ADD PRIMARY KEY (`PackageId`);

--
-- Indexes for table `tblusers`
--
ALTER TABLE `tblusers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `EmailId` (`EmailId`),
  ADD KEY `EmailId_2` (`EmailId`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `tbladmin`
--
ALTER TABLE `tbladmin`
  MODIFY `ID` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT for table `tblbooking`
--
ALTER TABLE `tblbooking`
  MODIFY `BookingId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `tblcompany`
--
ALTER TABLE `tblcompany`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `tbltourpackages`
--
ALTER TABLE `tbltourpackages`
  MODIFY `PackageId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `tblusers`
--
ALTER TABLE `tblusers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
