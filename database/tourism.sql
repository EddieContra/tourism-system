-- ---------------------------------------------------------------------------
-- Five Star Tours — database schema and demo data
--
-- Import this file into an empty database called `tourism`
-- (Docker does this automatically on first start).
--
-- Demo accounts (change these before going live):
--   Admin panel  /admin  username: admin   password: Admin@1234  (Super admin)
--                        username: staff   password: Staff@1234  (Staff)
--   Customer site        email: demo@fivestartours.test  password: Demo@1234
-- ---------------------------------------------------------------------------

SET NAMES utf8mb4;
SET time_zone = '+00:00';
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `tblpassword_resets`;
DROP TABLE IF EXISTS `tblenquiry`;
DROP TABLE IF EXISTS `tblbooking`;
DROP TABLE IF EXISTS `tbltourpackages`;
DROP TABLE IF EXISTS `tblusers`;
DROP TABLE IF EXISTS `tbladmin`;

SET FOREIGN_KEY_CHECKS = 1;

-- ---------------------------------------------------------------------------
-- Staff accounts for the admin panel.
-- AdminName holds the role: 'Superuser', 'Admin' or 'User' (staff).
-- ---------------------------------------------------------------------------
CREATE TABLE `tbladmin` (
  `ID` int(10) NOT NULL AUTO_INCREMENT,
  `Staffid` varchar(50) DEFAULT NULL,
  `AdminName` varchar(50) NOT NULL DEFAULT 'User',
  `UserName` varchar(120) NOT NULL,
  `FirstName` varchar(100) DEFAULT NULL,
  `LastName` varchar(100) DEFAULT NULL,
  `MobileNumber` varchar(20) DEFAULT NULL,
  `Email` varchar(200) DEFAULT NULL,
  `Status` tinyint(1) NOT NULL DEFAULT 1,
  `Photo` varchar(255) NOT NULL DEFAULT '',
  `Password` varchar(255) NOT NULL,
  `AdminRegdate` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`ID`),
  UNIQUE KEY `uniq_admin_username` (`UserName`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `tbladmin` (`ID`, `Staffid`, `AdminName`, `UserName`, `FirstName`, `LastName`, `MobileNumber`, `Email`, `Status`, `Photo`, `Password`, `AdminRegdate`) VALUES
(1, 'FST-001', 'Superuser', 'admin', 'Daniel', 'Mollel', '0754000101', 'admin@fivestartours.test', 1, '', '$2y$10$lUu9tr4g2MN0l9f2/Pz2euZpaRt1DvIhQvIzDYOm38FLOZEASnApO', '2026-01-10 08:00:00'),
(2, 'FST-002', 'User', 'staff', 'Neema', 'Kimaro', '0754000102', 'staff@fivestartours.test', 1, '', '$2y$10$71htVvdqhRJruRdkYRff5u6vxLBvAAFtPBS0pCLkp8yLLpHfk4ef2', '2026-02-14 09:30:00');

-- ---------------------------------------------------------------------------
-- Customers.
-- ---------------------------------------------------------------------------
CREATE TABLE `tblusers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `FullName` varchar(100) NOT NULL,
  `MobileNumber` varchar(20) DEFAULT NULL,
  `EmailId` varchar(150) NOT NULL,
  `Password` varchar(255) NOT NULL,
  `RegDate` timestamp NULL DEFAULT current_timestamp(),
  `UpdationDate` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_user_email` (`EmailId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `tblusers` (`id`, `FullName`, `MobileNumber`, `EmailId`, `Password`, `RegDate`) VALUES
(1, 'Amina Juma', '0745000201', 'demo@fivestartours.test', '$2y$10$8I4yaf.RlT8f7PmcOoWXR.jjzLkLO/pjrkIFTGaswUnPzFGaXcy/y', '2026-05-02 10:15:00'),
(2, 'James Carter', '0745000202', 'james.carter@example.com', '$2y$10$8I4yaf.RlT8f7PmcOoWXR.jjzLkLO/pjrkIFTGaswUnPzFGaXcy/y', '2026-06-18 14:40:00'),
(3, 'Sofia Rossi', '0745000203', 'sofia.rossi@example.com', '$2y$10$8I4yaf.RlT8f7PmcOoWXR.jjzLkLO/pjrkIFTGaswUnPzFGaXcy/y', '2026-08-03 07:55:00');

-- ---------------------------------------------------------------------------
-- Tour packages.
-- ---------------------------------------------------------------------------
CREATE TABLE `tbltourpackages` (
  `PackageId` int(11) NOT NULL AUTO_INCREMENT,
  `PackageName` varchar(200) NOT NULL,
  `PackageType` varchar(150) DEFAULT NULL,
  `PackageLocation` varchar(150) DEFAULT NULL,
  `PackageDuration` varchar(50) DEFAULT NULL,
  `PackagePrice` int(11) NOT NULL DEFAULT 0,
  `PackageFetures` text DEFAULT NULL,
  `PackageDetails` mediumtext DEFAULT NULL,
  `PackageImage` varchar(255) DEFAULT NULL,
  `Creationdate` timestamp NULL DEFAULT current_timestamp(),
  `UpdationDate` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`PackageId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `tbltourpackages` (`PackageId`, `PackageName`, `PackageType`, `PackageLocation`, `PackageDuration`, `PackagePrice`, `PackageFetures`, `PackageDetails`, `PackageImage`, `Creationdate`) VALUES
(1, 'Tarangire National Park', 'Safari', 'Manyara, Tanzania', '3 days / 2 nights', 1800,
 'Game drives with expert guides, Walking safaris, Maasai cultural experiences, Luxury lodges, Bush dinners, Hot air balloon rides',
 'Our Tarangire National Park package offers an array of unforgettable experiences, including thrilling game drives with expert guides, diverse birdwatching, and scenic views of iconic baobab trees and the wildlife-rich Tarangire River. Guests can enjoy walking safaris, engaging Maasai cultural experiences, and traditional craft workshops.\n\nAccommodation options feature luxurious lodges and tented camps with modern amenities, where you can savour gourmet bush dinners under the stars. For adventure seekers, early morning hot air balloon rides provide breathtaking aerial views, while night game drives reveal the park''s nocturnal wonders.',
 'tarangire.jpg', '2026-01-13 14:23:44'),
(2, 'Ngorongoro Conservation Area', 'Discovery', 'Ngorongoro, Arusha', '2 days / 1 night', 2433,
 'Crater game drives, Big Five sightings, Maasai village visit, Crater-rim lodge, Guided walking safari, Olduvai Gorge',
 'Our Ngorongoro Conservation Area Discovery package offers an immersive journey into one of Africa''s most spectacular landscapes. Delve into the heart of the vast volcanic caldera on exhilarating game drives, where you''ll encounter a diverse array of wildlife, including the Big Five, against the backdrop of stunning crater views.\n\nImmerse yourself in Maasai culture with visits to local villages, relax in comfortable lodges overlooking the crater rim, and visit archaeological sites like Olduvai Gorge for fascinating insights into human evolution.',
 'ngorongoro.jpg', '2026-01-16 15:24:26'),
(3, 'Serengeti National Park', 'Adventure', 'Mara & Simiyu Regions, Tanzania', '4 days / 3 nights', 3600,
 'Great wildebeest migration, Game drives, Luxury tented camps, Guided bush walks, Hot air balloon safari',
 'Our Serengeti National Park Adventure package invites you on a journey of unparalleled wildlife encounters and breathtaking landscapes. Witness the annual wildebeest migration, where millions of animals cross the plains in search of greener pastures, on game drives led by expert guides.\n\nRetreat to luxurious tented camps in the heart of the wilderness, take guided bush walks, and float above the plains on a sunrise hot air balloon safari for a bird''s-eye view of the majestic wildlife below.',
 'serengeti.jpg', '2026-02-23 16:00:58'),
(4, 'Lake Manyara National Park', 'Exploration', 'Manyara, Tanzania', '2 days / 1 night', 1800,
 'Tree-climbing lions, Flamingo birdwatching, Guided nature walks, Lodges with lake views, Cultural village visits',
 'Our Lake Manyara National Park Exploration package is a captivating adventure through lush forests, open grasslands and the shores of the lake, where you can encounter tree-climbing lions, elephants, giraffes and flocks of flamingos.\n\nStay in lodges or tented camps with stunning views of the park, and round off your trip with guided nature walks, birdwatching excursions and visits to nearby villages to learn about local traditions.',
 'lake-manyara.jpg', '2026-03-29 22:39:37'),
(5, 'Arusha National Park & Mount Meru', 'Expedition', 'Arusha, Tanzania', '4 days / 3 nights', 3200,
 'Mount Meru summit trek, Expert mountain guides, Rainforest and alpine meadows, Canoeing on Momella Lakes, Comfortable mountain huts',
 'Embark on an unforgettable adventure with our Mount Meru Expedition, where you''ll conquer Tanzania''s second-highest peak alongside experienced guides. The trek leads you through lush rainforest, alpine meadows and rugged volcanic terrain, culminating in breathtaking views from the summit.\n\nBefore and after the climb, relax in comfortable accommodation at the base of the mountain, with delicious meals and a dedicated support team throughout.',
 'arusha.jpg', '2026-04-23 22:42:10'),
(6, 'Kilimanjaro Summit Expedition', 'Summit Expedition', 'Kilimanjaro, Tanzania', '7 days / 6 nights', 5000,
 'Machame route trek, Certified mountain guides, All park and camping fees, Daily health checks, Summit certificate',
 'Embark on the adventure of a lifetime with our Kilimanjaro Summit Expedition to the Roof of Africa. Led by experienced guides, you''ll travel through diverse ecosystems — from lush rainforest to alpine desert — before reaching the snowy summit of Uhuru Peak.\n\nAlong the way you''ll enjoy comfortable campsites, meals prepared by our skilled crew and awe-inspiring views, with carefully planned itineraries designed for a safe and rewarding climb.',
 'kilimanjaro.jpg', '2026-05-01 08:01:08');

-- ---------------------------------------------------------------------------
-- Bookings. status: 0 = pending, 1 = confirmed, 2 = cancelled.
-- CancelledBy: 'u' = customer, 'a' = admin.
-- ---------------------------------------------------------------------------
CREATE TABLE `tblbooking` (
  `BookingId` int(11) NOT NULL AUTO_INCREMENT,
  `PackageId` int(11) NOT NULL,
  `UserEmail` varchar(150) NOT NULL,
  `FromDate` date NOT NULL,
  `ToDate` date NOT NULL,
  `Travellers` smallint(5) unsigned NOT NULL DEFAULT 1,
  `Comment` text DEFAULT NULL,
  `RegDate` timestamp NULL DEFAULT current_timestamp(),
  `status` tinyint(1) NOT NULL DEFAULT 0,
  `CancelledBy` char(1) DEFAULT NULL,
  `UpdationDate` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`BookingId`),
  KEY `idx_booking_email` (`UserEmail`),
  KEY `idx_booking_package` (`PackageId`),
  CONSTRAINT `fk_booking_package` FOREIGN KEY (`PackageId`) REFERENCES `tbltourpackages` (`PackageId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `tblbooking` (`BookingId`, `PackageId`, `UserEmail`, `FromDate`, `ToDate`, `Travellers`, `Comment`, `RegDate`, `status`, `CancelledBy`) VALUES
(1, 3, 'demo@fivestartours.test', '2026-12-08', '2026-12-11', 2, 'We would love a sunrise balloon ride if possible.', '2026-10-01 09:12:00', 1, NULL),
(2, 6, 'demo@fivestartours.test', '2027-02-14', '2027-02-20', 1, 'First time climbing — please share a packing list.', '2026-10-15 18:30:00', 0, NULL),
(3, 1, 'demo@fivestartours.test', '2026-08-20', '2026-08-22', 2, NULL, '2026-07-02 11:05:00', 2, 'u'),
(4, 2, 'james.carter@example.com', '2026-11-05', '2026-11-06', 4, 'Travelling with two children aged 8 and 11.', '2026-10-10 13:45:00', 0, NULL),
(5, 4, 'sofia.rossi@example.com', '2026-11-20', '2026-11-21', 2, 'Vegetarian meals please.', '2026-10-18 16:20:00', 1, NULL);

-- ---------------------------------------------------------------------------
-- Contact-form enquiries. Status: 0 = new, 1 = read.
-- ---------------------------------------------------------------------------
CREATE TABLE `tblenquiry` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `FullName` varchar(100) NOT NULL,
  `EmailId` varchar(150) NOT NULL,
  `MobileNumber` varchar(20) DEFAULT NULL,
  `Subject` varchar(150) NOT NULL,
  `Description` text NOT NULL,
  `PostingDate` timestamp NULL DEFAULT current_timestamp(),
  `Status` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `tblenquiry` (`FullName`, `EmailId`, `MobileNumber`, `Subject`, `Description`, `PostingDate`, `Status`) VALUES
('Liam Chen', 'liam.chen@example.com', '0712000301', 'Group discount', 'Hi, we are a group of 8 colleagues planning a Serengeti trip in March. Do you offer group pricing?', '2026-10-19 08:45:00', 0),
('Grace Mushi', 'grace.mushi@example.com', '0712000302', 'Airport pickup', 'Does the Kilimanjaro package include pickup from Kilimanjaro International Airport?', '2026-10-12 17:10:00', 1);

-- ---------------------------------------------------------------------------
-- One-time password reset tokens (only a SHA-256 hash of the token is stored).
-- ---------------------------------------------------------------------------
CREATE TABLE `tblpassword_resets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `EmailId` varchar(150) NOT NULL,
  `TokenHash` char(64) NOT NULL,
  `ExpiresAt` datetime NOT NULL,
  `CreatedAt` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_reset_token` (`TokenHash`),
  KEY `idx_reset_email` (`EmailId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
