-- phpMyAdmin SQL Dump
-- version 5.0.4
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 15, 2021 at 12:09 AM
-- Server version: 10.4.17-MariaDB
-- PHP Version: 7.4.15

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `database`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `id` int(11) NOT NULL,
  `username` varchar(24) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(256) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`id`, `username`, `password`) VALUES
(1, 'admin', '$2y$10$XfwZaq1ywWUOCOGL9WeAc.yHUiaOFgyGu0yyb5dESvMDY0V.0308a');

-- --------------------------------------------------------

--
-- Table structure for table `announcements`
--

CREATE TABLE `announcements` (
  `id` int(11) NOT NULL,
  `title` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `content` text COLLATE utf8_unicode_ci NOT NULL,
  `type` int(11) NOT NULL,
  `duration` date NOT NULL,
  `time` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `blocked`
--

CREATE TABLE `blocked` (
  `id` int(11) NOT NULL,
  `uid` int(11) NOT NULL,
  `by` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `chat`
--

CREATE TABLE `chat` (
  `id` int(11) NOT NULL,
  `from` int(11) NOT NULL,
  `to` int(11) NOT NULL,
  `message` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` varchar(1024) COLLATE utf8mb4_unicode_ci NOT NULL,
  `read` int(11) NOT NULL,
  `time` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `comments`
--

CREATE TABLE `comments` (
  `id` int(11) NOT NULL,
  `uid` int(11) NOT NULL,
  `mid` int(11) NOT NULL,
  `message` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `value` varchar(1024) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `time` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `likes` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `conversations`
--

CREATE TABLE `conversations` (
  `from` int(11) NOT NULL,
  `to` int(11) NOT NULL,
  `read` tinyint(4) NOT NULL,
  `cid` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `dislikes`
--

CREATE TABLE `dislikes` (
  `id` int(11) NOT NULL,
  `post` int(11) NOT NULL,
  `by` int(11) NOT NULL,
  `type` int(11) NOT NULL DEFAULT 0,
  `time` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `friendships`
--

CREATE TABLE `friendships` (
  `id` int(11) NOT NULL,
  `user1` int(11) NOT NULL,
  `user2` int(11) NOT NULL,
  `status` int(11) NOT NULL DEFAULT 0,
  `time` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `Discussions`
--

CREATE TABLE `Discussions` (
  `id` int(11) NOT NULL,
  `name` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `cover` varchar(128) COLLATE utf8mb4_unicode_ci NOT NULL,
  `privacy` int(11) NOT NULL,
  `posts` int(11) NOT NULL,
  `members` int(11) NOT NULL DEFAULT 0,
  `time` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `groups_users`
--

CREATE TABLE `groups_users` (
  `id` int(11) NOT NULL,
  `group` int(11) NOT NULL,
  `user` int(11) NOT NULL,
  `status` int(11) NOT NULL,
  `permissions` int(11) NOT NULL,
  `time` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `info_pages`
--

CREATE TABLE `info_pages` (
  `id` int(11) NOT NULL,
  `title` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `url` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `public` tinyint(4) NOT NULL,
  `content` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `likes`
--

CREATE TABLE `likes` (
  `id` int(11) NOT NULL,
  `post` int(11) NOT NULL,
  `by` int(11) NOT NULL,
  `type` int(11) NOT NULL,
  `time` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `id` int(12) NOT NULL,
  `uid` int(32) NOT NULL,
  `message` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `tag` varchar(256) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `type` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `value` varchar(2048) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `group` int(11) NOT NULL DEFAULT 0,
  `page` int(11) NOT NULL DEFAULT 0,
  `time` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `public` int(11) NOT NULL,
  `likes` int(11) NOT NULL DEFAULT 0,
  `comments` int(11) NOT NULL DEFAULT 0,
  `shares` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `from` int(11) NOT NULL,
  `to` int(11) NOT NULL DEFAULT 0,
  `parent` int(11) NOT NULL DEFAULT 0,
  `child` int(11) NOT NULL DEFAULT 0,
  `type` int(11) NOT NULL,
  `read` int(11) NOT NULL DEFAULT 0,
  `time` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pages`
--

CREATE TABLE `pages` (
  `id` int(11) NOT NULL,
  `by` int(11) NOT NULL,
  `name` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `category` int(3) NOT NULL,
  `description` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `address` varchar(128) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `website` varchar(128) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `phone` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `image` varchar(128) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `cover` varchar(128) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `verified` tinyint(1) NOT NULL DEFAULT 0,
  `likes` int(11) NOT NULL DEFAULT 0,
  `time` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `plugins`
--

CREATE TABLE `plugins` (
  `id` int(11) NOT NULL,
  `name` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `priority` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `plugins`
--

INSERT INTO `plugins` (`id`, `name`, `type`, `priority`) VALUES
(1, 'announcements', '5', 0),
(2, 'dislike', '789', 0),
(3, 'file_share', '189ed', 100),
(4, 'media_share', '189ed', 10),
(5, 'poll', '189de', 100),
(6, 'social_share', '789', 10),
(7, 'url_parser', '18d', 0),
(8, 'video_call', '89ed', 0),
(9, 'weather', '2389', 0),
(10, 'cookie_law', '489', 0);

-- --------------------------------------------------------

--
-- Table structure for table `plugins_settings`
--

CREATE TABLE `plugins_settings` (
  `name` varchar(128) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `plugins_settings`
--

INSERT INTO `plugins_settings` (`name`, `value`) VALUES
('cookie_law_color', 'black'),
('cookie_law_position', '0'),
('cookie_law_url', ''),
('file_share_allowed_extensions', 'zip,7z,rar,txt,pdf,docx,pptx,xlsx,jpg,png,gif,3gp,mp4,flv,mkv,mp3,wav'),
('file_share_max_files', '5'),
('file_share_max_size', '26214400'),
('file_share_max_upload_size', '10485760'),
('media_share_audio', '1'),
('media_share_audio_extensions', 'mp3'),
('media_share_max_size', '26214400'),
('media_share_services', 'youtube,vimeo,twitch,streamable,dailymotion,soundcloud,mixcloud,tunein,spotify,giphy,gfycat'),
('media_share_video', '1'),
('media_share_video_extensions', 'mp4'),
('social_share_services', 'facebook,twitter,pinterest,tumblr,email,vkontakte,reddit,linkedin,whatsapp,viber,digg,evernote,yummly,yahoo,gmail'),
('video_call_call_time', '12'),
('video_call_dial_time', '30'),
('video_call_twilio_account_sid', ''),
('video_call_twilio_key_secret', ''),
('video_call_twilio_key_sid', ''),
('weather_api_key', ''),
('weather_days', '5'),
('weather_default_location', 'New York'),
('weather_format', '0');

-- --------------------------------------------------------

--
-- Table structure for table `polls_answers`
--

CREATE TABLE `polls_answers` (
  `id` int(11) NOT NULL,
  `question` int(11) NOT NULL,
  `answer` varchar(128) COLLATE utf8_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `polls_durations`
--

CREATE TABLE `polls_durations` (
  `poll_id` int(11) NOT NULL,
  `poll_start` int(11) NOT NULL,
  `poll_stop` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `polls_results`
--

CREATE TABLE `polls_results` (
  `id` int(11) NOT NULL,
  `question` int(11) NOT NULL,
  `answer` int(11) NOT NULL,
  `by` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `reports`
--

CREATE TABLE `reports` (
  `id` int(12) NOT NULL,
  `post` varchar(11) COLLATE utf8mb4_unicode_ci NOT NULL,
  `parent` int(11) NOT NULL DEFAULT 0,
  `type` int(11) NOT NULL,
  `by` int(11) NOT NULL,
  `state` int(11) NOT NULL DEFAULT 0,
  `time` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `title` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `theme` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  `perpage` int(11) NOT NULL,
  `censor` varchar(2048) COLLATE utf8mb4_unicode_ci NOT NULL,
  `captcha` int(11) NOT NULL,
  `intervalm` int(11) NOT NULL,
  `intervaln` int(11) NOT NULL,
  `time` int(11) NOT NULL,
  `message` int(11) NOT NULL,
  `size` int(11) NOT NULL,
  `format` varchar(256) COLLATE utf8mb4_unicode_ci NOT NULL,
  `mail` int(11) NOT NULL,
  `sizemsg` int(11) NOT NULL,
  `formatmsg` varchar(256) COLLATE utf8mb4_unicode_ci NOT NULL,
  `cperpage` int(11) NOT NULL,
  `ilimit` int(11) NOT NULL,
  `climit` int(11) NOT NULL,
  `uperpage` int(11) NOT NULL,
  `sperpage` int(11) NOT NULL,
  `nperpage` tinyint(4) NOT NULL,
  `nperwidget` tinyint(4) NOT NULL,
  `aperip` int(11) NOT NULL,
  `conline` int(4) NOT NULL,
  `ronline` tinyint(4) NOT NULL,
  `mperpage` tinyint(4) NOT NULL,
  `verified` int(11) NOT NULL,
  `chatr` int(11) NOT NULL,
  `email_activation` tinyint(4) NOT NULL,
  `email_comment` tinyint(4) NOT NULL,
  `email_like` tinyint(4) NOT NULL,
  `email_new_friend` tinyint(4) NOT NULL,
  `email_group_invite` tinyint(4) NOT NULL,
  `email_page_invite` tinyint(4) NOT NULL,
  `email_mention` tinyint(4) NOT NULL,
  `smiles` tinyint(4) NOT NULL,
  `permalinks` tinyint(4) NOT NULL,
  `fbapp` int(11) NOT NULL,
  `fbappid` varchar(128) COLLATE utf8mb4_unicode_ci NOT NULL,
  `fbappsecret` varchar(128) COLLATE utf8mb4_unicode_ci NOT NULL,
  `smtp_email` int(11) NOT NULL,
  `smtp_host` varchar(128) COLLATE utf8mb4_unicode_ci NOT NULL,
  `smtp_port` int(11) NOT NULL,
  `smtp_secure` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  `smtp_auth` int(11) NOT NULL,
  `smtp_username` varchar(128) COLLATE utf8mb4_unicode_ci NOT NULL,
  `smtp_password` varchar(128) COLLATE utf8mb4_unicode_ci NOT NULL,
  `language` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_provider` varchar(256) COLLATE utf8mb4_unicode_ci NOT NULL,
  `friends_limit` int(11) NOT NULL,
  `pages_limit` int(11) NOT NULL,
  `groups_limit` int(11) NOT NULL,
  `pages` int(11) NOT NULL,
  `Discussions` int(11) NOT NULL,
  `timezone` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ad1` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `ad2` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `ad3` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `ad4` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `ad5` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `ad6` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `ad7` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `tracking_code` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `tos_url` varchar(128) COLLATE utf8mb4_unicode_ci NOT NULL,
  `privacy_url` varchar(128) COLLATE utf8mb4_unicode_ci NOT NULL,
  `lt` varchar(16) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `lk` varchar(128) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`title`, `theme`, `perpage`, `censor`, `captcha`, `intervalm`, `intervaln`, `time`, `message`, `size`, `format`, `mail`, `sizemsg`, `formatmsg`, `cperpage`, `ilimit`, `climit`, `uperpage`, `sperpage`, `nperpage`, `nperwidget`, `aperip`, `conline`, `ronline`, `mperpage`, `verified`, `chatr`, `email_activation`, `email_comment`, `email_like`, `email_new_friend`, `email_group_invite`, `email_page_invite`, `email_mention`, `smiles`, `permalinks`, `fbapp`, `fbappid`, `fbappsecret`, `smtp_email`, `smtp_host`, `smtp_port`, `smtp_secure`, `smtp_auth`, `smtp_username`, `smtp_password`, `language`, `email_provider`, `friends_limit`, `pages_limit`, `groups_limit`, `pages`, `Discussions`, `timezone`, `ad1`, `ad2`, `ad3`, `ad4`, `ad5`, `ad6`, `ad7`, `tracking_code`, `tos_url`, `privacy_url`, `lt`, `lk`) VALUES
('careerPal', 'dolphin', 10, 'word1,word2', 1, 10000, 10000, 0, 500, 20971520, 'png,jpg,gif,jpeg', 1, 20971520, 'png,jpg,gif,jpeg', 3, 9, 500, 10, 10, 100, 10, 3, 600, 10, 10, 0, 3, 0, 1, 1, 1, 1, 1, 1, 1, 0, 0, '', '', 0, '', 0, '0', 0, '', '', 'english', '', 2000, 50, 100, 1, 1, 'Africa/Nairobi', '', '', '', '', '', '', '', '', '', '', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `idu` int(11) NOT NULL,
  `username` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(256) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(256) COLLATE utf8mb4_unicode_ci NOT NULL,
  `first_name` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_name` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  `country` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `location` varchar(128) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `address` varchar(256) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `work` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `school` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `website` varchar(128) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `bio` varchar(160) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `date` date NOT NULL,
  `facebook` varchar(256) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `twitter` varchar(128) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `image` varchar(128) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `private` int(11) NOT NULL DEFAULT 0,
  `suspended` int(11) NOT NULL DEFAULT 0,
  `salted` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `login_token` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cover` varchar(128) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `verified` int(11) NOT NULL DEFAULT 0,
  `privacy` int(11) NOT NULL DEFAULT 1,
  `gender` tinyint(4) NOT NULL DEFAULT 0,
  `interests` tinyint(4) NOT NULL DEFAULT 0,
  `online` int(11) NOT NULL DEFAULT 0,
  `offline` tinyint(4) NOT NULL DEFAULT 0,
  `ip` varchar(45) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `user_group` int(11) NOT NULL DEFAULT 0,
  `notificationl` tinyint(4) NOT NULL,
  `notificationc` tinyint(4) NOT NULL,
  `notifications` tinyint(4) NOT NULL,
  `notificationd` tinyint(4) NOT NULL,
  `notificationf` tinyint(4) NOT NULL,
  `notificationg` tinyint(4) NOT NULL,
  `notificationx` tinyint(4) NOT NULL,
  `notificationp` tinyint(4) NOT NULL,
  `notificationm` tinyint(4) NOT NULL,
  `email_mention` tinyint(4) NOT NULL,
  `email_comment` tinyint(4) NOT NULL,
  `email_like` tinyint(4) NOT NULL,
  `email_new_friend` tinyint(4) NOT NULL,
  `email_group_invite` tinyint(4) NOT NULL,
  `email_page_invite` tinyint(4) NOT NULL,
  `sound_new_notification` tinyint(4) NOT NULL,
  `sound_new_chat` tinyint(4) NOT NULL,
  `born` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `video_calls`
--

CREATE TABLE `video_calls` (
  `id` int(11) NOT NULL,
  `from` int(11) NOT NULL,
  `to` int(11) NOT NULL,
  `type` int(11) NOT NULL,
  `status` int(11) NOT NULL,
  `time` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `announcements`
--
ALTER TABLE `announcements`
  ADD UNIQUE KEY `id` (`id`);

--
-- Indexes for table `blocked`
--
ALTER TABLE `blocked`
  ADD PRIMARY KEY (`id`),
  ADD KEY `block_status` (`uid`,`by`);

--
-- Indexes for table `chat`
--
ALTER TABLE `chat`
  ADD PRIMARY KEY (`id`),
  ADD KEY `unread_chat_messages` (`from`,`to`,`read`),
  ADD KEY `update_chat_status` (`to`,`read`);

--
-- Indexes for table `comments`
--
ALTER TABLE `comments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `mid` (`mid`),
  ADD KEY `uid` (`uid`),
  ADD KEY `admin_stats` (`time`);

--
-- Indexes for table `conversations`
--
ALTER TABLE `conversations`
  ADD KEY `chat_count` (`to`,`read`),
  ADD KEY `chat_notifications_AND_chat_pagination` (`cid`);

--
-- Indexes for table `dislikes`
--
ALTER TABLE `dislikes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `verify_dislike` (`post`,`by`);

--
-- Indexes for table `friendships`
--
ALTER TABLE `friendships`
  ADD PRIMARY KEY (`id`),
  ADD KEY `verify_friendship` (`user1`,`user2`,`status`),
  ADD KEY `user1_count_friends_AND_select_friends` (`user1`,`status`,`id`),
  ADD KEY `user2_count_friends_AND_select_friends` (`user2`,`status`,`id`);

--
-- Indexes for table `Discussions`
--
ALTER TABLE `Discussions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `name` (`name`),
  ADD KEY `admin_stats` (`time`);

--
-- Indexes for table `groups_users`
--
ALTER TABLE `groups_users`
  ADD PRIMARY KEY (`id`),
  ADD KEY `group_user_data` (`group`,`user`),
  ADD KEY `group_members_count` (`group`,`status`,`permissions`),
  ADD KEY `group_requests_blocked` (`group`,`status`,`time`),
  ADD KEY `joined_groups` (`user`,`status`);

--
-- Indexes for table `info_pages`
--
ALTER TABLE `info_pages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `likes`
--
ALTER TABLE `likes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `profile_likes_count` (`by`,`type`),
  ADD KEY `verify_like` (`post`,`by`,`type`),
  ADD KEY `likes_statistics` (`post`,`type`,`time`),
  ADD KEY `admin_stats` (`time`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `public` (`public`),
  ADD KEY `type` (`type`),
  ADD KEY `uid` (`uid`),
  ADD KEY `group` (`group`),
  ADD KEY `page` (`page`),
  ADD KEY `time` (`time`),
  ADD KEY `news_feed` (`uid`,`group`,`page`,`public`),
  ADD KEY `value` (`value`(255));

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `page_activity` (`from`,`type`),
  ADD KEY `notifications_widget` (`to`,`type`,`read`);

--
-- Indexes for table `pages`
--
ALTER TABLE `pages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `page_page` (`name`),
  ADD KEY `page_owner` (`by`),
  ADD KEY `admin_stats` (`time`);

--
-- Indexes for table `plugins`
--
ALTER TABLE `plugins`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `plugins_settings`
--
ALTER TABLE `plugins_settings`
  ADD PRIMARY KEY (`name`);

--
-- Indexes for table `polls_answers`
--
ALTER TABLE `polls_answers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `question` (`question`);

--
-- Indexes for table `polls_durations`
--
ALTER TABLE `polls_durations`
  ADD KEY `poll_id` (`poll_id`);

--
-- Indexes for table `polls_results`
--
ALTER TABLE `polls_results`
  ADD PRIMARY KEY (`id`),
  ADD KEY `question` (`question`),
  ADD KEY `by` (`by`);

--
-- Indexes for table `reports`
--
ALTER TABLE `reports`
  ADD PRIMARY KEY (`id`),
  ADD KEY `state` (`state`),
  ADD KEY `time` (`time`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`idu`),
  ADD KEY `username` (`username`),
  ADD KEY `gender` (`gender`),
  ADD KEY `admin_manage_user_moderators` (`user_group`),
  ADD KEY `admin_manage_user_verified` (`verified`),
  ADD KEY `admin_manage_user_suspended` (`suspended`),
  ADD KEY `user_active` (`idu`,`suspended`),
  ADD KEY `admin_stats_registered` (`date`),
  ADD KEY `admin_stats_online` (`online`);

--
-- Indexes for table `video_calls`
--
ALTER TABLE `video_calls`
  ADD PRIMARY KEY (`id`),
  ADD KEY `to` (`to`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `announcements`
--
ALTER TABLE `announcements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `blocked`
--
ALTER TABLE `blocked`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `chat`
--
ALTER TABLE `chat`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `comments`
--
ALTER TABLE `comments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `dislikes`
--
ALTER TABLE `dislikes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `friendships`
--
ALTER TABLE `friendships`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `Discussions`
--
ALTER TABLE `Discussions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `groups_users`
--
ALTER TABLE `groups_users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `info_pages`
--
ALTER TABLE `info_pages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `likes`
--
ALTER TABLE `likes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(12) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pages`
--
ALTER TABLE `pages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `plugins`
--
ALTER TABLE `plugins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `polls_answers`
--
ALTER TABLE `polls_answers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `polls_results`
--
ALTER TABLE `polls_results`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `reports`
--
ALTER TABLE `reports`
  MODIFY `id` int(12) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `idu` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `video_calls`
--
ALTER TABLE `video_calls`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
