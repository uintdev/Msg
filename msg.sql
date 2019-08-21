-- phpMyAdmin SQL Dump
-- version 4.9.0.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Aug 21, 2019 at 10:19 PM
-- Server version: 10.4.7-MariaDB-1:10.4.7+maria~disco-log
-- PHP Version: 7.3.8-1+ubuntu19.04.1+deb.sury.org+1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `msg`
--

-- --------------------------------------------------------

--
-- Table structure for table `accounts`
--

CREATE TABLE `accounts` (
  `id` int(11) NOT NULL,
  `uid` text CHARACTER SET latin1 COLLATE latin1_general_cs NOT NULL COMMENT 'cookie auth login string',
  `puid` text COLLATE utf32_bin NOT NULL COMMENT 'base user id',
  `email` text COLLATE utf32_bin NOT NULL,
  `username` text CHARACTER SET latin1 COLLATE latin1_general_cs NOT NULL,
  `password` text CHARACTER SET latin1 COLLATE latin1_general_cs NOT NULL,
  `passwhen` int(11) NOT NULL DEFAULT 0 COMMENT 'Time and date of last password set',
  `ip` text COLLATE utf32_bin NOT NULL,
  `useragent` text COLLATE utf32_bin NOT NULL,
  `avatar` text COLLATE utf32_bin NOT NULL,
  `signature` text COLLATE utf32_bin NOT NULL DEFAULT '',
  `time` int(11) NOT NULL,
  `activationkey` text CHARACTER SET latin1 COLLATE latin1_general_cs NOT NULL,
  `activationtime` int(11) NOT NULL DEFAULT 0 COMMENT 'time account activated',
  `regtime` int(11) NOT NULL COMMENT 'time registered',
  `cryptokey` text COLLATE utf32_bin NOT NULL DEFAULT '' COMMENT 'crpyto private key',
  `cryptoiv` text COLLATE utf32_bin NOT NULL DEFAULT '' COMMENT 'crypto iv',
  `cryptoaad` text COLLATE utf32_bin NOT NULL DEFAULT '' COMMENT 'crypto additional auth data'
) ENGINE=InnoDB DEFAULT CHARSET=utf32 COLLATE=utf32_bin ROW_FORMAT=COMPACT;

-- --------------------------------------------------------

--
-- Table structure for table `audit_log`
--

CREATE TABLE `audit_log` (
  `id` int(11) NOT NULL,
  `puid` text COLLATE utf32_bin NOT NULL,
  `action` text COLLATE utf32_bin NOT NULL,
  `reason` text COLLATE utf32_bin NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf32 COLLATE=utf32_bin;

-- --------------------------------------------------------

--
-- Table structure for table `authentication`
--

CREATE TABLE `authentication` (
  `id` int(11) NOT NULL,
  `puid` text COLLATE utf32_bin NOT NULL,
  `token` text COLLATE utf32_bin NOT NULL,
  `last_ip` text COLLATE utf32_bin NOT NULL,
  `created_ip` text COLLATE utf32_bin NOT NULL,
  `last_updated` int(11) NOT NULL,
  `created` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf32 COLLATE=utf32_bin;

-- --------------------------------------------------------

--
-- Table structure for table `block`
--

CREATE TABLE `block` (
  `id` int(11) NOT NULL,
  `whoset` text COLLATE utf32_bin NOT NULL,
  `shieldfrom` text COLLATE utf32_bin NOT NULL,
  `time` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf32 COLLATE=utf32_bin;

-- --------------------------------------------------------

--
-- Table structure for table `contacts`
--

CREATE TABLE `contacts` (
  `id` int(11) NOT NULL,
  `user` text COLLATE utf32_bin NOT NULL,
  `linked` text COLLATE utf32_bin NOT NULL,
  `time` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf32 COLLATE=utf32_bin;

-- --------------------------------------------------------

--
-- Table structure for table `global_privileges`
--

CREATE TABLE `global_privileges` (
  `id` int(11) NOT NULL,
  `name` text COLLATE utf32_bin NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf32 COLLATE=utf32_bin;

--
-- Dumping data for table `global_privileges`
--

INSERT INTO `global_privileges` (`id`, `name`) VALUES
(1, 'Founder'),
(2, 'Administrator'),
(3, 'Moderator');

-- --------------------------------------------------------

--
-- Table structure for table `mail`
--

CREATE TABLE `mail` (
  `id` int(11) NOT NULL,
  `secid` text COLLATE utf32_bin NOT NULL COMMENT 'message id',
  `sender` text COLLATE utf32_bin NOT NULL,
  `recepient` text COLLATE utf32_bin NOT NULL,
  `replyhook` text COLLATE utf32_bin NOT NULL COMMENT 'associate replies with initial thread',
  `cryptopkey` text COLLATE utf32_bin NOT NULL COMMENT 'crypto private key',
  `cryptoiv` text COLLATE utf32_bin NOT NULL COMMENT 'crypto iv',
  `cryptoaad` text COLLATE utf32_bin NOT NULL COMMENT 'crypto additional auth data',
  `subject` text COLLATE utf32_bin NOT NULL,
  `body` text COLLATE utf32_bin NOT NULL,
  `senderead` int(11) NOT NULL,
  `recepientread` int(11) NOT NULL,
  `ip` text COLLATE utf32_bin NOT NULL,
  `time` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf32 COLLATE=utf32_bin;

-- --------------------------------------------------------

--
-- Table structure for table `menu`
--

CREATE TABLE `menu` (
  `id` int(11) NOT NULL,
  `entry_order` int(11) NOT NULL,
  `name` text COLLATE utf32_bin NOT NULL,
  `path` text COLLATE utf32_bin NOT NULL,
  `privs` set('') COLLATE utf32_bin NOT NULL,
  `loggedin` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf32 COLLATE=utf32_bin;

--
-- Dumping data for table `menu`
--

INSERT INTO `menu` (`id`, `entry_order`, `name`, `path`, `privs`, `loggedin`) VALUES
(1, 1, 'Login', '', '', 0),
(2, 1, 'Mail', '', '', 1),
(3, 2, 'Account', 'account', '', 1),
(4, 3, 'About', 'about', '', 2),
(5, 4, 'Logout', 'logout/{AUTH_TOKEN}', '', 1);

-- --------------------------------------------------------

--
-- Table structure for table `pages`
--

CREATE TABLE `pages` (
  `id` int(11) NOT NULL,
  `name` text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `qid` text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `parameters` int(11) NOT NULL,
  `path` text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `permissions` set('none') COLLATE utf32_bin NOT NULL,
  `loginreq` tinyint(1) NOT NULL,
  `main` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf32 COLLATE=utf32_bin;

--
-- Dumping data for table `pages`
--

INSERT INTO `pages` (`id`, `name`, `qid`, `parameters`, `path`, `permissions`, `loginreq`, `main`) VALUES
(1, 'Logout', 'logout', 0, 'modules/logout/logout.php', '', 2, 0),
(2, 'Mail', '', 0, 'modules/mail/mail.php', '', 1, 1),
(3, 'Login', 'login', 0, 'modules/login/login.php', '', 0, 1),
(4, 'About', 'about', 0, 'modules/about/about.php', '', 2, 0),
(5, 'Account', 'account', 0, 'modules/account/account.php', '', 1, 0);

-- --------------------------------------------------------

--
-- Table structure for table `rate_limit`
--

CREATE TABLE `rate_limit` (
  `id` int(11) NOT NULL,
  `user_involved` text COLLATE utf32_bin NOT NULL COMMENT 'private user identifier if required',
  `ip` text COLLATE utf32_bin NOT NULL,
  `type` text COLLATE utf32_bin NOT NULL COMMENT 'feature rate limit seperation',
  `time` int(11) NOT NULL COMMENT 'epoch'
) ENGINE=InnoDB DEFAULT CHARSET=utf32 COLLATE=utf32_bin;

-- --------------------------------------------------------

--
-- Table structure for table `user_privileges`
--

CREATE TABLE `user_privileges` (
  `id` int(11) NOT NULL,
  `pid` int(11) NOT NULL COMMENT 'private id',
  `priv` int(11) NOT NULL COMMENT 'privilege id'
) ENGINE=InnoDB DEFAULT CHARSET=utf32 COLLATE=utf32_bin;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `accounts`
--
ALTER TABLE `accounts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `audit_log`
--
ALTER TABLE `audit_log`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `authentication`
--
ALTER TABLE `authentication`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `block`
--
ALTER TABLE `block`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `contacts`
--
ALTER TABLE `contacts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `global_privileges`
--
ALTER TABLE `global_privileges`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `mail`
--
ALTER TABLE `mail`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `menu`
--
ALTER TABLE `menu`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `pages`
--
ALTER TABLE `pages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `rate_limit`
--
ALTER TABLE `rate_limit`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `user_privileges`
--
ALTER TABLE `user_privileges`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `accounts`
--
ALTER TABLE `accounts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `audit_log`
--
ALTER TABLE `audit_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `authentication`
--
ALTER TABLE `authentication`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `block`
--
ALTER TABLE `block`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `contacts`
--
ALTER TABLE `contacts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `global_privileges`
--
ALTER TABLE `global_privileges`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `mail`
--
ALTER TABLE `mail`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `menu`
--
ALTER TABLE `menu`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `pages`
--
ALTER TABLE `pages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `rate_limit`
--
ALTER TABLE `rate_limit`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user_privileges`
--
ALTER TABLE `user_privileges`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
