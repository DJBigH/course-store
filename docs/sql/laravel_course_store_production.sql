-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Máy chủ: 127.0.0.1
-- Thời gian đã tạo: Th6 13, 2026 lúc 07:37 AM
-- Phiên bản máy phục vụ: 10.4.32-MariaDB
-- Phiên bản PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Cơ sở dữ liệu: `laravel_course_store_production`
--

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `active_logs`
--

CREATE TABLE `active_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `log_name` varchar(255) DEFAULT NULL,
  `action` varchar(255) NOT NULL,
  `subject_type` varchar(255) DEFAULT NULL,
  `subject_id` bigint(20) UNSIGNED DEFAULT NULL,
  `causer_type` varchar(255) DEFAULT NULL,
  `causer_id` bigint(20) UNSIGNED DEFAULT NULL,
  `properties` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`properties`)),
  `description` varchar(255) DEFAULT NULL,
  `ip` varchar(45) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `admin_session_trackers`
--

CREATE TABLE `admin_session_trackers` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `session_id` varchar(255) NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `browser` varchar(255) DEFAULT NULL,
  `platform` varchar(255) DEFAULT NULL,
  `device` varchar(255) DEFAULT NULL,
  `last_activity` int(10) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `announcements`
--

CREATE TABLE `announcements` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `title_en` varchar(255) DEFAULT NULL,
  `title_ko` varchar(255) DEFAULT NULL,
  `title_ja` varchar(255) DEFAULT NULL,
  `title_zh` varchar(255) DEFAULT NULL,
  `message` text NOT NULL COMMENT 'Short summary for notifications',
  `message_en` text DEFAULT NULL,
  `message_ko` text DEFAULT NULL,
  `message_ja` text DEFAULT NULL,
  `message_zh` text DEFAULT NULL,
  `content` longtext NOT NULL COMMENT 'Full HTML content for detail page',
  `content_en` longtext DEFAULT NULL,
  `content_ko` longtext DEFAULT NULL,
  `content_ja` longtext DEFAULT NULL,
  `content_zh` longtext DEFAULT NULL,
  `target_type` varchar(255) NOT NULL DEFAULT 'all' COMMENT 'all, students, teachers, selected',
  `action_url` varchar(255) DEFAULT NULL,
  `action_label` varchar(255) DEFAULT NULL,
  `action_label_en` varchar(255) DEFAULT NULL,
  `action_label_ko` varchar(255) DEFAULT NULL,
  `action_label_ja` varchar(255) DEFAULT NULL,
  `action_label_zh` varchar(255) DEFAULT NULL,
  `send_email` tinyint(1) NOT NULL DEFAULT 0,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `sent_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `announcement_reads`
--

CREATE TABLE `announcement_reads` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `announcement_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `announcement_user`
--

CREATE TABLE `announcement_user` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `announcement_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `categories`
--

CREATE TABLE `categories` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(200) DEFAULT NULL,
  `name_en` varchar(225) DEFAULT NULL,
  `name_ko` varchar(225) DEFAULT NULL,
  `name_ja` varchar(225) DEFAULT NULL,
  `name_zh` varchar(225) DEFAULT NULL,
  `slug` varchar(200) DEFAULT NULL,
  `slug_en` varchar(225) DEFAULT NULL,
  `slug_ko` varchar(225) DEFAULT NULL,
  `slug_ja` varchar(225) DEFAULT NULL,
  `slug_zh` varchar(225) DEFAULT NULL,
  `parent_id` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `categories`
--

INSERT INTO `categories` (`id`, `name`, `name_en`, `name_ko`, `name_ja`, `name_zh`, `slug`, `slug_en`, `slug_ko`, `slug_ja`, `slug_zh`, `parent_id`, `created_at`, `updated_at`, `deleted_at`) VALUES
(6, 'Front-End', 'Front-End', '프론트엔드', 'フロントエンド', '前端', 'front-end', 'front-end', '프론트엔드', 'フロントエンド', '前端', 0, '2026-02-03 08:33:38', '2026-03-04 13:09:19', NULL),
(7, 'Back-End', 'Back-End', '백엔드', 'バックエンド', '后端', 'back-end', 'back-end', '백엔드', 'バックエンド', '后端', 0, '2026-02-03 08:33:44', '2026-03-04 13:10:01', NULL),
(8, 'App Mobile', 'pp Mobile', '모바일 앱', 'モバイルアプリ', '移动应用', 'app-mobile', 'pp-mobile', '모바일-앱', 'モバイルアプリ', '移动应用', 0, '2026-02-03 08:33:50', '2026-03-04 13:12:31', NULL),
(9, 'Server - DevOps', 'Server - DevOps', '서버 - 데브옵스', 'サーバー - DevOps', '服务器 - DevOps', 'server-devops', 'server-devops', '서버-데브옵스', 'サーバー-devops', '服务器-devops', 0, '2026-02-03 08:34:03', '2026-03-04 13:15:08', NULL),
(10, 'Kali Adams III', NULL, NULL, NULL, NULL, 'quia-dolorem-dolor-id-ratione', NULL, NULL, NULL, NULL, 0, '2026-06-13 04:56:54', '2026-06-13 04:56:54', NULL),
(11, 'Tyree McKenzie III', NULL, NULL, NULL, NULL, 'at-voluptatem-est-sed-placeat-odit-non', NULL, NULL, NULL, NULL, 0, '2026-06-13 04:56:54', '2026-06-13 04:56:54', NULL),
(12, 'Sven Parker', NULL, NULL, NULL, NULL, 'itaque-aut-harum-nesciunt-aut-distinctio', NULL, NULL, NULL, NULL, 0, '2026-06-13 04:56:54', '2026-06-13 04:56:54', NULL),
(13, 'Kathryn Eichmann PhD', NULL, NULL, NULL, NULL, 'cumque-beatae-facere-perferendis-commodi-accusantium', NULL, NULL, NULL, NULL, 0, '2026-06-13 04:56:54', '2026-06-13 04:56:54', NULL),
(14, 'Dr. Audrey Kirlin', NULL, NULL, NULL, NULL, 'et-facere-quia-nam-ipsam-reprehenderit-eos-omnis', NULL, NULL, NULL, NULL, 0, '2026-06-13 04:56:54', '2026-06-13 04:56:54', NULL),
(15, 'Kendra Jones', NULL, NULL, NULL, NULL, 'repellat-non-laudantium-est-ea-eos', NULL, NULL, NULL, NULL, 0, '2026-06-13 04:57:07', '2026-06-13 04:57:07', NULL),
(16, 'Lucio Reichert', NULL, NULL, NULL, NULL, 'vitae-cupiditate-ut-sapiente-voluptatem-sequi', NULL, NULL, NULL, NULL, 0, '2026-06-13 04:57:07', '2026-06-13 04:57:07', NULL),
(17, 'Dr. Marilyne Gerlach I', NULL, NULL, NULL, NULL, 'voluptatem-ea-et-rerum-temporibus', NULL, NULL, NULL, NULL, 0, '2026-06-13 04:57:07', '2026-06-13 04:57:07', NULL),
(18, 'Marc Kertzmann MD', NULL, NULL, NULL, NULL, 'maiores-nihil-dicta-aut-inventore-voluptatem-beatae-qui', NULL, NULL, NULL, NULL, 0, '2026-06-13 04:57:07', '2026-06-13 04:57:07', NULL),
(19, 'Mr. Adolphus Boyle', NULL, NULL, NULL, NULL, 'cumque-facere-qui-qui-totam-nam-quis-praesentium', NULL, NULL, NULL, NULL, 0, '2026-06-13 04:57:07', '2026-06-13 04:57:07', NULL);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `categories_courses`
--

CREATE TABLE `categories_courses` (
  `id` int(10) UNSIGNED NOT NULL,
  `category_id` int(10) UNSIGNED NOT NULL,
  `courses_id` int(10) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `categories_courses`
--

INSERT INTO `categories_courses` (`id`, `category_id`, `courses_id`, `created_at`, `updated_at`) VALUES
(12, 6, 10, '2026-03-29 15:09:27', '2026-03-29 15:09:27'),
(13, 6, 11, '2026-03-29 15:09:13', '2026-03-29 15:09:13'),
(14, 6, 12, '2026-03-29 15:08:40', '2026-03-29 15:08:40'),
(15, 6, 13, '2026-03-29 15:08:15', '2026-03-29 15:08:15'),
(16, 6, 14, '2026-03-29 15:07:54', '2026-03-29 15:07:54'),
(17, 7, 15, '2026-03-29 15:07:39', '2026-03-29 15:07:39'),
(18, 7, 16, '2026-03-29 15:07:27', '2026-03-29 15:07:27'),
(19, 7, 17, '2026-03-29 15:07:13', '2026-03-29 15:07:13'),
(20, 7, 18, '2026-03-29 15:06:49', '2026-03-29 15:06:49'),
(21, 7, 19, '2026-03-29 15:06:37', '2026-03-29 15:06:37'),
(22, 7, 20, '2026-03-29 15:06:22', '2026-03-29 15:06:22'),
(23, 7, 21, '2026-03-29 15:05:58', '2026-03-29 15:05:58'),
(24, 7, 22, '2026-03-29 15:05:41', '2026-03-29 15:05:41'),
(25, 8, 23, '2026-03-29 15:05:26', '2026-03-29 15:05:26'),
(26, 9, 24, '2026-03-29 15:04:53', '2026-03-29 15:04:53'),
(27, 9, 25, '2026-03-29 15:03:56', '2026-03-29 15:03:56'),
(28, 9, 26, '2026-03-29 15:03:39', '2026-03-29 15:03:39'),
(33, 6, 30, '2026-04-03 05:09:16', '2026-04-03 05:09:16'),
(34, 6, 31, '2026-05-14 06:26:09', '2026-05-14 06:26:09'),
(36, 6, 33, '2026-05-05 17:08:29', '2026-05-05 17:08:29'),
(37, 6, 34, '2026-05-13 06:33:24', '2026-05-13 06:33:24'),
(38, 6, 35, '2026-05-14 07:12:53', '2026-05-14 07:12:53');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `chatbot_knowledge`
--

CREATE TABLE `chatbot_knowledge` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `question` varchar(255) NOT NULL,
  `keywords` text DEFAULT NULL,
  `answer` longtext NOT NULL,
  `priority` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `chatbot_knowledge`
--

INSERT INTO `chatbot_knowledge` (`id`, `question`, `keywords`, `answer`, `priority`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Tôi muốn làm giáo viên', 'giáo viên, teacher, giảng dạy', '🎓 Đăng ký trở thành giảng viên tại BigK Udemy\r\n\r\nChào bạn,\r\nCảm ơn bạn đã quan tâm đến việc trở thành giảng viên trên nền tảng của chúng tôi 🙌\r\n\r\nBạn có thể đăng ký tại đây:\r\n👉 http://127.0.0.1:8000/vi/tro-thanh-giang-vien\r\n\r\n📦 Các gói dành cho giảng viên:\r\n\r\nGói Free (Trải nghiệm):\r\nCho phép bạn bắt đầu miễn phí, làm quen với hệ thống và đăng tải khóa học cơ bản.\r\nGói nâng cao:\r\nMở rộng nhiều tính năng hơn như hỗ trợ marketing, ưu tiên hiển thị, công cụ quản lý chuyên sâu,...\r\n\r\n⚠️ Lưu ý quan trọng:\r\nĐể đảm bảo chất lượng giảng dạy trên nền tảng, chúng tôi có quy trình kiểm duyệt đầu vào khá nghiêm ngặt.\r\nVì vậy, bạn vui lòng chuẩn bị đầy đủ:\r\n\r\nThông tin cá nhân / hồ sơ chuyên môn\r\nKinh nghiệm giảng dạy / làm việc\r\nNội dung khóa học dự kiến\r\n\r\nNếu cần hỗ trợ thêm trong quá trình đăng ký, bạn có thể liên hệ đội ngũ hỗ trợ của chúng tôi bất cứ lúc nào 😊', 0, 1, '2026-04-02 15:15:05', '2026-04-02 15:15:05');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `chatbot_unresolved_questions`
--

CREATE TABLE `chatbot_unresolved_questions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `message` text NOT NULL,
  `normalized_message` varchar(191) DEFAULT NULL,
  `resolved_message` text DEFAULT NULL,
  `intent_tags` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`intent_tags`)),
  `source` varchar(50) DEFAULT NULL,
  `fallback_reason` varchar(100) DEFAULT NULL,
  `student_id` int(10) UNSIGNED DEFAULT NULL,
  `knowledge_id` bigint(20) UNSIGNED DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'pending',
  `hit_count` int(10) UNSIGNED NOT NULL DEFAULT 1,
  `last_asked_at` timestamp NULL DEFAULT NULL,
  `resolved_at` timestamp NULL DEFAULT NULL,
  `locale` varchar(10) DEFAULT NULL,
  `ip` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `chatbot_unresolved_questions`
--

INSERT INTO `chatbot_unresolved_questions` (`id`, `message`, `normalized_message`, `resolved_message`, `intent_tags`, `source`, `fallback_reason`, `student_id`, `knowledge_id`, `status`, `hit_count`, `last_asked_at`, `resolved_at`, `locale`, `ip`, `user_agent`, `created_at`, `updated_at`) VALUES
(1, 'chúc ngủ ngon', 'chuc ngu ngon', 'chúc ngủ ngon', '[\"quan t\\u00e2m khuy\\u1ebfn m\\u00e3i\"]', 'local', 'local_rule_bot', NULL, NULL, 'handled', 1, '2026-03-29 10:37:34', '2026-03-31 02:38:43', 'vi', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-03-29 10:37:34', '2026-03-31 02:38:43'),
(2, 'BIGK50', 'bigk50', 'BIGK50', '[\"quan t\\u00e2m khuy\\u1ebfn m\\u00e3i\"]', 'local', 'local_rule_bot', NULL, NULL, 'pending', 1, '2026-05-06 08:52:47', NULL, 'vi', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-05-06 08:52:47', '2026-05-06 08:52:47'),
(3, '123', '123', '123', '[]', 'local', 'local_rule_bot', 13, NULL, 'pending', 1, '2026-05-27 03:09:27', NULL, 'vi', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-27 03:09:27', '2026-05-27 03:09:27');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `contacts`
--

CREATE TABLE `contacts` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `subject` varchar(150) DEFAULT NULL,
  `submission_type` varchar(30) NOT NULL DEFAULT 'contact',
  `category` varchar(50) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `admin_note` text DEFAULT NULL,
  `status` tinyint(4) NOT NULL DEFAULT 0,
  `workflow_status` varchar(30) NOT NULL DEFAULT 'new',
  `source` varchar(30) NOT NULL DEFAULT 'public',
  `page_url` varchar(500) DEFAULT NULL,
  `student_id` int(10) UNSIGNED DEFAULT NULL,
  `teacher_id` int(10) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `contacts`
--

INSERT INTO `contacts` (`id`, `name`, `phone`, `email`, `subject`, `submission_type`, `category`, `message`, `admin_note`, `status`, `workflow_status`, `source`, `page_url`, `student_id`, `teacher_id`, `created_at`, `updated_at`, `deleted_at`) VALUES
(2, 'BigK', '0984573853', 'khanhbeotixiu9x@gmail.com', NULL, 'contact', NULL, 'Why was my account banned?', NULL, 1, 'new', 'public', NULL, NULL, NULL, '2026-02-24 14:39:25', '2026-02-24 14:39:58', NULL),
(3, 'BigK', '09845738533', 'khanhbeotixiu9x@gmail.com', '123', 'report', 'ui_ux', '123', NULL, 0, 'new', 'teacher_portal', 'http://127.0.0.1:8000/teacher/gop-y-bao-cao', 1, 15, '2026-04-08 16:27:25', '2026-04-08 16:27:25', NULL),
(4, 'BigK', '09845738533', 'khanhbeotixiu9x@gmail.com', '<p>IT TEST</p>', 'feedback', 'ui_ux', '<p>IT TEST</p>', NULL, 0, 'new', 'teacher_portal', 'http://127.0.0.1:8000/teacher/gop-y-bao-cao', 1, 15, '2026-04-23 07:36:27', '2026-04-23 07:36:27', NULL),
(5, 'BigK', '09845738533', 'khanhbeotixiu9x@gmail.com', '<p>Class \\&quot;App\\\\Models\\\\User\\&quot; not found</p>', 'feedback', 'feature_request', '<p>Class \\&quot;App\\\\Models\\\\User\\&quot; not found</p>', NULL, 0, 'new', 'teacher_portal', 'http://127.0.0.1:8000/teacher/gop-y-bao-cao', 1, 15, '2026-04-23 07:38:15', '2026-04-23 07:38:15', NULL),
(6, 'BigK', '09845738533', 'khanhbeotixiu9x@gmail.com', '<p>gop-y-bao-cao:2443 &nbsp;POST http://127.0.0.1:8000/teacher/gop-y-bao-cao 500 (Internal Server Error)</p>', 'feedback', 'feature_request', '<p>gop-y-bao-cao:2443 &nbsp;POST http://127.0.0.1:8000/teacher/gop-y-bao-cao 500 (Internal Server Error)</p>', NULL, 0, 'new', 'teacher_portal', 'http://127.0.0.1:8000/teacher/gop-y-bao-cao', 1, 15, '2026-04-23 07:39:05', '2026-04-23 07:39:05', NULL),
(7, 'IT TEST', '03123456778', 'khanhbeotixiu8@gmail.com', '123', 'contact', 'general_contact', '123', NULL, 0, 'new', 'student_portal', 'http://127.0.0.1:8000/vi/lien-he', 13, NULL, '2026-05-09 07:04:53', '2026-05-09 07:04:53', NULL);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `coupons`
--

CREATE TABLE `coupons` (
  `id` int(10) UNSIGNED NOT NULL,
  `teacher_id` int(10) UNSIGNED DEFAULT NULL,
  `created_by` int(10) UNSIGNED DEFAULT NULL,
  `code` varchar(100) NOT NULL,
  `discount_type` enum('percent','value') NOT NULL DEFAULT 'percent',
  `discount_value` int(11) NOT NULL DEFAULT 0,
  `total_condition` int(11) DEFAULT NULL,
  `count` int(11) DEFAULT NULL,
  `per_student_once` tinyint(1) NOT NULL DEFAULT 0,
  `start_date` timestamp NULL DEFAULT NULL,
  `end_date` timestamp NULL DEFAULT NULL,
  `package_locked_at` timestamp NULL DEFAULT NULL,
  `package_lock_reason` varchar(50) DEFAULT NULL,
  `is_package_priority` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `coupons_courses`
--

CREATE TABLE `coupons_courses` (
  `id` int(10) UNSIGNED NOT NULL,
  `coupon_id` int(10) UNSIGNED DEFAULT NULL,
  `course_id` int(10) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `coupons_students`
--

CREATE TABLE `coupons_students` (
  `id` int(10) UNSIGNED NOT NULL,
  `coupon_id` int(10) UNSIGNED DEFAULT NULL,
  `student_id` int(10) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `coupons_teacher_course_bundles`
--

CREATE TABLE `coupons_teacher_course_bundles` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `bundle_id` bigint(20) UNSIGNED NOT NULL,
  `coupon_id` int(10) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `coupons_usage`
--

CREATE TABLE `coupons_usage` (
  `id` int(10) UNSIGNED NOT NULL,
  `coupon_id` int(10) UNSIGNED DEFAULT NULL,
  `order_id` int(10) UNSIGNED DEFAULT NULL,
  `student_id` int(11) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `courses`
--

CREATE TABLE `courses` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(225) DEFAULT NULL,
  `name_en` varchar(225) DEFAULT NULL,
  `name_ko` varchar(225) DEFAULT NULL,
  `name_ja` varchar(225) DEFAULT NULL,
  `name_zh` varchar(225) DEFAULT NULL,
  `slug` varchar(225) DEFAULT NULL,
  `slug_en` varchar(225) DEFAULT NULL,
  `slug_ko` varchar(225) DEFAULT NULL,
  `slug_ja` varchar(225) DEFAULT NULL,
  `slug_zh` varchar(225) DEFAULT NULL,
  `detail` text DEFAULT NULL,
  `detail_en` text DEFAULT NULL,
  `detail_ko` text DEFAULT NULL,
  `detail_ja` text DEFAULT NULL,
  `detail_zh` text DEFAULT NULL,
  `teacher_id` int(10) UNSIGNED NOT NULL,
  `thumbnail` varchar(255) DEFAULT NULL,
  `price` double(15,2) NOT NULL DEFAULT 0.00,
  `quantity` int(11) DEFAULT NULL,
  `sale_price` double(15,2) NOT NULL DEFAULT 0.00,
  `price_en` decimal(15,2) DEFAULT NULL,
  `sale_price_en` decimal(15,2) DEFAULT NULL,
  `price_ko` decimal(20,2) DEFAULT NULL,
  `sale_price_ko` decimal(20,2) DEFAULT NULL,
  `price_ja` decimal(20,2) DEFAULT NULL,
  `sale_price_ja` decimal(20,2) DEFAULT NULL,
  `price_zh` decimal(20,2) DEFAULT NULL,
  `sale_price_zh` decimal(20,2) DEFAULT NULL,
  `code` varchar(100) DEFAULT NULL,
  `durations` double(8,2) NOT NULL DEFAULT 0.00,
  `is_document` tinyint(1) NOT NULL DEFAULT 0,
  `supports` text DEFAULT NULL,
  `supports_en` text DEFAULT NULL,
  `supports_ko` text DEFAULT NULL,
  `supports_ja` text DEFAULT NULL,
  `supports_zh` text DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 0,
  `completion_condition` varchar(50) NOT NULL DEFAULT 'all_lessons' COMMENT 'none, all_lessons, all_quizzes, all',
  `is_coming_soon` tinyint(1) NOT NULL DEFAULT 0,
  `coming_soon_start_at` timestamp NULL DEFAULT NULL,
  `end_at` timestamp NULL DEFAULT NULL,
  `package_locked_at` timestamp NULL DEFAULT NULL,
  `package_lock_reason` varchar(50) DEFAULT NULL,
  `is_package_priority` tinyint(1) NOT NULL DEFAULT 0,
  `is_learning_locked` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Chan truy cap bai hoc ke ca voi hoc vien da mua',
  `view` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `courses`
--

INSERT INTO `courses` (`id`, `name`, `name_en`, `name_ko`, `name_ja`, `name_zh`, `slug`, `slug_en`, `slug_ko`, `slug_ja`, `slug_zh`, `detail`, `detail_en`, `detail_ko`, `detail_ja`, `detail_zh`, `teacher_id`, `thumbnail`, `price`, `quantity`, `sale_price`, `price_en`, `sale_price_en`, `price_ko`, `sale_price_ko`, `price_ja`, `sale_price_ja`, `price_zh`, `sale_price_zh`, `code`, `durations`, `is_document`, `supports`, `supports_en`, `supports_ko`, `supports_ja`, `supports_zh`, `status`, `completion_condition`, `is_coming_soon`, `coming_soon_start_at`, `end_at`, `package_locked_at`, `package_lock_reason`, `is_package_priority`, `is_learning_locked`, `view`, `created_at`, `updated_at`, `deleted_at`) VALUES
(10, 'HTML - CSS dành cho người mới bắt đầu', 'HTML & CSS for Beginners', '초보자를 위한 HTML & CSS', '初心者向け HTML・CSS', '面向初学者的 HTML 与 CSS', 'html-css-danh-cho-nguoi-moi-bat-dau', 'html-css-for-beginners', '초보자를-위한-html-css', '初心者向け-htmlcss', '面向初学者的-html-与-css', '<p>Kho&aacute; học n&agrave;y sẽ gi&uacute;p bạn nắm vững kiến thức HTML - CSS từ cơ bản đến n&acirc;ng cao. Đặc biệt kho&aacute; học n&agrave;y rất ph&ugrave; hợp với những người mới tiếp cận với lập tr&igrave;nh web n&oacute;i chung, lập tr&igrave;nh Front-End n&oacute;i ri&ecirc;ng.</p>\r\n\r\n<p><strong>Bạn nhận được g&igrave; trong kho&aacute; học?</strong></p>\r\n\r\n<ul>\r\n	<li>Hiểu được c&aacute;ch website hoạt động, c&aacute;c th&agrave;nh phần cấu tạo của website</li>\r\n	<li>Tự tay x&acirc;y dựng được giao diện website bằng ng&ocirc;n ngữ HTML - CSS</li>\r\n	<li>Hiểu được c&aacute;ch ph&acirc;n t&iacute;ch, x&acirc;y dựng giao diện website từ d&ograve;ng code đầu ti&ecirc;n</li>\r\n	<li>Tự tay x&acirc;y dựng được giao diện website tương th&iacute;ch với c&aacute;c nền tảng thiết bị (Responsive Web Design)</li>\r\n	<li>C&oacute; lu&ocirc;n sản phẩm thực tế&nbsp;sau khi ho&agrave;n th&agrave;nh kho&aacute; học</li>\r\n</ul>\r\n\r\n<p><strong>Học thử miễn ph&iacute; tr&ecirc;n Youtube</strong>:</p>\r\n\r\n<p><strong>Sản phẩm dự &aacute;n trong kh&oacute;a học:</strong></p>\r\n\r\n<p><strong>C&acirc;u hỏi thường gặp</strong></p>\r\n\r\n<p><strong><em>T&ocirc;i chưa biết g&igrave; về web c&oacute; học được kh&ocirc;ng?</em></strong></p>\r\n\r\n<p>Tất nhi&ecirc;n rồi, kh&oacute;a học n&agrave;y d&agrave;nh cho người mới tiếp cận với lập tr&igrave;nh web</p>\r\n\r\n<p><em><strong>Trong qu&aacute; tr&igrave;nh học, t&ocirc;i c&oacute; được hỏi giảng vi&ecirc;n kh&ocirc;ng?</strong></em></p>\r\n\r\n<p>Được, nếu gặp kh&oacute; khăn bạn c&oacute; thể nhắn tin trực tiếp cho giảng vi&ecirc;n qua Zalo hoặc tr&ecirc;n group được tham gia</p>\r\n\r\n<p><em><strong>T&ocirc;i cần c&agrave;i đặt phần mềm g&igrave; để học?</strong></em></p>\r\n\r\n<p>Bạn cần c&oacute; phần mềm soạn thảo code: Visual Studio Code, Sublime Text... v&agrave; phần mềm tạo server ảo: xampp, ampps, live server extension,...</p>\r\n\r\n<p><em><strong>Trong b&agrave;i giảng bạn d&ugrave;ng phần mềm n&agrave;o để hướng dẫn học vi&ecirc;n?</strong></em></p>\r\n\r\n<p>Trong c&aacute;c b&agrave;i giảng đầu, t&ocirc;i đang d&ugrave;ng Sublime Text kết hợp với Ampps, c&aacute;c b&agrave;i giảng sau t&ocirc;i sử dụng Visual Studio Code kết hợp với LiveServer Extension</p>\r\n\r\n<p><em><strong>Kh&oacute;a học n&agrave;y c&ograve;n ph&ugrave; hợp với thời điểm hiện tại kh&ocirc;ng?</strong></em></p>\r\n\r\n<p>C&oacute;. HTML-CSS l&agrave; kiến thức nền n&ecirc;n kh&oacute; bị lỗi thời theo thời gian, bạn cứ y&ecirc;n t&acirc;m học</p>\r\n\r\n<p><strong>Lưu &yacute;: Ph&aacute;t &acirc;m Tiếng Anh của giảng vi&ecirc;n kh&ocirc;ng được chuẩn, mong c&aacute;c bạn th&ocirc;ng cảm</strong></p>', '<p>This course will help you master HTML and CSS knowledge from basic to advanced levels. It&#39;s especially suitable for those new to web programming in general, and front-end programming in particular.</p>\r\n\r\n<p>What will you gain from this course?</p>\r\n\r\n<p>Understanding how websites work and their components<br />\r\nBuilding your own website interface using HTML and CSS<br />\r\nUnderstanding how to analyze and build a website interface from scratch<br />\r\nBuilding a website interface compatible with various device platforms (Responsive Web Design)<br />\r\nA real-world product upon completion of the course<br />\r\nFree trial on YouTube:</p>\r\n\r\n<p>Project examples from the course:</p>\r\n\r\n<p>Frequently Asked Questions</p>\r\n\r\n<p>Can I take this course even if I have no prior web knowledge?</p>\r\n\r\n<p>Absolutely! This course is designed for beginners in web programming.</p>\r\n\r\n<p>Can I ask questions to the instructor during the course?</p>\r\n\r\n<p>Yes, if you encounter any difficulties, you can message the instructor directly via Zalo or in the group you&#39;re in.</p>\r\n\r\n<p>What software do I need to install to study?</p>\r\n\r\n<p>You need code editors: Visual Studio Code, Sublime Text... and virtual server software: XAMPP, AMPPS, Live Server Extension,...</p>\r\n\r\n<p>What software do you use to guide students in your lectures?</p>\r\n\r\n<p>In the initial lectures, I&#39;m using Sublime Text combined with AMPPS; in later lectures, I&#39;ll use Visual Studio Code combined with the LiveServer Extension.</p>\r\n\r\n<p>Is this course still relevant today?</p>\r\n\r\n<p>Yes. HTML-CSS is fundamental knowledge, so it&#39;s unlikely to become outdated over time. You can rest assured about learning.</p>\r\n\r\n<p>Note: The instructor&#39;s English pronunciation isn&#39;t perfect; please bear with me.</p>', '<p>이 강좌는 HTML과 CSS의 기초부터 고급까지 탄탄한 지식을 쌓을 수 있도록 도와줍니다. 특히 웹 프로그래밍, 그중에서도 프론트엔드 프로그래밍을 처음 접하는 분들에게 적합합니다.</p>\r\n\r\n<p>이 강좌를 통해 무엇을 얻을 수 있을까요?</p>\r\n\r\n<p>웹사이트의 작동 원리와 구성 요소 이해<br />\r\nHTML과 CSS를 활용한 나만의 웹사이트 인터페이스 구축<br />\r\n웹사이트 인터페이스를 분석하고 처음부터 구축하는 방법 이해<br />\r\n다양한 기기 플랫폼에 호환되는 웹사이트 인터페이스 구축 (반응형 웹 디자인)<br />\r\n강좌 수료 후 실제 사용 가능한 결과물 제공<br />\r\nYouTube 무료 체험:</p>\r\n\r\n<p>강좌 내 프로젝트 예시:</p>\r\n\r\n<p>자주 묻는 질문</p>\r\n\r\n<p>웹 프로그래밍 경험이 전혀 없어도 수강할 수 있나요?</p>\r\n\r\n<p>네, 물론입니다! 이 강좌는 웹 프로그래밍 초보자를 위해 설계되었습니다.</p>\r\n\r\n<p>강좌 중에 강사에게 질문할 수 있나요?</p>\r\n\r\n<p>네, 어려움이 있을 경우 Zalo 또는 그룹 채팅을 통해 강사에게 직접 메시지를 보낼 수 있습니다.</p>\r\n\r\n<p>수업을 위해 어떤 소프트웨어를 설치해야 하나요?</p>\r\n\r\n<p>코드 편집기(Visual Studio Code, Sublime Text 등)와 가상 서버 소프트웨어(XAMPP, AMPPS, Live Server Extension 등)가 필요합니다.</p>\r\n\r\n<p>강의에서는 어떤 소프트웨어를 사용하시나요?</p>\r\n\r\n<p>초반 강의에서는 Sublime Text와 AMPPS를 함께 사용하고, 후반 강의에서는 Visual Studio Code와 LiveServer Extension을 사용할 예정입니다.</p>\r\n\r\n<p>이 강좌는 지금도 여전히 유용한가요?</p>\r\n\r\n<p>네. HTML-CSS는 기본 지식이기 때문에 시간이 지나도 쓸모없어질 가능성은 낮습니다. 안심하고 배우셔도 됩니다.</p>\r\n\r\n<p>참고: 강사의 영어 발음이 완벽하지 않을 수 있습니다. 양해 부탁드립니다.</p>', '<p>このコースでは、HTMLとCSSの基礎から上級レベルまでを習得できます。特に、Webプログラミング全般、特にフロントエンドプログラミングを初めて学ぶ方に最適です。</p>\r\n\r\n<p>このコースで得られるもの</p>\r\n\r\n<p>ウェブサイトの仕組みと構成要素の理解</p>\r\n\r\n<p>HTMLとCSSを用いた独自のウェブサイトインターフェースの構築</p>\r\n\r\n<p>ウェブサイトインターフェースをゼロから分析・構築する方法の理解</p>\r\n\r\n<p>様々なデバイスプラットフォームに対応したウェブサイトインターフェースの構築（レスポンシブWebデザイン）<br />\r\nコース修了後には、実際に使える製品が完成<br />\r\nYouTubeでの無料トライアル：</p>\r\n\r\n<p>コースのプロジェクト例：</p>\r\n\r\n<p>よくある質問</p>\r\n\r\n<p>Webに関する知識が全くなくても、このコースを受講できますか？</p>\r\n\r\n<p>もちろんです！このコースはWebプログラミング初心者向けに設計されています。</p>\r\n\r\n<p>コース中に講師に質問することはできますか？</p>\r\n\r\n<p>はい。何か困ったことがあれば、Zaloまたは参加しているグループで講師に直接メッセージを送信できます。</p>\r\n\r\n<p>受講に必要なソフトウェアは？</p>\r\n\r\n<p>コードエディタ（Visual Studio Code、Sublime Textなど）と仮想サーバーソフトウェア（XAMPP、AMPPS、Live Server Extensionなど）が必要です。</p>\r\n\r\n<p>講義ではどのようなソフトウェアを使って学生を指導していますか？</p>\r\n\r\n<p>最初の講義ではSublime TextとAMPPSを組み合わせて使用​​し、後半の講義ではVisual Studio CodeとLiveServer Extensionを組み合わせて使用​​します。</p>\r\n\r\n<p>このコースは今でも役立ちますか？</p>\r\n\r\n<p>はい。HTMLとCSSは基礎知識なので、時間の経過とともに古くなる可能性は低いです。安心して学習できます。</p>\r\n\r\n<p>注：講師の英語の発音は完璧ではありません。ご容赦ください。</p>', '<p>本课程将帮助您从基础到高级掌握 HTML 和 CSS 知识。它尤其适合 Web 编程新手，特别是前端编程初学者。</p>\r\n\r\n<p>您将从本课程中获得什么？</p>\r\n\r\n<p>了解网站的工作原理及其组成部分</p>\r\n\r\n<p>使用 HTML 和 CSS 构建您自己的网站界面</p>\r\n\r\n<p>了解如何从零开始分析和构建网站界面</p>\r\n\r\n<p>构建与各种设备平台兼容的网站界面（响应式网页设计）</p>\r\n\r\n<p>完成课程后，您将获得一个实际项目</p>\r\n\r\n<p>YouTube 免费试听：</p>\r\n\r\n<p>课程项目示例：</p>\r\n\r\n<p>常见问题解答</p>\r\n\r\n<p>即使我没有任何 Web 编程基础，也可以参加本课程吗？</p>\r\n\r\n<p>当然可以！本课程专为 Web 编程初学者设计。</p>\r\n\r\n<p>我可以在课程期间向讲师提问吗？</p>\r\n\r\n<p>可以，如果您遇到任何困难，可以通过 Zalo 或您所在的群组直接联系讲师。</p>\r\n\r\n<p>我需要安装哪些软件才能学习？</p>\r\n\r\n<p>你需要代码编辑器：Visual Studio Code、Sublime Text&hellip;&hellip;以及虚拟服务器软件：XAMPP、AMPPS、Live Server Extension&hellip;&hellip;</p>\r\n\r\n<p>你在课堂上用什么软件来指导学生？</p>\r\n\r\n<p>在最初的几节课中，我使用 Sublime Text 搭配 AMPPS；在后面的课程中，我会使用 Visual Studio Code 搭配 LiveServer Extension。</p>\r\n\r\n<p>这门课程现在还适用吗？</p>\r\n\r\n<p>是的。HTML-CSS 是基础知识，所以不太可能过时。你可以放心学习。</p>\r\n\r\n<p>注：讲师的英语发音不太完美，请见谅。</p>', 6, '/storage/photos/2/003611c3-4523-4dbd-9d70-0463f9127e48.jpg', 795000.00, NULL, 695000.00, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'KH241021', 1221.00, 0, '<p>Học tr&ecirc;n mọi thiết bị Code mẫu,<br />\r\nT&agrave;i liệu đầy đủ<br />\r\nHỗ trợ 1-1 bởi giảng vi&ecirc;n,<br />\r\nNh&oacute;m k&iacute;n<br />\r\nGiới thiệu c&ocirc;ng việc ph&ugrave; hợp<br />\r\nThời hạn: Vĩnh viễn</p>', '<p>Learn on any device</p>\r\n\r\n<p>Sample code, complete documentation</p>\r\n\r\n<p>One-on-one support by instructors, private group</p>\r\n\r\n<p>Job placement assistance</p>\r\n\r\n<p>Duration: Lifetime</p>', '<p>모든 기기에서 학습 가능</p>\r\n\r\n<p>すべてのデバイスで学習可能</p>\r\n\r\n<p>예제 코드 및 완전한 학습 자료 제공</p>\r\n\r\n<p>강사의 1:1 지원 및 전용 커뮤니티</p>\r\n\r\n<p>역량에 맞는 취업 기회 제공</p>\r\n\r\n<p>평생 이용 가능</p>', '<p>すべてのデバイスで学習可能</p>\r\n\r\n<p>サンプルコードと完全な資料付き</p>\r\n\r\n<p>講師による1対1サポートと専用コミュニティ</p>\r\n\r\n<p>スキルに合った就職機会の紹介</p>\r\n\r\n<p>無期限アクセス</p>', '<p>支持在所有设备上学习</p>\r\n\r\n<p>提供示例代码和完整学习资料</p>\r\n\r\n<p>讲师一对一指导及专属学习社群</p>\r\n\r\n<p>推荐适合的工作机会</p>\r\n\r\n<p>永久访问</p>', 1, 'all_lessons', 0, NULL, NULL, NULL, NULL, 0, 0, 22, '2026-02-03 08:42:30', '2026-05-27 07:56:13', NULL),
(11, 'JavaScript từ cơ bản đến nâng cao', 'JavaScript from basic to advanced', 'JavaScript 기초부터 고급까지', 'JavaScript 基礎から上級まで', 'JavaScript 从基础到高级', 'javascript-tu-co-ban-den-nang-cao', 'javascript-from-basic-to-advanced', 'javascript-기초부터-고급까지', 'javascript-基礎から上級まで', 'javascript-从基础到高级', '<p>Kh&oacute;a học JavaScript của ch&uacute;ng t&ocirc;i sẽ gi&uacute;p bạn x&acirc;y dựng nền tảng vững chắc về ng&ocirc;n ngữ lập tr&igrave;nh quan trọng nhất trong ph&aacute;t triển web.</p>\r\n\r\n<p>Bắt đầu từ c&aacute;c kh&aacute;i niệm cơ bản như biến, h&agrave;m, v&agrave; c&acirc;u lệnh điều kiện, bạn sẽ từng bước l&agrave;m quen với c&aacute;c t&iacute;nh năng n&acirc;ng cao như thao t&aacute;c với DOM, sự kiện v&agrave; AJAX.</p>\r\n\r\n<p>Đặc biệt, kh&oacute;a học cũng sẽ đưa bạn v&agrave;o thực h&agrave;nh c&aacute;c dự &aacute;n thực tế, gi&uacute;p bạn tự tin x&acirc;y dựng c&aacute;c ứng dụng tương t&aacute;c.</p>\r\n\r\n<p><strong>Bạn sẽ học được:</strong></p>\r\n\r\n<p>- Kiến thức nền tảng về JavaScript từ cơ bản đến n&acirc;ng cao.</p>\r\n\r\n<p>- Kỹ năng l&agrave;m việc với API v&agrave; xử l&yacute; dữ liệu từ m&aacute;y chủ.</p>\r\n\r\n<p>- Tạo c&aacute;c hiệu ứng tương t&aacute;c v&agrave; quản l&yacute; sự kiện tr&ecirc;n giao diện người d&ugrave;ng.</p>\r\n\r\n<p>- X&acirc;y dựng c&aacute;c dự &aacute;n thực tế như To-Do List, Game đơn giản, v&agrave; SPA (Single Page Application).</p>\r\n\r\n<p><strong>Đối tượng:</strong></p>\r\n\r\n<p>- Ph&ugrave; hợp cho người mới bắt đầu hoặc c&aacute;c lập tr&igrave;nh vi&ecirc;n muốn cải thiện kỹ năng JavaScript.</p>\r\n\r\n<p><strong>Y&ecirc;u cầu đầu v&agrave;o:</strong></p>\r\n\r\n<p>- Chỉ cần c&oacute; kiến thức cơ bản về HTML v&agrave; CSS l&agrave; bạn c&oacute; thể bắt đầu.</p>', '<p>Our JavaScript course will help you build a solid foundation in the most important programming language in web development.</p>\r\n\r\n<p>Starting from basic concepts like variables, functions, and conditional statements, you will gradually learn advanced features such as DOM manipulation, events, and AJAX.</p>\r\n\r\n<p>In particular, the course will also put you through hands-on projects, helping you confidently build interactive applications.</p>\r\n\r\n<p>You will learn:</p>\r\n\r\n<p>- Fundamental knowledge of JavaScript from basic to advanced levels.</p>\r\n\r\n<p>- Skills in working with APIs and handling server-side data.</p>\r\n\r\n<p>- Creating interactive effects and managing events on the user interface.</p>\r\n\r\n<p>- Building practical projects such as To-Do Lists, simple Games, and SPAs (Single Page Applications).</p>\r\n\r\n<p>Target Audience:</p>\r\n\r\n<p>- Suitable for beginners or programmers who want to improve their JavaScript skills.</p>\r\n\r\n<p>Input requirements:</p>\r\n\r\n<p>- You only need basic knowledge of HTML and CSS to get started.</p>', '<p>我们的 JavaScript 课程将帮助您在 Web 开发中最重要的编程语言&mdash;&mdash;JavaScript 中打下坚实的基础。</p>\r\n\r\n<p>从变量、函数和条件语句等基本概念入手，您将逐步学习 DOM 操作、事件和 AJAX 等高级功能。</p>\r\n\r\n<p>此外，本课程还将通过实践项目，帮助您自信地构建交互式应用程序。</p>\r\n\r\n<p>您将学习：</p>\r\n\r\n<p>- 从基础到高级的 JavaScript 基础知识。</p>\r\n\r\n<p>- 使用 API 和处理服务器端数据的技能。</p>\r\n\r\n<p>- 创建交互效果和管理用户界面上的事件。</p>\r\n\r\n<p>- 构建实用项目，例如待办事项列表、简单游戏和单页应用程序 (SPA)。</p>\r\n\r\n<p>目标受众：</p>\r\n\r\n<p>- 适合初学者或希望提升 JavaScript 技能的程序员。</p>\r\n\r\n<p>入学要求：</p>\r\n\r\n<p>- 只需具备 HTML 和 CSS 的基础知识即可开始学习。</p>', '<p>当コースのJavaScriptコースは、Web開発において最も重要なプログラミング言語であるJavaScriptの確固たる基礎を築くお手伝いをします。</p>\r\n\r\n<p>変数、関数、条件文といった基本的な概念から始め、DOM操作、イベント、AJAXといった高度な機能を段階的に習得していきます。</p>\r\n\r\n<p>特に、このコースでは実践的なプロジェクトを通して、インタラクティブなアプリケーションを自信を持って構築できるよう支援します。</p>\r\n\r\n<p>学習内容：</p>\r\n\r\n<p>- 基礎レベルから応用レベルまでのJavaScriptの基礎知識。</p>\r\n\r\n<p>- APIの利用とサーバーサイドデータの処理スキル。</p>\r\n\r\n<p>- ユーザーインターフェース上でのインタラクティブな効果の作成とイベント管理。</p>\r\n\r\n<p>- ToDoリスト、シンプルなゲーム、SPA（シングルページアプリケーション）といった実践的なプロジェクトの構築。</p>\r\n\r\n<p>対象者：</p>\r\n\r\n<p>- 初心者またはJavaScriptスキルを向上させたいプログラマーに適しています。</p>\r\n\r\n<p>入力要件：</p>\r\n\r\n<p>- 開始するには、HTMLとCSSの基礎知識のみが必要です。</p>', '<p>我们的 JavaScript 课程将帮助您在 Web 开发中最重要的编程语言&mdash;&mdash;JavaScript 中打下坚实的基础。</p>\r\n\r\n<p>从变量、函数和条件语句等基本概念入手，您将逐步学习 DOM 操作、事件和 AJAX 等高级功能。</p>\r\n\r\n<p>此外，本课程还将通过实践项目，帮助您自信地构建交互式应用程序。</p>\r\n\r\n<p>您将学习：</p>\r\n\r\n<p>- 从基础到高级的 JavaScript 基础知识。</p>\r\n\r\n<p>- 使用 API 和处理服务器端数据的技能。</p>\r\n\r\n<p>- 创建交互效果和管理用户界面上的事件。</p>\r\n\r\n<p>- 构建实用项目，例如待办事项列表、简单游戏和单页应用程序 (SPA)。</p>\r\n\r\n<p>目标受众：</p>\r\n\r\n<p>- 适合初学者或希望提升 JavaScript 技能的程序员。</p>\r\n\r\n<p>入学要求：</p>\r\n\r\n<p>- 只需具备 HTML 和 CSS 的基础知识即可开始学习。</p>', 6, '/storage/photos/2/d6478fcf-596c-4a30-8383-dae2007a189d.jpg', 2850000.00, NULL, 795000.00, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'KH183485', 907.00, 0, '<p>Học tr&ecirc;n mọi thiết bị</p>\r\n\r\n<p>Code mẫu, t&agrave;i liệu đầy đủ</p>\r\n\r\n<p>Hỗ trợ 1-1 bởi giảng vi&ecirc;n, nh&oacute;m k&iacute;n</p>\r\n\r\n<p>Giới thiệu c&ocirc;ng việc ph&ugrave; hợp</p>\r\n\r\n<p>Thời hạn: Vĩnh viễn</p>', '<p>Learn on any device</p>\r\n\r\n<p>Sample code, complete documentation</p>\r\n\r\n<p>One-on-one support by instructors, private group</p>\r\n\r\n<p>Job placement assistance</p>\r\n\r\n<p>Duration: Lifetime</p>', '<p>모든 기기에서 학습 가능</p>\r\n\r\n<p>すべてのデバイスで学習可能</p>\r\n\r\n<p>예제 코드 및 완전한 학습 자료 제공</p>\r\n\r\n<p>강사의 1:1 지원 및 전용 커뮤니티</p>\r\n\r\n<p>역량에 맞는 취업 기회 제공</p>\r\n\r\n<p>평생 이용 가능</p>', '<p>すべてのデバイスで学習可能</p>\r\n\r\n<p>サンプルコードと完全な資料付き</p>\r\n\r\n<p>講師による1対1サポートと専用コミュニティ</p>\r\n\r\n<p>スキルに合った就職機会の紹介</p>\r\n\r\n<p>無期限アクセス</p>', '<p>支持在所有设备上学习</p>\r\n\r\n<p>提供示例代码和完整学习资料</p>\r\n\r\n<p>讲师一对一指导及专属学习社群</p>\r\n\r\n<p>推荐适合的工作机会</p>\r\n\r\n<p>永久访问</p>', 1, 'all_lessons', 0, NULL, NULL, NULL, NULL, 0, 0, 8, '2026-02-03 08:48:58', '2026-05-27 07:38:00', NULL),
(12, 'Lập trình Front-End với ReactJS + TypeScript', 'Front-End Programming with ReactJS + TypeScript', 'ReactJS + TypeScript로 프론트엔드 개발', 'ReactJS + TypeScriptによるフロントエンド開発', '使用 ReactJS + TypeScript 进行前端开发', 'lap-trinh-front-end-voi-reactjs-typescript', 'front-end-programming-with-reactjs-typescript', 'reactjs-typescript로-프론트엔드-개발', 'reactjs-typescriptによるフロントエンド開発', '使用-reactjs-typescript-进行前端开发', '<p>ReactJS&nbsp;l&agrave; 1 thư viện JavaScript m&atilde; nguồn mở được ph&aacute;t triển bởi đội ngũ kỹ sư đến từ Facebook; n&oacute; được giới thiệu v&agrave;o năm 2011.&nbsp;</p>\r\n\r\n<p>Nguy&ecirc;n l&yacute; x&acirc;y dựng của React dựa tr&ecirc;n components (component-based approach), c&oacute; thể t&aacute;i sử dụng v&agrave; ph&ugrave; hợp với ứng dụng 1 trang (Single Page Application &ndash; SPA). React gi&uacute;p lập tr&igrave;nh vi&ecirc;n x&acirc;y dựng giao diện người d&ugrave;ng dựa tr&ecirc;n JSX (m&ocirc;t c&uacute; ph&aacute;p mở rộng của JavaScript), tạo ra c&aacute;c DOM ảo (virtual DOM) để tối ưu việc render 1 trang web.</p>\r\n\r\n<p><strong>Kh&oacute;a học ReactJS</strong>&nbsp;được x&acirc;y dựng v&agrave; hướng dẫn bởi&nbsp;<strong>Ho&agrave;ng An Unicode</strong>&nbsp;gi&uacute;p học vi&ecirc;n trang bị cho m&igrave;nh kiến thức cần thiết nhất để đi l&agrave;m với thư viện n&agrave;y. Cuối kh&oacute;a học bạn sẽ được hướng dẫn x&acirc;y dựng 1 dự &aacute;n ho&agrave;n chỉnh ho&agrave;n to&agrave;n bằng thư viện ReactJS.</p>\r\n\r\n<p><strong>Video giới thiệu dự &aacute;n Threads Clone</strong></p>\r\n\r\n<p><strong>Bạn nhận được g&igrave; tại kh&oacute;a học?</strong></p>\r\n\r\n<p>► Kiến thức căn bản nhất về React JS</p>\r\n\r\n<p>► Hiểu r&otilde; bản chất c&aacute;ch hoạt động của c&aacute;c th&agrave;nh phần trong React JS th&ocirc;ng qua Class Component</p>\r\n\r\n<p>► R&egrave;n luyện tư duy lập tr&igrave;nh qua c&aacute;c Case Study được ph&acirc;n t&iacute;ch trong kh&oacute;a học</p>\r\n\r\n<p>► L&agrave;m việc với RESTful API</p>\r\n\r\n<p>► L&agrave;m việc với c&aacute;c React Hook từ cơ bản đến n&acirc;ng cao</p>\r\n\r\n<p>► Biết c&aacute;ch tự x&acirc;y dựng hệ thống quản l&yacute; Global State</p>\r\n\r\n<p>► Biết c&aacute;ch tự x&acirc;y dựng Hook ri&ecirc;ng trong React JS</p>\r\n\r\n<p>► L&agrave;m việc với React Router DOM</p>\r\n\r\n<p>► C&aacute;c kỹ thuật l&agrave;m việc với Assets v&agrave; c&aacute;c thư viện b&ecirc;n thứ ba</p>\r\n\r\n<p>► L&agrave;m việc với React Hook Form</p>\r\n\r\n<p>► Kỹ thuật xử l&yacute; Authentication - Authorization (JWT) từ cơ bản đến n&acirc;ng cao</p>\r\n\r\n<p>► T&iacute;ch hợp c&aacute;c thư viện xử l&yacute; Authentication b&ecirc;n thứ ba: Auth0, Firebase, Clerk,...</p>\r\n\r\n<p>► Kỹ thuật l&agrave;m việc với thư viện Redux, Redux Toolkit, Redux Toolkit Query</p>\r\n\r\n<p>► Kỹ thuật l&agrave;m việc với SWR, React Query (Tanstack Query)</p>\r\n\r\n<p>► Kỹ thuật xử l&yacute; với styled components,&nbsp;Framer Motion, i18n, Socket.IO, Testing,...</p>\r\n\r\n<p>► Biết c&aacute;ch kết hợp Typescript v&agrave;o ứng dụng ReactJS</p>\r\n\r\n<p>► V&agrave; rất nhiều kiến thức, c&ocirc;ng nghệ, thư viện phổ biến được chia sẻ trong kh&oacute;a học</p>\r\n\r\n<p><strong>C&acirc;u hỏi thường gặp</strong></p>\r\n\r\n<p><em><strong>Kh&oacute;a học n&agrave;y cần kiến thức nền g&igrave; kh&ocirc;ng?</strong></em></p>\r\n\r\n<p>ReactJS l&agrave; thư viện của JavaScript n&ecirc;n bạn cần c&oacute; kiến thức vững chắc về JavaScript v&agrave; HTML-CSS. Bạn vui l&ograve;ng xem b&agrave;i giảng miễn ph&iacute; &quot;<strong>Kiến thức cần chuẩn bị trước khi học ReactJS</strong>&quot;</p>\r\n\r\n<p><em><strong>T&ocirc;i kh&ocirc;ng biết về TypeScript c&oacute; học được kh&ocirc;ng?</strong></em></p>\r\n\r\n<p>Được, trong kh&oacute;a học bạn sẽ được hướng dẫn về TypeScript v&agrave; từ sau module TypeScript m&igrave;nh mới &aacute;p dụng trong c&aacute;c b&agrave;i hướng dẫn. C&ograve;n c&aacute;c nội dụng trước vẫn sử dụng JavaScript</p>\r\n\r\n<p><em><strong>N&ecirc;n sử dụng Vite hay Create React App?</strong></em></p>\r\n\r\n<p>Ở thời điểm hiện tại, Vite được chuộng hơn n&ecirc;n bạn c&oacute; thể sử dụng Vite lu&ocirc;n từ khi bắt đầu kh&oacute;a học. Phần đầu kh&oacute;a học m&igrave;nh sử dụng Create React App v&igrave; vấn đề thời điểm quay, tỷ lệ sử dụng Create React App vẫn phổ biến</p>\r\n\r\n<p><em><strong>Trong qu&aacute; tr&igrave;nh học, t&ocirc;i c&oacute; được hỏi giảng vi&ecirc;n kh&ocirc;ng?</strong></em></p>\r\n\r\n<p>Được, nếu gặp kh&oacute; khăn bạn c&oacute; thể nhắn tin trực tiếp cho giảng vi&ecirc;n qua Zalo hoặc tr&ecirc;n group được tham gia</p>\r\n\r\n<p><em><strong>T&ocirc;i cần c&agrave;i đặt những phần mềm/c&ocirc;ng cụ g&igrave; để học?</strong></em></p>\r\n\r\n<p>Để học ReactJS bạn cần sử dụng phần mềm Visual Studio Code để viết code v&agrave; c&agrave;i đặt NodeJS tr&ecirc;n m&aacute;y t&iacute;nh. Bạn c&oacute; thể sử dụng phần mềm viết code kh&aacute;c nếu ph&ugrave; hợp</p>\r\n\r\n<p><em><strong>Trong b&agrave;i giảng bạn d&ugrave;ng phần mềm n&agrave;o để hướng dẫn học vi&ecirc;n?</strong></em></p>\r\n\r\n<p>Trong kh&oacute;a học m&igrave;nh đang sử dụng phần mềm Visual Studio Code để viết code, m&igrave;nh cũng khuy&ecirc;n bạn sử dụng phần mềm n&agrave;y v&igrave; n&oacute; hỗ trợ rất mạnh cho Front-End</p>\r\n\r\n<p><em><strong>Trong kh&oacute;a học đang hướng dẫn ở phi&ecirc;n bản ReactJS bao nhi&ecirc;u?</strong></em></p>\r\n\r\n<p>Trong kh&oacute;a học, m&igrave;nh hướng dẫn tr&ecirc;n ReactJS 18 v&agrave; bắt đầu từ class để học vi&ecirc;n hiểu được tư duy v&agrave; luồng chạy của ReactJS</p>\r\n\r\n<p><em><strong>Kh&oacute;a học n&agrave;y c&ograve;n ph&ugrave; hợp với thời điểm hiện tại kh&ocirc;ng?</strong></em></p>\r\n\r\n<p>Kh&oacute;a học rất ph&ugrave; hợp với thời điểm hiện tại, v&igrave; phần lớn nội dung kh&oacute;a học từ phần React Hook được quay trong năm 2024</p>\r\n\r\n<p><em><strong>Kh&oacute;a học n&agrave;y c&oacute; được cập nhật trong tương lai kh&ocirc;ng?</strong></em></p>\r\n\r\n<p>C&oacute; bạn nh&eacute;. M&igrave;nh sẽ cập nhật những kiến thức mới, tư duy mới trong ReactJS ph&ugrave; hợp với nhu cầu tuyển dụng</p>', '<p>ReactJS is an open-source JavaScript library developed by a team of engineers from Facebook; it was introduced in 2011.</p>\r\n\r\n<p>React&#39;s design principles are based on a component-based approach, making it reusable and suitable for single-page applications (SPAs). React helps programmers build user interfaces using JSX (an extension of JavaScript), creating virtual DOMs to optimize web page rendering.</p>\r\n\r\n<p>The ReactJS course, developed and taught by Hoang An Unicode, equips students with the necessary knowledge to work with this library. At the end of the course, you will be guided to build a complete project entirely using the ReactJS library.</p>\r\n\r\n<p>[Video introducing the Threads Clone project]</p>\r\n\r\n<p>What will you receive from the course?</p>\r\n\r\n<p>► Fundamental knowledge of React JS</p>\r\n\r\n<p>► Understanding the nature of how React JS components work through Class Components</p>\r\n\r\n<p>► Developing programming thinking through case studies analyzed in the course</p>\r\n\r\n<p>► Working with RESTful APIs</p>\r\n\r\n<p>► Working with React Hooks from basic to advanced levels</p>\r\n\r\n<p>► Knowing how to build your own Global State management system</p>\r\n\r\n<p>► Knowing how to build your own Hooks in React JS</p>\r\n\r\n<p>► Working with React Router DOM</p>\r\n\r\n<p>► Techniques for working with Assets and third-party libraries</p>\r\n\r\n<p>► Working with React Hook Forms</p>\r\n\r\n<p>► Techniques for handling Authentication - Authorization (JWT) from basic to advanced levels</p>\r\n\r\n<p>► Integrating third-party authentication libraries: Auth0, Firebase, Clerk, etc.</p>\r\n\r\n<p>► Techniques for working with Redux, Redux Toolkit, and Redux Toolkit Query</p>\r\n\r\n<p>► Techniques for working with SWR and React Query (Tanstack Query)</p>\r\n\r\n<p>► Techniques for handling styled components, Framer Motion, i18n, Socket.IO, Testing, etc.</p>\r\n\r\n<p>► Knowing how to integrate TypeScript into ReactJS applications</p>\r\n\r\n<p>► And much more knowledge, technologies, and popular libraries will be shared in the course.</p>\r\n\r\n<p>Frequently Asked Questions</p>\r\n\r\n<p>What prior knowledge is required for this course?</p>\r\n\r\n<p>ReactJS is a JavaScript library, so you need a solid understanding of JavaScript and HTML-CSS. Please see the free lecture &quot;Knowledge to prepare before learning ReactJS&quot;.</p>\r\n\r\n<p>I don&#39;t know TypeScript, can I still learn?</p>\r\n\r\n<p>Yes, you will be guided on TypeScript in the course, and we will only apply it in the tutorials after the TypeScript module. The content before that will still use JavaScript.</p>\r\n\r\n<p>Should I use Vite or Create React App?</p>\r\n\r\n<p>Currently, Vite is more popular, so you can use Vite from the beginning of the course. For the first part of the course, I used Create React App because of the timing of filming, and the usage rate for Create React App was still common.</p>\r\n\r\n<p>During the course, can I ask the instructor questions?</p>\r\n\r\n<p>Yes, if you encounter difficulties, you can message the instructor directly via Zalo or in the group you are in.</p>\r\n\r\n<p>What software/tools do I need to install to learn?</p>\r\n\r\n<p>To learn ReactJS, you need to use Visual Studio Code to write code and install NodeJS on your computer. You can use other coding software if suitable.</p>\r\n\r\n<p>What software do you use to guide students in the lectures?</p>\r\n\r\n<p>In this course, I am using Visual Studio Code to write code, and I also recommend this software because it has strong support for Front-End development.</p>\r\n\r\n<p>What version of ReactJS is being taught in this course?</p>\r\n\r\n<p>In this course, I teach using ReactJS 18 and start with classes so that students understand the thinking and workflow of ReactJS.</p>\r\n\r\n<p>Is this course still relevant today?</p>\r\n\r\n<p>Yes, the course is very relevant today, as most of the course content, from the React Hook section onwards, was filmed in 2024.</p>\r\n\r\n<p>Will this course be updated in the future?</p>\r\n\r\n<p>Yes, it will. I will update it with new knowledge and thinking in ReactJS to meet recruitment needs.</p>', '<p>ReactJS 是一个开源的 JavaScript 库，由 Facebook 的工程师团队开发，于 2011 年发布。</p>\r\n\r\n<p>React 的设计原则基于组件化方法，使其具有可重用性，并适用于单页应用程序 (SPA)。React 帮助程序员使用 JSX（JavaScript 的一个扩展）构建用户界面，创建虚拟 DOM 以优化网页渲染。</p>\r\n\r\n<p>由 Hoang An Unicode 开发和授课的 ReactJS 课程，旨在帮助学生掌握使用该库所需的知识。课程结束时，您将在指导下完全使用 ReactJS 库构建一个完整的项目。</p>\r\n\r\n<p>[Threads Clone 项目介绍视频]</p>\r\n\r\n<p>您将从本课程中获得什么？</p>\r\n\r\n<p>► React JS 基础知识</p>\r\n\r\n<p>► 通过类组件理解 React JS 组件的工作原理</p>\r\n\r\n<p>► 通过课程中分析的案例研究培养编程思维</p>\r\n\r\n<p>► 使用 RESTful API</p>\r\n\r\n<p>► 从基础到高级掌握 React Hooks 的使用</p>\r\n\r\n<p>► 掌握如何构建自己的全局状态管理系统</p>\r\n\r\n<p>► 掌握如何在 React JS 中构建自己的 Hooks</p>\r\n\r\n<p>► 使用 React Router DOM</p>\r\n\r\n<p>► 使用资源和第三方库的技巧</p>\r\n\r\n<p>► 使用 React Hook 表单</p>\r\n\r\n<p>► 从基础到高级掌握身份验证/授权（JWT）的处理技巧</p>\r\n\r\n<p>► 集成第三方身份验证库：Auth0、Firebase、Clerk 等</p>\r\n\r\n<p>► 使用 Redux、Redux Toolkit 和 Redux Toolkit Query 的技巧</p>\r\n\r\n<p>► 使用 SWR 和 React Query（Tanstack Query）的技巧</p>\r\n\r\n<p>► 使用 styled-components、Framer Motion、i18n、Socket.IO 等的技巧测试等</p>\r\n\r\n<p>► 了解如何将 TypeScript 集成到 ReactJS 应用中</p>\r\n\r\n<p>► 课程中还将分享更多知识、技术和常用库。</p>\r\n\r\n<p>常见问题解答</p>\r\n\r\n<p>学习本课程需要哪些先验知识？</p>\r\n\r\n<p>ReactJS 是一个 JavaScript 库，因此您需要对 JavaScript 和 HTML-CSS 有扎实的理解。请观看免费讲座&ldquo;学习 ReactJS 前的准备知识&rdquo;。</p>\r\n\r\n<p>我不懂 TypeScript，还能学习吗？</p>\r\n\r\n<p>可以，课程中会指导您学习 TypeScript，我们只会在 TypeScript 模块之后的教程中使用它。之前的内容仍然使用 JavaScript。</p>\r\n\r\n<p>我应该使用 Vite 还是 Create React App？</p>\r\n\r\n<p>目前 Vite 更流行，因此您可以从课程一开始就使用 Vite。由于录制时间的关系，课程前半部分我使用了 Create React App，而且当时 Create React App 的使用率仍然很高。</p>\r\n\r\n<p>课程期间我可以向讲师提问吗？</p>\r\n\r\n<p>是的，如果您遇到任何困难，可以直接通过 Zalo 或您所在的群组联系讲师。</p>\r\n\r\n<p>我需要安装哪些软件/工具才能学习？</p>\r\n\r\n<p>要学习 ReactJS，您需要使用 Visual Studio Code 编写代码，并在您的计算机上安装 NodeJS。您也可以使用其他合适的编码软件。</p>\r\n\r\n<p>您在课堂上使用什么软件来指导学生？</p>\r\n\r\n<p>在本课程中，我使用 Visual Studio Code 编写代码，我也推荐这款软件，因为它对前端开发提供了强大的支持。</p>\r\n\r\n<p>本课程教授的是哪个版本的 ReactJS？</p>\r\n\r\n<p>在本课程中，我使用 ReactJS 18 进行教学，并从基础课程开始，帮助学生理解 ReactJS 的思路和工作流程。</p>\r\n\r\n<p>这门课程现在还适用吗？</p>\r\n\r\n<p>是的，这门课程现在仍然非常适用，因为从 React Hook 部分开始的大部分课程内容都是在 2024 年录制的。</p>\r\n\r\n<p>这门课程将来会更新吗？</p>\r\n\r\n<p>是的，会的。我将根据招聘需求，运用 ReactJS 方面的新知识和新思路对其进行更新。</p>', '<p>ReactJSは、Facebookのエンジニアチームによって開発されたオープンソースのJavaScriptライブラリで、2011年にリリースされました。</p>\r\n\r\n<p>Reactの設計原則はコンポーネントベースのアプローチに基づいており、再利用性に優れ、シングルページアプリケーション（SPA）に適しています。Reactは、JSX（JavaScriptの拡張機能）を使用してユーザーインターフェースを構築し、仮想DOMを作成してWebページのレンダリングを最適化するのに役立ちます。</p>\r\n\r\n<p>Hoang An Unicodeが開発・指導するReactJSコースでは、受講生にこのライブラリの利用に必要な知識を習得させます。コース修了時には、ReactJSライブラリのみを使用して完全なプロジェクトを構築できるようになります。</p>\r\n\r\n<p>[Threads Cloneプロジェクト紹介ビデオ]</p>\r\n\r\n<p>このコースで得られるもの</p>\r\n\r\n<p>► React JSの基礎知識</p>\r\n\r\n<p>► クラスコンポーネントを通してReact JSコンポーネントの動作原理を理解する</p>\r\n\r\n<p>► コースで分析するケーススタディを通してプログラミング思考を養う</p>\r\n\r\n<p>► RESTful APIの操作</p>\r\n\r\n<p>► 初級から上級までのReact Hooksの操作</p>\r\n\r\n<p>► 独自のグローバル状態管理システムを構築する方法を理解する</p>\r\n\r\n<p>► React JSで独自のHooksを構築する方法を理解する</p>\r\n\r\n<p>► React Router DOMの操作</p>\r\n\r\n<p>► Assetsとサードパーティライブラリの操作テクニック</p>\r\n\r\n<p>► React Hook Formsの操作</p>\r\n\r\n<p>► 初級から上級までの認証 - 認可 (JWT) の処理テクニック</p>\r\n\r\n<p>► サードパーティ認証ライブラリの統合：Auth0、Firebase、Clerkなど</p>\r\n\r\n<p>► Redux、Redux Toolkit、Redux Toolkit Queryの操作テクニック</p>\r\n\r\n<p>► SWRとReact Query (Tanstack Query)の操作テクニック</p>\r\n\r\n<p>► スタイル付きコンポーネント、Framerの操作テクニックMotion、i18n、Socket.IO、テストなど</p>\r\n\r\n<p>► TypeScriptをReactJSアプリケーションに統合する方法を知る</p>\r\n\r\n<p>► その他、多くの知識、テクノロジー、そして人気のライブラリについてもコースで共有します。</p>\r\n\r\n<p>よくある質問</p>\r\n\r\n<p>このコースを受講するには、どのような事前知識が必要ですか？</p>\r\n\r\n<p>ReactJSはJavaScriptライブラリなので、JavaScriptとHTML-CSSの確かな理解が必要です。無料レクチャー「ReactJSを学ぶ前に準備すべき知識」をご覧ください。</p>\r\n\r\n<p>TypeScriptを知らないのですが、それでも学習できますか？</p>\r\n\r\n<p>はい、コースではTypeScriptについて解説し、TypeScriptモジュール後のチュートリアルでのみTypeScriptを使用します。それ以前のコンテンツではJavaScriptを使用します。</p>\r\n\r\n<p>ViteとCreate React Appのどちらを使うべきですか？</p>\r\n\r\n<p>現在、Viteの方が普及しているので、コースの最初からViteを使っても構いません。コース前半では、撮影のタイミングとCreate React Appの使用率がまだ高かったため、Create React Appを使用しました。</p>\r\n\r\n<p>コース中に講師に質問することはできますか？</p>\r\n\r\n<p>はい。困った場合は、Zalo または参加しているグループで講師に直接メッセージを送信できます。</p>\r\n\r\n<p>学習に必要なソフトウェアやツールは何ですか？</p>\r\n\r\n<p>ReactJS を学習するには、Visual Studio Code を使用してコードを記述し、コンピューターに NodeJS をインストールする必要があります。他のコーディングソフトウェアでも、適切なものであれば使用できます。</p>\r\n\r\n<p>講義ではどのようなソフトウェアを使用して学生を指導していますか？</p>\r\n\r\n<p>このコースでは、Visual Studio Code を使用してコードを記述しています。フロントエンド開発に強力なサポートを提供しているため、このソフトウェアをお勧めします。</p>\r\n\r\n<p>このコースではどのバージョンの ReactJS を学習しますか？</p>\r\n\r\n<p>このコースでは、ReactJS 18 を使用して講義を行い、学生が ReactJS の考え方とワークフローを理解できるようにしています。</p>\r\n\r\n<p>このコースは現在でも有効ですか？</p>\r\n\r\n<p>はい、このコースは今日でも非常に関連性が高く、React Hookセクション以降のコースコンテンツの大部分は2024年に撮影されました。</p>\r\n\r\n<p>このコースは将来更新されますか？</p>\r\n\r\n<p>はい、更新されます。採用ニーズに応えるため、ReactJSに関する新しい知識と考え方を取り入れて更新していきます。</p>', '<p>ReactJS 是一个开源的 JavaScript 库，由 Facebook 的工程师团队开发，于 2011 年发布。</p>\r\n\r\n<p>React 的设计原则基于组件化方法，使其具有可重用性，并适用于单页应用程序 (SPA)。React 帮助程序员使用 JSX（JavaScript 的一个扩展）构建用户界面，创建虚拟 DOM 以优化网页渲染。</p>\r\n\r\n<p>由 Hoang An Unicode 开发和授课的 ReactJS 课程，旨在帮助学生掌握使用该库所需的知识。课程结束时，您将在指导下完全使用 ReactJS 库构建一个完整的项目。</p>\r\n\r\n<p>[Threads Clone 项目介绍视频]</p>\r\n\r\n<p>您将从本课程中获得什么？</p>\r\n\r\n<p>► React JS 基础知识</p>\r\n\r\n<p>► 通过类组件理解 React JS 组件的工作原理</p>\r\n\r\n<p>► 通过课程中分析的案例研究培养编程思维</p>\r\n\r\n<p>► 使用 RESTful API</p>\r\n\r\n<p>► 从基础到高级掌握 React Hooks 的使用</p>\r\n\r\n<p>► 掌握如何构建自己的全局状态管理系统</p>\r\n\r\n<p>► 掌握如何在 React JS 中构建自己的 Hooks</p>\r\n\r\n<p>► 使用 React Router DOM</p>\r\n\r\n<p>► 使用资源和第三方库的技巧</p>\r\n\r\n<p>► 使用 React Hook 表单</p>\r\n\r\n<p>► 从基础到高级掌握身份验证/授权（JWT）的处理技巧</p>\r\n\r\n<p>► 集成第三方身份验证库：Auth0、Firebase、Clerk 等</p>\r\n\r\n<p>► 使用 Redux、Redux Toolkit 和 Redux Toolkit Query 的技巧</p>\r\n\r\n<p>► 使用 SWR 和 React Query（Tanstack Query）的技巧</p>\r\n\r\n<p>► 使用 styled-components、Framer Motion、i18n、Socket.IO 等的技巧测试等</p>\r\n\r\n<p>► 了解如何将 TypeScript 集成到 ReactJS 应用中</p>\r\n\r\n<p>► 课程中还将分享更多知识、技术和常用库。</p>\r\n\r\n<p>常见问题解答</p>\r\n\r\n<p>学习本课程需要哪些先验知识？</p>\r\n\r\n<p>ReactJS 是一个 JavaScript 库，因此您需要对 JavaScript 和 HTML-CSS 有扎实的理解。请观看免费讲座&ldquo;学习 ReactJS 前的准备知识&rdquo;。</p>\r\n\r\n<p>我不懂 TypeScript，还能学习吗？</p>\r\n\r\n<p>可以，课程中会指导您学习 TypeScript，我们只会在 TypeScript 模块之后的教程中使用它。之前的内容仍然使用 JavaScript。</p>\r\n\r\n<p>我应该使用 Vite 还是 Create React App？</p>\r\n\r\n<p>目前 Vite 更流行，因此您可以从课程一开始就使用 Vite。由于录制时间的关系，课程前半部分我使用了 Create React App，而且当时 Create React App 的使用率仍然很高。</p>\r\n\r\n<p>课程期间我可以向讲师提问吗？</p>\r\n\r\n<p>是的，如果您遇到任何困难，可以直接通过 Zalo 或您所在的群组联系讲师。</p>\r\n\r\n<p>我需要安装哪些软件/工具才能学习？</p>\r\n\r\n<p>要学习 ReactJS，您需要使用 Visual Studio Code 编写代码，并在您的计算机上安装 NodeJS。您也可以使用其他合适的编码软件。</p>\r\n\r\n<p>您在课堂上使用什么软件来指导学生？</p>\r\n\r\n<p>在本课程中，我使用 Visual Studio Code 编写代码，我也推荐这款软件，因为它对前端开发提供了强大的支持。</p>\r\n\r\n<p>本课程教授的是哪个版本的 ReactJS？</p>\r\n\r\n<p>在本课程中，我使用 ReactJS 18 进行教学，并从基础课程开始，帮助学生理解 ReactJS 的思路和工作流程。</p>\r\n\r\n<p>这门课程现在还适用吗？</p>\r\n\r\n<p>是的，这门课程现在仍然非常适用，因为从 React Hook 部分开始的大部分课程内容都是在 2024 年录制的。</p>\r\n\r\n<p>这门课程将来会更新吗？</p>\r\n\r\n<p>是的，会的。我将根据招聘需求，运用 ReactJS 方面的新知识和新思路对其进行更新。</p>', 6, '/storage/photos/2/d82e2898-2695-4e27-a855-1396b5990fd8.jpg', 2580000.00, NULL, 895000.00, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'KH446584', 615.00, 0, '<p>Code mẫu, t&agrave;i liệu đầy đủ</p>\r\n\r\n<p>Hỗ trợ 1-1 bởi giảng vi&ecirc;n, nh&oacute;m k&iacute;n</p>\r\n\r\n<p>Giới thiệu c&ocirc;ng việc ph&ugrave; hợp</p>\r\n\r\n<p>Thời hạn: Vĩnh viễn</p>', '<p>Learn on any device</p>\r\n\r\n<p>Sample code, complete documentation</p>\r\n\r\n<p>One-on-one support by instructors, private group</p>\r\n\r\n<p>Job placement assistance</p>\r\n\r\n<p>Duration: Lifetime</p>', '<p>모든 기기에서 학습 가능</p>\r\n\r\n<p>すべてのデバイスで学習可能</p>\r\n\r\n<p>예제 코드 및 완전한 학습 자료 제공</p>\r\n\r\n<p>강사의 1:1 지원 및 전용 커뮤니티</p>\r\n\r\n<p>역량에 맞는 취업 기회 제공</p>\r\n\r\n<p>평생 이용 가능</p>', '<p>すべてのデバイスで学習可能</p>\r\n\r\n<p>サンプルコードと完全な資料付き</p>\r\n\r\n<p>講師による1対1サポートと専用コミュニティ</p>\r\n\r\n<p>スキルに合った就職機会の紹介</p>\r\n\r\n<p>無期限アクセス</p>', '<p>支持在所有设备上学习</p>\r\n\r\n<p>提供示例代码和完整学习资料</p>\r\n\r\n<p>讲师一对一指导及专属学习社群</p>\r\n\r\n<p>推荐适合的工作机会</p>\r\n\r\n<p>永久访问</p>', 1, 'all_lessons', 0, NULL, NULL, NULL, NULL, 0, 0, 6, '2026-02-03 08:51:15', '2026-05-27 08:20:44', NULL);
INSERT INTO `courses` (`id`, `name`, `name_en`, `name_ko`, `name_ja`, `name_zh`, `slug`, `slug_en`, `slug_ko`, `slug_ja`, `slug_zh`, `detail`, `detail_en`, `detail_ko`, `detail_ja`, `detail_zh`, `teacher_id`, `thumbnail`, `price`, `quantity`, `sale_price`, `price_en`, `sale_price_en`, `price_ko`, `sale_price_ko`, `price_ja`, `sale_price_ja`, `price_zh`, `sale_price_zh`, `code`, `durations`, `is_document`, `supports`, `supports_en`, `supports_ko`, `supports_ja`, `supports_zh`, `status`, `completion_condition`, `is_coming_soon`, `coming_soon_start_at`, `end_at`, `package_locked_at`, `package_lock_reason`, `is_package_priority`, `is_learning_locked`, `view`, `created_at`, `updated_at`, `deleted_at`) VALUES
(13, 'Lập trình Front-End với NextJS + TypeScript', 'Front-End Programming with NextJS + TypeScript', 'NextJS + TypeScript로 프론트엔드 개발', 'NextJS + TypeScriptによるフロントエンド開発', '使用 NextJS + TypeScript 进行前端开发', 'lap-trinh-front-end-voi-nextjs-15-typescript', 'front-end-programming-with-nextjs-typescript', 'nextjs-typescript로-프론트엔드-개발', 'nextjs-typescriptによるフロントエンド開発', '使用-nextjs-typescript-进行前端开发', '<p><strong>NextJS</strong>&nbsp;l&agrave; framework m&atilde; nguồn mở được x&acirc;y dựng tr&ecirc;n nền tảng của React,&nbsp;được ph&aacute;t triển bởi Vercel, gi&uacute;p đơn giản h&oacute;a việc x&acirc;y dựng c&aacute;c ứng dụng web React, đặc biệt l&agrave; c&aacute;c ứng dụng c&oacute; y&ecirc;u cầu cao về hiệu năng v&agrave; SEO. NextJS c&ograve;n được gọi l&agrave; Fullstack Web Framework (L&agrave;m được cả Front-End v&agrave; Back-End)</p>\r\n\r\n<p>Kh&oacute;a học n&agrave;y được thiết kế bởi&nbsp;<strong>Ho&agrave;ng An Unicode</strong>&nbsp;nhằm gi&uacute;p bạn nắm vững c&aacute;ch kết hợp NextJS với TypeScript để ph&aacute;t triển c&aacute;c ứng dụng web mạnh mẽ, tối ưu h&oacute;a hiệu năng v&agrave; dễ bảo tr&igrave;. Bạn sẽ học từ c&aacute;c kh&aacute;i niệm cơ bản đến n&acirc;ng cao, với c&aacute;c b&agrave;i giảng chi tiết v&agrave; c&aacute;c dự &aacute;n thực h&agrave;nh từ đơn giản đến phức tạp.</p>\r\n\r\n<p>Cuối kh&oacute;a học bạn sẽ được hướng dẫn x&acirc;y dựng 1 dự &aacute;n ho&agrave;n chỉnh ho&agrave;n to&agrave;n bằng Framework NextJS.</p>\r\n\r\n<p><strong>Tổng quan dự &aacute;n Notion Clone trong kh&oacute;a học</strong></p>\r\n\r\n<p>►&nbsp;<strong>Video giới thiệu dự &aacute;n Notion</strong>:&nbsp;</p>\r\n\r\n<p>►&nbsp;<strong>Demo dự &aacute;n</strong>:</p>\r\n\r\n<p><strong>Bạn nhận được g&igrave; tại kh&oacute;a học?</strong></p>\r\n\r\n<p>► Kiến thức TypeScript từ cơ bản đến n&acirc;ng cao, đủ cho bạn l&agrave;m việc với c&aacute;c dự &aacute;n sử dụng TypeScript</p>\r\n\r\n<p>► Kiến thức b&agrave;i bản&nbsp;về NextJS từ cơ bản đến n&acirc;ng cao</p>\r\n\r\n<p>► Biết c&aacute;ch l&agrave;m việc với Middleware trong NextJS v&agrave; ứng dụng thực tế</p>\r\n\r\n<p>► Ứng dụng Route Handler, Server Actions để giải quyết c&aacute;c b&agrave;i to&aacute;n ph&iacute;a Server</p>\r\n\r\n<p>► Biết c&aacute;ch Data Fetching ở Client v&agrave; Server trong NextJS</p>\r\n\r\n<p>► Biết c&aacute;ch &aacute;p dụng sức mạnh của Cache trong NextJS</p>\r\n\r\n<p>► Kỹ thuật l&agrave;m việc với SWR để ứng dụng&nbsp;Data Fetching</p>\r\n\r\n<p>► Kỹ thuật l&agrave;m việc với Authentication trong NextJS</p>\r\n\r\n<p>► Kỹ thuật l&agrave;m việc với thư viện NextAuth từ cơ bản đến n&acirc;ng cao</p>\r\n\r\n<p>► Thao t&aacute;c với Database trong NextJS với Prisma ORM</p>\r\n\r\n<p>► Kỹ thuật Route n&acirc;ng cao v&agrave; đa ng&ocirc;n ngữ trong NextJS</p>\r\n\r\n<p>► Biết c&aacute;ch tối ưu h&oacute;a chuy&ecirc;n s&acirc;u trong NextJS</p>\r\n\r\n<p>► Ứng dụng Redux Toolkit, Redux Tookit Query để quản l&yacute; Global State, Data Fetching</p>\r\n\r\n<p>► &Aacute;p dụng WebSocket, thư viện Socket.io để giải quyết c&aacute;c b&agrave;i to&aacute;n Realtime</p>\r\n\r\n<p>► Được học Testing v&agrave; Debug trong NextJS th&ocirc;ng qua: Jest, Testing Libary,...</p>\r\n\r\n<p>► X&acirc;y dựng dự &aacute;n Notion b&agrave;i bản từ đầu đến khi ho&agrave;n thiện</p>\r\n\r\n<p>► V&agrave; rất nhiều kiến thức, c&ocirc;ng nghệ, thư viện phổ biến được chia sẻ trong kh&oacute;a học</p>\r\n\r\n<p><strong>C&acirc;u hỏi thường gặp</strong></p>\r\n\r\n<p><em><strong>Kh&oacute;a học n&agrave;y cần kiến thức nền g&igrave; kh&ocirc;ng?</strong></em></p>\r\n\r\n<p>NextJS l&agrave; Framework của ReactJS n&ecirc;n c&aacute;c bạn cần c&oacute; kiến thức nền tảng về ReactJS</p>\r\n\r\n<p><em><strong>T&ocirc;i kh&ocirc;ng biết về TypeScript c&oacute; học được kh&ocirc;ng?</strong></em></p>\r\n\r\n<p>Được, trong kh&oacute;a học bạn sẽ được hướng dẫn về TypeScript từ đầu</p>\r\n\r\n<p><em><strong>Trong qu&aacute; tr&igrave;nh học, t&ocirc;i c&oacute; được hỏi giảng vi&ecirc;n kh&ocirc;ng?</strong></em></p>\r\n\r\n<p>Được, nếu gặp kh&oacute; khăn bạn c&oacute; thể nhắn tin trực tiếp cho giảng vi&ecirc;n qua Zalo hoặc tr&ecirc;n group được tham gia</p>\r\n\r\n<p><em><strong>T&ocirc;i cần c&agrave;i đặt những phần mềm/c&ocirc;ng cụ g&igrave; để học?</strong></em></p>\r\n\r\n<p>Để học NextJS&nbsp;bạn cần sử dụng phần mềm Visual Studio Code để viết code v&agrave; c&agrave;i đặt NodeJS tr&ecirc;n m&aacute;y t&iacute;nh. Bạn c&oacute; thể sử dụng phần mềm viết code kh&aacute;c nếu ph&ugrave; hợp</p>\r\n\r\n<p><em><strong>Trong b&agrave;i giảng bạn d&ugrave;ng phần mềm n&agrave;o để hướng dẫn học vi&ecirc;n?</strong></em></p>\r\n\r\n<p>Trong kh&oacute;a học m&igrave;nh đang sử dụng phần mềm Visual Studio Code để viết code, m&igrave;nh cũng khuy&ecirc;n bạn sử dụng phần mềm n&agrave;y v&igrave; n&oacute; hỗ trợ rất mạnh cho Front-End</p>\r\n\r\n<p><em><strong>Trong kh&oacute;a học đang hướng dẫn ở phi&ecirc;n bản NextJS&nbsp;bao nhi&ecirc;u?</strong></em></p>\r\n\r\n<p>Trong kh&oacute;a học, m&igrave;nh hướng dẫn tr&ecirc;n NextJS 15</p>\r\n\r\n<p><em><strong>Kh&oacute;a học n&agrave;y c&ograve;n ph&ugrave; hợp với thời điểm hiện tại kh&ocirc;ng?</strong></em></p>\r\n\r\n<p>Kh&oacute;a học rất ph&ugrave; hợp với thời điểm hiện tại. V&igrave; hiện tại vẫn l&agrave; phi&ecirc;n bản 15</p>\r\n\r\n<p><em><strong>Kh&oacute;a học n&agrave;y c&oacute; được cập nhật trong tương lai kh&ocirc;ng?</strong></em></p>\r\n\r\n<p>C&oacute; bạn nh&eacute;. M&igrave;nh sẽ cập nhật những kiến thức mới, tư duy mới trong NextJS&nbsp;ph&ugrave; hợp với nhu cầu tuyển dụng</p>', '<p>NextJS is an open-source framework built on the React foundation, developed by Vercel, that simplifies the development of React web applications, especially those with high performance and SEO requirements. NextJS is also known as a Fullstack Web Framework (capable of both front-end and back-end development).</p>\r\n\r\n<p>This course, designed by Hoang An Unicode, aims to help you master how to combine NextJS with TypeScript to develop powerful, performance-optimized, and easy-to-maintain web applications. You will learn from basic to advanced concepts, with detailed lectures and practical projects ranging from simple to complex.</p>\r\n\r\n<p>At the end of the course, you will be guided to build a complete project entirely using the NextJS framework.</p>\r\n\r\n<p>Overview of the Notion Clone project in the course:</p>\r\n\r\n<p>► Video introducing the Notion project:</p>\r\n\r\n<p>► Project demo:</p>\r\n\r\n<p>What will you receive from the course?</p>\r\n\r\n<p>► Basic to advanced TypeScript knowledge, sufficient for working on TypeScript projects.</p>\r\n\r\n<p>► Comprehensive knowledge of NextJS from basic to advanced levels.</p>\r\n\r\n<p>► Understanding how to work with Middleware in NextJS and its practical applications.</p>\r\n\r\n<p>► Applying Route Handler and Server Actions to solve server-side problems.</p>\r\n\r\n<p>► Understanding client-side and server-side data fetching in NextJS.</p>\r\n\r\n<p>► Understanding how to leverage the power of caching in NextJS.</p>\r\n\r\n<p>► Techniques for working with SWR to implement data fetching.</p>\r\n\r\n<p>► Techniques for working with authentication in NextJS.</p>\r\n\r\n<p>► Techniques for working with the NextAuth library from basic to advanced levels.</p>\r\n\r\n<p>► Manipulating databases in NextJS with Prisma ORM.</p>\r\n\r\n<p>► Advanced and multi-language routing techniques in NextJS.</p>\r\n\r\n<p>► Understanding in-depth optimization in NextJS.</p>\r\n\r\n<p>► Applying Redux Toolkit and Redux. Query Toolkit for Global State Management and Data Fetching</p>\r\n\r\n<p>► Applying WebSockets and the Socket.io library to solve real-time problems</p>\r\n\r\n<p>► Learning Testing and Debugging in NextJS through: Jest, Testing Library, etc.</p>\r\n\r\n<p>► Building a systematic Notion project from start to finish</p>\r\n\r\n<p>► And much more knowledge, technologies, and popular libraries shared in the course</p>\r\n\r\n<p>Frequently Asked Questions</p>\r\n\r\n<p>What prior knowledge is required for this course?</p>\r\n\r\n<p>NextJS is a ReactJS framework, so you need a foundation in ReactJS.</p>\r\n\r\n<p>I don&#39;t know TypeScript, can I still learn?</p>\r\n\r\n<p>Yes, you will be taught TypeScript from the beginning in this course.</p>\r\n\r\n<p>Can I ask the instructor questions during the course?</p>\r\n\r\n<p>Yes, if you encounter difficulties, you can message the instructor directly via Zalo or in the group you are in.</p>\r\n\r\n<p>What software/tools do I need to install to learn?</p>\r\n\r\n<p>To learn NextJS, you need to use Visual Studio Code to write code and install Node.js on your computer. You can use other coding software if suitable.</p>\r\n\r\n<p>What software do you use to guide students in the course?</p>\r\n\r\n<p>In this course, I am using Visual Studio Code to write code. I also recommend this software because it provides strong support for front-end development.</p>\r\n\r\n<p>What version of NextJS is being taught in this course?</p>\r\n\r\n<p>In this course, I am teaching on NextJS 15.</p>\r\n\r\n<p>Is this course still relevant today?</p>\r\n\r\n<p>The course is very relevant today, as it is still version 15.</p>\r\n\r\n<p>Will this course be updated in the future?</p>\r\n\r\n<p>Yes, it will. I will update the course with new knowledge and new approaches in NextJS to meet recruitment needs.</p>', '<p>NextJS는 Vercel에서 개발한 React 기반의 오픈 소스 프레임워크로, 특히 고성능 및 SEO 요구 사항이 높은 React 웹 애플리케이션 개발을 간소화합니다. NextJS는 프런트엔드와 백엔드 개발 모두 가능한 풀스택 웹 프레임워크로도 알려져 있습니다.</p>\r\n\r\n<p>Hoang An Unicode가 설계한 이 강좌는 NextJS와 TypeScript를 결합하여 강력하고 성능이 최적화되었으며 유지보수가 쉬운 웹 애플리케이션을 개발하는 방법을 마스터하는 데 도움을 주는 것을 목표로 합니다. 기초부터 고급 개념까지 자세한 강의와 간단한 프로젝트부터 복잡한 프로젝트까지 다양한 실습을 통해 학습할 수 있습니다.</p>\r\n\r\n<p>강좌 마지막에는 NextJS 프레임워크만을 사용하여 완전한 프로젝트를 구축할 수 있도록 안내합니다.</p>\r\n\r\n<p>강좌에서 다루는 Notion 클론 프로젝트 개요:</p>\r\n\r\n<p>► Notion 프로젝트 소개 영상:</p>\r\n\r\n<p>► 프로젝트 데모:</p>\r\n\r\n<p>이 강좌를 통해 얻을 수 있는 것:</p>\r\n\r\n<p>► TypeScript 프로젝트 작업에 필요한 기초부터 고급까지의 TypeScript 지식</p>\r\n\r\n<p>► 기초부터 고급까지 NextJS에 대한 포괄적인 지식</p>\r\n\r\n<p>► NextJS에서 미들웨어를 사용하는 방법과 실제 적용 사례 이해</p>\r\n\r\n<p>► 라우트 핸들러와 서버 액션을 활용하여 서버 측 문제 해결</p>\r\n\r\n<p>► NextJS에서 클라이언트 측 및 서버 측 데이터 가져오기 이해</p>\r\n\r\n<p>► NextJS 캐싱의 강력한 기능 활용법 이해</p>\r\n\r\n<p>► SWR을 활용한 데이터 가져오기 구현 기법</p>\r\n\r\n<p>► NextJS에서 인증 구현 기법</p>\r\n\r\n<p>► NextAuth 라이브러리의 기초부터 고급까지 활용 기법</p>\r\n\r\n<p>► Prisma ORM을 사용하여 NextJS에서 데이터베이스 조작</p>\r\n\r\n<p>► NextJS의 고급 및 다국어 라우팅 기법</p>\r\n\r\n<p>► NextJS 최적화에 대한 심층적인 이해</p>\r\n\r\n<p>► Redux Toolkit 및 Redux 적용 전역 상태 관리 및 데이터 가져오기를 위한 쿼리 툴킷</p>\r\n\r\n<p>► WebSockets 및 Socket.io 라이브러리를 활용한 실시간 문제 해결</p>\r\n\r\n<p>► Jest, Testing Library 등을 활용한 NextJS 테스트 및 디버깅 학습</p>\r\n\r\n<p>► Notion 프로젝트를 처음부터 끝까지 체계적으로 구축하는 방법</p>\r\n\r\n<p>► 그 외에도 이 강좌에서는 다양한 지식, 기술 및 인기 라이브러리를 다룹니다.</p>\r\n\r\n<p>자주 묻는 질문</p>\r\n\r\n<p>이 강좌를 수강하려면 어떤 사전 지식이 필요합니까?</p>\r\n\r\n<p>NextJS는 ReactJS 프레임워크이므로 ReactJS에 대한 기초 지식이 필요합니다.</p>\r\n\r\n<p>TypeScript를 모르는데 배울 수 있나요?</p>\r\n\r\n<p>네, 이 강좌에서 처음부터 TypeScript를 배우게 됩니다.</p>\r\n\r\n<p>강의 중에 강사에게 질문할 수 있나요?</p>\r\n\r\n<p>네, 어려움이 있을 경우 Zalo 또는 그룹 채팅을 통해 강사에게 직접 메시지를 보낼 수 있습니다.</p>\r\n\r\n<p>학습을 위해 어떤 소프트웨어/도구를 설치해야 하나요?</p>\r\n\r\n<p>NextJS를 배우려면 Visual Studio Code를 사용하여 코드를 작성하고 컴퓨터에 Node.js를 설치해야 합니다. 적합한 경우 다른 코딩 소프트웨어를 사용해도 됩니다.</p>\r\n\r\n<p>강의에서 학생들을 지도하는 데 어떤 소프트웨어를 사용하시나요?</p>\r\n\r\n<p>이 강좌에서는 Visual Studio Code를 사용하여 코드를 작성합니다. 프런트엔드 개발에 강력한 지원을 제공하기 때문에 이 소프트웨어를 추천합니다.</p>\r\n\r\n<p>이 강좌에서는 어떤 버전의 NextJS를 가르치나요?</p>\r\n\r\n<p>이 강좌에서는 NextJS 15 버전을 사용합니다.</p>\r\n\r\n<p>이 강좌는 지금도 유효한가요?</p>\r\n\r\n<p>네, 15 버전을 기준으로 작성되었기 때문에 지금도 매우 유용합니다.</p>\r\n\r\n<p>이 강좌는 향후 업데이트될 예정인가요?</p>\r\n\r\n<p>네, 그렇습니다. 채용 수요에 맞춰 NextJS의 새로운 지식과 접근 방식을 반영하여 강좌를 지속적으로 업데이트할 예정입니다.</p>', '<p>NextJS는 Vercel에서 개발한 React 기반의 오픈 소스 프레임워크로, 특히 고성능 및 SEO 요구 사항이 높은 React 웹 애플리케이션 개발을 간소화합니다. NextJS는 프런트엔드와 백엔드 개발 모두 가능한 풀스택 웹 프레임워크로도 알려져 있습니다.</p>\r\n\r\n<p>Hoang An Unicode가 설계한 이 강좌는 NextJS와 TypeScript를 결합하여 강력하고 성능이 최적화되었으며 유지보수가 쉬운 웹 애플리케이션을 개발하는 방법을 마스터하는 데 도움을 주는 것을 목표로 합니다. 기초부터 고급 개념까지 자세한 강의와 간단한 프로젝트부터 복잡한 프로젝트까지 다양한 실습을 통해 학습할 수 있습니다.</p>\r\n\r\n<p>강좌 마지막에는 NextJS 프레임워크만을 사용하여 완전한 프로젝트를 구축할 수 있도록 안내합니다.</p>\r\n\r\n<p>강좌에서 다루는 Notion 클론 프로젝트 개요:</p>\r\n\r\n<p>► Notion 프로젝트 소개 영상:</p>\r\n\r\n<p>► 프로젝트 데모:</p>\r\n\r\n<p>이 강좌를 통해 얻을 수 있는 것:</p>\r\n\r\n<p>► TypeScript 프로젝트 작업에 필요한 기초부터 고급까지의 TypeScript 지식</p>\r\n\r\n<p>► 기초부터 고급까지 NextJS에 대한 포괄적인 지식</p>\r\n\r\n<p>► NextJS에서 미들웨어를 사용하는 방법과 실제 적용 사례 이해</p>\r\n\r\n<p>► 라우트 핸들러와 서버 액션을 활용하여 서버 측 문제 해결</p>\r\n\r\n<p>► NextJS에서 클라이언트 측 및 서버 측 데이터 가져오기 이해</p>\r\n\r\n<p>► NextJS 캐싱의 강력한 기능 활용법 이해</p>\r\n\r\n<p>► SWR을 활용한 데이터 가져오기 구현 기법</p>\r\n\r\n<p>► NextJS에서 인증 구현 기법</p>\r\n\r\n<p>► NextAuth 라이브러리의 기초부터 고급까지 활용 기법</p>\r\n\r\n<p>► Prisma ORM을 사용하여 NextJS에서 데이터베이스 조작</p>\r\n\r\n<p>► NextJS의 고급 및 다국어 라우팅 기법</p>\r\n\r\n<p>► NextJS 최적화에 대한 심층적인 이해</p>\r\n\r\n<p>► Redux Toolkit 및 Redux 적용 전역 상태 관리 및 데이터 가져오기를 위한 쿼리 툴킷</p>\r\n\r\n<p>► WebSockets 및 Socket.io 라이브러리를 활용한 실시간 문제 해결</p>\r\n\r\n<p>► Jest, Testing Library 등을 활용한 NextJS 테스트 및 디버깅 학습</p>\r\n\r\n<p>► Notion 프로젝트를 처음부터 끝까지 체계적으로 구축하는 방법</p>\r\n\r\n<p>► 그 외에도 이 강좌에서는 다양한 지식, 기술 및 인기 라이브러리를 다룹니다.</p>\r\n\r\n<p>자주 묻는 질문</p>\r\n\r\n<p>이 강좌를 수강하려면 어떤 사전 지식이 필요합니까?</p>\r\n\r\n<p>NextJS는 ReactJS 프레임워크이므로 ReactJS에 대한 기초 지식이 필요합니다.</p>\r\n\r\n<p>TypeScript를 모르는데 배울 수 있나요?</p>\r\n\r\n<p>네, 이 강좌에서 처음부터 TypeScript를 배우게 됩니다.</p>\r\n\r\n<p>강의 중에 강사에게 질문할 수 있나요?</p>\r\n\r\n<p>네, 어려움이 있을 경우 Zalo 또는 그룹 채팅을 통해 강사에게 직접 메시지를 보낼 수 있습니다.</p>\r\n\r\n<p>학습을 위해 어떤 소프트웨어/도구를 설치해야 하나요?</p>\r\n\r\n<p>NextJS를 배우려면 Visual Studio Code를 사용하여 코드를 작성하고 컴퓨터에 Node.js를 설치해야 합니다. 적합한 경우 다른 코딩 소프트웨어를 사용해도 됩니다.</p>\r\n\r\n<p>강의에서 학생들을 지도하는 데 어떤 소프트웨어를 사용하시나요?</p>\r\n\r\n<p>이 강좌에서는 Visual Studio Code를 사용하여 코드를 작성합니다. 프런트엔드 개발에 강력한 지원을 제공하기 때문에 이 소프트웨어를 추천합니다.</p>\r\n\r\n<p>이 강좌에서는 어떤 버전의 NextJS를 가르치나요?</p>\r\n\r\n<p>이 강좌에서는 NextJS 15 버전을 사용합니다.</p>\r\n\r\n<p>이 강좌는 지금도 유효한가요?</p>\r\n\r\n<p>네, 15 버전을 기준으로 작성되었기 때문에 지금도 매우 유용합니다.</p>\r\n\r\n<p>이 강좌는 향후 업데이트될 예정인가요?</p>\r\n\r\n<p>네, 그렇습니다. 채용 수요에 맞춰 NextJS의 새로운 지식과 접근 방식을 반영하여 강좌를 지속적으로 업데이트할 예정입니다.</p>', '<p>NextJS 是一个基于 React 基础架构的开源框架，由 Vercel 开发，旨在简化 React Web 应用的开发，尤其适用于对性能和 SEO 有较高要求的应用。NextJS 也被称为全栈 Web 框架（能够同时进行前端和后端开发）。</p>\r\n\r\n<p>本课程由 Hoang An Unicode 设计，旨在帮助您掌握如何将 NextJS 与 TypeScript 结合使用，开发功能强大、性能优化且易于维护的 Web 应用。您将从基础到高级学习相关概念，课程包含详细的讲解和从简单到复杂的实践项目。</p>\r\n\r\n<p>课程结束时，您将在指导下使用 NextJS 框架构建一个完整的项目。</p>\r\n\r\n<p>课程中的 Notion Clone 项目概览：</p>\r\n\r\n<p>► Notion 项目介绍视频：</p>\r\n\r\n<p>► 项目演示：</p>\r\n\r\n<p>您将从本课程中获得什么？</p>\r\n\r\n<p>► 足以胜任 TypeScript 项目开发的 TypeScript 基础到高级知识。</p>\r\n\r\n<p>► 从基础到高级的 NextJS 全面知识。</p>\r\n\r\n<p>► 理解 NextJS 中中间件的使用方法及其实际应用。</p>\r\n\r\n<p>► 应用路由处理器和服务器操作来解决服务器端问题。</p>\r\n\r\n<p>► 理解 NextJS 中的客户端和服务端数据获取。</p>\r\n\r\n<p>► 理解如何利用 NextJS 中的缓存机制。</p>\r\n\r\n<p>► 使用 SWR 实现数据获取的技巧。</p>\r\n\r\n<p>► NextJS 中的身份验证技巧。</p>\r\n\r\n<p>► 从基础到高级，掌握 NextAuth 库的使用技巧。</p>\r\n\r\n<p>► 使用 Prisma ORM 在 NextJS 中操作数据库。</p>\r\n\r\n<p>► NextJS 中的高级路由和多语言路由技巧。</p>\r\n\r\n<p>► 深入理解 NextJS 中的优化。</p>\r\n\r\n<p>► 应用 Redux Toolkit 和 Redux。用于全局状态管理和数据获取的查询工具包</p>\r\n\r\n<p>► 应用 WebSocket 和 Socket.io 库解决实时问题</p>\r\n\r\n<p>► 通过 Jest、Testing Library 等工具学习 NextJS 中的测试和调试</p>\r\n\r\n<p>► 从头到尾构建一个系统的 Notion 项目</p>\r\n\r\n<p>► 以及课程中分享的更多知识、技术和常用库</p>\r\n\r\n<p>常见问题解答</p>\r\n\r\n<p>本课程需要哪些先验知识？</p>\r\n\r\n<p>NextJS 是一个 ReactJS 框架，因此您需要具备 ReactJS 的基础知识。</p>\r\n\r\n<p>我不懂 TypeScript，还能学习吗？</p>\r\n\r\n<p>可以，本课程将从头开始教授 TypeScript。</p>\r\n\r\n<p>课程期间我可以向老师提问吗？</p>\r\n\r\n<p>可以，如果您遇到任何问题，可以通过 Zalo 或您所在的小组直接联系老师。</p>\r\n\r\n<p>学习本课程需要安装哪些软件/工具？</p>\r\n\r\n<p>要学习 NextJS，您需要使用 Visual Studio Code 编写代码，并在您的计算机上安装 Node.js。如果合适，您可以使用其他编码软件。</p>\r\n\r\n<p>您在课程中使用什么软件来指导学生？</p>\r\n\r\n<p>在本课程中，我使用 Visual Studio Code 编写代码。我推荐这款软件，因为它为前端开发提供了强大的支持。</p>\r\n\r\n<p>本课程教授的是哪个版本的 NextJS？</p>\r\n\r\n<p>本课程教授的是 NextJS 15。</p>\r\n\r\n<p>这门课程现在还适用吗？</p>\r\n\r\n<p>这门课程现在仍然非常适用，因为它仍然基于 NextJS 15 版本。</p>\r\n\r\n<p>这门课程将来会更新吗？</p>\r\n\r\n<p>是的，会的。我会根据招聘需求，在 NextJS 领域加入新的知识和方法来更新课程。</p>', 6, '/storage/photos/2/3b83512c-01eb-486e-9312-90feb2b9a451.jpg', 2950000.00, NULL, 995000.00, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'KH319978', 504.00, 0, '<p>Học tr&ecirc;n mọi thiết bị</p>\r\n\r\n<p>Code mẫu, t&agrave;i liệu đầy đủ</p>\r\n\r\n<p>Hỗ trợ 1-1 bởi giảng vi&ecirc;n, nh&oacute;m k&iacute;n</p>\r\n\r\n<p>Giới thiệu c&ocirc;ng việc ph&ugrave; hợp</p>\r\n\r\n<p>Thời hạn: Vĩnh viễn</p>', '<p>Learn on any device</p>\r\n\r\n<p>Sample code, complete documentation</p>\r\n\r\n<p>One-on-one support by instructors, private group</p>\r\n\r\n<p>Job placement assistance</p>\r\n\r\n<p>Duration: Lifetime</p>', '<p>모든 기기에서 학습 가능</p>\r\n\r\n<p>すべてのデバイスで学習可能</p>\r\n\r\n<p>예제 코드 및 완전한 학습 자료 제공</p>\r\n\r\n<p>강사의 1:1 지원 및 전용 커뮤니티</p>\r\n\r\n<p>역량에 맞는 취업 기회 제공</p>\r\n\r\n<p>평생 이용 가능</p>', '<p>すべてのデバイスで学習可能</p>\r\n\r\n<p>サンプルコードと完全な資料付き</p>\r\n\r\n<p>講師による1対1サポートと専用コミュニティ</p>\r\n\r\n<p>スキルに合った就職機会の紹介</p>\r\n\r\n<p>無期限アクセス</p>', '<p>支持在所有设备上学习</p>\r\n\r\n<p>提供示例代码和完整学习资料</p>\r\n\r\n<p>讲师一对一指导及专属学习社群</p>\r\n\r\n<p>推荐适合的工作机会</p>\r\n\r\n<p>永久访问</p>', 1, 'all_lessons', 0, NULL, NULL, NULL, NULL, 0, 0, 4, '2026-02-03 08:52:53', '2026-03-29 15:08:15', NULL),
(14, 'Lập trình TypeScript từ cơ bản đến nâng cao', 'TypeScript Programming from Basic to Advanced', 'TypeScript 기초부터 고급까지 프로그래밍', 'TypeScript 基礎から上級までプログラミング', 'TypeScript 从基础到高级编程', 'lap-trinh-typescript-tu-co-ban-den-nang-cao', 'typescript-programming-from-basic-to-advanced', 'typescript-기초부터-고급까지-프로그래밍', 'typescript-基礎から上級までプログラミング', 'typescript-从基础到高级编程', '<p>Bạn đ&atilde; từng l&agrave;m việc với JavaScript nhưng muốn code của m&igrave;nh an to&agrave;n, dễ bảo tr&igrave; v&agrave; chuy&ecirc;n nghiệp hơn? H&atilde;y tham gia&nbsp;<strong>kh&oacute;a học TypeScript từ cơ bản đến n&acirc;ng cao</strong>!</p>\r\n\r\n<h4><strong>Điểm nổi bật của kh&oacute;a học:</strong></h4>\r\n\r\n<ul>\r\n	<li>Nội dung chi tiết từ cơ bản đến n&acirc;ng cao, ph&ugrave; hợp cho cả người mới bắt đầu v&agrave; những ai đ&atilde; c&oacute; kinh nghiệm.</li>\r\n	<li>Học qua c&aacute;c dự &aacute;n thực tế, gi&uacute;p bạn hiểu r&otilde; c&aacute;ch &aacute;p dụng TypeScript trong c&ocirc;ng việc.</li>\r\n	<li>T&agrave;i liệu hỗ trợ phong ph&uacute;, k&egrave;m b&agrave;i tập thực h&agrave;nh chi tiết.</li>\r\n	<li>Được hướng dẫn bởi giảng vi&ecirc;n gi&agrave;u kinh nghiệm, sẵn s&agrave;ng hỗ trợ giải đ&aacute;p thắc mắc.</li>\r\n</ul>\r\n\r\n<h4><strong>Kết quả đạt được sau kh&oacute;a học:</strong></h4>\r\n\r\n<ul>\r\n	<li>Th&agrave;nh thạo TypeScript v&agrave; tự tin &aacute;p dụng v&agrave;o c&aacute;c dự &aacute;n.</li>\r\n	<li>Biết c&aacute;ch x&acirc;y dựng code sạch, dễ bảo tr&igrave;, v&agrave; hạn chế tối đa lỗi runtime.</li>\r\n	<li>N&acirc;ng cao năng lực lập tr&igrave;nh, sẵn s&agrave;ng cho c&aacute;c cơ hội nghề nghiệp cao cấp hơn.</li>\r\n</ul>\r\n\r\n<h4><strong>Ai n&ecirc;n tham gia kh&oacute;a học n&agrave;y?</strong></h4>\r\n\r\n<ul>\r\n	<li>Lập tr&igrave;nh vi&ecirc;n Front-End hoặc Back-End muốn n&acirc;ng cấp kỹ năng với TypeScript.</li>\r\n	<li>Những người đ&atilde; quen với JavaScript v&agrave; muốn học c&aacute;ch viết code chuy&ecirc;n nghiệp hơn.</li>\r\n	<li>Sinh vi&ecirc;n hoặc người mới học lập tr&igrave;nh c&oacute; định hướng l&agrave;m việc với c&aacute;c framework hiện đại như React, Angular, hoặc Node.js.</li>\r\n</ul>', '<p>Have you worked with JavaScript before but want your code to be safer, more maintainable, and more professional? Join our TypeScript course from basic to advanced!</p>\r\n\r\n<p>Course Highlights:<br />\r\nDetailed content from basic to advanced, suitable for both beginners and experienced users.<br />\r\nLearning through real-world projects, helping you understand how to apply TypeScript in your work.<br />\r\nAbundant supporting materials, including detailed practice exercises.<br />\r\nInstructed by experienced instructors, ready to answer your questions.<br />\r\nAchievements after the course:<br />\r\nMastering TypeScript and confidently applying it to projects.<br />\r\nLearning how to build clean, maintainable code and minimize runtime errors.<br />\r\nImproving your programming skills, preparing you for higher-level career opportunities.<br />\r\nWho should take this course?<br />\r\nFront-End or Back-End programmers who want to upgrade their skills with TypeScript.<br />\r\nThose who are already familiar with JavaScript and want to learn how to write more professional code.<br />\r\nStudents or beginners in programming who aim to work with modern frameworks such as React, Angular, or Node.js.</p>', '<p>자바스크립트를 사용해 본 경험은 있지만, 코드를 더 안전하고 유지보수하기 쉽고 전문적으로 만들고 싶으신가요? 기초부터 고급까지 TypeScript 강좌에 참여하세요!</p>\r\n\r\n<p>강좌 주요 특징:<br />\r\n초보자부터 숙련자까지 모두에게 적합한 기초부터 고급까지 상세한 내용 제공<br />\r\n실제 프로젝트를 통해 TypeScript를 업무에 적용하는 방법을 이해하도록 지원<br />\r\n상세한 연습 문제를 포함한 풍부한 학습 자료 제공<br />\r\n질문에 답변해 줄 준비가 되어 있는 경험 많은 강사진의 강의<br />\r\n강좌 수료 후 기대 효과:<br />\r\nTypeScript를 완벽하게 마스터하고 프로젝트에 자신 있게 적용<br />\r\n깔끔하고 유지보수하기 쉬운 코드를 작성하고 런타임 오류를 최소화하는 방법 습득<br />\r\n프로그래밍 실력 향상으로 더 높은 수준의 경력 기회에 대비<br />\r\n수강 대상:<br />\r\nTypeScript를 활용하여 실력을 향상시키고자 하는 프론트엔드 또는 백엔드 프로그래머<br />\r\n자바스크립트에 이미 익숙하지만 더 전문적인 코드를 작성하고 싶은 분<br />\r\nReact, Angular, Node.js와 같은 최신 프레임워크를 사용해보고자 하는 프로그래밍 전공 학생 또는 초보자</p>', '<p>JavaScript を使った経験はあっても、コードをより安全で、メンテナンスしやすく、プロフェッショナルなものにしたいとお考えですか？TypeScript の基礎から上級まで学べるコースにぜひご参加ください！</p>\r\n\r\n<p>コースのハイライト：<br />\r\n基礎から上級まで、初心者にも経験豊富な方にも適した詳細なコンテンツです。<br />\r\n実際のプロジェクトを通して学習することで、TypeScript を業務にどのように適用するかを理解できます。<br />\r\n詳細な練習問題を含む豊富なサポート資料をご用意しています。<br />\r\n経験豊富な講師陣が、ご質問にお答えします。<br />\r\nコース修了後の成果：<br />\r\nTypeScript を習得し、自信を持ってプロジェクトに適用できるようになります。<br />\r\nクリーンでメンテナンス性の高いコードを構築し、実行時エラーを最小限に抑える方法を学ぶことができます。<br />\r\nプログラミングスキルを向上させ、より高いレベルのキャリアアップを目指します。<br />\r\nこのコースはどのような方に適していますか？<br />\r\nTypeScript のスキルアップを目指したいフロントエンドまたはバックエンドプログラマー。<br />\r\n既に JavaScript に精通していて、よりプロフェッショナルなコードの書き方を学びたい方。<br />\r\nReact、Angular、Node.js などの最新のフレームワークの使用を目指すプログラミングの学生または初心者。</p>', '<p>您之前使用过 JavaScript，但希望代码更安全、更易维护、更专业吗？欢迎参加我们的 TypeScript 课程，从基础到高级，全面提升您的 TypeScript 技能！</p>\r\n\r\n<p>课程亮点：</p>\r\n\r\n<p>内容详尽，涵盖从基础到高级的各个方面，适合初学者和经验丰富的用户。</p>\r\n\r\n<p>通过真实项目进行学习，帮助您了解如何在工作中应用 TypeScript。</p>\r\n\r\n<p>丰富的配套学习资料，包括详细的练习题。</p>\r\n\r\n<p>由经验丰富的讲师授课，随时解答您的疑问。</p>\r\n\r\n<p>课程结束后您将获得的成就：</p>\r\n\r\n<p>精通 TypeScript，并能自信地将其应用于各种项目中。</p>\r\n\r\n<p>学习如何编写简洁、易维护的代码，并最大限度地减少运行时错误。</p>\r\n\r\n<p>提升您的编程技能，为更高层次的职业发展做好准备。</p>\r\n\r\n<p>适合人群：</p>\r\n\r\n<p>希望提升 TypeScript 技能的前端或后端程序员。</p>\r\n\r\n<p>熟悉 JavaScript 并希望学习如何编写更专业代码的用户。</p>\r\n\r\n<p>希望学习使用 React、Angular 或 Node.js 等现代框架的编程学生或初学者。</p>', 6, '/storage/photos/2/d6478fcf-596c-4a30-8383-dae2007a189d.jpg', 0.00, NULL, 0.00, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'KH512952', 522.00, 0, '<p>Học tr&ecirc;n mọi thiết bị</p>\r\n\r\n<p>Code mẫu, t&agrave;i liệu đầy đủ</p>\r\n\r\n<p>Hỗ trợ 1-1 bởi giảng vi&ecirc;n, nh&oacute;m k&iacute;n</p>\r\n\r\n<p>Giới thiệu c&ocirc;ng việc ph&ugrave; hợp</p>\r\n\r\n<p>Thời hạn: Vĩnh viễn</p>', '<p>Learn on any device</p>\r\n\r\n<p>Sample code, complete documentation</p>\r\n\r\n<p>One-on-one support by instructors, private group</p>\r\n\r\n<p>Job placement assistance</p>\r\n\r\n<p>Duration: Lifetime</p>', '<p>모든 기기에서 학습 가능</p>\r\n\r\n<p>すべてのデバイスで学習可能</p>\r\n\r\n<p>예제 코드 및 완전한 학습 자료 제공</p>\r\n\r\n<p>강사의 1:1 지원 및 전용 커뮤니티</p>\r\n\r\n<p>역량에 맞는 취업 기회 제공</p>\r\n\r\n<p>평생 이용 가능</p>', '<p>すべてのデバイスで学習可能</p>\r\n\r\n<p>サンプルコードと完全な資料付き</p>\r\n\r\n<p>講師による1対1サポートと専用コミュニティ</p>\r\n\r\n<p>スキルに合った就職機会の紹介</p>\r\n\r\n<p>無期限アクセス</p>', '<p>支持在所有设备上学习</p>\r\n\r\n<p>提供示例代码和完整学习资料</p>\r\n\r\n<p>讲师一对一指导及专属学习社群</p>\r\n\r\n<p>推荐适合的工作机会</p>\r\n\r\n<p>永久访问</p>', 1, 'all_lessons', 0, NULL, NULL, NULL, NULL, 0, 0, 5, '2026-02-03 08:54:21', '2026-05-12 15:53:27', NULL),
(15, 'Lập trình PHP & MySQL cơ bản dành cho người mới', 'Basic PHP & MySQL Programming for Beginners', '초보자를 위한 PHP & MySQL 기초 프로그래밍', '初心者向け PHP & MySQL 基礎プログラミング', '面向初学者的 PHP 与 MySQL 基础编程', 'lap-trinh-php-mysql-co-ban-danh-cho-nguoi-moi', 'basic-php-mysql-programming-for-beginners', '초보자를-위한-php-mysql-기초-프로그래밍', '初心者向け-php-mysql-基礎プログラミング', '面向初学者的-php-与-mysql-基础编程', '<p>Kho&aacute; học PHP &amp; MySQL cơ bản d&agrave;nh cho người mới bao gồm gần 300&nbsp;video hướng dẫn học vi&ecirc;n học lập tr&igrave;nh PHP &amp; MySQL từ d&ograve;ng code đầu ti&ecirc;n cho đến khi l&agrave;m được sản phẩm</p>\r\n\r\n<p><strong>Bạn sẽ nhận được g&igrave; tại kh&oacute;a học?</strong></p>\r\n\r\n<ul>\r\n	<li>Hiểu r&otilde; bản chất cốt l&otilde;i ng&ocirc;n ngữ lập tr&igrave;nh PHP</li>\r\n	<li>Được học theo lộ tr&igrave;nh r&otilde; r&agrave;ng, b&agrave;i bản</li>\r\n	<li>Nắm vững kiến thức nền tảng của ng&ocirc;n ngữ lập tr&igrave;nh PHP, từ đ&oacute; dễ d&agrave;ng xử l&yacute; c&aacute;c b&agrave;i to&aacute;n phức tạp, tiếp cận c&aacute;c kiến thức n&acirc;ng cao</li>\r\n	<li>Được r&egrave;n luyện về logic, thuật to&aacute;n qua c&aacute;c b&agrave;i tập trong kho&aacute; học</li>\r\n	<li>Nắm vững kiến thức về cơ sở dữ liệu, ng&ocirc;n ngữ truy vấn SQL từ đ&oacute; x&acirc;y dựng ứng dụng thực tế kết hợp PHP &amp; MySQL</li>\r\n	<li>Từng bước x&acirc;y dựng website ho&agrave;n chỉnh từ đầu đến khi đẩy website l&ecirc;n Internet</li>\r\n	<li>Được hỗ trợ trực tiếp bởi giảng vi&ecirc;n, qua group k&iacute;n,...</li>\r\n</ul>\r\n\r\n<p><strong>C&acirc;u hỏi thường gặp?</strong></p>\r\n\r\n<p><em><strong>Kh&oacute;a học n&agrave;y cần kiến thức nền g&igrave; kh&ocirc;ng?</strong></em></p>\r\n\r\n<p>Trong kh&oacute;a học m&igrave;nh c&oacute; c&aacute;c v&iacute; dụ sử dụng HTML - CSS - Javascript n&ecirc;n bạn cần nắm được kiến thức cơ bản để tr&aacute;nh bỡ ngỡ khi tham gia</p>\r\n\r\n<p><em><strong>T&ocirc;i chưa biết g&igrave; về PHP c&oacute; tham gia được kh&ocirc;ng?</strong></em></p>\r\n\r\n<p>Được. Đ&acirc;y l&agrave; kh&oacute;a học PHP d&agrave;nh cho người mới n&ecirc;n rất ph&ugrave; hợp với những bạn chưa biết g&igrave; về PHP v&agrave; Database</p>\r\n\r\n<p><em><strong>Trong qu&aacute; tr&igrave;nh học, t&ocirc;i c&oacute; được hỏi giảng vi&ecirc;n kh&ocirc;ng?</strong></em></p>\r\n\r\n<p>Được, nếu gặp kh&oacute; khăn bạn c&oacute; thể nhắn tin trực tiếp cho giảng vi&ecirc;n qua Zalo hoặc tr&ecirc;n group được tham gia</p>\r\n\r\n<p><em><strong>T&ocirc;i cần c&agrave;i đặt những phần mềm/c&ocirc;ng cụ g&igrave; để học?</strong></em></p>\r\n\r\n<p>Để học PHP, bạn cần c&agrave;i đặt phần mềm để viết code (PHPStorrm, Visual Studio Code,...) v&agrave; phần mềm tạo Server ảo&nbsp;(Xampp, Wamp, Ampps,...)</p>\r\n\r\n<p><em><strong>Trong b&agrave;i giảng bạn d&ugrave;ng phần mềm n&agrave;o để hướng dẫn học vi&ecirc;n?</strong></em></p>\r\n\r\n<p>Trong kh&oacute;a học, m&igrave;nh đang d&ugrave;ng phần mềm PHPStorm để viết code v&agrave; Ampps để tạo Server ảo. Bạn ho&agrave;n to&agrave;n c&oacute; thể d&ugrave;ng Visual Studio Code để viết code v&agrave; bất kỳ phần mềm tạo Server ảo n&agrave;o</p>\r\n\r\n<p><em><strong>Trong kh&oacute;a học đang hướng dẫn ở phi&ecirc;n bản PHP bao nhi&ecirc;u?</strong></em></p>\r\n\r\n<p>Tại thời điểm quay kh&oacute;a học, m&igrave;nh đang d&ugrave;ng phi&ecirc;n bản 7.3</p>\r\n\r\n<p><em><strong>Kh&oacute;a học n&agrave;y c&ograve;n ph&ugrave; hợp với thời điểm hiện tại kh&ocirc;ng?</strong></em></p>\r\n\r\n<p>Tuy rằng hiện tại PHP l&agrave; phi&ecirc;n bản 8.x nhưng bạn ho&agrave;n to&agrave;n c&oacute; thể sử dụng kiến thức trong b&agrave;i giảng để học m&agrave; kh&ocirc;ng bị ảnh hưởng g&igrave;</p>\r\n\r\n<p><em><strong>Kh&oacute;a học n&agrave;y c&oacute; được cập nhật trong tương lai kh&ocirc;ng?</strong></em></p>\r\n\r\n<p>C&oacute; bạn nh&eacute;. M&igrave;nh sẽ cập nhật những kiến thức mới, tư duy mới trong PHP ph&ugrave; hợp với y&ecirc;u cầu v&agrave; xu hướng trong tuyển dụng</p>', '<p>The basic PHP &amp; MySQL course for beginners includes nearly 300 videos guiding students through PHP &amp; MySQL programming from the first line of code to creating a finished product.</p>\r\n\r\n<p>What will you gain from this course?</p>\r\n\r\n<p>A thorough understanding of the core principles of the PHP programming language<br />\r\nA clear and structured learning path<br />\r\nMastering the fundamental knowledge of the PHP programming language, making it easy to handle complex problems and access advanced concepts<br />\r\nDeveloping logic and algorithms through course exercises<br />\r\nMastering database knowledge and SQL query language, enabling the creation of practical applications combining PHP &amp; MySQL<br />\r\nBuilding a complete website step-by-step from scratch to launching it online<br />\r\nReceiving direct support from instructors, through a private group, etc.<br />\r\nFrequently Asked Questions?</p>\r\n\r\n<p>What prior knowledge is required for this course?</p>\r\n\r\n<p>The course includes examples using HTML, CSS, and Javascript, so you need to have a basic understanding to avoid feeling lost.</p>\r\n\r\n<p>Can I participate even if I don&#39;t know anything about PHP?</p>\r\n\r\n<p>Yes. This is a PHP course for beginners, so it&#39;s very suitable for those who have no prior knowledge of PHP and databases.</p>\r\n\r\n<p>Can I ask the instructor questions during the course?</p>\r\n\r\n<p>Yes, if you encounter difficulties, you can message the instructor directly via Zalo or in the group you&#39;re in.</p>\r\n\r\n<p>What software/tools do I need to install to learn?</p>\r\n\r\n<p>To learn PHP, you need to install software to write code (PHPStorm, Visual Studio Code, etc.) and software to create a virtual server (Xampp, Wamp, Ampps, etc.).</p>\r\n\r\n<p>Which software do you use in the lectures to guide students?</p>\r\n\r\n<p>In this course, I&#39;m using PHPStorm to write code and Ampps to create a virtual server. You can absolutely use Visual Studio Code to write code and any virtual server software.</p>\r\n\r\n<p>What PHP version is being used in this course?</p>\r\n\r\n<p>At the time of recording the course, I was using version 7.3.</p>\r\n\r\n<p>Is this course still relevant today?</p>\r\n\r\n<p>Although PHP is currently version 8.x, you can still use the knowledge from the lectures without any issues.</p>\r\n\r\n<p>Will this course be updated in the future?</p>\r\n\r\n<p>Yes, it will. I will update the course with new knowledge and approaches in PHP to meet the requirements and trends in recruitment.</p>', '<p>PHP &amp; MySQL 기초 과정은 초보자를 위해 약 300개의 비디오 강의로 구성되어 있으며, 첫 코드 작성부터 최종 결과물 제작까지 PHP와 MySQL 프로그래밍의 모든 단계를 안내합니다.</p>\r\n\r\n<p>이 과정을 통해 무엇을 얻을 수 있을까요?</p>\r\n\r\n<p>PHP 프로그래밍 언어의 핵심 원리에 대한 완벽한 이해<br />\r\n명확하고 체계적인 학습 경로<br />\r\nPHP 프로그래밍 언어의 기초 지식을 습득하여 복잡한 문제를 쉽게 해결하고 고급 개념을 활용할 수 있게 됩니다.<br />\r\n강의 연습 문제를 통해 논리와 알고리즘을 개발할 수 있습니다.<br />\r\n데이터베이스 지식과 SQL 쿼리 언어를 숙달하여 PHP와 MySQL을 결합한 실용적인 애플리케이션을 개발할 수 있습니다.<br />\r\n웹사이트를 처음부터 단계별로 구축하고 온라인에 게시할 수 있습니다.<br />\r\n강사, 개인 그룹 등을 통해 직접적인 지원을 받을 수 있습니다.<br />\r\n자주 묻는 질문?</p>\r\n\r\n<p>이 과정을 수강하려면 어떤 사전 지식이 필요합니까?</p>\r\n\r\n<p>이 과정에는 HTML, CSS, JavaScript를 사용한 예제가 포함되어 있으므로, 어려움을 느끼지 않으려면 이러한 기본 사항에 대한 이해가 필요합니다.</p>\r\n\r\n<p>PHP에 대해 전혀 몰라도 참여할 수 있습니까?</p>\r\n\r\n<p>네. 이 강좌는 PHP 초보자를 위한 강좌이므로 PHP와 데이터베이스에 대한 사전 지식이 없는 분들에게 매우 적합합니다.</p>\r\n\r\n<p>강의 중에 강사에게 질문할 수 있나요?</p>\r\n\r\n<p>네, 어려움이 있을 경우 Zalo를 통해 강사에게 직접 메시지를 보내거나 참여 중인 그룹에서 질문할 수 있습니다.</p>\r\n\r\n<p>학습을 위해 어떤 소프트웨어/도구를 설치해야 하나요?</p>\r\n\r\n<p>PHP를 배우려면 코드 작성 소프트웨어(PHPStorm, Visual Studio Code 등)와 가상 서버 생성 소프트웨어(Xampp, Wamp, Ampps 등)를 설치해야 합니다.</p>\r\n\r\n<p>강의에서는 어떤 소프트웨어를 사용하여 학생들을 지도하나요?</p>\r\n\r\n<p>이 강좌에서는 코드 작성에는 PHPStorm을, 가상 서버 생성에는 Ampps를 사용합니다. Visual Studio Code를 사용하거나 다른 가상 서버 소프트웨어를 사용하셔도 무방합니다.</p>\r\n\r\n<p>이 강좌에서는 어떤 PHP 버전을 사용하나요?</p>\r\n\r\n<p>강의 녹화 당시에는 PHP 7.3 버전을 사용했습니다.</p>\r\n\r\n<p>이 강좌는 현재에도 여전히 유효한가요?</p>\r\n\r\n<p>PHP는 현재 8.x 버전이지만, 강의에서 배운 내용은 문제없이 활용할 수 있습니다.</p>\r\n\r\n<p>이 강좌는 향후 업데이트될 예정인가요?</p>\r\n\r\n<p>네, 그렇습니다. 채용 시장의 요구 사항과 트렌드에 맞춰 PHP의 새로운 지식과 접근 방식을 반영하여 강좌를 지속적으로 업데이트할 예정입니다.</p>', '<p>初心者向けのPHP &amp; MySQL基礎コースには、約300本のビデオが含まれており、最初のコード行から完成品の作成まで、PHPとMySQLプログラミングを網羅しています。</p>\r\n\r\n<p>このコースで得られるもの</p>\r\n\r\n<p>PHPプログラミング言語の基本原則を徹底的に理解</p>\r\n\r\n<p>明確で体系的な学習パス<br />\r\nPHPプログラミング言語の基礎知識を習得し、複雑な問題への対処を容易にし、高度な概念にもアクセスできるようになる</p>\r\n\r\n<p>コースの演習を通してロジックとアルゴリズムを開発する</p>\r\n\r\n<p>データベースの知識とSQLクエリ言語を習得し、PHPとMySQLを組み合わせた実用的なアプリケーションを作成できるようになる</p>\r\n\r\n<p>完全なウェブサイトをゼロから段階的に構築し、オンラインで公開する</p>\r\n\r\n<p>プライベートグループなどを通じて、講師から直接サポートを受けることができる<br />\r\nよくある質問</p>\r\n\r\n<p>このコースを受講するには、どのような事前知識が必要ですか？</p>\r\n\r\n<p>このコースでは、HTML、CSS、JavaScriptを使用した例題が取り上げられるため、迷うことがないよう基本的な知識が必要です。</p>\r\n\r\n<p>PHPについて全く知識がなくても参加できますか？</p>\r\n\r\n<p>はい。このコースは初心者向けのPHPコースなので、PHPとデータベースの知識が全くない方にも最適です。</p>\r\n\r\n<p>コース中に講師に質問することはできますか？</p>\r\n\r\n<p>はい。困った場合は、Zaloまたは参加しているグループで講師に直接メッセージを送信できます。</p>\r\n\r\n<p>学習に必要なソフトウェアやツールは何ですか？</p>\r\n\r\n<p>PHPを学習するには、コード記述ソフトウェア（PHPStorm、Visual Studio Codeなど）と仮想サーバー構築ソフトウェア（Xampp、Wamp、Amppsなど）をインストールする必要があります。</p>\r\n\r\n<p>講義では、学生の指導にどのようなソフトウェアを使用していますか？</p>\r\n\r\n<p>このコースでは、コード記述にPHPStorm、仮想サーバー構築にAmppsを使用しています。コード記述にはVisual Studio Codeを使用し、仮想サーバーソフトウェアはどれでも構いません。</p>\r\n\r\n<p>このコースで使用されているPHPのバージョンは何ですか？</p>\r\n\r\n<p>コース収録時はバージョン7.3を使用していました。</p>\r\n\r\n<p>このコースは現在でも役立ちますか？</p>\r\n\r\n<p>PHPは現在バージョン8.xですが、講義で学んだ知識は問題なくご利用いただけます。</p>\r\n\r\n<p>このコースは今後更新されますか？</p>\r\n\r\n<p>はい、更新されます。採用の要件やトレンドに合わせて、PHPに関する新しい知識とアプローチを盛り込み、コースを更新していきます。</p>', '<p>这门面向初学者的 PHP &amp; MySQL 基础课程包含近 300 个视频，指导学员从编写第一行代码到创建最终产品，逐步掌握 PHP &amp; MySQL 编程。</p>\r\n\r\n<p>您将从这门课程中获得什么？</p>\r\n\r\n<p>对 PHP 编程语言核心原理的透彻理解</p>\r\n\r\n<p>清晰且结构化的学习路径</p>\r\n\r\n<p>掌握 PHP 编程语言的基础知识，轻松应对复杂问题并掌握高级概念</p>\r\n\r\n<p>通过课程练习培养逻辑思维和算法能力</p>\r\n\r\n<p>掌握数据库知识和 SQL 查询语言，能够创建结合 PHP 和 MySQL 的实用应用程序</p>\r\n\r\n<p>从零开始逐步构建完整的网站并上线</p>\r\n\r\n<p>获得讲师的直接支持，以及通过私人小组等方式获得帮助</p>\r\n\r\n<p>常见问题？</p>\r\n\r\n<p>这门课程需要哪些先验知识？</p>\r\n\r\n<p>课程包含使用 HTML、CSS 和 Javascript 的示例，因此您需要具备一定的基础知识，以免感到迷茫。</p>\r\n\r\n<p>即使我对 PHP 一无所知，也可以参加吗？</p>\r\n\r\n<p>可以。这是一门面向初学者的 PHP 课程，非常适合没有任何 PHP 和数据库基础的人。</p>\r\n\r\n<p>课程期间我可以向老师提问吗？</p>\r\n\r\n<p>可以，如果您遇到任何困难，可以通过 Zalo 或您所在的群组直接联系老师。</p>\r\n\r\n<p>我需要安装哪些软件/工具才能学习？</p>\r\n\r\n<p>学习 PHP 需要安装一些软件，例如用于编写代码的软件（如 PHPStorm、Visual Studio Code 等）以及用于创建虚拟服务器的软件（如 Xampp、Wamp、Ampps 等）。</p>\r\n\r\n<p>您在授课时使用哪些软件来指导学生？</p>\r\n\r\n<p>在本课程中，我使用 PHPStorm 编写代码，并使用 Ampps 创建虚拟服务器。您当然也可以使用 Visual Studio Code 编写代码，并使用任何虚拟服务器软件。</p>\r\n\r\n<p>本课程使用的是哪个 PHP 版本？</p>\r\n\r\n<p>录制课程时，我使用的是 7.3 版本。</p>\r\n\r\n<p>这门课程现在还适用吗？</p>\r\n\r\n<p>虽然 PHP 目前是 8.x 版本，但您仍然可以毫无问题地运用课程中学到的知识。</p>\r\n\r\n<p>这门课程未来会更新吗？</p>\r\n\r\n<p>是的，会的。我会根据招聘需求和趋势，不断更新课程内容，加入 PHP 的最新知识和方法。</p>', 6, '/storage/photos/2/4dd484eb-f278-4a3f-b6e2-52031f5d8485.jpg', 2095000.00, NULL, 795500.00, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'KH448799', 7970.00, 1, '<p>Học tr&ecirc;n mọi thiết bị</p>\r\n\r\n<p>Code mẫu, t&agrave;i liệu đầy đủ</p>\r\n\r\n<p>Hỗ trợ 1-1 bởi giảng vi&ecirc;n, nh&oacute;m k&iacute;n</p>\r\n\r\n<p>Giới thiệu c&ocirc;ng việc ph&ugrave; hợp</p>\r\n\r\n<p>Thời hạn: Vĩnh viễn</p>', '<p>Learn on any device</p>\r\n\r\n<p>Sample code, complete documentation</p>\r\n\r\n<p>One-on-one support by instructors, private group</p>\r\n\r\n<p>Job placement assistance</p>\r\n\r\n<p>Duration: Lifetime</p>', '<p>모든 기기에서 학습 가능</p>\r\n\r\n<p>すべてのデバイスで学習可能</p>\r\n\r\n<p>예제 코드 및 완전한 학습 자료 제공</p>\r\n\r\n<p>강사의 1:1 지원 및 전용 커뮤니티</p>\r\n\r\n<p>역량에 맞는 취업 기회 제공</p>\r\n\r\n<p>평생 이용 가능</p>', '<p>すべてのデバイスで学習可能</p>\r\n\r\n<p>サンプルコードと完全な資料付き</p>\r\n\r\n<p>講師による1対1サポートと専用コミュニティ</p>\r\n\r\n<p>スキルに合った就職機会の紹介</p>\r\n\r\n<p>無期限アクセス</p>', '<p>支持在所有设备上学习</p>\r\n\r\n<p>提供示例代码和完整学习资料</p>\r\n\r\n<p>讲师一对一指导及专属学习社群</p>\r\n\r\n<p>推荐适合的工作机会</p>\r\n\r\n<p>永久访问</p>', 1, 'all_lessons', 0, NULL, NULL, NULL, NULL, 0, 0, 77, '2026-02-03 08:56:18', '2026-05-12 15:57:29', NULL);
INSERT INTO `courses` (`id`, `name`, `name_en`, `name_ko`, `name_ja`, `name_zh`, `slug`, `slug_en`, `slug_ko`, `slug_ja`, `slug_zh`, `detail`, `detail_en`, `detail_ko`, `detail_ja`, `detail_zh`, `teacher_id`, `thumbnail`, `price`, `quantity`, `sale_price`, `price_en`, `sale_price_en`, `price_ko`, `sale_price_ko`, `price_ja`, `sale_price_ja`, `price_zh`, `sale_price_zh`, `code`, `durations`, `is_document`, `supports`, `supports_en`, `supports_ko`, `supports_ja`, `supports_zh`, `status`, `completion_condition`, `is_coming_soon`, `coming_soon_start_at`, `end_at`, `package_locked_at`, `package_lock_reason`, `is_package_priority`, `is_learning_locked`, `view`, `created_at`, `updated_at`, `deleted_at`) VALUES
(16, 'Lập trình PHP nâng cao - chuyên sâu để đi làm', 'Advanced PHP programming - in-depth skills for employment.', '실무를 위한 고급 PHP 프로그래밍', '実務向け高度PHPプログラミング', '面向工作的高级 PHP 编程', 'lap-trinh-php-nang-cao-chuyen-sau-de-di-lam', 'advanced-php-programming-in-depth-skills-for-employment', '실무를-위한-고급-php-프로그래밍', '実務向け高度phpプログラミング', '面向工作的高级-php-编程', '<p>Kho&aacute; học PHP n&acirc;ng cao trang bị cho học vi&ecirc;n c&aacute;c kiến thức n&acirc;ng cao về ng&ocirc;n ngữ lập tr&igrave;nh PHP để chuẩn bị đi l&agrave;m v&agrave; học n&acirc;ng cao l&ecirc;n c&aacute;c PHP Framework. Kh&oacute;a học n&agrave;y rất kh&oacute; v&igrave; n&oacute; li&ecirc;n quan nhiều đến kiến tr&uacute;c, ph&acirc;n t&iacute;ch c&aacute;c logic nghiệp vụ, tư duy hệ thống,... Học vi&ecirc;n cần chuẩn bị trước c&aacute;c kiến thức nền tảng trong kh&oacute;a học PHP cơ bản.</p>\r\n\r\n<p>Bạn sẽ nhận được g&igrave; tại kh&oacute;a học?</p>\r\n\r\n<p>Biểu thức ch&iacute;nh quy (Regular Expression) từ cơ bản đến n&acirc;ng cao<br />\r\nKiến thức lập tr&igrave;nh hướng đối tượng (OOP) từ cơ bản đến n&acirc;ng cao<br />\r\nKỹ thuật l&agrave;m việc với cURL để thao t&aacute;c với HTTP Request - HTTP Response<br />\r\nThao t&aacute;c với File - Folder<br />\r\nBiết c&aacute;ch từng bước x&acirc;y dựng m&ocirc; h&igrave;nh MVC (Model - View - Controller) từ cơ bản đến n&acirc;ng cao<br />\r\n&Aacute;p dụng m&ocirc; h&igrave;nh MVC v&agrave;o c&aacute;c t&iacute;nh năng thực tế<br />\r\nBiết c&aacute;ch quản l&yacute; c&aacute;c thư viện qua Composer v&agrave; c&aacute;c thao t&aacute;c với Composer<br />\r\nKiến thức về API - RESTful API, biết c&aacute;ch từng bước x&acirc;y dựng RESTful API từ cơ bản đến n&acirc;ng cao<br />\r\nĐược học về x&acirc;y dựng API Authentication, Authorization v&agrave; c&aacute;ch &aacute;p dụng thực tế với JavaScript<br />\r\nĐược học về c&aacute;ch x&acirc;y dựng hệ thống ph&acirc;n quyền động từ cơ bản đến n&acirc;ng cao<br />\r\nĐược học về nguy&ecirc;n l&yacute; SOLID trong lập tr&igrave;nh hướng đối tượng v&agrave; c&aacute;c t&igrave;nh huống thực tế<br />\r\nBiết c&aacute;ch t&iacute;ch hợp đăng nhập th&ocirc;ng qua mạng x&atilde; hội<br />\r\nĐược học về h&agrave;ng đợi v&agrave; c&aacute;ch x&acirc;y dựng hệ thống h&agrave;ng đợi từ cơ bản đến n&acirc;ng cao<br />\r\nĐược học về Redis v&agrave; c&aacute;c thao t&aacute;c với Redis trong PHP<br />\r\nKiến thức về c&aacute;c Design Pattern phổ biến trong Back-End v&agrave; c&aacute;ch vận dụng<br />\r\nC&acirc;u hỏi thường gặp?</p>\r\n\r\n<p>Kh&oacute;a học n&agrave;y cần kiến thức nền g&igrave; kh&ocirc;ng?</p>\r\n\r\n<p>Trong kh&oacute;a học m&igrave;nh c&oacute; c&aacute;c v&iacute; dụ sử dụng HTML - CSS - Javascript n&ecirc;n bạn cần nắm được kiến thức cơ bản để tr&aacute;nh bỡ ngỡ khi tham gia<br />\r\nC&aacute;c kiến thức căn bản về c&uacute; ph&aacute;p, kiểu dữ liệu trong ng&ocirc;n ngữ lập tr&igrave;nh PHP, tư duy lập tr&igrave;nh,...<br />\r\nKiến thức về Database, cấu tr&uacute;c, truy vấn trong Database, c&aacute;c loại quan hệ<br />\r\nTừng triển khai dự &aacute;n bằng PHP &amp; MySQL<br />\r\nTham khảo những nội dung trong kh&oacute;a học PHP cơ bản trước học kh&oacute;a học n&agrave;y<br />\r\nTrong qu&aacute; tr&igrave;nh học, t&ocirc;i c&oacute; được hỏi giảng vi&ecirc;n kh&ocirc;ng?</p>\r\n\r\n<p>Được, nếu gặp kh&oacute; khăn bạn c&oacute; thể nhắn tin trực tiếp cho giảng vi&ecirc;n qua Zalo hoặc tr&ecirc;n group được tham gia</p>\r\n\r\n<p>T&ocirc;i cần c&agrave;i đặt những phần mềm/c&ocirc;ng cụ g&igrave; để học?</p>\r\n\r\n<p>Để học PHP, bạn cần c&agrave;i đặt phần mềm để viết code (PHPStorrm, Visual Studio Code,...) v&agrave; phần mềm tạo Server ảo (Xampp, Wamp, Ampps,...)</p>\r\n\r\n<p>Trong b&agrave;i giảng bạn d&ugrave;ng phần mềm n&agrave;o để hướng dẫn học vi&ecirc;n?</p>\r\n\r\n<p>Trong kh&oacute;a học, m&igrave;nh đang d&ugrave;ng phần mềm PHPStorm để viết code v&agrave; Ampps để tạo Server ảo. Thời gian sau m&igrave;nh c&oacute; sử dụng Visual Studio Code v&agrave; Xampp để hướng dẫn. Bạn ho&agrave;n to&agrave;n c&oacute; thể d&ugrave;ng Visual Studio Code để viết code v&agrave; bất kỳ phần mềm tạo Server ảo n&agrave;o</p>\r\n\r\n<p>Trong kh&oacute;a học đang hướng dẫn ở phi&ecirc;n bản PHP bao nhi&ecirc;u?</p>\r\n\r\n<p>Tại thời điểm quay kh&oacute;a học, m&igrave;nh đang d&ugrave;ng phi&ecirc;n bản 7.3 sau đ&oacute; m&igrave;nh n&acirc;ng dần l&ecirc;n c&aacute;c phi&ecirc;n bản 8.x</p>\r\n\r\n<p>Kh&oacute;a học n&agrave;y c&ograve;n ph&ugrave; hợp với thời điểm hiện tại kh&ocirc;ng?</p>\r\n\r\n<p>Kh&oacute;a học n&agrave;y ph&ugrave; hợp với thời điểm hiện tại v&igrave; phần lớn m&igrave;nh sử dụng phi&ecirc;n bản 8.x v&agrave; hiện tại vẫn đang cập nhật</p>\r\n\r\n<p>Kh&oacute;a học n&agrave;y c&oacute; được cập nhật trong tương lai kh&ocirc;ng?</p>\r\n\r\n<p>C&oacute; bạn nh&eacute;. M&igrave;nh sẽ cập nhật những kiến thức mới, tư duy mới trong PHP ph&ugrave; hợp với y&ecirc;u cầu v&agrave; xu hướng trong tuyển dụng</p>', '<p>The Advanced PHP course equips students with advanced knowledge of the PHP programming language to prepare them for employment and to learn more advanced PHP frameworks. This course is challenging as it involves a lot of architecture, business logic analysis, systems thinking, etc. Students need to have a solid foundation in the basic PHP course beforehand.</p>\r\n\r\n<p>What will you gain from this course?</p>\r\n\r\n<p>Regular Expressions from Basic to Advanced<br />\r\nObject-Oriented Programming (OOP) Knowledge from Basic to Advanced<br />\r\nTechniques for working with cURL to manipulate HTTP Requests and Responses<br />\r\nFile and Folder Manipulation<br />\r\nLearning how to build MVC (Model-View-Controller) models step-by-step from basic to advanced<br />\r\nApplying MVC models to practical features<br />\r\nLearning how to manage libraries via Composer and perform operations with Composer<br />\r\nApplications of APIs - RESTful APIs, learning how to build RESTful APIs step-by-step from basic to advanced<br />\r\nLearning about building Authentication and Authorization APIs and how to apply them in practice with JavaScript<br />\r\nLearning about building dynamic authorization systems from basic to advanced<br />\r\nLearning about SOLID principles in object-oriented programming and real-world scenarios<br />\r\nLearning how to integrate login through social networks<br />\r\nLearning about queues and how to build queue systems from basic to advanced<br />\r\nLearning about Redis and Redis operations in PHP<br />\r\nKnowledge of common Back-End Design Patterns and how to apply them<br />\r\nFrequently Asked Questions?</p>\r\n\r\n<p>What prior knowledge is required for this course?</p>\r\n\r\n<p>The course includes examples using HTML, CSS, and Javascript, so you need to have a basic understanding to avoid confusion.<br />\r\nBasic knowledge of syntax, data types in the PHP programming language, programming thinking, etc.<br />\r\nKnowledge of Databases, structures, queries in Databases, types of relationships<br />\r\nPrevious project implementation using PHP &amp; MySQL<br />\r\nRefer to the content of the basic PHP course before taking this course.</p>\r\n\r\n<p>Can I ask the instructor questions during the course?</p>\r\n\r\n<p>Yes, if you encounter difficulties, you can message the instructor directly via Zalo or in the group you are in.</p>\r\n\r\n<p>What software/tools do I need to install to learn?</p>\r\n\r\n<p>To learn PHP, you need to install software to write code (PHPStorm, Visual Studio Code, etc.) and software to create a virtual server (Xampp, Wamp, Ampps, etc.).</p>\r\n\r\n<p>Which software do you use to guide students in your lectures?</p>\r\n\r\n<p>In this course, I&#39;m using PHPStorm to write code and Ampps to create a virtual server. Later, I&#39;ll use Visual Studio Code and Xampp for instruction. You can absolutely use Visual Studio Code to write code and any virtual server software.</p>\r\n\r\n<p>What PHP version are you using in this course?</p>\r\n\r\n<p>At the time of filming, I was using version 7.3, then I gradually upgraded to versions 8.x.</p>\r\n\r\n<p>Is this course still relevant today?</p>\r\n\r\n<p>This course is relevant today because I mostly use version 8.x and I&#39;m still updating it.</p>\r\n\r\n<p>Will this course be updated in the future?</p>\r\n\r\n<p>Yes, it will. I will update my knowledge and thinking in PHP to meet the requirements and trends in recruitment.</p>', '<p>고급 PHP 과정은 학생들이 PHP 프로그래밍 언어에 대한 심도 있는 지식을 습득하여 취업 및 고급 PHP 프레임워크 학습을 준비할 수 있도록 설계되었습니다. 이 과정은 아키텍처, 비즈니스 로직 분석, 시스템적 사고 등 다양한 내용을 다루기 때문에 난이도가 높습니다. 수강생은 기초 PHP 과정을 이수하여 탄탄한 기초를 다져야 합니다.</p>\r\n\r\n<p>이 과정을 통해 무엇을 얻을 수 있을까요?</p>\r\n\r\n<p>정규 표현식 기초부터 고급까지<br />\r\n객체 지향 프로그래밍(OOP) 기초부터 고급까지<br />\r\ncURL을 사용하여 HTTP 요청 및 응답을 조작하는 기술<br />\r\n파일 및 폴더 조작<br />\r\nMVC(모델-뷰-컨트롤러) 모델을 기초부터 고급까지 단계별로 구축하는 방법 학습<br />\r\nMVC 모델을 실제 기능에 적용하기<br />\r\nComposer를 통해 라이브러리를 관리하고 Composer를 사용하여 작업을 수행하는 방법 학습<br />\r\nAPI 응용 - RESTful API, RESTful API를 기초부터 고급까지 단계별로 구축하는 방법 학습<br />\r\n인증 및 권한 부여 API를 구축하고 JavaScript를 사용하여 실제 적용하는 방법 학습<br />\r\n동적 권한 부여 시스템을 기초부터 고급까지 구축하는 방법 학습<br />\r\n객체 지향 프로그래밍에서 SOLID 원칙과 실제 시나리오 학습<br />\r\n소셜 네트워크를 통한 로그인 통합 방법 학습<br />\r\n큐와 큐 시스템을 기초부터 고급까지 구축하는 방법 학습<br />\r\nRedis 및 PHP에서의 Redis 작업 학습<br />\r\n일반적인 백엔드 디자인 패턴에 대한 지식과 적용 방법<br />\r\n자주 사용되는 질문이 있으신가요?</p>\r\n\r\n<p>이 강좌를 수강하려면 어떤 사전 지식이 필요합니까?</p>\r\n\r\n<p>이 강좌에는 HTML, CSS, JavaScript를 사용한 예제가 포함되어 있으므로 혼란을 피하려면 이러한 기술에 대한 기본적인 이해가 필요합니다.<br />\r\nPHP 프로그래밍 언어의 구문, 데이터 유형, 프로그래밍 사고방식 등에 대한 기본 지식<br />\r\n데이터베이스, 데이터베이스 구조, 쿼리, 관계 유형에 대한 지식<br />\r\nPHP 및 MySQL을 사용한 프로젝트 구현 경험<br />\r\n이 강좌를 수강하기 전에 기초 PHP 강좌 내용을 참고하세요.</p>\r\n\r\n<p>강의 중에 강사에게 질문할 수 있습니까?</p>\r\n\r\n<p>네, 어려움이 있는 경우 Zalo를 통해 강사에게 직접 메시지를 보내거나 그룹 채팅을 통해 질문할 수 있습니다.</p>\r\n\r\n<p>학습을 위해 어떤 소프트웨어/도구를 설치해야 합니까?</p>\r\n\r\n<p>PHP를 학습하려면 코드 작성 소프트웨어(PHPStorm, Visual Studio Code 등)와 가상 서버 생성 소프트웨어(Xampp, Wamp, Ampps 등)를 설치해야 합니다.</p>\r\n\r\n<p>강의에서 학생들을 안내하는 데 어떤 소프트웨어를 사용하십니까?</p>\r\n\r\n<p>이 강의에서는 PHPStorm을 사용하여 코드를 작성하고 AMPPS를 사용하여 가상 서버를 생성합니다. 나중에는 Visual Studio Code와 XAMPP를 활용한 강의도 진행할 예정입니다. 물론 Visual Studio Code를 사용하여 코드를 작성하거나 다른 가상 서버 소프트웨어를 사용하셔도 무방합니다.</p>\r\n\r\n<p>이 강의에서 사용하는 PHP 버전은 무엇인가요?</p>\r\n\r\n<p>촬영 당시에는 7.3 버전을 사용했고, 이후 8.x 버전으로 점차 업그레이드했습니다.</p>\r\n\r\n<p>이 강의는 지금도 유효한가요?</p>\r\n\r\n<p>네, 그렇습니다. 제가 주로 8.x 버전을 사용하고 있으며, 강의 내용도 계속 업데이트하고 있기 때문입니다.</p>\r\n\r\n<p>이 강의는 앞으로도 업데이트될 예정인가요?</p>\r\n\r\n<p>네, 그렇습니다. 채용 시장의 요구 사항과 트렌드에 맞춰 PHP에 대한 지식과 생각을 지속적으로 업데이트할 것입니다.</p>', '<p>上級PHPコースでは、PHPプログラミング言語に関する高度な知識を身につけ、就職活動やより高度なPHPフレームワークの習得に備えることができます。このコースは、アーキテクチャ、ビジネスロジック分析、システム思考など、幅広い分野を扱うため、難易度は高めです。受講者は、事前にPHP基礎コースでしっかりとした基礎を身に付けておく必要があります。</p>\r\n\r\n<p>このコースで得られるものは何ですか？</p>\r\n\r\n<p>正規表現の基礎から応用まで<br />\r\nオブジェクト指向プログラミング（OOP）の基礎から応用まで<br />\r\ncURL を使って HTTP リクエストとレスポンスを操作するテクニック<br />\r\nファイルとフォルダの操作<br />\r\nMVC（モデル・ビュー・コントローラ）モデルの構築方法を基礎から応用まで段階的に学習<br />\r\nMVC モデルを実用的な機能に適用<br />\r\nComposer を使ってライブラリを管理し、操作を実行する方法を学ぶ<br />\r\nAPI の応用 - RESTful API。RESTful API の構築方法を基礎から応用まで段階的に学習<br />\r\n認証および認可 API の構築方法と、JavaScript でそれらを実践的に適用する方法を学ぶ<br />\r\n動的認可システムの構築方法を基礎から応用まで学習<br />\r\nオブジェクト指向プログラミングと実際のシナリオにおける SOLID 原則を学ぶ<br />\r\nソーシャルネットワークを介したログインを統合する方法を学ぶ<br />\r\nキューの基礎と、キューシステムの構築方法を基礎から応用まで学習<br />\r\nPHP での Redis と Redis 操作を学ぶ<br />\r\n一般的なバックエンド設計パターンの知識と適用方法それら<br />\r\nよくある質問</p>\r\n\r\n<p>このコースを受講するにはどのような事前知識が必要ですか？</p>\r\n\r\n<p>このコースにはHTML、CSS、Javascriptを使った例題が含まれているため、混乱を避けるために基本的な知識が必要です。<br />\r\nPHPプログラミング言語の構文、データ型、プログラミング思考などに関する基礎知識<br />\r\nデータベース、データベース構造、データベース内のクエリ、リレーションシップの種類に関する知識<br />\r\nPHPとMySQLを使用したプロジェクトの実装経験<br />\r\nこのコースを受講する前に、PHP基礎コースの内容を参照してください。</p>\r\n\r\n<p>コース中に講師に質問することはできますか？</p>\r\n\r\n<p>はい。困った場合は、Zaloまたは参加しているグループで講師に直接メッセージを送信できます。</p>\r\n\r\n<p>学習に必要なソフトウェア/ツールは何ですか？</p>\r\n\r\n<p>PHPを学習するには、コード記述ソフトウェア（PHPStorm、Visual Studio Codeなど）と仮想サーバー作成ソフトウェア（Xampp、Wamp、Amppsなど）をインストールする必要があります。</p>\r\n\r\n<p>講義ではどのようなソフトウェアを使用して学生を指導していますか？</p>\r\n\r\n<p>このコースでは、PHPStorm を使ってコードを記述し、Ampps を使って仮想サーバーを構築します。その後、Visual Studio Code と Xampp を使って解説します。Visual Studio Code は、コードの記述や仮想サーバーソフトウェアとして使用できます。</p>\r\n\r\n<p>このコースで使用している PHP のバージョンは？</p>\r\n\r\n<p>撮影時はバージョン 7.3 を使用していましたが、その後徐々にバージョン 8.x にアップグレードしました。</p>\r\n\r\n<p>このコースは現在でも通用しますか？</p>\r\n\r\n<p>このコースは、主にバージョン 8.x を使用しており、現在も更新を続けていることから、現在でも通用します。</p>\r\n\r\n<p>このコースは今後更新されますか？</p>\r\n\r\n<p>はい、更新されます。採用の要件やトレンドに合わせて、PHP に関する知識と考え方をアップデートしていきます。</p>', '<p>高级 PHP 课程旨在为学生提供 PHP 编程语言的进阶知识，帮助他们做好就业准备并学习更高级的 PHP 框架。本课程具有挑战性，因为它涉及大量的架构设计、业务逻辑分析、系统思维等内容。学生需要事先掌握扎实的 PHP 基础课程。</p>\r\n\r\n<p>您将从本课程中获得什么？</p>\r\n\r\n<p>正则表达式：从基础到高级</p>\r\n\r\n<p>面向对象编程 (OOP)：从基础到高级</p>\r\n\r\n<p>使用 cURL 操作 HTTP 请求和响应的技巧</p>\r\n\r\n<p>文件和文件夹操作</p>\r\n\r\n<p>学习如何从基础到高级逐步构建 MVC（模型-视图-控制器）模型</p>\r\n\r\n<p>将 MVC 模型应用于实际功能</p>\r\n\r\n<p>学习如何通过 Composer 管理库并使用 Composer 执行操作</p>\r\n\r\n<p>API 应用 - RESTful API：学习如何从基础到高级逐步构建 RESTful API</p>\r\n\r\n<p>学习如何构建身份验证和授权 API 以及如何使用 JavaScript 将其应用于实践</p>\r\n\r\n<p>学习如何从基础到高级构建动态授权系统</p>\r\n\r\n<p>学习面向对象编程中的 SOLID 原则及其在实际场景中的应用</p>\r\n\r\n<p>学习如何集成社交网络登录</p>\r\n\r\n<p>学习如何从基础到高级学习队列以及如何构建队列系统</p>\r\n\r\n<p>学习 PHP 中的 Redis 和 Redis 操作</p>\r\n\r\n<p>了解常见的后端设计模式及其应用</p>\r\n\r\n<p>经常有问题吗？</p>\r\n\r\n<p>这门课程需要哪些预备知识？</p>\r\n\r\n<p>本课程包含使用 HTML、CSS 和 Javascript 的示例，因此您需要具备一定的基础知识，以免混淆。</p>\r\n\r\n<p>PHP 编程语言的语法、数据类型、编程思维等方面的基础知识。</p>\r\n\r\n<p>数据库知识，包括数据库结构、查询语句和关系类型。</p>\r\n\r\n<p>之前使用 PHP 和 MySQL 进行过项目实践。</p>\r\n\r\n<p>在学习本课程之前，请参考 PHP 基础课程的内容。</p>\r\n\r\n<p>我可以在课程期间向老师提问吗？</p>\r\n\r\n<p>可以。如果您遇到任何困难，可以通过 Zalo 或您所在的小组直接联系老师。</p>\r\n\r\n<p>我需要安装哪些软件/工具才能学习？</p>\r\n\r\n<p>要学习 PHP，您需要安装用于编写代码的软件（例如 PHPStorm、Visual Studio Code 等）以及用于创建虚拟服务器的软件（例如 Xampp、Wamp、Ampps 等）。</p>\r\n\r\n<p>您在授课时使用哪些软件来指导学生？</p>\r\n\r\n<p>在本课程中，我使用 PHPStorm 编写代码，并使用 Xampp 创建虚拟服务器。之后，我会使用 Visual Studio Code 和 Xampp 进行讲解。您完全可以使用 Visual Studio Code 编写代码，并使用任何虚拟服务器软件。</p>\r\n\r\n<p>本课程中使用的是哪个 PHP 版本？</p>\r\n\r\n<p>录制课程时，我使用的是 7.3 版本，之后逐步升级到 8.x 版本。</p>\r\n\r\n<p>本课程现在仍然适用吗？</p>\r\n\r\n<p>本课程现在仍然适用，因为我主要使用 8.x 版本，并且还在不断更新。</p>\r\n\r\n<p>本课程未来会更新吗？</p>\r\n\r\n<p>会的。我会不断更新我的 PHP 知识和理念，以满足招聘需求和发展趋势。</p>', 6, '/storage/photos/2/2d89eda8-acab-4a45-9c80-c7451efc62ce.jpg', 2595000.00, NULL, 995000.00, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'KH282976', 565.00, 0, '<p>Học tr&ecirc;n mọi thiết bị</p>\r\n\r\n<p>Code mẫu, t&agrave;i liệu đầy đủ</p>\r\n\r\n<p>Hỗ trợ 1-1 bởi giảng vi&ecirc;n, nh&oacute;m k&iacute;n</p>\r\n\r\n<p>Giới thiệu c&ocirc;ng việc ph&ugrave; hợp</p>\r\n\r\n<p>Thời hạn: Vĩnh viễn</p>', '<p>Learn on any device</p>\r\n\r\n<p>Sample code, complete documentation</p>\r\n\r\n<p>One-on-one support by instructors, private group</p>\r\n\r\n<p>Job placement assistance</p>\r\n\r\n<p>Duration: Lifetime</p>', '<p>모든 기기에서 학습 가능</p>\r\n\r\n<p>すべてのデバイスで学習可能</p>\r\n\r\n<p>예제 코드 및 완전한 학습 자료 제공</p>\r\n\r\n<p>강사의 1:1 지원 및 전용 커뮤니티</p>\r\n\r\n<p>역량에 맞는 취업 기회 제공</p>\r\n\r\n<p>평생 이용 가능</p>', '<p>すべてのデバイスで学習可能</p>\r\n\r\n<p>サンプルコードと完全な資料付き</p>\r\n\r\n<p>講師による1対1サポートと専用コミュニティ</p>\r\n\r\n<p>スキルに合った就職機会の紹介</p>\r\n\r\n<p>無期限アクセス</p>', '<p>支持在所有设备上学习</p>\r\n\r\n<p>提供示例代码和完整学习资料</p>\r\n\r\n<p>讲师一对一指导及专属学习社群</p>\r\n\r\n<p>推荐适合的工作机会</p>\r\n\r\n<p>永久访问</p>', 1, 'all_lessons', 0, NULL, NULL, NULL, NULL, 0, 0, 4, '2026-02-03 08:57:00', '2026-03-29 15:07:27', NULL),
(17, 'Lập trình web PHP & MySQL với Laravel Framework', 'PHP & MySQL web programming with Laravel Framework', 'Laravel Framework를 활용한 PHP & MySQL 웹 개발', 'Laravelフレームワークを使用したPHP & MySQLウェブ開発', '使用 Laravel 框架进行 PHP & MySQL Web 开发', 'lap-trinh-web-php-mysql-voi-laravel-framework', 'php-mysql-web-programming-with-laravel-framework', 'laravel-framework를-활용한-php-mysql-웹-개발', 'laravelフレームワークを使用したphp-mysqlウェブ開発', '使用-laravel-框架进行-php-mysql-web-开发', '<p>Kho&aacute; học Laravel Framework trang bị cho học vi&ecirc;n c&aacute;c kiến thức từ cơ bản đến n&acirc;ng cao về Laravel Framework, từ đ&oacute; gi&uacute;p học vi&ecirc;n tự l&agrave;m dự &aacute;n với PHP Framework n&agrave;y.</p>\r\n\r\n<p>Ngo&agrave;i ra, học vi&ecirc;n được r&egrave;n luyện tư duy khi l&agrave;m việc với Framework từ đ&oacute; c&oacute; thể triển khai c&aacute;c dự &aacute;n phức tạp, c&oacute; t&iacute;nh logic cao.</p>\r\n\r\n<p>Kho&aacute; học n&agrave;y kh&ocirc;ng chia sẻ tất cả mọi thứ về Laravel nhưng sẽ trang bị cho học vi&ecirc;n những kiến thức quan trọng, cần thiết khi đi l&agrave;m cần sử dụng tới v&agrave; quan trọng l&agrave; tư duy thực tế của giảng vi&ecirc;n</p>\r\n\r\n<p><strong>Bạn nhận được g&igrave; tại kh&oacute;a học?</strong></p>\r\n\r\n<ul>\r\n	<li>Tư duy, luồng chạy của Laravel từ đ&oacute; hiểu bản chất v&agrave; c&aacute;ch hoạt động của Laravel</li>\r\n	<li>C&aacute;c kiến thức căn bản của Laravel: Route, Controller, Model, Views, Request - Response, Blade Template, Validation,...</li>\r\n	<li>Kỹ thuật l&agrave;m việc li&ecirc;n quan đến Database: Raw Query, Query Builder, Eloquent ORM</li>\r\n	<li>C&aacute;c kỹ năng l&agrave;m việc với Database tr&ecirc;n c&aacute;c hệ thống&nbsp;Back-End chuy&ecirc;n nghiệp: Migrations, Seeders, Factories,...</li>\r\n	<li>Authentication, Authorization trong Laravel</li>\r\n	<li>Kỹ thuật đăng nhập th&ocirc;ng qua mạng x&atilde; hội</li>\r\n	<li>Biết c&aacute;ch x&acirc;y dựng RESTful API trong Laravel</li>\r\n	<li>Biết c&aacute;ch x&acirc;y dựng ứng dụng x&aacute;c thực OAuth 2.0</li>\r\n	<li>Được học về c&aacute;c thao t&aacute;c với Queue trong Laravel</li>\r\n	<li>Được học về c&aacute;c thao t&aacute;c với Task Sheduler v&agrave; Cronjob</li>\r\n	<li>Kỹ thuật Compling Asset với Laravel Mix v&agrave; Vite</li>\r\n	<li>Hiểu được Cache trong Back-End v&agrave; kỹ thuật l&agrave;m việc với Cache trong Laravel</li>\r\n	<li>C&aacute;c thao t&aacute;c với Event v&agrave; ứng dụng thực tế</li>\r\n	<li>Artisan Console v&agrave; c&aacute;ch định nghĩa Artisan Console để phục vụ mục đ&iacute;ch ri&ecirc;ng</li>\r\n	<li>Được học về Repository Design Pattern v&agrave; c&aacute;ch x&acirc;y dựng Repository từ đầu trong Laravel</li>\r\n	<li>Kiến tr&uacute;c Laravel Modules v&agrave; c&aacute;ch n&acirc;ng cấp từ MVC l&ecirc;n Modules</li>\r\n	<li>Được chia sẻ c&aacute;c case thực tế về Authentication m&agrave; trong c&aacute;c dự &aacute;n thực tế sẽ d&ugrave;ng đến</li>\r\n	<li>Được hướng dẫn từng bước x&acirc;y dựng dự &aacute;n Elearning từ đầu bằng m&ocirc; h&igrave;nh Laravel Modules</li>\r\n	<li>C&ograve;n nhiều nội dung hay v&agrave; c&aacute;c kinh nghiệm thực tế được chia sẻ trong dự &aacute;n Elearning</li>\r\n</ul>\r\n\r\n<p><strong>C&acirc;u hỏi thường gặp?</strong></p>\r\n\r\n<p><em><strong>Kh&oacute;a học n&agrave;y cần kiến thức nền g&igrave; kh&ocirc;ng?</strong></em></p>\r\n\r\n<ul>\r\n	<li>Bởi v&igrave; Laravel l&agrave; Framework của PHP n&ecirc;n bạn cần c&oacute; kiến thức vững chắc về PHP đặc biệt l&agrave; lập tr&igrave;nh hướng đối tượng v&agrave; hiểu được luồng chạy của m&ocirc; h&igrave;nh MVC</li>\r\n	<li>Ngo&agrave;i ra, bạn n&ecirc;n c&oacute; kiến thức về HTML, CSS, Javascript, Bootstrap&nbsp;để b&aacute;m theo được những hướng dẫn trong kh&oacute;a học</li>\r\n	<li><em>N&ecirc;n tham khảo nội dung của kh&oacute;a PHP cơ bản v&agrave; PHP n&acirc;ng cao trước</em></li>\r\n</ul>\r\n\r\n<p><em><strong>Trong qu&aacute; tr&igrave;nh học, t&ocirc;i c&oacute; được hỏi giảng vi&ecirc;n kh&ocirc;ng?</strong></em></p>\r\n\r\n<p>Được, nếu gặp kh&oacute; khăn bạn c&oacute; thể nhắn tin trực tiếp cho giảng vi&ecirc;n qua Zalo hoặc tr&ecirc;n group được tham gia</p>\r\n\r\n<p><em><strong>T&ocirc;i cần c&agrave;i đặt những phần mềm/c&ocirc;ng cụ g&igrave; để học?</strong></em></p>\r\n\r\n<p>Để học Laravel, bạn cần c&agrave;i đặt phần mềm để viết code (PHPStorrm, Visual Studio Code,...) v&agrave; phần mềm tạo Server ảo&nbsp;(Xampp, Wamp, Ampps,...)</p>\r\n\r\n<p><em><strong>Trong b&agrave;i giảng bạn d&ugrave;ng phần mềm n&agrave;o để hướng dẫn học vi&ecirc;n?</strong></em></p>\r\n\r\n<p>Trong kh&oacute;a học m&igrave;nh đang sử dụng Visual Studio Code để viết code v&agrave; Ampps để tạo Server ảo. Thời gian sau m&igrave;nh chuyển qua Xampp để tạo Server ảo. Bạn c&oacute; thể d&ugrave;ng bất kỳ c&ocirc;ng cụ n&agrave;o m&agrave; kh&ocirc;ng ảnh hưởng đến chất lượng học tập</p>\r\n\r\n<p><em><strong>Trong kh&oacute;a học đang hướng dẫn ở phi&ecirc;n bản Laravel bao nhi&ecirc;u?</strong></em></p>\r\n\r\n<p>Tại thời điểm quay kh&oacute;a học, m&igrave;nh sử dụng Laravel 8.x sau đ&oacute; n&acirc;ng l&ecirc;n c&aacute;c phi&ecirc;n 9.x, 10.x</p>\r\n\r\n<p><em><strong>Kh&oacute;a học n&agrave;y c&ograve;n ph&ugrave; hợp với thời điểm hiện tại kh&ocirc;ng?</strong></em></p>\r\n\r\n<p>C&oacute;. Kh&oacute;a học vẫn ph&ugrave; hợp với thời điểm hiện tại. Tuy rằng, phi&ecirc;n bản 11.x c&oacute; sự thay đổi về cấu tr&uacute;c thư mục nhưng những th&agrave;nh phần cốt l&otilde;i của Laravel&nbsp;vẫn &aacute;p dụng được nội dung của kh&oacute;a học n&agrave;y&nbsp;v&agrave; kh&ocirc;ng xảy ra lỗi. Những thay đổi về cấu tr&uacute;c thư mục bạn c&oacute; thể tham khảo c&aacute;ch thiết lập tr&ecirc;n docs của Laravel hoặc nhờ sự trợ gi&uacute;p của giảng vi&ecirc;n qua c&aacute;c k&ecirc;nh hỗ trợ.</p>\r\n\r\n<p><em><strong>Kh&oacute;a học n&agrave;y c&oacute; được cập nhật trong tương lai kh&ocirc;ng?</strong></em></p>\r\n\r\n<p>C&oacute; bạn nh&eacute;. M&igrave;nh sẽ cập nhật những kiến thức mới, tư duy mới trong Laravel ph&ugrave; hợp với y&ecirc;u cầu v&agrave; xu hướng trong tuyển dụng</p>', '<p>The Laravel Framework course equips students with knowledge from basic to advanced levels of the Laravel Framework, enabling them to create their own projects using this PHP framework.</p>\r\n\r\n<p>In addition, students will develop critical thinking skills while working with the framework, allowing them to implement complex, highly logical projects.</p>\r\n\r\n<p>This course doesn&#39;t cover everything about Laravel, but it will provide students with essential knowledge needed for work, and importantly, the practical thinking of the instructors.</p>\r\n\r\n<p>What will you gain from this course?</p>\r\n\r\n<p>Understanding the mindset and workflow of Laravel, thereby grasping its essence and operation.<br />\r\nFundamental Laravel knowledge: Routes, Controllers, Models, Views, Request-Response, Blade Templates, Validation, etc.<br />\r\nDatabase-related techniques: Raw Query, Query Builder, Eloquent ORM.<br />\r\nSkills in working with databases on professional back-end systems: Migrations, Seeders, Factories, etc. Authentication and Authorization in Laravel.<br />\r\nSocial media login techniques.<br />\r\nHow to build RESTful APIs in Laravel.<br />\r\nHow to build OAuth 2.0 authentication applications.<br />\r\nLearning about Queue operations in Laravel. Learning about Task Shedler and Cronjob operations.<br />\r\nAsset Compiling techniques with Laravel Mix and Vite.<br />\r\nUnderstanding caching in the back-end and techniques for working with cache in Laravel. Event operations and practical applications. Artisan Console and how to define the Artisan Console. Course Objectives<br />\r\nLearn about Repository Design Patterns and how to build a Repository from scratch in Laravel<br />\r\nLaravel Module Architecture and how to upgrade from MVC to Modules<br />\r\nLearn about real-world authentication case studies used in actual projects<br />\r\nLearn step-by-step how to build an Elearning project from scratch using the Laravel Modules model<br />\r\nMany more useful contents and practical experiences will be shared in the Elearning project<br />\r\nFrequently Asked Questions?</p>\r\n\r\n<p>What prior knowledge is required for this course?</p>\r\n\r\n<p>Because Laravel is a PHP Framework, you need a solid understanding of PHP, especially object-oriented programming and understanding the flow of the MVC model.<br />\r\nIn addition, you should have knowledge of HTML, CSS, Javascript, and Bootstrap to follow the instructions in the course.<br />\r\nIt is recommended to review the content of the basic and advanced PHP courses beforehand.<br />\r\nCan I ask questions to the instructor during the course?</p>\r\n\r\n<p>Yes, if you encounter any difficulties, you can message the instructor directly via Zalo or in the group you&#39;re in.</p>\r\n\r\n<p>What software/tools do I need to install to learn?</p>\r\n\r\n<p>To learn Laravel, you need to install software for writing code (PHPStorrm, Visual Studio Code,...) and software for creating virtual servers (Xampp, Wamp, Ampps,...)</p>\r\n\r\n<p>Which software do you use to guide students in the lectures?</p>\r\n\r\n<p>In this course, I&#39;m using Visual Studio Code to write code and Ampps to create virtual servers. Later, I&#39;ll switch to Xampp to create virtual servers. You can use any tool without affecting the quality of learning.</p>\r\n\r\n<p>What Laravel version is being taught in this course?</p>\r\n\r\n<p>At the time of recording the course, I was using Laravel 8.x, then upgraded to versions 9.x and 10.x.</p>\r\n\r\n<p>Is this course still relevant today?</p>\r\n\r\n<p>Yes. The course is still relevant today. Although version 11.x has changed the directory structure, the core components of Laravel are still applicable to the content of this course and will not cause errors. You can refer to the Laravel documentation for instructions on how to set up the directory structure or ask the instructor for help through the support channels.</p>\r\n\r\n<p>Will this course be updated in the future?</p>\r\n\r\n<p>Yes, it will. I will update the course with new knowledge and approaches in Laravel to meet the requirements and trends in recruitment.</p>', '<p>Laravel 프레임워크 강좌는 수강생들이 Laravel 프레임워크의 기초부터 고급 수준까지 학습하여 이 PHP 프레임워크를 활용한 자신만의 프로젝트를 개발할 수 있도록 지원합니다.</p>\r\n\r\n<p>또한, 프레임워크를 다루는 과정에서 비판적 사고 능력을 함양하고, 복잡하고 논리적인 프로젝트를 구현할 수 있게 됩니다.</p>\r\n\r\n<p>본 강좌는 Laravel의 모든 것을 다루지는 않지만, 실무에 필요한 핵심 지식과 함께 강사진의 실질적인 경험을 제공합니다.</p>\r\n\r\n<p>본 강좌를 통해 얻을 수 있는 것:</p>\r\n\r\n<p>Laravel의 사고방식과 워크플로우를 이해하고, 핵심 원리와 작동 방식을 파악할 수 있습니다.</p>\r\n\r\n<p>Laravel 기본 지식: 라우트, 컨트롤러, 모델, 뷰, 요청-응답, Blade 템플릿, 유효성 검사 등</p>\r\n\r\n<p>데이터베이스 관련 기술: Raw Query, Query Builder, Eloquent ORM</p>\r\n\r\n<p>전문 백엔드 시스템에서 데이터베이스를 활용하는 기술: 마이그레이션, 시더, 팩토리 등</p>\r\n\r\n<p>Laravel에서의 인증 및 권한 부여</p>\r\n\r\n<p>소셜 미디어 로그인 기술</p>\r\n\r\n<p>Laravel을 이용한 RESTful API 구축 방법 OAuth 2.0 인증 애플리케이션 구축 방법<br />\r\nLaravel의 큐 운영 방식 학습<br />\r\nTask Shelder 및 Cronjob 운영 방식 학습<br />\r\nLaravel Mix 및 Vite를 사용한 에셋 컴파일 기법<br />\r\n백엔드 캐싱 이해 및 Laravel에서 캐시를 활용하는 기법<br />\r\n이벤트 운영 및 실제 적용 사례<br />\r\nArtisan 콘솔 및 Artisan 콘솔 정의 방법 학습<br />\r\n과정 목표<br />\r\n리포지토리 디자인 패턴 및 Laravel에서 리포지토리를 처음부터 구축하는 방법 학습<br />\r\nLaravel 모듈 아키텍처 및 MVC에서 모듈로 업그레이드하는 방법 학습<br />\r\n실제 프로젝트에서 사용된 실제 인증 사례 연구 학습<br />\r\nLaravel 모듈 모델을 사용하여 이러닝 프로젝트를 처음부터 구축하는 단계별 학습<br />\r\n이러닝 프로젝트에서 더 많은 유용한 콘텐츠와 실무 경험을 공유합니다.<br />\r\n자주 묻는 질문?</p>\r\n\r\n<p>이 과정을 수강하기 위해 필요한 사전 지식은 무엇인가요?</p>\r\n\r\n<p>Laravel은 PHP 프레임워크이기 때문에 PHP, 특히 객체 지향 프로그래밍과 MVC 모델의 흐름에 대한 탄탄한 이해가 필요합니다.<br />\r\n또한, 강의 내용을 따라가려면 HTML, CSS, JavaScript, Bootstrap에 대한 지식도 있어야 합니다.<br />\r\nPHP 기초 및 고급 강좌 내용을 미리 복습하는 것을 권장합니다.<br />\r\n강의 중에 강사에게 질문할 수 있나요?</p>\r\n\r\n<p>네, 어려움이 있을 경우 Zalo를 통해 강사에게 직접 메시지를 보내거나 소속된 그룹에서 질문할 수 있습니다.</p>\r\n\r\n<p>학습을 위해 어떤 소프트웨어/도구를 설치해야 하나요?</p>\r\n\r\n<p>Laravel을 배우려면 코드 작성 소프트웨어(PHPStorm, Visual Studio Code 등)와 가상 서버 생성 소프트웨어(Xampp, Wamp, Ampps 등)를 설치해야 합니다.</p>\r\n\r\n<p>강의에서는 어떤 소프트웨어를 사용하시나요?</p>\r\n\r\n<p>이 강좌에서는 코드 작성에는 Visual Studio Code를, 가상 서버 생성에는 Ampps를 사용합니다. 나중에는 가상 서버 생성에 Xampp를 사용할 예정입니다. 학습의 질에 영향을 주지 않고 어떤 도구를 사용하셔도 괜찮습니다.</p>\r\n\r\n<p>이 강좌에서는 어떤 Laravel 버전을 사용하나요?</p>\r\n\r\n<p>강의 녹화 당시에는 Laravel 8.x 버전을 사용했고, 이후 9.x와 10.x 버전으로 업그레이드했습니다.</p>\r\n\r\n<p>이 강좌는 지금도 유효한가요?</p>\r\n\r\n<p>네, 그렇습니다. 이 강좌는 지금도 유효합니다. 11.x 버전에서는 디렉토리 구조가 변경되었지만, Laravel의 핵심 구성 요소는 이 강좌 내용에 여전히 적용 가능하며 오류가 발생하지 않습니다. 디렉토리 구조 설정 방법은 Laravel 문서를 참조하거나 지원 채널을 통해 강사에게 문의하세요.</p>\r\n\r\n<p>이 강좌는 향후 업데이트될 예정인가요?</p>\r\n\r\n<p>네, 그렇습니다. 채용 시장의 요구 사항과 트렌드에 맞춰 Laravel의 새로운 지식과 접근 방식을 반영하여 강좌를 지속적으로 업데이트할 예정입니다.</p>', '<p>Laravel 프레임워크 강좌는 수강생들이 Laravel 프레임워크의 기초부터 고급 수준까지 학습하여 이 PHP 프레임워크를 활용한 자신만의 프로젝트를 개발할 수 있도록 지원합니다.</p>\r\n\r\n<p>또한, 프레임워크를 다루는 과정에서 비판적 사고 능력을 함양하고, 복잡하고 논리적인 프로젝트를 구현할 수 있게 됩니다.</p>\r\n\r\n<p>본 강좌는 Laravel의 모든 것을 다루지는 않지만, 실무에 필요한 핵심 지식과 함께 강사진의 실질적인 경험을 제공합니다.</p>\r\n\r\n<p>본 강좌를 통해 얻을 수 있는 것:</p>\r\n\r\n<p>Laravel의 사고방식과 워크플로우를 이해하고, 핵심 원리와 작동 방식을 파악할 수 있습니다.</p>\r\n\r\n<p>Laravel 기본 지식: 라우트, 컨트롤러, 모델, 뷰, 요청-응답, Blade 템플릿, 유효성 검사 등</p>\r\n\r\n<p>데이터베이스 관련 기술: Raw Query, Query Builder, Eloquent ORM</p>\r\n\r\n<p>전문 백엔드 시스템에서 데이터베이스를 활용하는 기술: 마이그레이션, 시더, 팩토리 등</p>\r\n\r\n<p>Laravel에서의 인증 및 권한 부여</p>\r\n\r\n<p>소셜 미디어 로그인 기술</p>\r\n\r\n<p>Laravel을 이용한 RESTful API 구축 방법 OAuth 2.0 인증 애플리케이션 구축 방법<br />\r\nLaravel의 큐 운영 방식 학습<br />\r\nTask Shelder 및 Cronjob 운영 방식 학습<br />\r\nLaravel Mix 및 Vite를 사용한 에셋 컴파일 기법<br />\r\n백엔드 캐싱 이해 및 Laravel에서 캐시를 활용하는 기법<br />\r\n이벤트 운영 및 실제 적용 사례<br />\r\nArtisan 콘솔 및 Artisan 콘솔 정의 방법 학습<br />\r\n과정 목표<br />\r\n리포지토리 디자인 패턴 및 Laravel에서 리포지토리를 처음부터 구축하는 방법 학습<br />\r\nLaravel 모듈 아키텍처 및 MVC에서 모듈로 업그레이드하는 방법 학습<br />\r\n실제 프로젝트에서 사용된 실제 인증 사례 연구 학습<br />\r\nLaravel 모듈 모델을 사용하여 이러닝 프로젝트를 처음부터 구축하는 단계별 학습<br />\r\n이러닝 프로젝트에서 더 많은 유용한 콘텐츠와 실무 경험을 공유합니다.<br />\r\n자주 묻는 질문?</p>\r\n\r\n<p>이 과정을 수강하기 위해 필요한 사전 지식은 무엇인가요?</p>\r\n\r\n<p>Laravel은 PHP 프레임워크이기 때문에 PHP, 특히 객체 지향 프로그래밍과 MVC 모델의 흐름에 대한 탄탄한 이해가 필요합니다.<br />\r\n또한, 강의 내용을 따라가려면 HTML, CSS, JavaScript, Bootstrap에 대한 지식도 있어야 합니다.<br />\r\nPHP 기초 및 고급 강좌 내용을 미리 복습하는 것을 권장합니다.<br />\r\n강의 중에 강사에게 질문할 수 있나요?</p>\r\n\r\n<p>네, 어려움이 있을 경우 Zalo를 통해 강사에게 직접 메시지를 보내거나 소속된 그룹에서 질문할 수 있습니다.</p>\r\n\r\n<p>학습을 위해 어떤 소프트웨어/도구를 설치해야 하나요?</p>\r\n\r\n<p>Laravel을 배우려면 코드 작성 소프트웨어(PHPStorm, Visual Studio Code 등)와 가상 서버 생성 소프트웨어(Xampp, Wamp, Ampps 등)를 설치해야 합니다.</p>\r\n\r\n<p>강의에서는 어떤 소프트웨어를 사용하시나요?</p>\r\n\r\n<p>이 강좌에서는 코드 작성에는 Visual Studio Code를, 가상 서버 생성에는 Ampps를 사용합니다. 나중에는 가상 서버 생성에 Xampp를 사용할 예정입니다. 학습의 질에 영향을 주지 않고 어떤 도구를 사용하셔도 괜찮습니다.</p>\r\n\r\n<p>이 강좌에서는 어떤 Laravel 버전을 사용하나요?</p>\r\n\r\n<p>강의 녹화 당시에는 Laravel 8.x 버전을 사용했고, 이후 9.x와 10.x 버전으로 업그레이드했습니다.</p>\r\n\r\n<p>이 강좌는 지금도 유효한가요?</p>\r\n\r\n<p>네, 그렇습니다. 이 강좌는 지금도 유효합니다. 11.x 버전에서는 디렉토리 구조가 변경되었지만, Laravel의 핵심 구성 요소는 이 강좌 내용에 여전히 적용 가능하며 오류가 발생하지 않습니다. 디렉토리 구조 설정 방법은 Laravel 문서를 참조하거나 지원 채널을 통해 강사에게 문의하세요.</p>\r\n\r\n<p>이 강좌는 향후 업데이트될 예정인가요?</p>\r\n\r\n<p>네, 그렇습니다. 채용 시장의 요구 사항과 트렌드에 맞춰 Laravel의 새로운 지식과 접근 방식을 반영하여 강좌를 지속적으로 업데이트할 예정입니다.</p>', '<p>Laravel 框架课程旨在帮助学生掌握 Laravel 框架从基础到高级的知识，使他们能够使用这个 PHP 框架创建自己的项目。</p>\r\n\r\n<p>此外，学生在学习框架的过程中还将培养批判性思维能力，从而能够实现复杂且逻辑严密的项目。</p>\r\n\r\n<p>本课程并非涵盖 Laravel 的所有内容，而是为学生提供工作所需的关键知识，更重要的是，还能让他们接触到讲师的实践经验。</p>\r\n\r\n<p>您将从本课程中获得什么？</p>\r\n\r\n<p>理解 Laravel 的思维模式和工作流程，从而掌握其本质和运行机制。</p>\r\n\r\n<p>Laravel 基础知识：路由、控制器、模型、视图、请求-响应、Blade 模板、验证等。</p>\r\n\r\n<p>数据库相关技术：原始查询、查询构建器、Eloquent ORM。</p>\r\n\r\n<p>在专业后端系统中操作数据库的技能：迁移、数据填充、工厂等。Laravel 中的身份验证和授权。</p>\r\n\r\n<p>社交媒体登录技术。</p>\r\n\r\n<p>如何在 Laravel 中构建 RESTful API。</p>\r\n\r\n<p>如何构建 OAuth 2.0 身份验证应用程序。</p>\r\n\r\n<p>学习 Laravel 中的队列操作。学习任务调度器和定时任务操作。</p>\r\n\r\n<p>使用 Laravel Mix 和 Vite 进行资源编译。</p>\r\n\r\n<p>理解后端缓存以及在 Laravel 中使用缓存的技巧。事件操作及其应用实例。Artisan 控制台及其定义方法。课程目标</p>\r\n\r\n<p>学习仓库设计模式以及如何在 Laravel 中从零开始构建仓库。</p>\r\n\r\n<p>Laravel 模块架构以及如何从 MVC 升级到模块架构。</p>\r\n\r\n<p>学习实际项目中使用的真实身份验证案例研究。</p>\r\n\r\n<p>学习如何使用 Laravel 模块模型从零开始构建在线学习项目。</p>\r\n\r\n<p>在线学习项目中将分享更多实用内容和实践经验。</p>\r\n\r\n<p>常见问题？</p>\r\n\r\n<p>本课程需要哪些先验知识？</p>\r\n\r\n<p>由于 Laravel 是一个 PHP 框架，您需要对 PHP 有扎实的理解，尤其是面向对象编程和 MVC 模型的流程。</p>\r\n\r\n<p>此外，您还需要掌握 HTML、CSS、JavaScript 和 Bootstrap 的相关知识，以便更好地理解课程内容。</p>\r\n\r\n<p>建议您事先复习一下 PHP 基础和进阶课程的内容。</p>\r\n\r\n<p>课程期间我可以向老师提问吗？</p>\r\n\r\n<p>可以，如果您遇到任何困难，可以通过 Zalo 或您所在的群组直接联系老师。</p>\r\n\r\n<p>学习 Laravel 需要安装哪些软件/工具？</p>\r\n\r\n<p>学习 Laravel 需要安装一些用于编写代码的软件（例如 PHPStorm、Visual Studio Code 等）以及用于创建虚拟服务器的软件（例如 Xampp、Wamp、Ampps 等）。</p>\r\n\r\n<p>您在授课时使用哪些软件？</p>\r\n\r\n<p>在本课程中，我使用 Visual Studio Code 编写代码，并使用 Ampps 创建虚拟服务器。之后，我会切换到 Xampp 创建虚拟服务器。您可以使用任何工具，这不会影响学习质量。</p>\r\n\r\n<p>本课程教授的是哪个 Laravel 版本？</p>\r\n\r\n<p>录制本课程时，我使用的是 Laravel 8.x 版本，之后升级到了 9.x 和 10.x 版本。</p>\r\n\r\n<p>本课程现在仍然适用吗？</p>\r\n\r\n<p>是的。本课程现在仍然适用。虽然 11.x 版本更改了目录结构，但 Laravel 的核心组件仍然适用于本课程的内容，不会出现错误。您可以参考 Laravel 文档了解如何设置目录结构，或者通过支持渠道向讲师寻求帮助。</p>\r\n\r\n<p>本课程未来会更新吗？</p>\r\n\r\n<p>是的，会的。我会根据招聘需求和趋势，更新课程内容，加入 Laravel 的最新知识和方法。</p>', 6, '/storage/photos/2/3f863552-ab07-49e9-8065-fe71b663c05d.jpg', 2950000.00, NULL, 1095000.00, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'KH405678', 1100.00, 0, '<p>Học tr&ecirc;n mọi thiết bị</p>\r\n\r\n<p>Code mẫu, t&agrave;i liệu đầy đủ</p>\r\n\r\n<p>Hỗ trợ 1-1 bởi giảng vi&ecirc;n, nh&oacute;m k&iacute;n</p>\r\n\r\n<p>Giới thiệu c&ocirc;ng việc ph&ugrave; hợp</p>\r\n\r\n<p>Thời hạn: Vĩnh viễn</p>', '<p>Learn on any device</p>\r\n\r\n<p>Sample code, complete documentation</p>\r\n\r\n<p>One-on-one support by instructors, private group</p>\r\n\r\n<p>Job placement assistance</p>\r\n\r\n<p>Duration: Lifetime</p>', '<p>모든 기기에서 학습 가능</p>\r\n\r\n<p>すべてのデバイスで学習可能</p>\r\n\r\n<p>예제 코드 및 완전한 학습 자료 제공</p>\r\n\r\n<p>강사의 1:1 지원 및 전용 커뮤니티</p>\r\n\r\n<p>역량에 맞는 취업 기회 제공</p>\r\n\r\n<p>평생 이용 가능</p>', '<p>すべてのデバイスで学習可能</p>\r\n\r\n<p>サンプルコードと完全な資料付き</p>\r\n\r\n<p>講師による1対1サポートと専用コミュニティ</p>\r\n\r\n<p>スキルに合った就職機会の紹介</p>\r\n\r\n<p>無期限アクセス</p>', '<p>支持在所有设备上学习</p>\r\n\r\n<p>提供示例代码和完整学习资料</p>\r\n\r\n<p>讲师一对一指导及专属学习社群</p>\r\n\r\n<p>推荐适合的工作机会</p>\r\n\r\n<p>永久访问</p>', 1, 'all_lessons', 0, NULL, NULL, NULL, NULL, 0, 0, 11, '2026-02-03 08:58:36', '2026-05-12 16:43:44', NULL),
(18, 'Khóa học Laravel Livewire từ cơ bản đến nâng cao', 'Laravel Livewire course from basic to advanced', 'Laravel Livewire 기초부터 고급까지 강의', 'Laravel Livewire 基礎から上級までコース', 'aravel Livewire 从基础到高级课程', 'khoa-hoc-laravel-livewire-tu-co-ban-den-nang-cao', 'laravel-livewire-course-from-basic-to-advanced', 'laravel-livewire-기초부터-고급까지-강의', 'laravel-livewire-基礎から上級までコース', 'aravel-livewire-从基础到高级课程', '<p>Laravel Livewire l&agrave; một full-stack framework cho Laravel gi&uacute;p việc x&acirc;y dựng c&aacute;c giao diện động trở n&ecirc;n đơn giản hơn. N&oacute; cho ph&eacute;p bạn tạo c&aacute;c ứng dụng single page (SPA) m&agrave; kh&ocirc;ng cần viết bất kỳ m&atilde; JavaScript n&agrave;o.</p>\r\n\r\n<p>Bạn ho&agrave;n to&agrave;n c&oacute; thể kết hợp Livewire trong dự &aacute;n Laravel để x&acirc;y dựng dự &aacute;n Fullstack bao gồm cả Front-End v&agrave; Back-End m&agrave; chỉ cần sử dụng HTML, CSS, PHP</p>\r\n\r\n<p><strong>Kh&oacute;a học Laravel Livewire 3.x</strong>&nbsp;được hướng dẫn trực tiếp bởi Ho&agrave;ng An Unicode gi&uacute;p bạn từng bước tiếp cận với Framework n&agrave;y v&agrave; ho&agrave;n to&agrave;n c&oacute; thể tự x&acirc;y dựng sản phẩm ri&ecirc;ng với Laravel Livewire</p>\r\n\r\n<p><strong>Bạn nhận được g&igrave; tại kh&oacute;a học?</strong></p>\r\n\r\n<ul>\r\n	<li>Hiểu được tư duy v&agrave; c&aacute;ch hoạt động của Laravel Livewire</li>\r\n	<li>C&aacute;c kiến thức căn bản trong Livewire: Component, Fullpage Component, Properties,...</li>\r\n	<li>Kỹ thuật l&agrave;m việc với Form &amp; Action: Validation, Event, Navigation, Dispatch Event,...</li>\r\n	<li>T&igrave;m hiểu mối quan hệ giữa Livewire v&agrave; Alpine, c&aacute;ch l&agrave;m việc với Alpine</li>\r\n	<li>Kỹ thuật xử l&yacute; URL trong Livewire</li>\r\n	<li>T&igrave;m hiểu về ph&acirc;n trang v&agrave; upload file từ cơ bản đến n&acirc;ng cao trong Livewire</li>\r\n	<li>C&aacute;c kỹ thuật l&agrave;m việc với Authentication trong Livewire từ cơ bản đến n&acirc;ng cao</li>\r\n	<li>T&igrave;m hiểu c&aacute;c PHP Attribute được hỗ trợ trong Livewire</li>\r\n	<li>T&igrave;m hiểu về Volt trong Livewire v&agrave; ứng dụng thực tế</li>\r\n</ul>\r\n\r\n<p><strong>C&acirc;u hỏi thường gặp?</strong></p>\r\n\r\n<p><em><strong>Kh&oacute;a học n&agrave;y cần kiến thức nền g&igrave; kh&ocirc;ng?</strong></em></p>\r\n\r\n<p>C&oacute;. Livewire l&agrave; package của Laravel n&ecirc;n cần chạy tr&ecirc;n nền của Laravel c&oacute; nghĩa bạn cần c&oacute; c&aacute;c kiến thức li&ecirc;n quan đến Laravel v&agrave; PHP</p>\r\n\r\n<p><em><strong>Trong qu&aacute; tr&igrave;nh học, t&ocirc;i c&oacute; được hỏi giảng vi&ecirc;n kh&ocirc;ng?</strong></em></p>\r\n\r\n<p>Được, nếu gặp kh&oacute; khăn bạn c&oacute; thể nhắn tin trực tiếp cho giảng vi&ecirc;n qua Zalo hoặc tr&ecirc;n group được tham gia</p>\r\n\r\n<p><em><strong>T&ocirc;i cần c&agrave;i đặt những phần mềm/c&ocirc;ng cụ g&igrave; để học?</strong></em></p>\r\n\r\n<p>Để học Livewire, bạn cần c&agrave;i đặt phần mềm để viết code (PHPStorrm, Visual Studio Code,...) v&agrave; phần mềm tạo Server ảo&nbsp;(Xampp, Wamp, Ampps,...)</p>\r\n\r\n<p><em><strong>Trong b&agrave;i giảng bạn d&ugrave;ng phần mềm n&agrave;o để hướng dẫn học vi&ecirc;n?</strong></em></p>\r\n\r\n<p>Trong kh&oacute;a học m&igrave;nh đang sử dụng Visual Studio Code để viết code v&agrave; Xampp để tạo Server ảo.&nbsp;Bạn c&oacute; thể d&ugrave;ng bất kỳ c&ocirc;ng cụ n&agrave;o m&agrave; kh&ocirc;ng ảnh hưởng đến chất lượng học tập</p>\r\n\r\n<p><em><strong>Trong kh&oacute;a học đang hướng dẫn ở phi&ecirc;n bản Laravel Livewire bao nhi&ecirc;u?</strong></em></p>\r\n\r\n<p>Tại thời điểm quay kh&oacute;a học, m&igrave;nh đang sử dụng phi&ecirc;n bản Livewire 3.x v&agrave; chạy tr&ecirc;n Laravel 10.x</p>\r\n\r\n<p><em><strong>Kh&oacute;a học n&agrave;y c&ograve;n ph&ugrave; hợp với thời điểm hiện tại kh&ocirc;ng?</strong></em></p>\r\n\r\n<p>C&oacute;. Phi&ecirc;n bản Laravel Livewire&nbsp;3.x l&agrave; bản mới nhất tại thời điểm hiện tại</p>\r\n\r\n<p><em><strong>Kh&oacute;a học n&agrave;y c&oacute; được cập nhật trong tương lai kh&ocirc;ng?</strong></em></p>\r\n\r\n<p>C&oacute; bạn nh&eacute;. M&igrave;nh sẽ cập nhật những kiến thức mới, tư duy mới trong Laravel Livewire ph&ugrave; hợp với y&ecirc;u cầu v&agrave; xu hướng trong tuyển dụng</p>', '<p>Laravel Livewire is a full-stack framework for Laravel that simplifies building dynamic interfaces. It allows you to create single-page applications (SPAs) without writing any JavaScript code.</p>\r\n\r\n<p>You can integrate Livewire into your Laravel project to build a full-stack project including both front-end and back-end using only HTML, CSS, and PHP.</p>\r\n\r\n<p>The Laravel Livewire 3.x course, taught directly by Hoang An Unicode, will guide you step-by-step to this framework and enable you to build your own products with Laravel Livewire.</p>\r\n\r\n<p>What will you gain from this course?</p>\r\n\r\n<p>Understanding the mindset and workings of Laravel Livewire<br />\r\nFundamental knowledge in Livewire: Components, Fullpage Components, Properties, etc.<br />\r\nTechniques for working with Forms &amp; Actions: Validation, Events, Navigation, Dispatch Events, etc.<br />\r\nUnderstanding the relationship between Livewire and Alpine, how to work with Alpine<br />\r\nTechniques for handling URLs in Livewire<br />\r\nLearning about pagination and file uploads from basic to advanced in Livewire<br />\r\nTechniques for working with Authentication in Livewire from basic to advanced<br />\r\nUnderstanding the PHP Attributes supported in Livewire<br />\r\nUnderstanding Volt in Livewire and its practical applications<br />\r\nFrequently Asked Questions?</p>\r\n\r\n<p>Does this course require any prior knowledge?</p>\r\n\r\n<p>Yes. Livewire is a Laravel package, so it needs to run on the Laravel platform, meaning you need knowledge related to Laravel and PHP.</p>\r\n\r\n<p>Can I ask questions to the instructor during the course?</p>\r\n\r\n<p>Yes, if you encounter any difficulties, you can message the instructor directly via Zalo or in the group you&#39;re in.</p>\r\n\r\n<p>What software/tools do I need to install to learn?</p>\r\n\r\n<p>To learn Livewire, you need to install software for writing code (PHPStorrm, Visual Studio Code,...) and software for creating virtual servers (Xampp, Wamp, Ampps,...)</p>\r\n\r\n<p>Which software do you use to guide students in the lecture?</p>\r\n\r\n<p>In this course, I&#39;m using Visual Studio Code to write code and Xampp to create virtual servers. You can use any tool without affecting the quality of learning.</p>\r\n\r\n<p>What version of Laravel Livewire is being used in this course?</p>\r\n\r\n<p>At the time of recording the course, I was using Livewire 3.x and running it on Laravel 10.x.</p>\r\n\r\n<p>Is this course still relevant today?</p>\r\n\r\n<p>Yes. Laravel Livewire version 3.x is the latest version currently available.</p>\r\n\r\n<p>Will this course be updated in the future?</p>\r\n\r\n<p>Yes, it will. I will update the course with new knowledge and approaches in Laravel Livewire to meet the requirements and trends in recruitment.</p>', '<p>Laravel Livewire는 동적 인터페이스 구축을 간소화하는 Laravel용 풀스택 프레임워크입니다. JavaScript 코드를 전혀 작성하지 않고도 단일 페이지 애플리케이션(SPA)을 만들 수 있습니다.</p>\r\n\r\n<p>Livewire를 Laravel 프로젝트에 통합하면 HTML, CSS, PHP만으로 프런트엔드와 백엔드를 모두 포함하는 풀스택 프로젝트를 구축할 수 있습니다.</p>\r\n\r\n<p>Hoang An Unicode가 직접 진행하는 Laravel Livewire 3.x 강좌는 이 프레임워크를 단계별로 안내하여 Laravel Livewire를 활용한 자신만의 제품을 개발할 수 있도록 도와줍니다.</p>\r\n\r\n<p>이 강좌를 통해 무엇을 얻을 수 있을까요?</p>\r\n\r\n<p>Laravel Livewire의 사고방식과 작동 방식 이해<br />\r\nLivewire의 기본 지식: 컴포넌트, 풀페이지 컴포넌트, 속성 등<br />\r\n폼 및 액션 작업 기법: 유효성 검사, 이벤트, 내비게이션, 디스패치 이벤트 등<br />\r\nLivewire와 Alpine의 관계 및 Alpine 사용 방법 이해<br />\r\nLivewire에서 URL 처리 기법<br />\r\nLivewire에서 페이지네이션 및 파일 업로드(기초부터 고급까지) 학습<br />\r\nLivewire에서 인증(기초부터 고급까지) 작업 기법<br />\r\nLivewire에서 지원하는 PHP 속성 이해<br />\r\nLivewire의 Volt 및 실제 적용 사례 이해<br />\r\n자주 묻는 질문?</p>\r\n\r\n<p>이 과정은 사전 지식이 필요한가요?</p>\r\n\r\n<p>네. Livewire는 Laravel 패키지이므로 Laravel 플랫폼에서 실행되어야 합니다. 즉, Laravel 및 PHP 관련 지식이 필요합니다.</p>\r\n\r\n<p>수업 중에 강사에게 질문할 수 있나요?</p>\r\n\r\n<p>네, 어려움이 있으시면 Zalo를 통해 강사에게 직접 메시지를 보내거나 참여 중인 그룹에서 문의하실 수 있습니다.</p>\r\n\r\n<p>학습을 위해 어떤 소프트웨어/도구를 설치해야 하나요?</p>\r\n\r\n<p>Livewire를 학습하려면 코드 작성 소프트웨어(PHPStorm, Visual Studio Code 등)와 가상 서버 생성 소프트웨어(Xampp, Wamp, Ampps 등)를 설치해야 합니다.</p>\r\n\r\n<p>강의에서는 어떤 소프트웨어를 사용하시나요?</p>\r\n\r\n<p>이 강좌에서는 코드 작성에는 Visual Studio Code를, 가상 서버 생성에는 Xampp를 사용합니다. 학습의 질에는 영향을 미치지 않으므로 어떤 도구를 사용하셔도 무방합니다.</p>\r\n\r\n<p>이 강좌에서는 어떤 버전의 Laravel Livewire를 사용하나요?</p>\r\n\r\n<p>강의 녹화 당시에는 Livewire 3.x 버전을 Laravel 10.x 환경에서 사용했습니다.</p>\r\n\r\n<p>이 강좌는 현재에도 유효한가요?</p>\r\n\r\n<p>네. Laravel Livewire 3.x 버전이 현재 사용 가능한 최신 버전입니다.</p>\r\n\r\n<p>이 강좌는 향후 업데이트될 예정인가요?</p>\r\n\r\n<p>네, 업데이트될 예정입니다. 채용 시장의 요구 사항과 트렌드에 맞춰 Laravel Livewire에 대한 새로운 지식과 접근 방식을 추가하여 강의를 업데이트할 예정입니다.</p>', '<p>Laravel Livewireは、動的なインターフェースの構築を簡素化するLaravelのフルスタックフレームワークです。JavaScriptコードを一切書かずに、シングルページアプリケーション（SPA）を作成できます。</p>\r\n\r\n<p>LivewireをLaravelプロジェクトに統合することで、HTML、CSS、PHPのみを使用して、フロントエンドとバックエンドの両方を含むフルスタックプロジェクトを構築できます。</p>\r\n\r\n<p>Hoang An Unicodeが直接指導するLaravel Livewire 3.xコースでは、このフレームワークの使い方をステップバイステップで解説し、Laravel Livewireを使った独自の製品開発を習得できます。</p>\r\n\r\n<p>このコースで得られるもの</p>\r\n\r\n<p>Laravel Livewire の考え方と仕組みを理解する<br />\r\nLivewire の基礎知識：コンポーネント、フルページコンポーネント、プロパティなど<br />\r\nフォームとアクションの操作テクニック：バリデーション、イベント、ナビゲーション、ディスパッチイベントなど<br />\r\nLivewire と Alpine の関係と、Alpine の使い方を理解する<br />\r\nLivewire で URL を扱うテクニック<br />\r\nLivewire におけるページネーションとファイルアップロードについて、基礎から応用まで学ぶ<br />\r\nLivewire における認証の操作テクニックを基礎から応用まで学ぶ<br />\r\nLivewire でサポートされている PHP 属性を理解する<br />\r\nLivewire における Volt とその実用的な応用を理解する<br />\r\nよくある質問</p>\r\n\r\n<p>このコースを受講するには、何か事前の知識が必要ですか？</p>\r\n\r\n<p>はい。Livewire は Laravel パッケージであるため、Laravel プラットフォーム上で実行する必要があります。つまり、Laravel と PHP に関する知識が必要です。</p>\r\n\r\n<p>コース中に講師に質問することはできますか？</p>\r\n\r\n<p>はい。何か問題が発生した場合は、Zalo または参加しているグループで講師に直接メッセージを送信できます。</p>\r\n\r\n<p>学習に必要なソフトウェアやツールは何ですか？</p>\r\n\r\n<p>Livewire を学習するには、コード記述用のソフトウェア（PHPStorrm、Visual Studio Code など）と仮想サーバー作成用のソフトウェア（Xampp、Wamp、Ampps など）をインストールする必要があります。</p>\r\n\r\n<p>講義では、学生の指導にどのソフトウェアを使用していますか？</p>\r\n\r\n<p>このコースでは、コード記述に Visual Studio Code、仮想サーバー作成に Xampp を使用しています。学習の質に影響を与えることなく、どのツールを使用しても構いません。</p>\r\n\r\n<p>このコースでは、どのバージョンの Laravel Livewire を使用していますか？</p>\r\n\r\n<p>コースの収録時点では、Livewire 3.x を使用しており、Laravel 10.x で実行していました。</p>\r\n\r\n<p>このコースは現在でも役立ちますか？</p>\r\n\r\n<p>はい。Laravel Livewire バージョン 3.x が現在利用可能な最新バージョンです。</p>\r\n\r\n<p>このコースは将来更新されますか？</p>\r\n\r\n<p>はい、更新されます。採用の要件とトレンドを満たすために、Laravel Livewire の新しい知識とアプローチを盛り込んでコースを更新します。</p>', '<p>Laravel Livewire 是一个适用于 Laravel 的全栈框架，它简化了动态界面的构建。它允许您创建单页应用程序 (SPA)，而无需编写任何 JavaScript 代码。</p>\r\n\r\n<p>您可以将 Livewire 集成到您的 Laravel 项目中，仅使用 HTML、CSS 和 PHP 构建包含前端和后端的全栈项目。</p>\r\n\r\n<p>由 Hoang An Unicode 亲自授课的 Laravel Livewire 3.x 课程将一步步指导您掌握此框架，并使您能够使用 Laravel Livewire 构建自己的产品。</p>\r\n\r\n<p>您将从本课程中获得什么？</p>\r\n\r\n<p>理解 Laravel Livewire 的思维模式和工作原理</p>\r\n\r\n<p>Livewire 基础知识：组件、全页组件、属性等</p>\r\n\r\n<p>表单和操作技巧：验证、事件、导航、分发事件等</p>\r\n\r\n<p>理解 Livewire 和 Alpine 之间的关系，以及如何在 Alpine 中工作</p>\r\n\r\n<p>Livewire 中 URL 的处理技巧</p>\r\n\r\n<p>从基础到高级学习 Livewire 中的分页和文件上传</p>\r\n\r\n<p>从基础到高级学习 Livewire 中的身份验证技巧</p>\r\n\r\n<p>理解 Livewire 中支持的 PHP 属性</p>\r\n\r\n<p>理解 Livewire 中的 Volt 及其实际应用</p>\r\n\r\n<p>常见问题解答？</p>\r\n\r\n<p>本课程需要任何先验知识吗？</p>\r\n\r\n<p>是的。Livewire 是一个 Laravel 包，因此它需要在 Laravel 平台上运行，这意味着您需要具备 Laravel 和 PHP 的相关知识。</p>\r\n\r\n<p>我可以在课程期间向讲师提问吗？</p>\r\n\r\n<p>是的，如果您遇到任何困难，可以直接通过 Zalo 或您所在的群组联系讲师。</p>\r\n\r\n<p>我需要安装哪些软件/工具才能学习？</p>\r\n\r\n<p>要学习 Livewire，您需要安装用于编写代码的软件（例如 PHPStorm、Visual Studio Code 等）以及用于创建虚拟服务器的软件（例如 Xampp、Wamp、Ampps 等）。</p>\r\n\r\n<p>您在授课时使用哪些软件来指导学生？</p>\r\n\r\n<p>在本课程中，我使用 Visual Studio Code 编写代码，并使用 Xampp 创建虚拟服务器。您可以使用任何工具，这不会影响学习质量。</p>\r\n\r\n<p>本课程中使用的是哪个版本的 Laravel Livewire？</p>\r\n\r\n<p>录制本课程时，我使用的是 Livewire 3.x 版本，运行在 Laravel 10.x 上。</p>\r\n\r\n<p>本课程现在仍然适用吗？</p>\r\n\r\n<p>是的。Laravel Livewire 3.x 版本是目前可用的最新版本。</p>\r\n\r\n<p>本课程将来会更新吗？</p>\r\n\r\n<p>是的，会的。我将根据招聘需求和趋势，对 Laravel Livewire 课程进行更新，引入新的知识和方法。</p>', 6, '/storage/photos/2/3f863552-ab07-49e9-8065-fe71b663c05d.jpg', 2295000.00, NULL, 795000.00, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'KH401500', 648.00, 0, '<p>Học tr&ecirc;n mọi thiết bị</p>\r\n\r\n<p>Code mẫu, t&agrave;i liệu đầy đủ</p>\r\n\r\n<p>Hỗ trợ 1-1 bởi giảng vi&ecirc;n, nh&oacute;m k&iacute;n</p>\r\n\r\n<p>Giới thiệu c&ocirc;ng việc ph&ugrave; hợp</p>\r\n\r\n<p>Thời hạn: Vĩnh viễn</p>', '<p>Learn on any device</p>\r\n\r\n<p>Sample code, complete documentation</p>\r\n\r\n<p>One-on-one support by instructors, private group</p>\r\n\r\n<p>Job placement assistance</p>\r\n\r\n<p>Duration: Lifetime</p>', '<p>모든 기기에서 학습 가능</p>\r\n\r\n<p>すべてのデバイスで学習可能</p>\r\n\r\n<p>예제 코드 및 완전한 학습 자료 제공</p>\r\n\r\n<p>강사의 1:1 지원 및 전용 커뮤니티</p>\r\n\r\n<p>역량에 맞는 취업 기회 제공</p>\r\n\r\n<p>평생 이용 가능</p>', '<p>すべてのデバイスで学習可能</p>\r\n\r\n<p>サンプルコードと完全な資料付き</p>\r\n\r\n<p>講師による1対1サポートと専用コミュニティ</p>\r\n\r\n<p>スキルに合った就職機会の紹介</p>\r\n\r\n<p>無期限アクセス</p>', '<p>支持在所有设备上学习</p>\r\n\r\n<p>提供示例代码和完整学习资料</p>\r\n\r\n<p>讲师一对一指导及专属学习社群</p>\r\n\r\n<p>推荐适合的工作机会</p>\r\n\r\n<p>永久访问</p>', 1, 'all_lessons', 0, NULL, NULL, NULL, NULL, 0, 0, 3, '2026-02-03 08:59:22', '2026-03-29 15:06:49', NULL);
INSERT INTO `courses` (`id`, `name`, `name_en`, `name_ko`, `name_ja`, `name_zh`, `slug`, `slug_en`, `slug_ko`, `slug_ja`, `slug_zh`, `detail`, `detail_en`, `detail_ko`, `detail_ja`, `detail_zh`, `teacher_id`, `thumbnail`, `price`, `quantity`, `sale_price`, `price_en`, `sale_price_en`, `price_ko`, `sale_price_ko`, `price_ja`, `sale_price_ja`, `price_zh`, `sale_price_zh`, `code`, `durations`, `is_document`, `supports`, `supports_en`, `supports_ko`, `supports_ja`, `supports_zh`, `status`, `completion_condition`, `is_coming_soon`, `coming_soon_start_at`, `end_at`, `package_locked_at`, `package_lock_reason`, `is_package_priority`, `is_learning_locked`, `view`, `created_at`, `updated_at`, `deleted_at`) VALUES
(19, 'Lập trình Back-End với NodeJS Express', 'Back-End Programming with NodeJS Express', 'Node.js Express로 백엔드 개발', 'Node.js Expressによるバックエンド開発', '使用 Node.js Express 进行后端开发', 'lap-trinh-back-end-voi-nodejs-express', 'back-end-programming-with-nodejs-express', 'nodejs-express로-백엔드-개발', 'nodejs-expressによるバックエンド開発', '使用-nodejs-express-进行后端开发', '<p><strong>L&agrave;m chủ Backend - Kh&ocirc;ng chỉ l&agrave; viết Code, đ&oacute; l&agrave; x&acirc;y dựng Hệ thống</strong></p>\r\n\r\n<p>Trong kỷ nguy&ecirc;n của Microservices v&agrave; Real-time applications, Node.js đ&atilde; trở th&agrave;nh &quot;vũ kh&iacute;&quot; h&agrave;ng đầu của c&aacute;c kỹ sư Backend tại c&aacute;c tập đo&agrave;n lớn. Tuy nhi&ecirc;n, việc chỉ biết d&ugrave;ng Express để tạo v&agrave;i API đơn giản l&agrave; chưa đủ để bạn chạm tay v&agrave;o những vị tr&iacute; Senior với mức lương ngh&igrave;n đ&ocirc;.</p>\r\n\r\n<p><strong>Bạn c&oacute; bao giờ tự hỏi:</strong></p>\r\n\r\n<p>► L&agrave;m sao để xử l&yacute; h&agrave;ng triệu dữ liệu m&agrave; kh&ocirc;ng l&agrave;m &quot;treo&quot; Server?</p>\r\n\r\n<p>► L&agrave;m sao để hệ thống tự động gửi h&agrave;ng ng&agrave;n Email c&ugrave;ng l&uacute;c m&agrave; kh&ocirc;ng ảnh hưởng đến trải nghiệm người d&ugrave;ng?</p>\r\n\r\n<p>► L&agrave;m sao để tối ưu h&oacute;a tốc độ phản hồi chỉ c&ograve;n dưới 100ms?</p>\r\n\r\n<p>► Kh&oacute;a học n&agrave;y được thiết kế để biến bạn từ một người mới bắt đầu trở th&agrave;nh một&nbsp;<strong>Backend Architect</strong>&nbsp;thực thụ, nắm vững mọi ng&oacute;c ng&aacute;ch của hệ sinh th&aacute;i Node.js.</p>\r\n\r\n<p><strong>Nội dung kh&oacute;a học c&oacute; g&igrave; đặc biệt?</strong></p>\r\n\r\n<p>Ch&uacute;ng ta sẽ đi một lộ tr&igrave;nh xuy&ecirc;n suốt từ &quot;Gốc rễ&quot; đến &quot;Ngọn&quot;, bao phủ to&agrave;n bộ những c&ocirc;ng nghệ m&agrave; một Backend Developer chuy&ecirc;n nghiệp bắt buộc phải biết.</p>\r\n\r\n<p><strong>Node.js Core Deep Dive (Nền tảng th&eacute;p)</strong></p>\r\n\r\n<p>Đừng chỉ học c&aacute;ch d&ugrave;ng, h&atilde;y học c&aacute;ch n&oacute; vận h&agrave;nh. Ch&uacute;ng ta sẽ giải m&atilde;:</p>\r\n\r\n<p>►&nbsp;<strong>Event Loop &amp; Libuv:</strong>&nbsp;Hiểu cơ chế bất đồng bộ để viết code kh&ocirc;ng &quot;blocking&quot;.</p>\r\n\r\n<p>►&nbsp;<strong>Buffer &amp; Streams:</strong>&nbsp;Xử l&yacute; file lớn, video streaming v&agrave; dữ liệu nhị ph&acirc;n hiệu năng cao.</p>\r\n\r\n<p>►&nbsp;<strong>Process &amp; Child Process:</strong>&nbsp;Tận dụng tối đa sức mạnh đa nh&acirc;n của CPU.</p>\r\n\r\n<p><strong>Express.js &amp; RESTful API Architecture</strong></p>\r\n\r\n<p>X&acirc;y dựng nền m&oacute;ng ứng dụng chuẩn c&ocirc;ng nghiệp:</p>\r\n\r\n<p>► Thiết kế&nbsp;<strong>RESTful API</strong>&nbsp;đ&uacute;ng chuẩn quốc tế.</p>\r\n\r\n<p>► Tổ chức Source Code theo m&ocirc; h&igrave;nh&nbsp;<strong>MVC (Model-View-Controller)</strong>&nbsp;sạch sẽ, dễ bảo tr&igrave;.</p>\r\n\r\n<p>►&nbsp;<strong>Middleware chuy&ecirc;n s&acirc;u:</strong>&nbsp;Authentication (JWT), Validation, Error Handling tập trung.</p>\r\n\r\n<p>►&nbsp;<strong>Database Mastery (SQL &amp; NoSQL)</strong></p>\r\n\r\n<p>L&agrave;m chủ dữ liệu với cả hai trường ph&aacute;i:</p>\r\n\r\n<p>►&nbsp;<strong>MongoDB (Mongoose):</strong>&nbsp;Linh hoạt, tốc độ cho c&aacute;c ứng dụng hiện đại.</p>\r\n\r\n<p>►&nbsp;<strong>PostgreSQL/MySQL:</strong>&nbsp;Chặt chẽ cho c&aacute;c hệ thống t&agrave;i ch&iacute;nh, quản l&yacute;.</p>\r\n\r\n<p><strong>Advanced Backend Skills (Kỹ thuật cao cấp)</strong></p>\r\n\r\n<p>Đ&acirc;y l&agrave; phần gi&uacute;p bạn kh&aacute;c biệt ho&agrave;n to&agrave;n với phần c&ograve;n lại của thị trường:</p>\r\n\r\n<p>►&nbsp;<strong>Caching với Redis:</strong>&nbsp;Tăng tốc ứng dụng gấp 10 lần bằng c&aacute;ch tối ưu h&oacute;a truy vấn bộ nhớ đệm.</p>\r\n\r\n<p>►&nbsp;<strong>Message Queue (BullMQ/RabbitMQ):</strong>&nbsp;Xử l&yacute; t&aacute;c vụ ngầm (Background Jobs) gi&uacute;p hệ thống lu&ocirc;n mượt m&agrave;.</p>\r\n\r\n<p>►&nbsp;<strong>Cronjobs:</strong>&nbsp;Tự động h&oacute;a c&aacute;c t&aacute;c vụ lặp lại (qu&eacute;t dữ liệu, gửi b&aacute;o c&aacute;o h&agrave;ng tuần).</p>\r\n\r\n<p>►&nbsp;<strong>Security:</strong>&nbsp;Chống tấn c&ocirc;ng XSS, CSRF, Rate Limiting để bảo vệ hệ thống.</p>\r\n\r\n<p>►&nbsp;<strong>WebSocket</strong>: X&acirc;y dựng ứng dụng thời gian thực</p>\r\n\r\n<p><strong>Sau kh&oacute;a học, bạn sẽ nhận được g&igrave;?</strong></p>\r\n\r\n<p>►&nbsp;<strong>Kiến thức to&agrave;n diện:</strong>&nbsp;Nắm trọn bộ stack Backend từ cơ bản đến n&acirc;ng cao</p>\r\n\r\n<p>►&nbsp;<strong>Tư duy hệ thống:</strong>&nbsp;Biết c&aacute;ch phối hợp giữa Database, Cache v&agrave; Queue để giải quyết b&agrave;i to&aacute;n tải lớn</p>\r\n\r\n<p>►&nbsp;<strong>Project thực chiến:</strong>&nbsp;Ho&agrave;n thiện một dự &aacute;n thực tế &quot;khủng&quot; để đưa v&agrave;o Portfolio</p>\r\n\r\n<p><strong>Th&ocirc;ng tin d&agrave;nh cho bạn</strong></p>\r\n\r\n<p>►&nbsp;<strong>Đối tượng:</strong>&nbsp;Developer muốn theo đuổi con đường Full-stack/Backend chuy&ecirc;n nghiệp.</p>\r\n\r\n<p>►&nbsp;<strong>Y&ecirc;u cầu:</strong>&nbsp;Chỉ cần c&oacute; kiến thức JavaScript cơ bản, c&ograve;n lại ch&uacute;ng t&ocirc;i sẽ dẫn dắt bạn.</p>\r\n\r\n<p>►&nbsp;<strong>Hỗ trợ:</strong>&nbsp;Cộng đồng học vi&ecirc;n năng động &amp; Mentor hỗ trợ trực tiếp.</p>\r\n\r\n<p><em><strong>Backend l&agrave; bộ n&atilde;o của mọi ứng dụng. Đừng chỉ x&acirc;y dựng một bộ n&atilde;o chạy được, h&atilde;y x&acirc;y dựng một bộ n&atilde;o th&ocirc;ng minh v&agrave; mạnh mẽ</strong></em></p>', '<p>Mastering Backend Development - More Than Just Writing Code, It&#39;s Building Systems</p>\r\n\r\n<p>In the era of Microservices and Real-time applications, Node.js has become the leading tool for backend engineers at large corporations. However, simply knowing how to use Express to create a few simple APIs isn&#39;t enough to reach senior positions with salaries in the thousands of dollars.</p>\r\n\r\n<p>Have you ever wondered:</p>\r\n\r\n<p>► How to process millions of data points without crashing the server?</p>\r\n\r\n<p>► How to automate sending thousands of emails simultaneously without affecting user experience?</p>\r\n\r\n<p>► How to optimize response times to under 100ms?</p>\r\n\r\n<p>► This course is designed to transform you from a beginner into a true Backend Architect, mastering every aspect of the Node.js ecosystem.</p>\r\n\r\n<p>What&#39;s special about the course content?</p>\r\n\r\n<p>We will take a comprehensive path from &quot;Roots&quot; to &quot;Tops,&quot; covering all the technologies that a professional Backend Developer absolutely must know.</p>\r\n\r\n<p>Node.js Core Deep Dive (Steel Foundation)</p>\r\n\r\n<p>Don&#39;t just learn how to use it, learn how it works. We will decode:</p>\r\n\r\n<p>► Event Loop &amp; Libuv: Understanding asynchronous mechanisms to write non-blocking code.</p>\r\n\r\n<p>► Buffer &amp; Streams: Handling large files, video streaming, and high-performance binary data.</p>\r\n\r\n<p>► Process &amp; Child Process: Maximizing the multi-core power of the CPU.</p>\r\n\r\n<p>Express.js &amp; RESTful API Architecture</p>\r\n\r\n<p>Building an industry-standard application foundation:</p>\r\n\r\n<p>► Designing RESTful APIs according to international standards.</p>\r\n\r\n<p>► Organizing source code using a clean, maintainable MVC (Model-View-Controller) model.</p>\r\n\r\n<p>► Advanced Middleware: Centralized Authentication (JWT), Validation, and Error Handling.</p>\r\n\r\n<p>► Database Mastery (SQL &amp; NoSQL)</p>\r\n\r\n<p>Master data with both approaches:</p>\r\n\r\n<p>► MongoDB (Mongoose): Flexible and fast for modern applications.</p>\r\n\r\n<p>► PostgreSQL/MySQL: Robust for financial and management systems.</p>\r\n\r\n<p>Advanced Backend Skills</p>\r\n\r\n<p>This is what sets you apart from the rest of the market:</p>\r\n\r\n<p>► Caching with Redis: Accelerate applications up to 10 times by optimizing cache queries.</p>\r\n\r\n<p>► Message Queue (BullMQ/RabbitMQ): Handles background jobs to keep your system running smoothly.</p>\r\n\r\n<p>► Cronjobs: Automate repetitive tasks (data scanning, sending weekly reports).</p>\r\n\r\n<p>► Security: Protect against XSS, CSRF, and Rate Limiting attacks to safeguard the system.</p>\r\n\r\n<p>► WebSocket: Build real-time applications.</p>\r\n\r\n<p>What will you gain after the course?</p>\r\n\r\n<p>► Comprehensive knowledge: Master the entire backend stack from basic to advanced.</p>\r\n\r\n<p>► Systems thinking: Learn how to coordinate databases, caches, and queues to solve high-load problems.</p>\r\n\r\n<p>► Practical project: Complete a real-world, high-performance project to add to your portfolio.</p>\r\n\r\n<p>Information for you:</p>\r\n\r\n<p>► Target audience: Developers who want to pursue a professional full-stack/backend career.</p>\r\n\r\n<p>► Requirements: Only basic JavaScript knowledge is needed; we will guide you through the rest.</p>\r\n\r\n<p>► Support: Active student community &amp; direct mentor support.</p>\r\n\r\n<p>The backend is the brain of every application. Don&#39;t just build a brain that works, build a smart and powerful brain.</p>', '<p>精通后端开发&mdash;&mdash;不仅仅是编写代码，更是构建系统</p>\r\n\r\n<p>在微服务和实时应用时代，Node.js 已成为大型企业后端工程师的首选工具。然而，仅仅掌握如何使用 Express 创建几个简单的 API 并不足以让你获得年薪数千美元的高级职位。</p>\r\n\r\n<p>你是否曾想过：</p>\r\n\r\n<p>► 如何在不导致服务器崩溃的情况下处理数百万个数据点？</p>\r\n\r\n<p>► 如何在不影响用户体验的情况下自动同时发送数千封电子邮件？</p>\r\n\r\n<p>► 如何将响应时间优化到 100 毫秒以内？</p>\r\n\r\n<p>► 本课程旨在将你从入门级培养成真正的后端架构师，让你精通 Node.js 生态系统的方方面面。</p>\r\n\r\n<p>课程内容有何特色？</p>\r\n\r\n<p>我们将从&ldquo;基础&rdquo;到&ldquo;精通&rdquo;，全面讲解专业后端开发人员必须掌握的所有技术。</p>\r\n\r\n<p>Node.js 核心深度解析（钢铁基础）</p>\r\n\r\n<p>不要只学习如何使用它，更要了解它的工作原理。我们将深入解析：</p>\r\n\r\n<p>► 事件循环与 Libuv：理解异步机制，编写非阻塞代码。</p>\r\n\r\n<p>► 缓冲区与流：处理大型文件、视频流和高性能二进制数据。</p>\r\n\r\n<p>► 进程与子进程：最大化利用 CPU 的多核性能。</p>\r\n\r\n<p>Express.js 与 RESTful API 架构</p>\r\n\r\n<p>构建符合行业标准的应用程序基础：</p>\r\n\r\n<p>► 根据国际标准设计 RESTful API。</p>\r\n\r\n<p>► 使用简洁易维护的 MVC（模型-视图-控制器）架构组织源代码。</p>\r\n\r\n<p>► 高级中间件：集中式身份验证（JWT）、验证和错误处理。</p>\r\n\r\n<p>► 数据库精通（SQL 和 NoSQL）</p>\r\n\r\n<p>掌握两种数据处理方法：</p>\r\n\r\n<p>► MongoDB（Mongoose）：灵活快速，适用于现代应用程序。</p>\r\n\r\n<p>► PostgreSQL/MySQL：适用于财务和管理系统。</p>\r\n\r\n<p>高级后端技能</p>\r\n\r\n<p>以下技能将使您在众多竞争者中脱颖而出：</p>\r\n\r\n<p>► 使用 Redis 进行缓存：通过优化缓存查询，将应用程序速度提升高达 10 倍。</p>\r\n\r\n<p>► 消息队列（BullMQ/RabbitMQ）：处理后台任务，确保系统流畅运行。</p>\r\n\r\n<p>► 定时任务：自动化重复性任务（数据扫描、发送每周报告）。</p>\r\n\r\n<p>► 安全性：防御 XSS、CSRF 和速率限制攻击，保障系统安全。</p>\r\n\r\n<p>► WebSocket：构建实时应用程序。</p>\r\n\r\n<p>完成课程后，您将获得什么？</p>\r\n\r\n<p>► 全面知识：掌握从基础到高级的整个后端技术栈。</p>\r\n\r\n<p>► 系统思维：学习如何协调数据库、缓存和队列，解决高负载问题。</p>\r\n\r\n<p>► 实践项目：完成一个真实世界的高性能项目，丰富您的作品集。</p>\r\n\r\n<p>课程信息：</p>\r\n\r\n<p>► 目标受众：希望从事全栈/后端开发职业的开发者。</p>\r\n\r\n<p>► 要求：只需具备基本的 JavaScript 知识；我们会指导您完成其余部分。</p>\r\n\r\n<p>► 支持：活跃的学生社区和导师的直接支持。</p>\r\n\r\n<p>后端是每个应用程序的大脑。不要仅仅构建一个能运行的大脑，而是要构建一个智能且强大的大脑。</p>', '<p>バックエンド開発をマスターする - コードを書くだけでなく、システムを構築する</p>\r\n\r\n<p>マイクロサービスとリアルタイムアプリケーションの時代において、Node.jsは大企業のバックエンドエンジニアにとって主要なツールとなっています。しかし、Expressを使っていくつかのシンプルなAPIを作成する方法を知っているだけでは、数千ドルの給与が支払われる上級管理職に就くには不十分です。</p>\r\n\r\n<p>こんな疑問を持ったことはありませんか？</p>\r\n\r\n<p>► サーバーをクラッシュさせずに数百万のデータポイントを処理するには？</p>\r\n\r\n<p>► ユーザーエクスペリエンスに影響を与えずに数千通のメールを同時送信する方法は？</p>\r\n\r\n<p>► 応答時間を100ミリ秒未満に最適化するには？</p>\r\n\r\n<p>► このコースは、初心者から真のバックエンドアーキテクトへと成長し、Node.jsエコシステムのあらゆる側面を習得できるように設計されています。</p>\r\n\r\n<p>コース内容の特徴</p>\r\n\r\n<p>「ルーツ」から「トップ」まで、プロフェッショナルなバックエンド開発者が絶対に知っておくべきすべてのテクノロジーを網羅した包括的なコースです。</p>\r\n\r\n<p>Node.js Core Deep Dive (Steel Foundation)</p>\r\n\r\n<p>使い方を学ぶだけでなく、仕組みも理解しましょう。以下の内容を解説します。</p>\r\n\r\n<p>► イベントループとLibuv：非同期メカニズムを理解し、ノンブロッキングコードを書く。</p>\r\n\r\n<p>► バッファとストリーム：大容量ファイル、ビデオストリーミング、高性能バイナリデータの処理。</p>\r\n\r\n<p>► プロセスと子プロセス：CPUのマルチコアパワーを最大限に活用する。</p>\r\n\r\n<p>Express.jsとRESTful APIアーキテクチャ</p>\r\n\r\n<p>業界標準のアプリケーション基盤の構築：</p>\r\n\r\n<p>► 国際標準に準拠したRESTful APIの設計。</p>\r\n\r\n<p>► クリーンで保守性の高いMVC（モデル・ビュー・コントローラ）モデルを用いたソースコードの整理。</p>\r\n\r\n<p>► 高度なミドルウェア：集中認証（JWT）、検証、エラー処理。</p>\r\n\r\n<p>► データベースの習得（SQLとNoSQL）</p>\r\n\r\n<p>両方のアプローチによるマスターデータ：</p>\r\n\r\n<p>► MongoDB（Mongoose）：最新のアプリケーションに最適な柔軟性と高速性。</p>\r\n\r\n<p>► PostgreSQL/MySQL：財務・経営システムに最適な堅牢性。</p>\r\n\r\n<p>高度なバックエンドスキル</p>\r\n\r\n<p>他社との差別化要因：</p>\r\n\r\n<p>► Redisによるキャッシュ：キャッシュクエリを最適化することで、アプリケーションを最大10倍高速化します。</p>\r\n\r\n<p>► メッセージキュー（BullMQ/RabbitMQ）：バックグラウンドジョブを処理し、システムのスムーズな稼働を維持します。</p>\r\n\r\n<p>► Cronジョブ：反復タスク（データスキャン、週次レポートの送信など）を自動化します。</p>\r\n\r\n<p>► セキュリティ：XSS、CSRF、レート制限攻撃からシステムを保護します。</p>\r\n\r\n<p>► WebSocket：リアルタイムアプリケーションを構築します。</p>\r\n\r\n<p>このコースで得られるもの：</p>\r\n\r\n<p>► 包括的な知識：バックエンドスタック全体を基礎から応用まで習得します。</p>\r\n\r\n<p>► システム思考：データベース、キャッシュ、キューを調整して、高負荷の問題を解決する方法を学びます。</p>\r\n\r\n<p>► 実践的なプロジェクト：実践的な高パフォーマンスプロジェクトを完了し、ポートフォリオに追加します。</p>\r\n\r\n<p>あなたへの情報：</p>\r\n\r\n<p>► 対象者：フルスタック/バックエンドのプロフェッショナルキャリアを目指す開発者</p>\r\n\r\n<p>► 要件：基本的なJavaScriptの知識のみが必要です。残りは私たちがサポートします。</p>\r\n\r\n<p>► サポート：活発な学生コミュニティとメンターによる直接サポート</p>\r\n\r\n<p>バックエンドはあらゆるアプリケーションの頭脳です。単に動作する頭脳を構築するだけでなく、賢く強力な頭脳を構築しましょう。</p>', '<p>精通后端开发&mdash;&mdash;不仅仅是编写代码，更是构建系统</p>\r\n\r\n<p>在微服务和实时应用时代，Node.js 已成为大型企业后端工程师的首选工具。然而，仅仅掌握如何使用 Express 创建几个简单的 API 并不足以让你获得年薪数千美元的高级职位。</p>\r\n\r\n<p>你是否曾想过：</p>\r\n\r\n<p>► 如何在不导致服务器崩溃的情况下处理数百万个数据点？</p>\r\n\r\n<p>► 如何在不影响用户体验的情况下自动同时发送数千封电子邮件？</p>\r\n\r\n<p>► 如何将响应时间优化到 100 毫秒以内？</p>\r\n\r\n<p>► 本课程旨在将你从入门级培养成真正的后端架构师，让你精通 Node.js 生态系统的方方面面。</p>\r\n\r\n<p>课程内容有何特色？</p>\r\n\r\n<p>我们将从&ldquo;基础&rdquo;到&ldquo;精通&rdquo;，全面讲解专业后端开发人员必须掌握的所有技术。</p>\r\n\r\n<p>Node.js 核心深度解析（钢铁基础）</p>\r\n\r\n<p>不要只学习如何使用它，更要了解它的工作原理。我们将深入解析：</p>\r\n\r\n<p>► 事件循环与 Libuv：理解异步机制，编写非阻塞代码。</p>\r\n\r\n<p>► 缓冲区与流：处理大型文件、视频流和高性能二进制数据。</p>\r\n\r\n<p>► 进程与子进程：最大化利用 CPU 的多核性能。</p>\r\n\r\n<p>Express.js 与 RESTful API 架构</p>\r\n\r\n<p>构建符合行业标准的应用程序基础：</p>\r\n\r\n<p>► 根据国际标准设计 RESTful API。</p>\r\n\r\n<p>► 使用简洁易维护的 MVC（模型-视图-控制器）架构组织源代码。</p>\r\n\r\n<p>► 高级中间件：集中式身份验证（JWT）、验证和错误处理。</p>\r\n\r\n<p>► 数据库精通（SQL 和 NoSQL）</p>\r\n\r\n<p>掌握两种数据处理方法：</p>\r\n\r\n<p>► MongoDB（Mongoose）：灵活快速，适用于现代应用程序。</p>\r\n\r\n<p>► PostgreSQL/MySQL：适用于财务和管理系统。</p>\r\n\r\n<p>高级后端技能</p>\r\n\r\n<p>以下技能将使您在众多竞争者中脱颖而出：</p>\r\n\r\n<p>► 使用 Redis 进行缓存：通过优化缓存查询，将应用程序速度提升高达 10 倍。</p>\r\n\r\n<p>► 消息队列（BullMQ/RabbitMQ）：处理后台任务，确保系统流畅运行。</p>\r\n\r\n<p>► 定时任务：自动化重复性任务（数据扫描、发送每周报告）。</p>\r\n\r\n<p>► 安全性：防御 XSS、CSRF 和速率限制攻击，保障系统安全。</p>\r\n\r\n<p>► WebSocket：构建实时应用程序。</p>\r\n\r\n<p>完成课程后，您将获得什么？</p>\r\n\r\n<p>► 全面知识：掌握从基础到高级的整个后端技术栈。</p>\r\n\r\n<p>► 系统思维：学习如何协调数据库、缓存和队列，解决高负载问题。</p>\r\n\r\n<p>► 实践项目：完成一个真实世界的高性能项目，丰富您的作品集。</p>\r\n\r\n<p>课程信息：</p>\r\n\r\n<p>► 目标受众：希望从事全栈/后端开发职业的开发者。</p>\r\n\r\n<p>► 要求：只需具备基本的 JavaScript 知识；我们会指导您完成其余部分。</p>\r\n\r\n<p>► 支持：活跃的学生社区和导师的直接支持。</p>\r\n\r\n<p>后端是每个应用程序的大脑。不要仅仅构建一个能运行的大脑，而是要构建一个智能且强大的大脑。</p>', 6, '/storage/photos/2/3274e63b-c37a-4217-bb04-1a046dee0173.jpg', 2495000.00, NULL, 995000.00, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'KH349882', 549.00, 0, '<p>Học tr&ecirc;n mọi thiết bị</p>\r\n\r\n<p>Code mẫu, t&agrave;i liệu đầy đủ</p>\r\n\r\n<p>Hỗ trợ 1-1 bởi giảng vi&ecirc;n, nh&oacute;m k&iacute;n</p>\r\n\r\n<p>Giới thiệu c&ocirc;ng việc ph&ugrave; hợp</p>\r\n\r\n<p>Thời hạn: Vĩnh viễn</p>', '<p>Learn on any device</p>\r\n\r\n<p>Sample code, complete documentation</p>\r\n\r\n<p>One-on-one support by instructors, private group</p>\r\n\r\n<p>Job placement assistance</p>\r\n\r\n<p>Duration: Lifetime</p>', '<p>모든 기기에서 학습 가능</p>\r\n\r\n<p>すべてのデバイスで学習可能</p>\r\n\r\n<p>예제 코드 및 완전한 학습 자료 제공</p>\r\n\r\n<p>강사의 1:1 지원 및 전용 커뮤니티</p>\r\n\r\n<p>역량에 맞는 취업 기회 제공</p>\r\n\r\n<p>평생 이용 가능</p>', '<p>すべてのデバイスで学習可能</p>\r\n\r\n<p>サンプルコードと完全な資料付き</p>\r\n\r\n<p>講師による1対1サポートと専用コミュニティ</p>\r\n\r\n<p>スキルに合った就職機会の紹介</p>\r\n\r\n<p>無期限アクセス</p>', '<p>支持在所有设备上学习</p>\r\n\r\n<p>提供示例代码和完整学习资料</p>\r\n\r\n<p>讲师一对一指导及专属学习社群</p>\r\n\r\n<p>推荐适合的工作机会</p>\r\n\r\n<p>永久访问</p>', 1, 'all_lessons', 0, NULL, NULL, NULL, NULL, 0, 0, 2, '2026-02-03 09:02:13', '2026-03-29 15:06:37', NULL),
(20, 'Xây dựng Back-End Nodejs bằng Strapi CMS', 'Building a Node.js Back-End using Strapi CMS', 'Strapi CMS로 Node.js 백엔드 구축', 'Strapi CMSを使用したNode.jsバックエンド構築', '使用 Strapi CMS 构建 Node.js 后端', 'xay-dung-back-end-nodejs-bang-strapi-cms', 'building-a-nodejs-back-end-using-strapi-cms', 'strapi-cms로-nodejs-백엔드-구축', 'strapi-cmsを使用したnodejsバックエンド構築', '使用-strapi-cms-构建-nodejs-后端', '<p>Strapi l&agrave; một&nbsp;<strong>Headless CMS m&atilde; nguồn mở</strong>&nbsp;mạnh mẽ, cho ph&eacute;p bạn x&acirc;y dựng hệ thống quản l&yacute; nội dung linh hoạt, dễ mở rộng v&agrave; dễ t&iacute;ch hợp với c&aacute;c frontend framework như&nbsp;<strong>React, Next.js, Nuxt.js, Vue</strong>&nbsp;hoặc mobile app.</p>\r\n\r\n<p><em><strong>Trong kh&oacute;a học n&agrave;y, bạn sẽ:</strong></em></p>\r\n\r\n<p>- Hiểu r&otilde;&nbsp;<strong>kiến tr&uacute;c Headless CMS</strong>&nbsp;v&agrave; l&yacute; do Strapi được ưa chuộng.</p>\r\n\r\n<p>- Th&agrave;nh thạo&nbsp;<strong>CRUD API</strong>, Authentication, Role &amp; Permission trong Strapi.</p>\r\n\r\n<p>- T&ugrave;y biến&nbsp;<strong>controller, service, middleware, plugin</strong>&nbsp;để ph&ugrave; hợp nhu cầu thực tế.</p>\r\n\r\n<p>- Học c&aacute;ch&nbsp;<strong>triển khai Strapi</strong>&nbsp;l&ecirc;n m&ocirc;i trường Production (Docker, VPS, Cloud).</p>\r\n\r\n<p><strong>Bạn nhận được g&igrave; tại kh&oacute;a học?</strong></p>\r\n\r\n<p><strong>- Kiến thức từ cơ bản đến n&acirc;ng cao</strong>&nbsp;về Strapi v5.</p>\r\n\r\n<p>- Hướng dẫn&nbsp;<strong>x&acirc;y dựng hệ thống CMS ri&ecirc;ng</strong>&nbsp;cho website/blog/app.</p>\r\n\r\n<p>- Thực h&agrave;nh&nbsp;<strong>API-first development</strong>&nbsp;với frontend (React/Next/Nuxt).</p>\r\n\r\n<p>- Hiểu v&agrave; triển khai&nbsp;<strong>OAuth2, JWT, bảo mật API</strong>&nbsp;trong Strapi.</p>\r\n\r\n<p>- C&oacute; thể&nbsp;<strong>t&ugrave;y chỉnh Strapi</strong>&nbsp;để phục vụ c&aacute;c dự &aacute;n lớn: e-learning, thương mại điện tử, SaaS.</p>\r\n\r\n<p>- Bộ&nbsp;<strong>mini project thực tế</strong>&nbsp;để &aacute;p dụng ngay.</p>\r\n\r\n<p><strong>C&acirc;u hỏi thường gặp</strong></p>\r\n\r\n<p><em><strong>1. Kh&oacute;a học n&agrave;y ph&ugrave; hợp với ai?</strong></em></p>\r\n\r\n<p>Lập tr&igrave;nh vi&ecirc;n frontend muốn c&oacute; backend nhanh ch&oacute;ng, backend dev muốn thử Headless CMS, hoặc freelancer muốn r&uacute;t ngắn thời gian ph&aacute;t triển dự &aacute;n.</p>\r\n\r\n<p><em><strong>2. C&oacute; cần biết backend trước kh&ocirc;ng?</strong></em></p>\r\n\r\n<p>Kh&ocirc;ng bắt buộc. Kh&oacute;a học sẽ hướng dẫn từ đầu, nhưng c&oacute; kiến thức Node.js/REST API sẽ học nhanh hơn.</p>\r\n\r\n<p><em><strong>3. Sau kh&oacute;a học t&ocirc;i c&oacute; thể l&agrave;m g&igrave;?</strong></em></p>\r\n\r\n<p>Bạn c&oacute; thể tự tin x&acirc;y dựng hệ thống CMS cho doanh nghiệp, c&aacute; nh&acirc;n h&oacute;a API cho mobile/web, v&agrave; deploy sản phẩm thực tế l&ecirc;n server.</p>\r\n\r\n<p><em><strong>4. T&ocirc;i sẽ học bằng phi&ecirc;n bản Strapi n&agrave;o?</strong></em></p>\r\n\r\n<p>Kh&oacute;a học d&ugrave;ng&nbsp;<strong>Strapi v5</strong>&nbsp;&ndash; bản mới nhất, tối ưu v&agrave; bảo mật hơn.</p>', '<p>Strapi is a powerful open-source Headless CMS that allows you to build a flexible, scalable content management system that integrates easily with frontend frameworks like React, Next.js, Nuxt.js, Vue, or mobile apps.</p>\r\n\r\n<p>In this course, you will:</p>\r\n\r\n<p>- Understand the Headless CMS architecture and why Strapi is so popular.</p>\r\n\r\n<p>- Master the CRUD API, Authentication, Roles &amp; Permissions in Strapi.</p>\r\n\r\n<p>- Customize controllers, services, middleware, and plugins to suit your specific needs.</p>\r\n\r\n<p>- Learn how to deploy Strapi to a production environment (Docker, VPS, Cloud).</p>\r\n\r\n<p>What will you gain from this course?</p>\r\n\r\n<p>- Knowledge from basic to advanced levels of Strapi v5.</p>\r\n\r\n<p>- Guidance on building your own CMS system for your website/blog/app.</p>\r\n\r\n<p>- Practice API-first development with the frontend (React/Next/Nuxt).</p>\r\n\r\n<p>- Understand and implement OAuth2, JWT, and API security in Strapi.</p>\r\n\r\n<p>- Be able to customize Strapi to serve large projects: e-learning, e-commerce, SaaS.</p>\r\n\r\n<p>- A set of practical mini-projects for immediate application.</p>\r\n\r\n<p>Frequently Asked Questions</p>\r\n\r\n<p>1. Who is this course for?</p>\r\n\r\n<p>Frontend developers who want to quickly develop a backend, backend developers who want to try a Headless CMS, or freelancers who want to shorten project development time.</p>\r\n\r\n<p>2. Do I need to know backend development beforehand?</p>\r\n\r\n<p>Not required. The course will guide you from the beginning, but having Node.js/REST API knowledge will help you learn faster.</p>\r\n\r\n<p>3. What can I do after the course?</p>\r\n\r\n<p>You can confidently build CMS systems for businesses, personalize APIs for mobile/web, and deploy real-world products to servers. 4. Which version of Strapi will I be using?</p>\r\n\r\n<p>The course uses Strapi v5 &ndash; the latest, optimized, and more secure version.</p>', '<p>Strapi는 강력한 오픈 소스 헤드리스 CMS로, React, Next.js, Nuxt.js, Vue와 같은 프런트엔드 프레임워크 또는 모바일 앱과 쉽게 통합되는 유연하고 확장 가능한 콘텐츠 관리 시스템을 구축할 수 있도록 지원합니다.</p>\r\n\r\n<p>이 강좌에서는 다음을 학습합니다.</p>\r\n\r\n<p>- 헤드리스 CMS 아키텍처와 Strapi의 인기 요인 이해</p>\r\n\r\n<p>- Strapi의 CRUD API, 인증, 역할 및 권한 관리 마스터</p>\r\n\r\n<p>- 특정 요구 사항에 맞게 컨트롤러, 서비스, 미들웨어 및 플러그인 사용자 정의</p>\r\n\r\n<p>- 프로덕션 환경(Docker, VPS, 클라우드)에 Strapi 배포 방법 학습</p>\r\n\r\n<p>이 강좌를 통해 얻을 수 있는 것:</p>\r\n\r\n<p>- Strapi v5의 기초부터 고급 수준까지 학습</p>\r\n\r\n<p>- 웹사이트/블로그/앱을 위한 자체 CMS 시스템 구축 가이드</p>\r\n\r\n<p>- 프런트엔드(React/Next/Nuxt)를 활용한 API 우선 개발 실습</p>\r\n\r\n<p>- Strapi에서 OAuth2, JWT 및 API 보안 이해 및 구현</p>\r\n\r\n<p>- 대규모 프로젝트(e-러닝, 전자상거래, SaaS 등)에 맞춰 Strapi를 맞춤 설정할 수 있습니다.</p>\r\n\r\n<p>- 바로 적용 가능한 실용적인 미니 프로젝트 모음.</p>\r\n\r\n<p>자주 묻는 질문</p>\r\n\r\n<p>1. 이 과정은 누구를 위한 과정인가요?</p>\r\n\r\n<p>백엔드를 빠르게 개발하고 싶은 프론트엔드 개발자, 헤드리스 CMS를 사용해보고 싶은 백엔드 개발자, 또는 프로젝트 개발 시간을 단축하고 싶은 프리랜서에게 적합합니다.</p>\r\n\r\n<p>2. 백엔드 개발 경험이 있어야 하나요?</p>\r\n\r\n<p>필수 사항은 아닙니다. 본 과정에서는 기초부터 차근차근 안내해 드리지만, Node.js/REST API에 대한 지식이 있으면 학습 속도를 높이는 데 도움이 됩니다.</p>\r\n\r\n<p>3. 과정 수료 후에는 무엇을 할 수 있나요?</p>\r\n\r\n<p>본 과정을 통해 기업용 CMS 시스템을 자신 있게 구축하고, 모바일/웹용 API를 맞춤 설정하며, 실제 제품을 서버에 배포할 수 있습니다. 4. 어떤 버전의 Strapi를 사용하나요?</p>\r\n\r\n<p>본 과정에서는 최신 버전이자 최적화되고 보안이 강화된 Strapi v5를 사용합니다.</p>', '<p>Strapiは、React、Next.js、Nuxt.js、Vue、モバイルアプリなどのフロントエンドフレームワークと簡単に統合できる、柔軟でスケーラブルなコンテンツ管理システムを構築できる、強力なオープンソースのヘッドレスCMSです。</p>\r\n\r\n<p>このコースでは、以下の内容を学習します。</p>\r\n\r\n<p>- ヘッドレスCMSのアーキテクチャと、Strapiがなぜこれほど人気が​​あるのか​​を理解する。</p>\r\n\r\n<p>- StrapiのCRUD API、認証、ロールと権限を習得する。</p>\r\n\r\n<p>- コントローラー、サービス、ミドルウェア、プラグインを、特定のニーズに合わせてカスタマイズする。</p>\r\n\r\n<p>- Strapiを本番環境（Docker、VPS、クラウド）にデプロイする方法を学ぶ。</p>\r\n\r\n<p>このコースで得られるもの</p>\r\n\r\n<p>- Strapi v5の基礎から応用レベルまでの知識。</p>\r\n\r\n<p>- ウェブサイト、ブログ、アプリ用の独自のCMSシステムを構築するためのガイダンス。</p>\r\n\r\n<p>- フロントエンド（React/Next/Nuxt）を使用したAPIファースト開発の実践。</p>\r\n\r\n<p>- Strapi における OAuth2、JWT、API セキュリティを理解し、実装します。</p>\r\n\r\n<p>- eラーニング、eコマース、SaaS などの大規模プロジェクト向けに Strapi をカスタマイズできるようになります。</p>\r\n\r\n<p>- すぐに応用できる実践的なミニプロジェクト集です。</p>\r\n\r\n<p>よくある質問</p>\r\n\r\n<p>1. このコースは誰を対象としていますか？</p>\r\n\r\n<p>バックエンドを迅速に開発したいフロントエンド開発者、ヘッドレス CMS を試してみたいバックエンド開発者、プロジェクト開発期間を短縮したいフリーランサー。</p>\r\n\r\n<p>2. 受講前にバックエンド開発の知識は必要ですか？</p>\r\n\r\n<p>必須ではありません。このコースでは基礎から解説しますが、Node.js/REST API の知識があれば、より早く習得できます。</p>\r\n\r\n<p>3. コース修了後は何ができますか？</p>\r\n\r\n<p>企業向け CMS システムの構築、モバイル/Web 向け API のカスタマイズ、そして実際の製品をサーバーにデプロイできるようになります。4. 使用する Strapi のバージョンは？</p>\r\n\r\n<p>このコースでは、最新の最適化された、より安全なバージョンである Strapi v5 を使用します。</p>', '<p>Strapi 是一款功能强大的开源无头 CMS，它允许您构建灵活、可扩展的内容管理系统，并能轻松集成 React、Next.js、Nuxt.js、Vue 等前端框架或移动应用。</p>\r\n\r\n<p>在本课程中，您将：</p>\r\n\r\n<p>- 了解无头 CMS 架构以及 Strapi 如此受欢迎的原因。</p>\r\n\r\n<p>- 掌握 Strapi 中的 CRUD API、身份验证、角色和权限。<br />\r\n​​<br />\r\n- 自定义控制器、服务、中间件和插件，以满足您的特定需求。</p>\r\n\r\n<p>- 学习如何将 Strapi 部署到生产环境（Docker、VPS、云）。</p>\r\n\r\n<p>您将从本课程中获得什么？</p>\r\n\r\n<p>- 从基础到高级的 Strapi v5 知识。</p>\r\n\r\n<p>- 构建您自己的网站/博客/应用 CMS 系统的指导。</p>\r\n\r\n<p>- 使用前端（React/Next/Nuxt）进行 API 优先开发实践。</p>\r\n\r\n<p>- 理解并实现 Strapi 中的 OAuth2、JWT 和 API 安全机制。</p>\r\n\r\n<p>- 能够自定义 Strapi 以服务于大型项目：例如在线学习、电子商务和 SaaS。</p>\r\n\r\n<p>- 一系列可供立即应用的实用小项目。</p>\r\n\r\n<p>常见问题解答</p>\r\n\r\n<p>1. 本课程适合哪些人？</p>\r\n\r\n<p>希望快速开发后端的前端开发人员、希望尝试无头 CMS 的后端开发人员，以及希望缩短项目开发时间的自由职业者。</p>\r\n\r\n<p>2. 我需要事先了解后端开发吗？</p>\r\n\r\n<p>不需要。本课程将从零开始指导您，但具备 Node.js/REST API 知识将有助于您更快地学习。</p>\r\n\r\n<p>3. 完成课程后我能做什么？</p>\r\n\r\n<p>您可以自信地为企业构建 CMS 系统，为移动/Web 定制 API，并将实际产品部署到服务器。4. 我将使用哪个版本的 Strapi？</p>\r\n\r\n<p>本课程使用 Strapi v5&mdash;&mdash;最新、优化且更安全的版本。</p>', 6, '/storage/photos/2/e4c5917d-2521-44b9-bb68-a241b5f58cf7.jpg', 2395000.00, NULL, 795000.00, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'KH727568', 901.00, 0, '<p>Học tr&ecirc;n mọi thiết bị</p>\r\n\r\n<p>Code mẫu, t&agrave;i liệu đầy đủ</p>\r\n\r\n<p>Hỗ trợ 1-1 bởi giảng vi&ecirc;n, nh&oacute;m k&iacute;n</p>\r\n\r\n<p>Giới thiệu c&ocirc;ng việc ph&ugrave; hợp</p>\r\n\r\n<p>Thời hạn: Vĩnh viễn</p>', '<p>Learn on any device</p>\r\n\r\n<p>Sample code, complete documentation</p>\r\n\r\n<p>One-on-one support by instructors, private group</p>\r\n\r\n<p>Job placement assistance</p>\r\n\r\n<p>Duration: Lifetime</p>', '<p>모든 기기에서 학습 가능</p>\r\n\r\n<p>すべてのデバイスで学習可能</p>\r\n\r\n<p>예제 코드 및 완전한 학습 자료 제공</p>\r\n\r\n<p>강사의 1:1 지원 및 전용 커뮤니티</p>\r\n\r\n<p>역량에 맞는 취업 기회 제공</p>\r\n\r\n<p>평생 이용 가능</p>', '<p>すべてのデバイスで学習可能</p>\r\n\r\n<p>サンプルコードと完全な資料付き</p>\r\n\r\n<p>講師による1対1サポートと専用コミュニティ</p>\r\n\r\n<p>スキルに合った就職機会の紹介</p>\r\n\r\n<p>無期限アクセス</p>', '<p>支持在所有设备上学习</p>\r\n\r\n<p>提供示例代码和完整学习资料</p>\r\n\r\n<p>讲师一对一指导及专属学习社群</p>\r\n\r\n<p>推荐适合的工作机会</p>\r\n\r\n<p>永久访问</p>', 1, 'all_lessons', 0, NULL, NULL, NULL, NULL, 0, 0, 2, '2026-02-03 09:03:55', '2026-03-29 15:06:22', NULL),
(21, 'Xây dựng ứng dụng kết hợp Laravel - ReactJS - NextJS', 'Building an application combining Laravel - ReactJS - NextJS', 'Laravel - ReactJS - NextJS를 활용한 애플리케이션 개발', 'Laravel・ReactJS・NextJSを組み合わせたアプリケーション開発', '使用 Laravel - ReactJS - NextJS 构建应用程序', 'xay-dung-ung-dung-ket-hop-laravel-reactjs-nextjs', 'building-an-application-combining-laravel-reactjs-nextjs', 'laravel-reactjs-nextjs를-활용한-애플리케이션-개발', 'laravelreactjsnextjsを組み合わせたアプリケーション開発', '使用-laravel-reactjs-nextjs-构建应用程序', '<p>Kh&oacute;a học n&agrave;y tổng hợp c&aacute;c b&agrave;i giảng miễn ph&iacute; hướng dẫn x&acirc;y dựng ứng dụng web sử dụng NextJS v&agrave; Laravel nhằm mục đ&iacute;ch cho c&aacute;c bạn học vi&ecirc;n nắm c&aacute;ch x&acirc;y dựng ứng dụng thực tế với 2 framework n&agrave;y</p>', '<p>This course compiles free lectures guiding students on building web applications using NextJS and Laravel, aiming to help them understand how to build practical applications with these two frameworks.</p>', '<p>이 강좌는 NextJS와 Laravel을 사용하여 웹 애플리케이션을 구축하는 방법을 안내하는 무료 강의들을 모아놓은 것으로, 학생들이 이 두 프레임워크로 실용적인 애플리케이션을 만드는 방법을 이해하는 데 도움을 주는 것을 목표로 합니다.</p>', '<p>このコースは、NextJS と Laravel を使用して Web アプリケーションを構築する方法について学生に指導する無料の講義をまとめたもので、これら 2 つのフレームワークを使用して実用的なアプリケーションを構築する方法を学生が理解できるようにすることを目的としています。</p>', '<p>本课程汇集了免费讲座，指导学生使用 NextJS 和 Laravel 构建 Web 应用程序，旨在帮助他们了解如何使用这两个框架构建实际应用程序。</p>', 6, '/storage/photos/2/5e568a5c-16c9-4795-b827-90533602ea5d.jpg', 0.00, NULL, 0.00, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'KH309452', 1157.00, 0, '<p>Học tr&ecirc;n mọi thiết bị</p>\r\n\r\n<p>Kh&ocirc;ng giới hạn thời gian</p>', '<p>Learn on any device</p>\r\n\r\n<p>Sample code, complete documentation</p>\r\n\r\n<p>One-on-one support by instructors, private group</p>\r\n\r\n<p>Job placement assistance</p>\r\n\r\n<p>Duration: Lifetime</p>', '<p>모든 기기에서 학습 가능</p>\r\n\r\n<p>すべてのデバイスで学習可能</p>\r\n\r\n<p>예제 코드 및 완전한 학습 자료 제공</p>\r\n\r\n<p>강사의 1:1 지원 및 전용 커뮤니티</p>\r\n\r\n<p>역량에 맞는 취업 기회 제공</p>\r\n\r\n<p>평생 이용 가능</p>', '<p>すべてのデバイスで学習可能</p>\r\n\r\n<p>サンプルコードと完全な資料付き</p>\r\n\r\n<p>講師による1対1サポートと専用コミュニティ</p>\r\n\r\n<p>スキルに合った就職機会の紹介</p>\r\n\r\n<p>無期限アクセス</p>', '<p>支持在所有设备上学习</p>\r\n\r\n<p>提供示例代码和完整学习资料</p>\r\n\r\n<p>讲师一对一指导及专属学习社群</p>\r\n\r\n<p>推荐适合的工作机会</p>\r\n\r\n<p>永久访问</p>', 1, 'all_lessons', 0, NULL, NULL, NULL, NULL, 0, 0, 3, '2026-02-03 09:05:02', '2026-03-29 15:05:58', NULL),
(22, 'RESTful API với NestJS & TypeORM', 'RESTful API with NestJS & TypeORM', 'NestJS & TypeORM을 활용한 RESTful API 개발', 'NestJS & TypeORMによるRESTful API開発', '使用 NestJS 与 TypeORM 开发 RESTful API', 'restful-api-voi-nestjs-typeorm', 'restful-api-with-nestjs-typeorm', 'nestjs-typeorm을-활용한-restful-api-개발', 'nestjs-typeormによるrestful-api開発', '使用-nestjs-与-typeorm-开发-restful-api', '<p>Kh&oacute;a học NestJS miễn ph&iacute; được thiết kế d&agrave;nh cho những người mới bắt đầu hoặc đ&atilde; c&oacute; kiến thức cơ bản về lập tr&igrave;nh web, mong muốn x&acirc;y dựng c&aacute;c dự &aacute;n back-end hiện đại, hiệu quả v&agrave; dễ bảo tr&igrave;. Với NestJS - một framework mạnh mẽ dựa tr&ecirc;n Node.js, kh&oacute;a học sẽ hướng dẫn bạn từ những bước đầu ti&ecirc;n đến việc triển khai ứng dụng thực tế.</p>', '<p>This free NestJS course is designed for beginners or those with basic web programming knowledge who want to build modern, efficient, and easy-to-maintain back-end projects. Using NestJS &ndash; a powerful framework based on Node.js &ndash; the course will guide you from the very beginning to deploying a real-world application.</p>', '<p>이 무료 NestJS 강좌는 웹 프로그래밍 기초 지식을 갖춘 초보자를 위해 설계되었으며, 현대적이고 효율적이며 유지보수가 쉬운 백엔드 프로젝트를 구축하는 방법을 알려줍니다. Node.js 기반의 강력한 프레임워크인 NestJS를 사용하여 기초부터 실제 애플리케이션 배포까지 단계별로 안내합니다.</p>', '<p>この無料のNestJSコースは、初心者の方、または基本的なWebプログラミングの知識があり、最新かつ効率的でメンテナンスしやすいバックエンドプロジェクトを構築したい方を対象としています。Node.jsベースの強力なフレームワークであるNestJSを使用し、基礎から実際のアプリケーションのデプロイまでを網羅的に学習します。</p>', '<p>这门免费的 NestJS 课程专为初学者或具备基本 Web 编程知识，并希望构建现代化、高效且易于维护的后端项目的人员而设计。课程将使用基于 Node.js 的强大框架 NestJS，从零开始指导您最​​终部署一个实际应用程序。</p>', 6, '/storage/photos/2/89414cfb-e36c-42ad-a6bc-702b0ffbe38b.jpg', 0.00, NULL, 0.00, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'KH385152', 584.00, 0, '<p>Học tr&ecirc;n mọi thiết bị</p>\r\n\r\n<p>Kh&ocirc;ng giới hạn thời gian</p>', '<p>Learn on any device</p>\r\n\r\n<p>Sample code, complete documentation</p>\r\n\r\n<p>One-on-one support by instructors, private group</p>\r\n\r\n<p>Job placement assistance</p>\r\n\r\n<p>Duration: Lifetime</p>', '<p>모든 기기에서 학습 가능</p>\r\n\r\n<p>すべてのデバイスで学習可能</p>\r\n\r\n<p>예제 코드 및 완전한 학습 자료 제공</p>\r\n\r\n<p>강사의 1:1 지원 및 전용 커뮤니티</p>\r\n\r\n<p>역량에 맞는 취업 기회 제공</p>\r\n\r\n<p>평생 이용 가능</p>', '<p>すべてのデバイスで学習可能</p>\r\n\r\n<p>サンプルコードと完全な資料付き</p>\r\n\r\n<p>講師による1対1サポートと専用コミュニティ</p>\r\n\r\n<p>スキルに合った就職機会の紹介</p>\r\n\r\n<p>無期限アクセス</p>', '<p>支持在所有设备上学习</p>\r\n\r\n<p>提供示例代码和完整学习资料</p>\r\n\r\n<p>讲师一对一指导及专属学习社群</p>\r\n\r\n<p>推荐适合的工作机会</p>\r\n\r\n<p>永久访问</p>', 1, 'all_lessons', 0, NULL, NULL, NULL, NULL, 0, 0, 1, '2026-02-03 09:05:58', '2026-03-29 15:05:41', NULL),
(23, 'Lập trình App Mobile với React Native + TypeScript', 'Mobile App Development with React Native + TypeScript', 'React Native + TypeScript로 모바일 앱 개발', 'React Native + TypeScriptでモバイルアプリ開発', '使用 React Native + TypeScript 开发移动应用', 'lap-trinh-app-mobile-voi-react-native-typescript', 'mobile-app-development-with-react-native-typescript', 'react-native-typescript로-모바일-앱-개발', 'react-native-typescriptでモバイルアプリ開発', '使用-react-native-typescript-开发移动应用', '<p><strong>Bạn muốn x&acirc;y dựng ứng dụng di động chuy&ecirc;n nghiệp chạy tr&ecirc;n cả iOS v&agrave; Android m&agrave; kh&ocirc;ng c&oacute; kiến thức lập tr&igrave;nh Native?</strong>&nbsp;Kh&oacute;a học&nbsp;<em>L&agrave;m chủ React Native từ cơ bản đến chuy&ecirc;n s&acirc;u</em>&nbsp;l&agrave; lựa chọn ho&agrave;n hảo cho người mới bắt đầu! Với lộ tr&igrave;nh chi tiết, thực tế v&agrave; tập trung v&agrave;o thực h&agrave;nh, bạn sẽ đi từ con số 0 đến việc tự tin ph&aacute;t triển ứng dụng di động ho&agrave;n chỉnh.</p>\r\n\r\n<p dir=\"ltr\"><strong>Bạn sẽ học được g&igrave;?</strong></p>\r\n\r\n<p dir=\"ltr\"><strong>- Nền tảng lập tr&igrave;nh React:&nbsp;</strong>Được &ocirc;n tập lại về nền tảng ReactJS để chuẩn bị h&agrave;nh trang cho lập tr&igrave;nh React Native</p>\r\n\r\n<p dir=\"ltr\"><strong>- X&acirc;y dựng giao diện</strong>: Sử dụng c&aacute;c th&agrave;nh phần cốt l&otilde;i của React Native để thiết kế UI responsive, đẹp mắt.</p>\r\n\r\n<p dir=\"ltr\"><strong>- Điều hướng v&agrave; quản l&yacute; trạng th&aacute;i</strong>: Tạo ứng dụng đa m&agrave;n h&igrave;nh với navigation mượt m&agrave;, quản l&yacute; dữ liệu hiệu quả bằng hooks v&agrave; Redux.</p>\r\n\r\n<p dir=\"ltr\"><strong>- T&iacute;ch hợp API v&agrave; t&iacute;nh năng thiết bị</strong>: Kết nối backend, sử dụng camera, định vị GPS, push notifications để tạo app thực tế.</p>\r\n\r\n<p dir=\"ltr\"><strong>- Tối ưu h&oacute;a v&agrave; triển khai</strong>: Học c&aacute;ch tối ưu hiệu suất, debug, test v&agrave; đưa ứng dụng l&ecirc;n Google Play/App Store.</p>\r\n\r\n<p dir=\"ltr\"><strong>- Dự &aacute;n thực tế</strong>: X&acirc;y dựng ứng dụng ho&agrave;n chỉnh (như app e-commerce hoặc mạng x&atilde; hội) để l&agrave;m portfolio.</p>\r\n\r\n<p dir=\"ltr\"><strong>Đối tượng ph&ugrave; hợp</strong></p>\r\n\r\n<p dir=\"ltr\">- Người mới bắt đầu tiếp cận với lập tr&igrave;nh Mobile</p>\r\n\r\n<p dir=\"ltr\">- Sinh vi&ecirc;n, người chuyển nghề hoặc lập tr&igrave;nh vi&ecirc;n muốn học ph&aacute;t triển ứng dụng cross-platform.</p>\r\n\r\n<p dir=\"ltr\">- Những ai muốn x&acirc;y dựng ứng dụng mobile để l&agrave;m việc freelance hoặc startup.</p>\r\n\r\n<p dir=\"ltr\"><strong>Tại sao chọn kh&oacute;a học n&agrave;y?</strong></p>\r\n\r\n<p dir=\"ltr\"><strong>- Học từ con số 0</strong>: Kh&ocirc;ng cần nền tảng, lộ tr&igrave;nh được thiết kế dễ hiểu, từng bước.</p>\r\n\r\n<p dir=\"ltr\"><strong>- Thực h&agrave;nh thực tế</strong>: Code trực tiếp, test tr&ecirc;n thiết bị thật, dự &aacute;n s&aacute;t với nhu cầu doanh nghiệp.</p>\r\n\r\n<p dir=\"ltr\"><strong>- Hỗ trợ Elearning</strong>: Video b&agrave;i giảng r&otilde; r&agrave;ng, t&agrave;i liệu chi tiết, b&agrave;i tập v&agrave; code mẫu.</p>\r\n\r\n<p dir=\"ltr\"><strong>- Hỗ trợ trực tiếp từ giảng</strong>: Giảng vi&ecirc;n hỗ trợ trực tiếp để giải đ&aacute;p c&aacute;c thắc mắc</p>\r\n\r\n<p dir=\"ltr\"><strong>Y&ecirc;u cầu</strong></p>\r\n\r\n<p dir=\"ltr\">- M&aacute;y t&iacute;nh (Windows/Mac/Linux) với Node.js v&agrave; VS Code.</p>\r\n\r\n<p dir=\"ltr\">- Điện thoại Android/iOS để test (khuyến kh&iacute;ch, kh&ocirc;ng bắt buộc).</p>\r\n\r\n<p dir=\"ltr\">- Đam m&ecirc; học hỏi v&agrave; sẵn s&agrave;ng d&agrave;nh 8-10 giờ/tuần để học v&agrave; thực h&agrave;nh.</p>\r\n\r\n<p dir=\"ltr\"><strong>Kết quả sau kh&oacute;a học</strong></p>\r\n\r\n<p dir=\"ltr\">- X&acirc;y dựng được ứng dụng mobile ho&agrave;n chỉnh, sẵn s&agrave;ng publish l&ecirc;n store.</p>\r\n\r\n<p dir=\"ltr\">- Nắm vững React Native, sẵn s&agrave;ng l&agrave;m việc thực tế hoặc ph&aacute;t triển dự &aacute;n c&aacute; nh&acirc;n.</p>\r\n\r\n<p dir=\"ltr\">- Sở hữu portfolio với dự &aacute;n thực tế để ứng tuyển hoặc l&agrave;m freelance.</p>', '<p>Want to build a professional mobile app that runs on both iOS and Android without any native programming knowledge? The &quot;Mastering React Native from Basic to Advanced&quot; course is the perfect choice for beginners! With a detailed, practical, and hands-on learning path, you&#39;ll go from zero to confidently developing complete mobile applications.</p>\r\n\r\n<p>What will you learn?</p>\r\n\r\n<p>- React programming foundation: Review ReactJS to prepare for React Native programming.</p>\r\n\r\n<p>- UI development: Use core React Native components to design responsive and visually appealing UI.</p>\r\n\r\n<p>- Navigation and state management: Create multi-screen applications with smooth navigation and efficient data management using hooks and Redux.</p>\r\n\r\n<p>- API and device integration: Connect the backend, use the camera, GPS location, and push notifications to create a practical app.</p>\r\n\r\n<p>- Optimization and Deployment: Learn how to optimize performance, debug, test, and publish your application to Google Play/App Store.</p>\r\n\r\n<p>- Real-world Project: Build a complete application (such as an e-commerce or social media app) to create a portfolio.</p>\r\n\r\n<p>Suitable for:</p>\r\n\r\n<p>- Beginners in mobile programming</p>\r\n\r\n<p>- Students, career changers, or programmers who want to learn cross-platform application development.</p>\r\n\r\n<p>- Those who want to build mobile applications for freelance work or startups.</p>\r\n\r\n<p>Why choose this course?</p>\r\n\r\n<p>- Learn from scratch: No prior knowledge required, easy-to-understand, step-by-step learning path.</p>\r\n\r\n<p>- Hands-on practice: Code directly, test on real devices, projects closely aligned with business needs.</p>\r\n\r\n<p>- Elearning support: Clear video lectures, detailed documentation, exercises, and sample code.</p>\r\n\r\n<p>- Direct support from instructors: Instructors provide direct support to answer questions.</p>\r\n\r\n<p>Requirements:</p>\r\n\r\n<p>- Computer (Windows/Mac/Linux) with Node.js and VS Code.</p>\r\n\r\n<p>- Android/iOS phone for testing (recommended, not required).</p>\r\n\r\n<p>- Passion for learning and willingness to dedicate 8-10 hours per week to learning and practice.</p>\r\n\r\n<p>Course Outcomes:</p>\r\n\r\n<p>- Build a complete mobile application, ready to publish to app stores.</p>\r\n\r\n<p>- Master React Native, ready for real-world work or personal project development.</p>\r\n\r\n<p>- Own a portfolio with real-world projects for job applications or freelance work.</p>', '<p>네이티브 프로그래밍 지식 없이 iOS와 Android 모두에서 작동하는 전문적인 모바일 앱을 만들고 싶으신가요? &quot;React Native 기초부터 고급까지 마스터하기&quot; 강좌는 초보자에게 완벽한 선택입니다! 상세하고 실용적인 실습 위주의 학습 과정을 통해 기초부터 시작하여 자신 있게 완벽한 모바일 앱을 개발할 수 있게 됩니다.</p>\r\n\r\n<p>무엇을 배우게 될까요?</p>\r\n\r\n<p>- React 프로그래밍 기초: ReactJS를 복습하여 React Native 프로그래밍을 준비합니다.</p>\r\n\r\n<p>- UI 개발: 핵심 React Native 컴포넌트를 사용하여 반응형의 시각적으로 매력적인 UI를 디자인합니다.</p>\r\n\r\n<p>- 내비게이션 및 상태 관리: Hooks와 Redux를 사용하여 부드러운 내비게이션과 효율적인 데이터 관리를 갖춘 멀티스크린 애플리케이션을 만듭니다.</p>\r\n\r\n<p>- API 및 기기 통합: 백엔드 연결, 카메라, GPS 위치 정보, 푸시 알림 등을 활용하여 실용적인 앱을 개발합니다.</p>\r\n\r\n<p>- 최적화 및 배포: 성능 최적화, 디버깅, 테스트, Google Play/App Store 배포 방법을 배웁니다.</p>\r\n\r\n<p>- 실제 프로젝트: 전자상거래 앱이나 소셜 미디어 앱과 같은 완벽한 애플리케이션을 개발하여 포트폴리오를 만듭니다.</p>\r\n\r\n<p>대상:</p>\r\n\r\n<p>- 모바일 프로그래밍 초보자</p>\r\n\r\n<p>- 크로스 플랫폼 애플리케이션 개발을 배우고 싶은 학생, 경력 전환자 또는 프로그래머</p>\r\n\r\n<p>- 프리랜서 또는 스타트업을 위해 모바일 애플리케이션을 개발하려는 사람</p>\r\n\r\n<p>이 강좌를 선택해야 하는 이유:</p>\r\n\r\n<p>- 기초부터 학습: 사전 지식이 없어도 수강 가능하며, 이해하기 쉬운 단계별 학습 과정을 제공합니다.</p>\r\n\r\n<p>- 실습 중심: 직접 코드를 작성하고 실제 기기에서 테스트하며, 비즈니스 요구 사항과 밀접하게 연관된 프로젝트를 진행합니다.</p>\r\n\r\n<p>- 풍부한 온라인 학습 자료: 명확한 비디오 강의, 자세한 문서, 연습 문제 및 샘플 코드를 제공합니다.</p>\r\n\r\n<p>- 강사와의 직접적인 소통: 강사가 질문에 직접 답변하며 학습을 지원합니다.</p>\r\n\r\n<p>필요 조건:</p>\r\n\r\n<p>- Node.js 및 VS Code가 설치된 컴퓨터 (Windows/Mac/Linux)</p>\r\n\r\n<p>- 테스트용 Android/iOS 스마트폰 (권장, 필수 아님)</p>\r\n\r\n<p>- 학습에 대한 열정과 주당 8~10시간을 투자할 의지</p>\r\n\r\n<p>학습 목표:</p>\r\n\r\n<p>- 앱 스토어에 출시할 수 있는 완벽한 모바일 애플리케이션을 개발합니다.</p>\r\n\r\n<p>- React Native를 숙달하여 실무 또는 개인 프로젝트 개발에 활용할 수 있습니다.</p>\r\n\r\n<p>- 취업 지원 또는 프리랜서 활동에 활용할 수 있는 실무 프로젝트 포트폴리오를 구축합니다.</p>', '<p>ネイティブプログラミングの知識がなくても、iOSとAndroidの両方で動作するプロフェッショナルなモバイルアプリを開発したいですか？「React Nativeを基礎から上級までマスター」コースは、初心者の方に最適です。詳細で実践的なハンズオン形式の学習パスで、ゼロから本格的なモバイルアプリを自信を持って開発できるようになります。</p>\r\n\r\n<p>学習内容</p>\r\n\r\n<p>- Reactプログラミングの基礎：ReactJSを復習し、React Nativeプログラミングの準備をします。</p>\r\n\r\n<p>- UI開発：React Nativeのコアコンポーネントを使用して、レスポンシブで視覚的に魅力的なUIを設計します。</p>\r\n\r\n<p>- ナビゲーションと状態管理：HooksとReduxを使用して、スムーズなナビゲーションと効率的なデータ管理を備えたマルチスクリーンアプリケーションを作成します。</p>\r\n\r\n<p>- APIとデバイス統合：バックエンドを接続し、カメラ、GPS位置情報、プッシュ通知を使用して、実用的なアプリを作成します。</p>\r\n\r\n<p>- 最適化とデプロイ：パフォーマンスの最適化、デバッグ、テスト、そしてGoogle Play/App Storeへのアプリケーションの公開方法を学びます。</p>\r\n\r\n<p>- 実践プロジェクト：eコマースアプリやソーシャルメディアアプリなどの本格的なアプリケーションを構築し、ポートフォリオを作成します。</p>\r\n\r\n<p>対象者：</p>\r\n\r\n<p>- モバイルプログラミング初心者</p>\r\n\r\n<p>- クロスプラットフォームアプリケーション開発を学びたい学生、転職希望者、またはプログラマー</p>\r\n\r\n<p>- フリーランスやスタートアップ企業向けにモバイルアプリケーションを開発したい方</p>\r\n\r\n<p>このコースを選ぶ理由</p>\r\n\r\n<p>- ゼロから学ぶ：事前の知識は不要。分かりやすいステップバイステップの学習パスです。</p>\r\n\r\n<p>- 実践的な演習：直接コーディングし、実機でテストを行い、ビジネスニーズに密着したプロジェクトを構築します。</p>\r\n\r\n<p>- eラーニングサポート：分かりやすいビデオ講義、詳細なドキュメント、演習、サンプルコードを提供します。</p>\r\n\r\n<p>- 講師による直接サポート：講師が質問に直接回答します。</p>\r\n\r\n<p>要件：</p>\r\n\r\n<p>- Node.jsとVS Codeを搭載したコンピューター（Windows/Mac/Linux）。</p>\r\n\r\n<p>- テスト用のAndroid/iOSスマートフォン（推奨、必須ではありません）。</p>\r\n\r\n<p>- 学習への情熱と、学習と実践に週8～10時間費やす意欲。</p>\r\n\r\n<p>コースの成果：</p>\r\n\r\n<p>- アプリストアへの公開準備が整った、完全なモバイルアプリケーションを構築します。</p>\r\n\r\n<p>- React Native を習得し、実務や個人プロジェクトの開発に活用できるようになります。</p>\r\n\r\n<p>- 就職活動やフリーランスとして活躍できるよう、実務プロジェクトをまとめたポートフォリオを作成します。</p>', '<p>想在没有任何原生编程知识的情况下，构建一款同时支持 iOS 和 Android 的专业移动应用吗？&ldquo;从基础到精通 React Native&rdquo;课程是初学者的理想之选！通过详尽、实用且注重实践的学习路径，您将从零基础开始，最终自信地开发完整的移动应用。</p>\r\n\r\n<p>您将学到什么？</p>\r\n\r\n<p>- React 编程基础：回顾 ReactJS，为 React Native 编程做好准备。</p>\r\n\r\n<p>- UI 开发：使用 React Native 核心组件设计响应式且美观的 UI。</p>\r\n\r\n<p>- 导航和状态管理：使用 Hooks 和 Redux 创建具有流畅导航和高效数据管理的多屏应用。</p>\r\n\r\n<p>- API 和设备集成：连接后端，使用摄像头、GPS 定位和推送通知功能，创建一个实用的应用。</p>\r\n\r\n<p>- 优化和部署：学习如何优化性能、调试、测试以及将应用发布到 Google Play/App Store。</p>\r\n\r\n<p>- 真实项目：构建一个完整的应用（例如电商或社交媒体应用），打造您的作品集。</p>\r\n\r\n<p>适合人群：</p>\r\n\r\n<p>- 移动编程初学者</p>\r\n\r\n<p>- 学生、职业转型者或希望学习跨平台应用开发的程序员。</p>\r\n\r\n<p>- 希望为自由职业或初创公司开发移动应用的人士。</p>\r\n\r\n<p>为什么选择这门课程？</p>\r\n\r\n<p>- 从零开始学习：无需任何编程基础，易于理解，循序渐进的学习路径。</p>\r\n\r\n<p>- 实践操作：直接编写代码，在真实设备上进行测试，项目与业务需求紧密结合。</p>\r\n\r\n<p>- 在线学习支持：清晰的视频课程、详细的文档、练习和示例代码。</p>\r\n\r\n<p>- 讲师直接支持：讲师提供直接支持，解答疑问。</p>\r\n\r\n<p>要求：</p>\r\n\r\n<p>- 电脑（Windows/Mac/Linux），已安装 Node.js 和 VS Code。</p>\r\n\r\n<p>- 用于测试的 Android/iOS 手机（推荐，非必需）。</p>\r\n\r\n<p>- 热爱学习，并愿意每周投入 8-10 小时进行学习和练习。</p>\r\n\r\n<p>课程目标：</p>\r\n\r\n<p>- 构建一个完整的移动应用程序，并准备好发布到应用商店。</p>\r\n\r\n<p>- 精通 React Native，能够胜任实际工作或个人项目开发。</p>\r\n\r\n<p>- 拥有包含实际项目的作品集，可用于求职或自由职业项目。</p>', 6, '/storage/photos/2/dc9cfbad-580c-491c-85ed-935957ee36cb.jpg', 2950000.00, NULL, 895000.00, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'KH296839', 699.00, 0, '<p>Học tr&ecirc;n mọi thiết bị</p>\r\n\r\n<p>Code mẫu, t&agrave;i liệu đầy đủ</p>\r\n\r\n<p>Hỗ trợ 1-1 bởi giảng vi&ecirc;n, nh&oacute;m k&iacute;n</p>\r\n\r\n<p>Giới thiệu c&ocirc;ng việc ph&ugrave; hợp</p>\r\n\r\n<p>Thời hạn: Vĩnh viễn</p>', '<p>Learn on any device</p>\r\n\r\n<p>Sample code, complete documentation</p>\r\n\r\n<p>One-on-one support by instructors, private group</p>\r\n\r\n<p>Job placement assistance</p>\r\n\r\n<p>Duration: Lifetime</p>', '<p>모든 기기에서 학습 가능</p>\r\n\r\n<p>すべてのデバイスで学習可能</p>\r\n\r\n<p>예제 코드 및 완전한 학습 자료 제공</p>\r\n\r\n<p>강사의 1:1 지원 및 전용 커뮤니티</p>\r\n\r\n<p>역량에 맞는 취업 기회 제공</p>\r\n\r\n<p>평생 이용 가능</p>', '<p>すべてのデバイスで学習可能</p>\r\n\r\n<p>サンプルコードと完全な資料付き</p>\r\n\r\n<p>講師による1対1サポートと専用コミュニティ</p>\r\n\r\n<p>スキルに合った就職機会の紹介</p>\r\n\r\n<p>無期限アクセス</p>', '<p>支持在所有设备上学习</p>\r\n\r\n<p>提供示例代码和完整学习资料</p>\r\n\r\n<p>讲师一对一指导及专属学习社群</p>\r\n\r\n<p>推荐适合的工作机会</p>\r\n\r\n<p>永久访问</p>', 1, 'all_lessons', 0, NULL, NULL, NULL, NULL, 0, 0, 4, '2026-02-03 09:07:40', '2026-04-27 08:11:10', NULL),
(24, 'Deploy Web - Triển khai website lên môi trường Internet', 'Web Deployment - Deploying a website to the Internet', '웹 배포 - 웹사이트를 인터넷 환경에 배포하기', 'Webデプロイ - ウェブサイトをインターネット環境へ公開', 'Web 部署 - 将网站部署到互联网环境', 'deploy-web-trien-khai-website-len-moi-truong-internet', 'web-deployment-deploying-a-website-to-the-internet', '웹-배포-웹사이트를-인터넷-환경에-배포하기', 'webデプロイ-ウェブサイトをインターネット環境へ公開', 'web-部署-将网站部署到互联网环境', '<p>This course, taught by Hoang An Unicode, guides you through deploying your website to a server for online product distribution. In this course, you will learn about servers, VPS, hosting, domain names, and step-by-step web application deployment, from beginner to mastering deployment, understanding the underlying workings, and troubleshooting errors.</p>\r\n\r\n<p>What will you gain from this course?</p>\r\n\r\n<p>- Client-Server Model</p>\r\n\r\n<p>- What is a domain name? How to own a domain name?</p>\r\n\r\n<p>- Differentiating between Server, VPS, and Hosting</p>\r\n\r\n<p>- How to buy a Server/Hosting</p>\r\n\r\n<p>- Server components for a website to function</p>\r\n\r\n<p>- How to get free hosting</p>\r\n\r\n<p>- Pointing the main domain and subdomains to hosting via IP</p>\r\n\r\n<p>- Pointing the domain to hosting via NameServer</p>\r\n\r\n<p>- Installing SSL certificates on hosting</p>\r\n\r\n<p>- Working with databases on hosting</p>\r\n\r\n<p>- Working with files on hosting</p>\r\n\r\n<p>- Working with the terminal on hosting</p>\r\n\r\n<p>- Connecting the client to hosting via FTP</p>\r\n\r\n<p>- Ways to upload code to hosting</p>\r\n\r\n<p>- Configuring URL redirection via .htaccess file</p>\r\n\r\n<p>- Changing the Document Root on hosting</p>\r\n\r\n<p>- Configuring HTTPS redirects on hosting</p>\r\n\r\n<p>- Deploying to a VPS using self-hosting</p>\r\n\r\n<p>- Deploying to a VPS using a free Control Panel/Script</p>\r\n\r\n<p>- Deploying to a VPS using Docker</p>\r\n\r\n<p>- Other issues when using VPS</p>\r\n\r\n<p>- Detailed knowledge and experience from Hoang An Unicode shared in the course</p>\r\n\r\n<p>Special Gift Special Offer:</p>\r\n\r\n<p>- Free 5GB hosting from 123host</p>\r\n\r\n<p>- 50% discount code for VPS from 123host</p>', NULL, '<p>이 강의는 <strong>Hoang An Unicode</strong> 강사가 진행하며, 웹사이트를 서버에 배포하여 온라인에서 서비스하는 방법을 단계적으로 안내합니다.</p>\r\n\r\n<p>강의에서는 <strong>서버, VPS, 호스팅, 도메인</strong>의 개념을 배우고, 웹 애플리케이션을 실제로 배포하는 과정을 처음부터 차근차근 학습합니다. 또한 시스템이 내부적으로 어떻게 동작하는지 이해하고, 배포 과정에서 발생할 수 있는 오류를 해결하는 방법도 익히게 됩니다.</p>', '<p>このコースは <strong>Hoang An Unicode</strong> によって提供され、Webサイトをサーバーにデプロイしてオンラインで公開する方法を学びます。</p>\r\n\r\n<p>サーバー、VPS、ホスティング、ドメインの基礎から始まり、Webアプリケーションのデプロイ手順を段階的に学習します。また、システムの内部構造を理解し、デプロイ時に発生するトラブルを解決する方法も習得できます。</p>', '<p>本课程由 <strong>Hoang An Unicode</strong> 授课，带你一步一步学习如何将网站部署到服务器，并将产品在线发布。</p>\r\n\r\n<p>课程将帮助你了解 <strong>服务器、VPS、主机、域名</strong> 等基础知识，并学习完整的网站部署流程。从入门到掌握部署技术，同时理解系统底层的工作原理，并能够解决常见的部署问题。</p>', 6, '/storage/photos/2/6ffa75c2-35b8-4eb0-b4b0-ab9002549f55.jpg', 1595000.00, NULL, 795000.00, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'KH824906', 704.00, 0, '<p>Học tr&ecirc;n mọi thiết bị</p>\r\n\r\n<p>Code mẫu, t&agrave;i liệu đầy đủ</p>\r\n\r\n<p>Hỗ trợ 1-1 bởi giảng vi&ecirc;n, nh&oacute;m k&iacute;n</p>\r\n\r\n<p>Giới thiệu c&ocirc;ng việc ph&ugrave; hợp</p>\r\n\r\n<p>Thời hạn: Vĩnh viễn</p>', '<p>Learn on any device</p>\r\n\r\n<p>Sample code, complete documentation</p>\r\n\r\n<p>One-on-one support by instructors, private group</p>\r\n\r\n<p>Job placement assistance</p>\r\n\r\n<p>Duration: Lifetime</p>', '<p>모든 기기에서 학습 가능</p>\r\n\r\n<p>すべてのデバイスで学習可能</p>\r\n\r\n<p>예제 코드 및 완전한 학습 자료 제공</p>\r\n\r\n<p>강사의 1:1 지원 및 전용 커뮤니티</p>\r\n\r\n<p>역량에 맞는 취업 기회 제공</p>\r\n\r\n<p>평생 이용 가능</p>', '<p>すべてのデバイスで学習可能</p>\r\n\r\n<p>サンプルコードと完全な資料付き</p>\r\n\r\n<p>講師による1対1サポートと専用コミュニティ</p>\r\n\r\n<p>スキルに合った就職機会の紹介</p>\r\n\r\n<p>無期限アクセス</p>', '<p>支持在所有设备上学习</p>\r\n\r\n<p>提供示例代码和完整学习资料</p>\r\n\r\n<p>讲师一对一指导及专属学习社群</p>\r\n\r\n<p>推荐适合的工作机会</p>\r\n\r\n<p>永久访问</p>', 1, 'all_lessons', 0, NULL, NULL, NULL, NULL, 0, 0, 2, '2026-02-03 09:09:05', '2026-04-27 08:16:36', NULL);
INSERT INTO `courses` (`id`, `name`, `name_en`, `name_ko`, `name_ja`, `name_zh`, `slug`, `slug_en`, `slug_ko`, `slug_ja`, `slug_zh`, `detail`, `detail_en`, `detail_ko`, `detail_ja`, `detail_zh`, `teacher_id`, `thumbnail`, `price`, `quantity`, `sale_price`, `price_en`, `sale_price_en`, `price_ko`, `sale_price_ko`, `price_ja`, `sale_price_ja`, `price_zh`, `sale_price_zh`, `code`, `durations`, `is_document`, `supports`, `supports_en`, `supports_ko`, `supports_ja`, `supports_zh`, `status`, `completion_condition`, `is_coming_soon`, `coming_soon_start_at`, `end_at`, `package_locked_at`, `package_lock_reason`, `is_package_priority`, `is_learning_locked`, `view`, `created_at`, `updated_at`, `deleted_at`) VALUES
(25, 'Khóa học Git & Git Flow thực chiến', 'Practical Git & Git Flow Course', '실전 Git & Git Flow 강의', '実践 Git & Git Flow コース', 'Git 与 Git Flow 实战课程', 'khoa-hoc-git-git-flow-thuc-chien', 'practical-git-git-flow-course', '실전-git-git-flow-강의', '実践-git-git-flow-コース', 'git-与-git-flow-实战课程', '<p><strong>Git is more than just add, commit, and push commands. In a professional team environment and with DevOps methodologies, Git is the foundation for risk management and code quality.</strong></p>\r\n\r\n<p><strong>This course is designed to elevate your Git skills from beginner to workflow master level. You will not only learn how to use commands but also how to apply standard branching strategies (Git Flow), resolve complex issues (handling conflicts, safely fixing history errors), and automatically integrate code with project management systems (Issues).</strong></p>\r\n\r\n<p><strong>After the course, you will be confident in participating in any large project, ensuring a clean and transparent code history, and ready to handle any urgent bug fixes without jeopardizing production code.</strong></p>\r\n\r\n<p><strong>What Will You Gain From This Course?</strong></p>\r\n\r\n<p><strong>This course equips you with the necessary tools and mindset to work in a high-quality environment:</strong></p>\r\n\r\n<p><strong>Mastering Git Flow Strategy:</strong></p>\r\n\r\n<p><strong>- Understand the roles of the main, develop, feature, release, and hotfix branches in a complete product development cycle.</strong></p>\r\n\r\n<p><strong>- Master the Pull Request (PR) process, establish branch protection rules, and perform effective code reviews.</strong></p>\r\n\r\n<p><strong>Resolving Code History Issues:</strong></p>\r\n\r\n<p><strong>- Practice safe &quot;time reversal&quot; techniques: `git revert` (safe undo) and `git reset --hard` (remove local changes).</strong></p>\r\n\r\n<p><strong>- Use `git commit --amend` to fix minor bugs in the last commit without creating a new one.</strong></p>\r\n\r\n<p><strong>Managing Changes Accurately:</strong></p>\r\n\r\n<p><strong>- Use `git stash` to store unfinished work and handle urgent bugs.</strong></p>\r\n\r\n<p><strong>- Apply `git cherry-pick` to selectively move a single bug fix commit from one branch to another without merging the entire branch.</strong></p>\r\n\r\n<p><strong>- Practice `git rebase` to rewrite local commit history linearly, keeping the PR history clean and easy to read.</strong></p>\r\n\r\n<p><strong>Integrating Management Tools:</strong></p>\r\n\r\n<p><strong>- Practice using Issues on GitHub/GitLab and writing correctly formatted commit messages to automatically link and close Issues when code is merged.</strong></p>\r\n\r\n<p><strong>Frequently Asked Questions (FAQ)</strong></p>\r\n\r\n<p><strong>Is this course right for me?</strong></p>\r\n\r\n<p><strong>Absolutely right if: You are a beginner and want to learn Git from scratch, including installation, basic commands, and want to immediately learn professional team workflows (Git Flow). The course will take you from zero to running large projects.</strong></p>\r\n\r\n<p><strong>Suitable if: You are an Intermediate/Senior Developer who already knows basic commands but wants to improve your skills to:</strong></p>\r\n\r\n<p><strong>- Master advanced commands such as rebase, reverse, and cherry-pick.</strong></p>\r\n\r\n<p><strong>- Standardize and manage critical processes such as Release and Hotfix (Git Flow).</strong></p>\r\n\r\n<p><strong>- Apply branch protection rules and manage Pull Requests (PR) effectively.</strong></p>\r\n\r\n<p><strong>What do I need to prepare before the course?</strong></p>\r\n\r\n<p><strong>- Previous programming experience.</strong></p>\r\n\r\n<p><strong>- Basic knowledge of Terminal/Command Line.</strong></p>\r\n\r\n<p><strong>Does the course include practical exercises?</strong></p>\r\n\r\n<p><strong>Yes. The course focuses on real-world scenarios such as handling emergency Hotfixes, cleaning history with Rebase, and merging Releases, helping you apply your knowledge to your work immediately.</strong></p>\r\n\r\n<p><strong>Can I interact and ask questions?</strong></p>\r\n\r\n<p><strong>Yes. You can ask questions directly on the platform or through the following communication channels: Zalo, Telegram.</strong></p>', NULL, '<p>Git은 단순히 <code>add</code>, <code>commit</code>, <code>push</code> 명령어만 사용하는 도구가 아닙니다.<br />\r\n전문적인 팀 환경과 DevOps 방법론에서는 Git이 <strong>리스크 관리와 코드 품질 관리의 핵심 기반</strong>입니다.</p>\r\n\r\n<p>이 강의는 Git 실력을 <strong>초급에서 워크플로우 마스터 수준까지</strong> 끌어올리기 위해 설계되었습니다.<br />\r\n단순히 명령어 사용법을 배우는 것이 아니라 <strong>Git Flow 브랜치 전략</strong>, 충돌 해결, 히스토리 오류 수정, 그리고 <strong>Issues 기반 프로젝트 관리와 코드 통합</strong>까지 배우게 됩니다.</p>\r\n\r\n<p>강의를 마치면 대규모 프로젝트에 자신 있게 참여하고, <strong>깨끗하고 투명한 코드 히스토리</strong>를 유지하며, 프로덕션 코드에 영향을 주지 않고 긴급 버그를 처리할 수 있습니다.</p>', '<p>Gitは単なる <code>add</code>、<code>commit</code>、<code>push</code> コマンドのツールではありません。<br />\r\nプロフェッショナルなチーム環境や DevOps の開発手法において、Gitは <strong>リスク管理とコード品質管理の基盤</strong>となります。</p>\r\n\r\n<p>このコースは、Gitのスキルを <strong>初心者レベルからワークフローマスターレベルまで</strong>引き上げるために設計されています。<br />\r\n単にコマンドを学ぶだけでなく、<strong>Git Flowブランチ戦略、コンフリクト解決、履歴修正、Issuesとの連携</strong>などを実践的に学びます。</p>\r\n\r\n<p>コース修了後には、大規模プロジェクトに自信を持って参加し、<strong>クリーンで透明性のあるコミット履歴</strong>を維持しながら、緊急のバグ修正にも対応できるようになります。</p>', '<p>Git 不仅仅是 <code>add</code>、<code>commit</code> 和 <code>push</code> 这些简单命令。<br />\r\n在专业团队环境和 DevOps 开发模式中，Git 是 <strong>风险管理和代码质量控制的核心基础</strong>。</p>\r\n\r\n<p>本课程旨在将你的 Git 技能从 <strong>初学者提升到工作流专家级别</strong>。<br />\r\n你不仅会学习 Git 命令的使用，还将掌握 <strong>Git Flow 分支策略、冲突处理、历史记录修复以及与 Issues 项目管理系统的集成</strong>。</p>\r\n\r\n<p>完成课程后，你将能够自信地参与大型项目开发，保持 <strong>清晰、透明的代码历史记录</strong>，并能够在不影响生产环境的情况下处理紧急 Bug。</p>', 6, '/storage/photos/2/e0cfc6cd-b8fe-49f3-ab0a-718f06d7c0d2.jpg', 1895000.00, NULL, 595000.00, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'KH921145', 815.00, 0, '<p>Học tr&ecirc;n mọi thiết bị</p>\r\n\r\n<p>Code mẫu, t&agrave;i liệu đầy đủ</p>\r\n\r\n<p>Hỗ trợ 1-1 bởi giảng vi&ecirc;n, nh&oacute;m k&iacute;n</p>\r\n\r\n<p>Giới thiệu c&ocirc;ng việc ph&ugrave; hợp</p>\r\n\r\n<p>Thời hạn: Vĩnh viễn</p>', '<p>Learn on any device</p>\r\n\r\n<p>Sample code, complete documentation</p>\r\n\r\n<p>One-on-one support by instructors, private group</p>\r\n\r\n<p>Job placement assistance</p>\r\n\r\n<p>Duration: Lifetime</p>', '<p>모든 기기에서 학습 가능</p>\r\n\r\n<p>すべてのデバイスで学習可能</p>\r\n\r\n<p>예제 코드 및 완전한 학습 자료 제공</p>\r\n\r\n<p>강사의 1:1 지원 및 전용 커뮤니티</p>\r\n\r\n<p>역량에 맞는 취업 기회 제공</p>\r\n\r\n<p>평생 이용 가능</p>', '<p>すべてのデバイスで学習可能</p>\r\n\r\n<p>サンプルコードと完全な資料付き</p>\r\n\r\n<p>講師による1対1サポートと専用コミュニティ</p>\r\n\r\n<p>スキルに合った就職機会の紹介</p>\r\n\r\n<p>無期限アクセス</p>', '<p>支持在所有设备上学习</p>\r\n\r\n<p>提供示例代码和完整学习资料</p>\r\n\r\n<p>讲师一对一指导及专属学习社群</p>\r\n\r\n<p>推荐适合的工作机会</p>\r\n\r\n<p>永久访问</p>', 1, 'all_lessons', 0, NULL, NULL, NULL, NULL, 0, 0, 2, '2026-02-03 09:10:05', '2026-03-29 15:03:56', NULL),
(26, 'Làm chủ Docker từ cơ bản đến nâng cao', 'Master Docker from basic to advanced.', '기초부터 고급까지 Docker 마스터하기', '基礎から上級までDockerをマスターする', '从基础到高级掌握 Docker', 'lam-chu-docker-tu-co-ban-den-nang-cao', 'master-docker-from-basic-to-advanced', '기초부터-고급까지-docker-마스터하기', '基礎から上級までdockerをマスターする', '从基础到高级掌握-docker', '<p><strong>Bạn l&agrave; lập tr&igrave;nh vi&ecirc;n, DevOps, hay sinh vi&ecirc;n CNTT đang muốn hiểu v&agrave; sử dụng Docker một c&aacute;ch b&agrave;i bản?</strong>&nbsp;Đ&acirc;y ch&iacute;nh l&agrave; kh&oacute;a học d&agrave;nh cho bạn!</p>\r\n\r\n<p><strong>Trong kh&oacute;a học n&agrave;y, bạn sẽ:</strong></p>\r\n\r\n<p>-&nbsp;<strong>Hiểu bản chất Docker</strong>: Container l&agrave; g&igrave;, tại sao n&ecirc;n d&ugrave;ng Docker thay v&igrave; c&agrave;i m&ocirc;i trường thủ c&ocirc;ng? So s&aacute;nh với m&aacute;y ảo, c&aacute;ch Docker quản l&yacute; t&agrave;i nguy&ecirc;n, layer image&hellip;</p>\r\n\r\n<p>-&nbsp;<strong>Thực h&agrave;nh từ đầu</strong>: C&agrave;i đặt Docker tr&ecirc;n Windows, MacOS, WSL, Ubuntu. Sử dụng c&aacute;c lệnh&nbsp;<code>run</code>,&nbsp;<code>exec</code>,&nbsp;<code>logs</code>,&nbsp;<code>volume</code>,&nbsp;<code>network</code>,&nbsp;<code>build</code>,&nbsp;<code>push</code>... một c&aacute;ch thực tế</p>\r\n\r\n<p>-&nbsp;<strong>X&acirc;y dựng image theo chuẩn production</strong>: Viết Dockerfile chuy&ecirc;n nghiệp, tối ưu dung lượng, multi-stage build, quản l&yacute; biến m&ocirc;i trường v&agrave; bảo mật</p>\r\n\r\n<p>-&nbsp;<strong>Docker Compose</strong>: Tổ chức nhiều container, chia microservices, cấu h&igrave;nh m&ocirc;i trường dev/test/prod chỉ với một lệnh&nbsp;<code>docker-compose up</code>.</p>\r\n\r\n<p>-&nbsp;<strong>Volume, Network, Mounts</strong>: Hiểu s&acirc;u c&aacute;ch Docker quản l&yacute; dữ liệu, li&ecirc;n kết giữa c&aacute;c container v&agrave; với host</p>\r\n\r\n<p><strong>- Triển khai thực tế</strong>: Dự &aacute;n mẫu triển khai app Node.js/PHP + MySQL/Redis + Nginx tr&ecirc;n Docker từ A đến Z</p>\r\n\r\n<p><strong>Kh&oacute;a học ph&ugrave; hợp với ai?</strong></p>\r\n\r\n<p>- Lập tr&igrave;nh vi&ecirc;n web muốn triển khai app gọn nhẹ, dễ maintain</p>\r\n\r\n<p>- Sinh vi&ecirc;n IT muốn nắm bắt xu hướng dev hiện đại</p>\r\n\r\n<p>- DevOps cần quản l&yacute; hạ tầng hiệu quả</p>\r\n\r\n<p>- Bất kỳ ai muốn hiểu bản chất container &amp; Docker</p>\r\n\r\n<p><strong>Y&ecirc;u cầu đầu v&agrave;o:</strong></p>\r\n\r\n<p>- Đ&atilde; biết cơ bản về lập tr&igrave;nh (PHP, Node.js, Python, v.v.)</p>\r\n\r\n<p>- Đ&atilde; từng deploy web app thủ c&ocirc;ng l&agrave; một lợi thế</p>\r\n\r\n<p>- Kiến thức Linux cơ bản</p>\r\n\r\n<p><strong>Học qua video Elearning + T&agrave;i liệu chi tiết + Hỗ trợ 1-1</strong></p>\r\n\r\n<p>Học xong bạn c&oacute; thể tự tin:</p>\r\n\r\n<p>- Viết Dockerfile chuẩn cho bất kỳ dự &aacute;n n&agrave;o</p>\r\n\r\n<p>- Tự tạo m&ocirc;i trường dev/test/prod bằng Docker Compose</p>\r\n\r\n<p>- Debug, tối ưu, triển khai ứng dụng bằng Docker chuy&ecirc;n nghiệp</p>', '<p>Are you a programmer, DevOps specialist, or IT student looking to understand and use Docker systematically? This course is for you!</p>\r\n\r\n<p>In this course, you will:</p>\r\n\r\n<p>- Understand the fundamentals of Docker: What are containers, why use Docker instead of manually setting up an environment? Compare it to virtual machines, how Docker manages resources, image layers, etc.</p>\r\n\r\n<p>- Practice from scratch: Install Docker on Windows, MacOS, WSL, Ubuntu. Use commands like run, exec, logs, volume, network, build, push... in a practical way.</p>\r\n\r\n<p>- Build production-standard images: Write professional Dockerfiles, optimize size, multi-stage build, manage environment variables, and ensure security.</p>\r\n\r\n<p>- Docker Compose: Organize multiple containers, divide microservices, and configure dev/test/prod environments with just one command: `docker-compose up`.</p>\r\n\r\n<p>- Volume, Network, Mounts: Gain a deep understanding of how Docker manages data, the connections between containers and with the host.</p>\r\n\r\n<p>- Practical Deployment: A sample project deploying a Node.js/PHP + MySQL/Redis + Nginx app on Docker from A to Z.</p>\r\n\r\n<p>Who is this course suitable for?</p>\r\n\r\n<p>- Web developers who want to deploy lightweight, easy-to-maintain apps</p>\r\n\r\n<p>- IT students who want to grasp modern development trends</p>\r\n\r\n<p>- DevOps professionals who need to manage infrastructure efficiently</p>\r\n\r\n<p>- Anyone who wants to understand the nature of containers &amp; Docker</p>\r\n\r\n<p>Entry requirements:</p>\r\n\r\n<p>- Basic programming knowledge (PHP, Node.js, Python, etc.)</p>\r\n\r\n<p>- Experience deploying web apps manually is an advantage</p>\r\n\r\n<p>- Basic Linux knowledge</p>\r\n\r\n<p>Learning via Elearning videos + Detailed documentation + 1-on-1 support</p>\r\n\r\n<p>After completing the course, you can confidently:</p>\r\n\r\n<p>- Write standard Dockerfiles for any project</p>\r\n\r\n<p>- Create your own development/testing/production environments using Docker Compose</p>\r\n\r\n<p>- Debugging, optimizing, and deploying applications professionally using Docker</p>', '<p>당신은 <strong>개발자, DevOps 엔지니어, 또는 IT 학생</strong>으로서 Docker를 체계적으로 배우고 제대로 활용하고 싶으신가요?<br />\r\n이 강의가 바로 당신을 위한 과정입니다.</p>\r\n\r\n<h3>이 강의에서 배우는 내용</h3>\r\n\r\n<ul>\r\n	<li>\r\n	<p><strong>Docker의 핵심 이해:</strong> 컨테이너란 무엇인가? 왜 수동 환경 설정 대신 Docker를 사용해야 하는가? 가상 머신과의 차이, Docker의 리소스 관리 방식, 이미지 레이어 구조 등을 이해합니다.</p>\r\n	</li>\r\n	<li>\r\n	<p><strong>처음부터 실습:</strong> Windows, macOS, WSL, Ubuntu에서 Docker 설치. <code>run</code>, <code>exec</code>, <code>logs</code>, <code>volume</code>, <code>network</code>, <code>build</code>, <code>push</code> 등의 명령어를 실제 환경에서 실습합니다.</p>\r\n	</li>\r\n	<li>\r\n	<p><strong>프로덕션 환경에 맞는 이미지 구축:</strong> 전문적인 Dockerfile 작성, 이미지 용량 최적화, multi-stage build 활용, 환경 변수 및 보안 관리.</p>\r\n	</li>\r\n	<li>\r\n	<p><strong>Docker Compose:</strong> 여러 컨테이너를 구성하고 마이크로서비스 구조를 설계하며 <code>docker-compose up</code> 명령 하나로 dev/test/prod 환경을 구성합니다.</p>\r\n	</li>\r\n	<li>\r\n	<p><strong>Volume, Network, Mounts:</strong> Docker가 데이터를 관리하고 컨테이너와 호스트 간 연결을 처리하는 방식을 깊이 있게 이해합니다.</p>\r\n	</li>\r\n	<li>\r\n	<p><strong>실전 배포:</strong> <strong>Node.js/PHP + MySQL/Redis + Nginx</strong> 기반의 실제 프로젝트를 Docker로 A부터 Z까지 배포합니다.</p>\r\n	</li>\r\n</ul>\r\n\r\n<h3>이런 분들에게 추천합니다</h3>\r\n\r\n<ul>\r\n	<li>\r\n	<p>가볍고 유지보수하기 쉬운 배포 환경을 원하는 웹 개발자</p>\r\n	</li>\r\n	<li>\r\n	<p>최신 개발 트렌드를 배우고 싶은 IT 학생</p>\r\n	</li>\r\n	<li>\r\n	<p>인프라 관리를 효율적으로 하고 싶은 DevOps 엔지니어</p>\r\n	</li>\r\n	<li>\r\n	<p>컨테이너와 Docker의 원리를 이해하고 싶은 모든 분</p>\r\n	</li>\r\n</ul>\r\n\r\n<h3>사전 요구 사항</h3>\r\n\r\n<ul>\r\n	<li>\r\n	<p>기본적인 프로그래밍 지식 (PHP, Node.js, Python 등)</p>\r\n	</li>\r\n	<li>\r\n	<p>웹 애플리케이션을 수동으로 배포해본 경험이 있으면 좋습니다</p>\r\n	</li>\r\n	<li>\r\n	<p>기본적인 Linux 지식</p>\r\n	</li>\r\n</ul>\r\n\r\n<h3>학습 방식</h3>\r\n\r\n<p>영상 기반 <strong>E-learning + 상세 문서 + 1:1 강사 지원</strong></p>\r\n\r\n<h3>강의를 마치면</h3>\r\n\r\n<ul>\r\n	<li>\r\n	<p>어떤 프로젝트든 사용할 수 있는 Dockerfile을 작성할 수 있습니다</p>\r\n	</li>\r\n	<li>\r\n	<p>Docker Compose로 dev/test/prod 환경을 구축할 수 있습니다</p>\r\n	</li>\r\n	<li>\r\n	<p>Docker를 활용해 애플리케이션을 디버깅, 최적화 및 배포할 수 있습니다</p>\r\n	</li>\r\n</ul>', '<p>あなたは <strong>開発者、DevOpsエンジニア、またはIT学生</strong>で、Dockerを体系的に理解し実践的に使えるようになりたいと考えていますか？<br />\r\nこのコースはあなたのためのものです。</p>\r\n\r\n<h3>このコースで学べること</h3>\r\n\r\n<ul>\r\n	<li>\r\n	<p><strong>Dockerの基礎理解:</strong> コンテナとは何か？なぜ手動で環境を構築する代わりにDockerを使うべきなのか？仮想マシンとの違い、Dockerのリソース管理、イメージレイヤーなどを理解します。</p>\r\n	</li>\r\n	<li>\r\n	<p><strong>ゼロからの実践:</strong> Windows、macOS、WSL、UbuntuでDockerをインストールし、<code>run</code>、<code>exec</code>、<code>logs</code>、<code>volume</code>、<code>network</code>、<code>build</code>、<code>push</code>などのコマンドを実践的に学びます。</p>\r\n	</li>\r\n	<li>\r\n	<p><strong>本番環境向けのイメージ作成:</strong> プロフェッショナルなDockerfileの作成、イメージサイズの最適化、multi-stage build、環境変数とセキュリティの管理。</p>\r\n	</li>\r\n	<li>\r\n	<p><strong>Docker Compose:</strong> 複数コンテナの管理、マイクロサービス構成、<code>docker-compose up</code>コマンド1つでdev/test/prod環境を構築。</p>\r\n	</li>\r\n	<li>\r\n	<p><strong>Volume / Network / Mount:</strong> Dockerのデータ管理やコンテナ間通信、ホストとの連携を深く理解します。</p>\r\n	</li>\r\n	<li>\r\n	<p><strong>実践プロジェクト:</strong> <strong>Node.js/PHP + MySQL/Redis + Nginx</strong> をDockerでAからZまで構築・デプロイします。</p>\r\n	</li>\r\n</ul>\r\n\r\n<h3>このコースに向いている方</h3>\r\n\r\n<ul>\r\n	<li>\r\n	<p>軽量で保守しやすいデプロイを行いたいWeb開発者</p>\r\n	</li>\r\n	<li>\r\n	<p>最新の開発トレンドを学びたいIT学生</p>\r\n	</li>\r\n	<li>\r\n	<p>インフラ管理を効率化したいDevOpsエンジニア</p>\r\n	</li>\r\n	<li>\r\n	<p>Dockerとコンテナの仕組みを理解したい方</p>\r\n	</li>\r\n</ul>\r\n\r\n<h3>必要条件</h3>\r\n\r\n<ul>\r\n	<li>\r\n	<p>基本的なプログラミング知識（PHP、Node.js、Pythonなど）</p>\r\n	</li>\r\n	<li>\r\n	<p>Webアプリを手動でデプロイした経験があれば尚可</p>\r\n	</li>\r\n	<li>\r\n	<p>基本的なLinuxの知識</p>\r\n	</li>\r\n</ul>\r\n\r\n<h3>学習形式</h3>\r\n\r\n<p>動画ベースの <strong>E-learning + 詳細ドキュメント + 講師による1対1サポート</strong></p>\r\n\r\n<h3>修了後にできること</h3>\r\n\r\n<ul>\r\n	<li>\r\n	<p>あらゆるプロジェクトに対応したDockerfileを作成できる</p>\r\n	</li>\r\n	<li>\r\n	<p>Docker Composeでdev/test/prod環境を構築できる</p>\r\n	</li>\r\n	<li>\r\n	<p>Dockerを使ったアプリケーションのデバッグ、最適化、デプロイ</p>\r\n	</li>\r\n</ul>', '<p>你是一名 <strong>开发者、DevOps 工程师或 IT 学生</strong>，希望系统地学习并掌握 Docker 吗？<br />\r\n这门课程正适合你！</p>\r\n\r\n<h3>在本课程中，你将学习：</h3>\r\n\r\n<ul>\r\n	<li>\r\n	<p><strong>理解 Docker 本质：</strong> 什么是容器？为什么使用 Docker 而不是手动安装环境？Docker 与虚拟机的区别、Docker 如何管理资源以及镜像层结构等。</p>\r\n	</li>\r\n	<li>\r\n	<p><strong>从零开始实践：</strong> 在 Windows、macOS、WSL、Ubuntu 上安装 Docker，学习并实践 <code>run</code>、<code>exec</code>、<code>logs</code>、<code>volume</code>、<code>network</code>、<code>build</code>、<code>push</code> 等常用命令。</p>\r\n	</li>\r\n	<li>\r\n	<p><strong>构建生产级镜像：</strong> 编写专业的 Dockerfile，优化镜像体积，使用 multi-stage build，并管理环境变量与安全配置。</p>\r\n	</li>\r\n	<li>\r\n	<p><strong>Docker Compose：</strong> 管理多个容器，构建微服务架构，通过一个命令 <code>docker-compose up</code> 配置开发、测试和生产环境。</p>\r\n	</li>\r\n	<li>\r\n	<p><strong>Volume、Network、Mount：</strong> 深入理解 Docker 如何管理数据、容器之间的连接以及与主机的交互。</p>\r\n	</li>\r\n	<li>\r\n	<p><strong>实际部署项目：</strong> 使用 <strong>Node.js/PHP + MySQL/Redis + Nginx</strong> 在 Docker 上完成从零到上线的完整部署。</p>\r\n	</li>\r\n</ul>\r\n\r\n<h3>本课程适合谁？</h3>\r\n\r\n<ul>\r\n	<li>\r\n	<p>希望轻量化部署并易于维护应用的 Web 开发者</p>\r\n	</li>\r\n	<li>\r\n	<p>想掌握现代开发技术的 IT 学生</p>\r\n	</li>\r\n	<li>\r\n	<p>需要高效管理基础设施的 DevOps 工程师</p>\r\n	</li>\r\n	<li>\r\n	<p>想深入理解 Docker 与容器技术的任何人</p>\r\n	</li>\r\n</ul>\r\n\r\n<h3>学习要求</h3>\r\n\r\n<ul>\r\n	<li>\r\n	<p>具备基础编程知识（PHP、Node.js、Python 等）</p>\r\n	</li>\r\n	<li>\r\n	<p>有手动部署 Web 应用经验更佳</p>\r\n	</li>\r\n	<li>\r\n	<p>具备基本 Linux 知识</p>\r\n	</li>\r\n</ul>\r\n\r\n<h3>学习方式</h3>\r\n\r\n<p>视频 <strong>Elearning + 详细文档 + 讲师一对一支持</strong></p>\r\n\r\n<h3>学完本课程，你将能够：</h3>\r\n\r\n<ul>\r\n	<li>\r\n	<p>为任何项目编写标准 Dockerfile</p>\r\n	</li>\r\n	<li>\r\n	<p>使用 Docker Compose 构建 dev/test/prod 环境</p>\r\n	</li>\r\n	<li>\r\n	<p>专业地调试、优化并部署 Docker 应用</p>\r\n	</li>\r\n</ul>', 6, '/storage/photos/2/6ffa75c2-35b8-4eb0-b4b0-ab9002549f55.jpg', 1995000.00, NULL, 795000.00, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'KH338256', 740.00, 0, '<p>Học tr&ecirc;n mọi thiết bị</p>\r\n\r\n<p>Code mẫu, t&agrave;i liệu đầy đủ</p>\r\n\r\n<p>Hỗ trợ 1-1 bởi giảng vi&ecirc;n, nh&oacute;m k&iacute;n</p>\r\n\r\n<p>Giới thiệu c&ocirc;ng việc ph&ugrave; hợp</p>\r\n\r\n<p>Thời hạn: Vĩnh viễn</p>', '<p>Learn on any device</p>\r\n\r\n<p>Sample code, complete documentation</p>\r\n\r\n<p>One-on-one support by instructors, private group</p>\r\n\r\n<p>Job placement assistance</p>\r\n\r\n<p>Duration: Lifetime</p>', '<p>모든 기기에서 학습 가능</p>\r\n\r\n<p>すべてのデバイスで学習可能</p>\r\n\r\n<p>예제 코드 및 완전한 학습 자료 제공</p>\r\n\r\n<p>강사의 1:1 지원 및 전용 커뮤니티</p>\r\n\r\n<p>역량에 맞는 취업 기회 제공</p>\r\n\r\n<p>평생 이용 가능</p>', '<p>すべてのデバイスで学習可能</p>\r\n\r\n<p>サンプルコードと完全な資料付き</p>\r\n\r\n<p>講師による1対1サポートと専用コミュニティ</p>\r\n\r\n<p>スキルに合った就職機会の紹介</p>\r\n\r\n<p>無期限アクセス</p>', '<p>支持在所有设备上学习</p>\r\n\r\n<p>提供示例代码和完整学习资料</p>\r\n\r\n<p>讲师一对一指导及专属学习社群</p>\r\n\r\n<p>推荐适合的工作机会</p>\r\n\r\n<p>永久访问</p>', 1, 'all_lessons', 0, NULL, NULL, NULL, NULL, 0, 0, 4, '2026-02-03 09:11:03', '2026-04-27 08:10:40', NULL),
(30, 'IT TEST', NULL, NULL, NULL, NULL, 'it-test', NULL, NULL, NULL, NULL, '123', NULL, NULL, NULL, NULL, 15, '123', 0.00, NULL, 0.00, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'KH880040', 5355.00, 0, '123', NULL, NULL, NULL, NULL, 0, 'all_lessons', 0, NULL, NULL, NULL, NULL, 0, 0, 6, '2026-04-03 03:53:34', '2026-05-27 08:29:24', NULL),
(31, '123', NULL, NULL, NULL, NULL, '123', NULL, NULL, NULL, NULL, '123', NULL, NULL, NULL, NULL, 15, '123', 20000000.00, NULL, 10000000.00, 77477.00, 38739.00, 1143792.00, 571896.00, 121869.00, 60934.00, 529139.00, 264569.00, 'KH239251', 6943.00, 0, '123', NULL, NULL, NULL, NULL, 1, 'all_lessons', 0, NULL, NULL, NULL, NULL, 0, 0, 11, '2026-04-09 14:53:36', '2026-05-27 08:29:24', NULL),
(33, 'Hãy test cùng tôi', NULL, NULL, NULL, NULL, 'hay-test-cung-toi', NULL, NULL, NULL, NULL, '<p>qưe</p>', NULL, NULL, NULL, NULL, 15, '/storage/photos/2/teacher img.jpg', 10000000.00, NULL, 0.00, 3870800.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 'KH993942', 2394.31, 0, '<p>qưe</p>', NULL, NULL, NULL, NULL, 1, 'all_lessons', 0, NULL, NULL, NULL, NULL, 0, 0, 12, '2026-04-27 08:08:56', '2026-05-27 08:29:24', NULL),
(34, 'Test countdown', NULL, NULL, NULL, NULL, 'test-countdown', NULL, NULL, NULL, NULL, '123', NULL, NULL, NULL, NULL, 15, '123', 1111111.00, NULL, 0.00, 430400.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 'KH484834', 0.00, 0, '123', NULL, NULL, NULL, NULL, 1, 'all_lessons', 1, '2026-05-14 06:31:00', NULL, NULL, NULL, 0, 0, 1, '2026-05-13 06:32:02', '2026-05-27 08:29:24', NULL),
(35, 'Test countdown (Bản sao)', NULL, NULL, NULL, NULL, 'test-countdown-copy-mhfg', NULL, NULL, NULL, NULL, '123', NULL, NULL, NULL, NULL, 15, '123', 1111111.00, NULL, 0.00, 430400.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 'KH484834-COPY-NNCM', 0.00, 0, '123', NULL, NULL, NULL, NULL, 0, 'all_lessons', 1, '2026-05-14 06:31:00', NULL, NULL, NULL, 0, 0, 0, '2026-05-14 07:12:53', '2026-05-27 08:29:24', NULL),
(37, 'Madonna Boehm', NULL, NULL, NULL, NULL, 'dignissimos-cum-eius-aut-quod-laboriosam', NULL, NULL, NULL, NULL, '123', NULL, NULL, NULL, NULL, 6, '123', 123.00, NULL, 123.00, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '123', 123.00, 0, '123', NULL, NULL, NULL, NULL, 0, 'all_lessons', 0, NULL, NULL, NULL, NULL, 0, 0, 0, '2026-06-13 04:57:07', '2026-06-13 04:57:07', NULL),
(38, 'Derek Ondricka', NULL, NULL, NULL, NULL, 'cumque-impedit-exercitationem-aut-dolores', NULL, NULL, NULL, NULL, '123', NULL, NULL, NULL, NULL, 6, '123', 123.00, NULL, 123.00, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '123', 123.00, 0, '123', NULL, NULL, NULL, NULL, 0, 'all_lessons', 0, NULL, NULL, NULL, NULL, 0, 0, 0, '2026-06-13 04:57:07', '2026-06-13 04:57:07', NULL),
(39, 'Ms. Katarina Grant', NULL, NULL, NULL, NULL, 'cum-facilis-quae-iure-sunt', NULL, NULL, NULL, NULL, '123', NULL, NULL, NULL, NULL, 6, '123', 123.00, NULL, 123.00, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '123', 123.00, 0, '123', NULL, NULL, NULL, NULL, 0, 'all_lessons', 0, NULL, NULL, NULL, NULL, 0, 0, 0, '2026-06-13 04:57:07', '2026-06-13 04:57:07', NULL),
(40, 'Mr. Trevion Donnelly', NULL, NULL, NULL, NULL, 'cupiditate-reiciendis-ratione-reprehenderit-molestias-et-at-facere', NULL, NULL, NULL, NULL, '123', NULL, NULL, NULL, NULL, 6, '123', 123.00, NULL, 123.00, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '123', 123.00, 0, '123', NULL, NULL, NULL, NULL, 0, 'all_lessons', 0, NULL, NULL, NULL, NULL, 0, 0, 0, '2026-06-13 04:57:07', '2026-06-13 04:57:07', NULL),
(41, 'Hayden Goldner', NULL, NULL, NULL, NULL, 'omnis-qui-doloremque-ut-quas-nisi-laboriosam-earum', NULL, NULL, NULL, NULL, '123', NULL, NULL, NULL, NULL, 6, '123', 123.00, NULL, 123.00, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '123', 123.00, 0, '123', NULL, NULL, NULL, NULL, 0, 'all_lessons', 0, NULL, NULL, NULL, NULL, 0, 0, 0, '2026-06-13 04:57:07', '2026-06-13 04:57:07', NULL);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `course_comments`
--

CREATE TABLE `course_comments` (
  `id` int(10) UNSIGNED NOT NULL,
  `course_id` int(10) UNSIGNED NOT NULL,
  `parent_id` int(10) UNSIGNED DEFAULT NULL,
  `student_id` int(10) UNSIGNED DEFAULT NULL,
  `user_id` int(10) UNSIGNED DEFAULT NULL,
  `content` text NOT NULL,
  `is_visible` tinyint(1) NOT NULL DEFAULT 1,
  `is_flagged` tinyint(1) NOT NULL DEFAULT 0,
  `flagged_terms` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `course_quizzes`
--

CREATE TABLE `course_quizzes` (
  `id` int(10) UNSIGNED NOT NULL,
  `course_id` int(10) UNSIGNED NOT NULL,
  `lesson_id` int(10) UNSIGNED DEFAULT NULL COMMENT 'Quiz gan voi bai hoc cu the',
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `passing_score` tinyint(3) UNSIGNED NOT NULL DEFAULT 70 COMMENT '% diem can dat de dau',
  `max_attempts` tinyint(3) UNSIGNED DEFAULT NULL COMMENT 'So lan lam toi da, null = khong gioi han',
  `time_limit_minutes` int(10) UNSIGNED DEFAULT NULL COMMENT 'Gioi han thoi gian lam bai',
  `show_answers_after` tinyint(1) NOT NULL DEFAULT 1 COMMENT 'Hien dap an sau khi nop bai',
  `position` smallint(5) UNSIGNED NOT NULL DEFAULT 0,
  `status` tinyint(4) NOT NULL DEFAULT 1 COMMENT '1=draft/active, 0=an',
  `published_at` timestamp NULL DEFAULT NULL,
  `deadline_at` timestamp NULL DEFAULT NULL COMMENT 'Han nop bai quiz',
  `created_by` int(10) UNSIGNED DEFAULT NULL COMMENT 'Teacher student id or admin id',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `course_quiz_assignments`
--

CREATE TABLE `course_quiz_assignments` (
  `id` int(10) UNSIGNED NOT NULL,
  `quiz_id` int(10) UNSIGNED NOT NULL,
  `student_id` int(10) UNSIGNED NOT NULL,
  `assigned_by` int(10) UNSIGNED DEFAULT NULL,
  `deadline_at` timestamp NULL DEFAULT NULL,
  `status` varchar(30) NOT NULL DEFAULT 'assigned',
  `note` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `course_quiz_choices`
--

CREATE TABLE `course_quiz_choices` (
  `id` int(10) UNSIGNED NOT NULL,
  `question_id` int(10) UNSIGNED NOT NULL,
  `choice_text` text NOT NULL,
  `is_correct` tinyint(1) NOT NULL DEFAULT 0,
  `is_exclusive` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Dung cho cau true_false hoac single choice',
  `position` smallint(5) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `course_quiz_questions`
--

CREATE TABLE `course_quiz_questions` (
  `id` int(10) UNSIGNED NOT NULL,
  `quiz_id` int(10) UNSIGNED NOT NULL,
  `question` text NOT NULL,
  `question_type` enum('single_choice','multiple_choice','true_false','short_answer') NOT NULL DEFAULT 'single_choice',
  `position` smallint(5) UNSIGNED NOT NULL DEFAULT 0,
  `points` tinyint(3) UNSIGNED NOT NULL DEFAULT 1 COMMENT 'Diem moi cau',
  `is_required` tinyint(1) NOT NULL DEFAULT 1,
  `explanation` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `course_quiz_submissions`
--

CREATE TABLE `course_quiz_submissions` (
  `id` int(10) UNSIGNED NOT NULL,
  `quiz_id` int(10) UNSIGNED NOT NULL,
  `student_id` int(10) UNSIGNED NOT NULL,
  `assignment_id` int(10) UNSIGNED DEFAULT NULL,
  `attempt_no` smallint(5) UNSIGNED NOT NULL DEFAULT 1,
  `score` smallint(5) UNSIGNED NOT NULL DEFAULT 0,
  `passed` tinyint(1) NOT NULL DEFAULT 0,
  `started_at` timestamp NULL DEFAULT NULL,
  `finished_at` timestamp NULL DEFAULT NULL,
  `submitted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `course_quiz_submission_answers`
--

CREATE TABLE `course_quiz_submission_answers` (
  `id` int(10) UNSIGNED NOT NULL,
  `submission_id` int(10) UNSIGNED NOT NULL,
  `question_id` int(10) UNSIGNED NOT NULL,
  `choice_id` int(10) UNSIGNED DEFAULT NULL,
  `answer_text` text DEFAULT NULL,
  `is_correct` tinyint(1) NOT NULL DEFAULT 0,
  `points_earned` smallint(5) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `course_ratings`
--

CREATE TABLE `course_ratings` (
  `id` int(10) UNSIGNED NOT NULL,
  `course_id` int(10) UNSIGNED NOT NULL,
  `student_id` int(10) UNSIGNED NOT NULL,
  `rating` decimal(2,1) NOT NULL,
  `status` tinyint(4) NOT NULL DEFAULT 1 COMMENT '1: Show, 0: Hide',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `course_view_trackings`
--

CREATE TABLE `course_view_trackings` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `course_id` bigint(20) UNSIGNED NOT NULL,
  `student_id` bigint(20) UNSIGNED DEFAULT NULL,
  `visitor_hash` varchar(64) NOT NULL,
  `view_date` date NOT NULL,
  `viewed_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `documents`
--

CREATE TABLE `documents` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `url` varchar(255) DEFAULT NULL,
  `size` double(8,2) DEFAULT 0.00,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `exchange_rates`
--

CREATE TABLE `exchange_rates` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `code` varchar(3) NOT NULL,
  `rate` decimal(20,8) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `groups`
--

CREATE TABLE `groups` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `is_admin` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `groups`
--

INSERT INTO `groups` (`id`, `name`, `slug`, `description`, `is_admin`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'Super Admin', 'super_admin', 'Toàn quyền quản trị hệ thống.', 1, '2026-03-29 16:21:59', '2026-03-30 00:12:15', NULL),
(2, 'Admin', 'admin', 'Quản trị viên vận hành hệ thống.', 1, '2026-03-29 16:21:59', '2026-03-30 00:13:48', NULL),
(3, 'Teacher', 'teacher', 'Giáo viên phụ trách nội dung khóa học và bài giảng.', 1, '2026-03-30 16:31:30', '2026-06-13 04:56:52', NULL);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `group_permission`
--

CREATE TABLE `group_permission` (
  `group_id` int(10) UNSIGNED NOT NULL,
  `permission_id` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `group_permission`
--

INSERT INTO `group_permission` (`group_id`, `permission_id`) VALUES
(1, 1),
(1, 2),
(1, 3),
(1, 4),
(1, 5),
(1, 6),
(1, 7),
(1, 8),
(1, 9),
(1, 10),
(1, 11),
(1, 12),
(1, 13),
(1, 14),
(1, 15),
(1, 16),
(1, 17),
(1, 18),
(1, 19),
(1, 20),
(1, 21),
(1, 22),
(1, 23),
(1, 24),
(1, 25),
(1, 26),
(1, 27),
(1, 28),
(1, 29),
(1, 30),
(1, 31),
(1, 32),
(1, 33),
(1, 34),
(1, 35),
(1, 36),
(1, 37),
(1, 38),
(1, 39),
(1, 40),
(1, 41),
(1, 42),
(1, 43),
(1, 44),
(1, 45),
(1, 46),
(1, 47),
(1, 48),
(1, 49),
(1, 50),
(1, 51),
(1, 52),
(1, 53),
(1, 54),
(1, 55),
(1, 56),
(1, 57),
(1, 58),
(1, 59),
(1, 60),
(1, 61),
(1, 62),
(1, 63),
(1, 64),
(1, 65),
(1, 66),
(1, 67),
(1, 68),
(1, 69),
(1, 70),
(1, 71),
(1, 72),
(1, 73),
(1, 74),
(1, 75),
(1, 76),
(1, 77),
(1, 78),
(1, 79),
(1, 80),
(1, 81),
(1, 82),
(1, 83),
(1, 84),
(1, 85),
(1, 86),
(1, 87),
(1, 88),
(1, 89),
(1, 90),
(1, 91),
(1, 92),
(1, 93),
(1, 94),
(1, 95),
(1, 96),
(1, 97),
(1, 98),
(1, 99),
(1, 100),
(1, 101),
(1, 102),
(1, 103),
(1, 104),
(1, 105),
(1, 106),
(1, 107),
(1, 108),
(1, 109),
(1, 110),
(1, 111),
(1, 112),
(1, 113),
(1, 114),
(1, 115),
(1, 116),
(1, 117),
(1, 118),
(1, 119),
(1, 120),
(1, 121),
(1, 122),
(1, 123),
(1, 124),
(1, 125),
(1, 126),
(1, 127),
(1, 128),
(1, 129),
(2, 1),
(2, 2),
(2, 3),
(2, 4),
(2, 5),
(2, 6),
(2, 7),
(2, 8),
(2, 9),
(2, 10),
(2, 11),
(2, 12),
(2, 13),
(2, 14),
(2, 15),
(2, 16),
(2, 17),
(2, 18),
(2, 19),
(2, 20),
(2, 21),
(2, 22),
(2, 23),
(2, 24),
(2, 25),
(2, 26),
(2, 27),
(2, 28),
(2, 29),
(2, 30),
(2, 31),
(2, 32),
(2, 33),
(2, 34),
(2, 35),
(2, 36),
(2, 37),
(2, 38),
(2, 39),
(2, 40),
(2, 41),
(2, 42),
(2, 43),
(2, 44),
(2, 45),
(2, 46),
(2, 47),
(2, 48),
(2, 49),
(2, 50),
(2, 51),
(2, 62),
(2, 63),
(2, 64),
(2, 65),
(2, 66),
(2, 67),
(2, 68),
(2, 69),
(2, 70),
(2, 71),
(2, 72),
(2, 73),
(2, 74),
(2, 75),
(2, 76),
(2, 77),
(2, 78),
(2, 79),
(2, 80),
(2, 81),
(2, 82),
(2, 83),
(2, 84),
(2, 85),
(2, 86),
(2, 87),
(2, 94),
(2, 95),
(2, 96),
(2, 97),
(2, 98),
(2, 99),
(2, 100),
(2, 101),
(2, 102),
(2, 103),
(2, 104),
(2, 105),
(2, 106),
(2, 107),
(2, 108),
(2, 109),
(2, 110),
(2, 111),
(2, 112),
(2, 113),
(2, 114),
(2, 115),
(2, 116),
(2, 117),
(2, 118),
(2, 119),
(2, 120),
(2, 121),
(2, 122),
(2, 123),
(2, 124),
(2, 125),
(2, 126),
(2, 127),
(2, 128),
(2, 129),
(3, 1),
(3, 2),
(3, 4),
(3, 9),
(3, 10),
(3, 11),
(3, 12),
(3, 13),
(3, 78),
(3, 94),
(3, 95);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `ip_blacklists`
--

CREATE TABLE `ip_blacklists` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `ip_address` varchar(255) NOT NULL,
  `reason` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) UNSIGNED NOT NULL,
  `reserved_at` int(10) UNSIGNED DEFAULT NULL,
  `available_at` int(10) UNSIGNED NOT NULL,
  `created_at` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `lessons`
--

CREATE TABLE `lessons` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `name_en` varchar(255) DEFAULT NULL,
  `name_ko` varchar(255) DEFAULT NULL,
  `name_ja` varchar(255) DEFAULT NULL,
  `name_zh` varchar(255) DEFAULT NULL,
  `slug` varchar(255) DEFAULT NULL,
  `slug_en` varchar(255) DEFAULT NULL,
  `slug_ko` varchar(255) DEFAULT NULL,
  `slug_ja` varchar(255) DEFAULT NULL,
  `slug_zh` varchar(255) DEFAULT NULL,
  `video_id` int(10) UNSIGNED DEFAULT NULL,
  `course_id` int(11) UNSIGNED DEFAULT NULL,
  `document_id` int(10) UNSIGNED DEFAULT NULL,
  `parent_id` int(10) UNSIGNED DEFAULT NULL,
  `is_trial` tinyint(1) NOT NULL DEFAULT 0,
  `view` int(11) NOT NULL DEFAULT 0,
  `position` int(11) NOT NULL DEFAULT 0,
  `durations` double(8,2) NOT NULL,
  `description` text DEFAULT NULL,
  `description_en` text DEFAULT NULL,
  `description_ko` text DEFAULT NULL,
  `description_ja` text DEFAULT NULL,
  `description_zh` text DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 0,
  `release_mode` varchar(40) NOT NULL DEFAULT 'immediate',
  `release_at` datetime DEFAULT NULL,
  `release_after_days` int(10) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `lessons`
--

INSERT INTO `lessons` (`id`, `name`, `name_en`, `name_ko`, `name_ja`, `name_zh`, `slug`, `slug_en`, `slug_ko`, `slug_ja`, `slug_zh`, `video_id`, `course_id`, `document_id`, `parent_id`, `is_trial`, `view`, `position`, `durations`, `description`, `description_en`, `description_ko`, `description_ja`, `description_zh`, `status`, `release_mode`, `release_at`, `release_after_days`, `created_at`, `updated_at`, `deleted_at`) VALUES
(57, 'Module 01: Nhập môn lập trình PHP', 'Module 01: Introduction to PHP Programming', '모듈 01: PHP 프로그래밍 입문', 'モジュール 01: PHPプログラミング入門', '模块 01：PHP 编程入门', 'module-01-nhap-mon-lap-trinh-php', 'module-01-introduction-to-php-programming', '모듈-01-php-프로그래밍-입문', 'モジュール-01-phpプログラミング入門', '模块-01php-编程入门', NULL, 15, 8, NULL, 0, 0, 1, 0.00, '<p>11</p>', NULL, NULL, NULL, NULL, 1, 'immediate', NULL, NULL, '2026-02-03 09:35:16', '2026-03-04 17:05:15', NULL),
(58, 'Giới thiệu ngôn ngữ PHP', 'Introduction to the PHP language', 'PHP 언어 소개', 'PHP言語の紹介', 'PHP 语言介绍', 'bai-1-gioi-thieu-ngon-ngu-php', 'introduction-to-the-php-language', 'php-언어-소개', 'php言語の紹介', 'php-语言介绍', 103, 15, NULL, 57, 1, 0, 2, 2375.00, '<p>123</p>', NULL, NULL, NULL, NULL, 1, 'immediate', NULL, NULL, '2026-02-03 09:36:10', '2026-03-04 15:05:10', NULL),
(61, 'Lộ trình - Phương pháp học PHP & MySQL hiệu quả', 'Roadmap and Effective Learning Methods for PHP & MySQL', 'PHP & MySQL 학습 로드맵 및 효과적인 학습 방법', 'PHP & MySQLの学習ロードマップと効果的な学習方法', 'PHP 与 MySQL 学习路线与高效学习方法', 'bai-02-lo-trinh-phuong-phap-hoc-php-mysql-hieu-qua', 'roadmap-and-effective-learning-methods-for-php-mysql', 'php-mysql-학습-로드맵-및-효과적인-학습-방법', 'php-mysqlの学習ロードマップと効果的な学習方法', 'php-与-mysql-学习路线与高效学习方法', 104, 15, NULL, 57, 1, 0, 3, 2138.00, '<p>123</p>', NULL, NULL, NULL, NULL, 1, 'immediate', NULL, NULL, '2026-02-10 03:08:12', '2026-03-04 15:06:21', NULL),
(64, 'Cài đặt công cụ - môi trường cần thiết', 'Installing Required Tools and Development Environment', '필요한 도구 및 개발 환경 설치', '必要なツールと開発環境のインストール', '安装所需工具与开发环境', 'cai-dat-cong-cu-moi-truong-can-thiet', 'installing-required-tools-and-development-environment', '필요한-도구-및-개발-환경-설치', '必要なツールと開発環境のインストール', '安装所需工具与开发环境', 102, 15, NULL, 57, 1, 0, 4, 3075.00, NULL, NULL, NULL, NULL, NULL, 1, 'immediate', NULL, NULL, '2026-02-12 16:02:29', '2026-03-04 15:07:14', NULL),
(65, 'Module 01: Giới thiệu và cài đặt Docker', 'Module 01: Introduction and Docker Installation', '모듈 01: Docker 소개 및 설치', 'モジュール 01: Dockerの紹介とインストール', '模块 01：Docker 介绍与安装', 'module-01-gioi-thieu-va-cai-dat-docker', 'module-01-introduction-and-docker-installation', '모듈-01-docker-소개-및-설치', 'モジュール-01-dockerの紹介とインストール', '模块-01docker-介绍与安装', NULL, 26, NULL, NULL, 0, 0, 1, 0.00, NULL, NULL, NULL, NULL, NULL, 1, 'immediate', NULL, NULL, '2026-03-04 14:04:53', '2026-03-04 14:05:37', NULL),
(66, 'Lời giới thiệu khóa học Docker', 'Introduction to the Docker Course', 'Docker 강의 소개', 'Dockerコースの紹介', 'Docker 课程介绍', 'loi-gioi-thieu-khoa-hoc-docker', 'introduction-to-the-docker-course', 'docker-강의-소개', 'dockerコースの紹介', 'docker-课程介绍', 105, 26, NULL, 65, 1, 1, 2, 187.00, NULL, NULL, NULL, NULL, NULL, 1, 'immediate', NULL, NULL, '2026-03-04 14:06:24', '2026-04-27 08:10:42', NULL),
(67, 'Tổng quan về Docker và các lệnh cơ bản', 'Overview of Docker and Basic Commands', 'Docker 개요 및 기본 명령어', 'Dockerの概要と基本コマンド', 'Docker 概述与基本命令', 'tong-quan-ve-docker-va-cac-lenh-co-ban', 'overview-of-docker-and-basic-commands', 'docker-개요-및-기본-명령어', 'dockerの概要と基本コマンド', 'docker-概述与基本命令', 106, 26, NULL, 65, 1, 0, 3, 242.00, NULL, NULL, NULL, NULL, NULL, 1, 'immediate', NULL, NULL, '2026-03-04 14:07:28', '2026-03-04 16:35:46', NULL),
(68, 'Cài đặt Docker trên Windows - MacOS - Ubuntu', 'Installing Docker on Windows, macOS, and Ubuntu', 'Windows, macOS, Ubuntu에서 Docker 설치', 'Windows・macOS・UbuntuでのDockerインストール', '在 Windows、macOS 和 Ubuntu 上安装 Docker', 'cai-dat-docker-tren-windows-macos-ubuntu', 'installing-docker-on-windows-macos-and-ubuntu', 'windows-macos-ubuntu에서-docker-설치', 'windowsmacosubuntuでのdockerインストール', '在-windowsmacos-和-ubuntu-上安装-docker', 107, 26, NULL, 65, 1, 0, 4, 138.00, NULL, NULL, NULL, NULL, NULL, 1, 'immediate', NULL, NULL, '2026-03-04 14:08:03', '2026-03-04 16:36:27', NULL),
(69, 'Module 02: Kiến thức Docker căn bản', 'Module 02: Basic Docker Concepts', '모듈 02: Docker 기초 지식', 'モジュール 02: Docker 基礎知識', '模块 02：Docker 基础知识', 'module-02-kien-thuc-docker-can-ban', 'module-02-basic-docker-concepts', '모듈-02-docker-기초-지식', 'モジュール-02-docker-基礎知識', 'module-02-kiến-thức-docker-căn-bản', NULL, 26, NULL, NULL, 0, 0, 5, 0.00, NULL, NULL, NULL, NULL, NULL, 1, 'immediate', NULL, NULL, '2026-03-04 14:08:34', '2026-03-04 14:08:56', NULL),
(70, 'Kiến trúc và thành phần của Docker', 'Docker Architecture and Components', 'Docker 아키텍처와 구성 요소', 'Dockerのアーキテクチャと構成要素', 'Docker 架构与组件\\', 'kien-truc-va-thanh-phan-cua-docker', 'docker-architecture-and-components', 'docker-아키텍처와-구성-요소', 'dockerのアーキテクチャと構成要素', 'docker-架构与组件', 108, 26, NULL, 69, 0, 1, 6, 173.00, NULL, NULL, NULL, NULL, NULL, 1, 'immediate', NULL, NULL, '2026-03-04 14:09:45', '2026-04-27 08:10:47', NULL),
(71, 'Module 01: Khởi động & cài đặt', 'Module 01: Getting Started & Installation', '모듈 01: 시작하기 및 설치', 'モジュール 01: 入門とインストール', '模块 01：入门与安装', 'module-01-khoi-dong-cai-dat', 'module-01-getting-started-installation', '모듈-01-시작하기-및-설치', 'モジュール-01-入門とインストール', '模块-01入门与安装', NULL, 25, NULL, NULL, 0, 0, 1, 0.00, NULL, NULL, NULL, NULL, NULL, 1, 'immediate', NULL, NULL, '2026-03-04 14:10:57', '2026-03-04 14:10:57', NULL),
(72, 'Giới thiệu tổng quan về khóa học', 'Course Overview', '강의 개요 소개', 'コース概要紹介', '课程概述介绍', 'gioi-thieu-tong-quan-ve-khoa-hoc', 'course-overview', '강의-개요-소개', 'コース概要紹介', '课程概述介绍', 109, 25, NULL, 71, 1, 0, 2, 149.00, NULL, NULL, NULL, NULL, NULL, 1, 'immediate', NULL, NULL, '2026-03-04 14:11:29', '2026-03-04 16:37:05', NULL),
(73, 'Tại sao lập trình viên cần Git?', 'Why Do Developers Need Git?', '왜 개발자에게 Git이 필요한가?', 'なぜ開発者にGitが必要なのか？', '为什么开发者需要 Git？', 'tai-sao-lap-trinh-vien-can-git', 'why-do-developers-need-git', '왜-개발자에게-git이-필요한가', 'なぜ開発者にgitが必要なのか', '为什么开发者需要-git', 110, 25, NULL, 71, 1, 0, 3, 230.00, NULL, NULL, NULL, NULL, NULL, 1, 'immediate', NULL, NULL, '2026-03-04 14:12:13', '2026-03-04 16:37:25', NULL),
(74, 'Cài đặt & Cấu hình Môi trường', 'Installation & Environment Setup', '설치 및 환경 설정', 'インストールと環境設定', '安装与环境配置', 'cai-dat-cau-hinh-moi-truong', 'installation-environment-setup', '설치-및-환경-설정', 'インストールと環境設定', '安装与环境配置', 111, 25, NULL, 71, 1, 0, 4, 230.00, NULL, NULL, NULL, NULL, NULL, 1, 'immediate', NULL, NULL, '2026-03-04 14:12:46', '2026-03-04 16:37:43', NULL),
(75, 'Module 02: Cơ bản về Git', 'Module 02: Git Fundamentals', '모듈 02: Git 기초', 'モジュール 02: Git 基礎', '模块 02：Git 基础', 'module-02-co-ban-ve-git', 'module-02-git-fundamentals', '모듈-02-git-기초', 'モジュール-02-git-基礎', '模块-02git-基础', NULL, 25, NULL, NULL, 0, 0, 5, 0.00, NULL, NULL, NULL, NULL, NULL, 1, 'immediate', NULL, NULL, '2026-03-04 14:14:44', '2026-03-04 14:14:44', NULL),
(76, 'Khởi tạo kho chứa (Repository)', 'Initialize a Repository', '저장소(Repository) 초기화', 'リポジトリの初期化', '初始化仓库（Repository）', 'khoi-tao-kho-chua-repository', 'initialize-a-repository', '저장소repository-초기화', 'リポジトリの初期化', '初始化仓库repository', 112, 25, NULL, 75, 0, 0, 6, 206.00, NULL, NULL, NULL, NULL, NULL, 1, 'immediate', NULL, NULL, '2026-03-04 14:15:15', '2026-03-04 16:37:56', NULL),
(77, 'Module 01: Tổng quan và kiến thức căn bản về Deploy', 'Module 01: Overview and Basic Deployment Concepts', '모듈 01: 배포 개요 및 기본 지식', 'モジュール 01: デプロイの概要と基礎知識', '模块 01：部署概述与基础知识', 'module-01-tong-quan-va-kien-thuc-can-ban-ve-deploy', 'module-01-overview-and-basic-deployment-concepts', '모듈-01-배포-개요-및-기본-지식', 'モジュール-01-デプロイの概要と基礎知識', '模块-01部署概述与基础知识', NULL, 24, NULL, NULL, 0, 0, 1, 0.00, NULL, NULL, NULL, NULL, NULL, 1, 'immediate', NULL, NULL, '2026-03-04 14:16:28', '2026-03-04 14:16:28', NULL),
(78, 'Tổng quan về khóa học Deploy Web', 'Overview of the Web Deployment Course', '웹 배포 강의 개요', 'Webデプロイコースの概要', 'Web 部署课程概述', 'tong-quan-ve-khoa-hoc-deploy-web', 'overview-of-the-web-deployment-course', '웹-배포-강의-개요', 'webデプロイコースの概要', 'web-部署课程概述', 117, 24, NULL, 77, 1, 1, 2, 152.00, NULL, NULL, NULL, NULL, NULL, 1, 'immediate', NULL, NULL, '2026-03-04 14:17:01', '2026-04-27 08:16:42', NULL),
(79, 'Mô hình Client - Server và cách website hoạt động', 'Client–Server Model and How Websites Work', '클라이언트-서버 모델과 웹사이트 작동 방식', 'クライアント・サーバーモデルとWebサイトの仕組み', '客户端-服务器模型与网站的工作原理', 'mo-hinh-client-server-va-cach-website-hoat-dong', 'clientserver-model-and-how-websites-work', '클라이언트-서버-모델과-웹사이트-작동-방식', 'クライアントサーバーモデルとwebサイトの仕組み', '客户端-服务器模型与网站的工作原理', 115, 24, NULL, 77, 1, 0, 3, 166.00, NULL, NULL, NULL, NULL, NULL, 1, 'immediate', NULL, NULL, '2026-03-04 14:17:32', '2026-03-04 16:39:37', NULL),
(80, 'Tên miền là gì? Cách mua và chọn nhà cung cấp tên miền', 'What Is a Domain Name? How to Buy and Choose a Domain Provider', '도메인이란 무엇인가? 도메인 구매 방법과 제공업체 선택', 'ドメインとは？ドメインの購入方法とプロバイダーの選び方', '什么是域名？如何购买并选择域名服务商', 'ten-mien-la-gi-cach-mua-va-chon-nha-cung-cap-ten-mien', 'what-is-a-domain-name-how-to-buy-and-choose-a-domain-provider', '도메인이란-무엇인가-도메인-구매-방법과-제공업체-선택', 'ドメインとはドメインの購入方法とプロバイダーの選び方', '什么是域名如何购买并选择域名服务商', 116, 24, NULL, 77, 1, 0, 4, 152.00, NULL, NULL, NULL, NULL, NULL, 1, 'immediate', NULL, NULL, '2026-03-04 14:18:06', '2026-03-04 16:39:59', NULL),
(81, 'Module 02: Deploy web lên hosting sử dụng Cpanel (Dùng các web PHP)', 'Module 02: Deploying Websites to Hosting Using cPanel (For PHP Websites)', '모듈 02: cPanel을 사용한 웹사이트 배포 (PHP 웹용)', 'モジュール 02: cPanelを使用したWebサイトのデプロイ（PHPサイト向け）', '模块 02：使用 cPanel 将网站部署到主机（适用于 PHP 网站）', 'module-02-deploy-web-len-hosting-su-dung-cpanel-dung-cac-web-php', 'module-02-deploying-websites-to-hosting-using-cpanel-for-php-websites', '모듈-02-cpanel을-사용한-웹사이트-배포-php-웹용', 'モジュール-02-cpanelを使用したwebサイトのデプロイphpサイト向け', '模块-02使用-cpanel-将网站部署到主机适用于-php-网站', NULL, 24, NULL, NULL, 0, 0, 5, 0.00, NULL, NULL, NULL, NULL, NULL, 1, 'immediate', NULL, NULL, '2026-03-04 14:19:07', '2026-03-04 14:19:07', NULL),
(82, 'Cách đăng ký và sở hữu hosting Cpanel miễn phí', 'How to Register and Get Free cPanel Hosting', '무료 cPanel 호스팅 등록 및 사용 방법', '無料cPanelホスティングの登録方法', '如何注册并获取免费的 cPanel 主机', 'cach-dang-ky-va-so-huu-hosting-cpanel-mien-phi', 'how-to-register-and-get-free-cpanel-hosting', '무료-cpanel-호스팅-등록-및-사용-방법', '無料cpanelホスティングの登録方法', '如何注册并获取免费的-cpanel-主机', 118, 24, NULL, 81, 0, 0, 6, 234.00, NULL, NULL, NULL, NULL, NULL, 1, 'immediate', NULL, NULL, '2026-03-04 14:19:38', '2026-03-04 16:40:50', NULL),
(83, 'Module 01: Giới thiệu và cài đặt', 'Module 01: Introduction and Installation', '모듈 01: 소개 및 설치', 'モジュール 01: 紹介とインストール', '模块 01：介绍与安装', 'module-01-gioi-thieu-va-cai-dat', 'module-01-introduction-and-installation', '모듈-01-소개-및-설치', 'モジュール-01-紹介とインストール', '模块-01介绍与安装', NULL, 23, NULL, NULL, 0, 0, 1, 0.00, NULL, NULL, NULL, NULL, NULL, 1, 'immediate', NULL, NULL, '2026-03-04 14:20:32', '2026-03-04 14:20:32', NULL),
(84, 'Giới thiệu về React Native và cách hoạt động', 'Introduction to React Native and How It Works', 'React Native 소개 및 작동 방식', 'React Nativeの紹介と仕組み', 'React Native 介绍及其工作原理', 'gioi-thieu-ve-react-native-va-cach-hoat-dong', 'introduction-to-react-native-and-how-it-works', 'react-native-소개-및-작동-방식', 'react-nativeの紹介と仕組み', 'react-native-介绍及其工作原理', 119, 23, NULL, 83, 1, 0, 2, 220.00, NULL, NULL, NULL, NULL, NULL, 1, 'immediate', NULL, NULL, '2026-03-04 14:21:10', '2026-03-04 16:45:09', NULL),
(85, 'Cài đặt môi trường và cài đặt React Native', 'Environment Setup and React Native Installation', '개발 환경 설정 및 React Native 설치', '開発環境の設定とReact Nativeのインストール', '开发环境配置与 React Native 安装', 'cai-dat-moi-truong-va-cai-dat-react-native', 'environment-setup-and-react-native-installation', '개발-환경-설정-및-react-native-설치', '開発環境の設定とreact-nativeのインストール', '开发环境配置与-react-native-安装', 120, 23, NULL, 83, 1, 0, 3, 165.00, NULL, NULL, NULL, NULL, NULL, 1, 'immediate', NULL, NULL, '2026-03-04 14:21:59', '2026-03-04 16:46:42', NULL),
(86, 'Chạy dự án trên thiết bị thật và thiết bị giả lập', 'Running the Project on Real Devices and Emulators', '실제 기기 및 에뮬레이터에서 프로젝트 실행', '実機およびエミュレーターでプロジェクトを実行', '在真实设备和模拟器上运行项目', 'chay-du-an-tren-thiet-bi-that-va-thiet-bi-gia-lap', 'running-the-project-on-real-devices-and-emulators', '실제-기기-및-에뮬레이터에서-프로젝트-실행', '実機およびエミュレーターでプロジェクトを実行', '在真实设备和模拟器上运行项目', 121, 23, NULL, 83, 1, 0, 4, 165.00, NULL, NULL, NULL, NULL, NULL, 1, 'immediate', NULL, NULL, '2026-03-04 14:22:44', '2026-03-04 16:48:01', NULL),
(87, 'Module 02: UI Component trong React Native', 'Module 02: UI Components in React Native', '모듈 02: React Native의 UI 컴포넌트', 'モジュール 02: React NativeのUIコンポーネント', '模块 02：React Native 的 UI 组件', 'module-02-ui-component-trong-react-native', 'module-02-ui-components-in-react-native', '모듈-02-react-native의-ui-컴포넌트', 'モジュール-02-react-nativeのuiコンポーネント', '模块-02react-native-的-ui-组件', NULL, 23, NULL, NULL, 0, 0, 5, 0.00, NULL, NULL, NULL, NULL, NULL, 1, 'immediate', NULL, NULL, '2026-03-04 14:23:20', '2026-03-04 14:23:20', NULL),
(88, 'Basic Component: View, Text', 'Basic Components: View, Text', '기본 컴포넌트: View, Text', '基本コンポーネント：View、Text', '基础组件：View、Text', 'basic-component-view-text', 'basic-components-view-text', '기본-컴포넌트-view-text', '基本コンポーネントviewtext', '基础组件viewtext', 122, 23, NULL, 87, 0, 1, 6, 149.00, NULL, NULL, NULL, NULL, NULL, 1, 'immediate', NULL, NULL, '2026-03-04 14:24:01', '2026-04-27 08:11:13', NULL),
(89, 'Module 01: Bắt đầu', 'Module 01: Getting Started', '모듈 01: 시작하기', 'モジュール 01: はじめに', '模块 01：开始', 'module-01-bat-dau', 'module-01-getting-started', '모듈-01-시작하기', 'モジュール-01-はじめに', '模块-01开始', NULL, 22, NULL, NULL, 0, 0, 1, 0.00, NULL, NULL, NULL, NULL, NULL, 1, 'immediate', NULL, NULL, '2026-03-04 14:25:32', '2026-03-04 14:25:32', NULL),
(90, 'Giới thiệu khóa học NestJS', 'Introduction to the NestJS Course', 'NestJS 강의 소개', 'NestJSコースの紹介', 'NestJS 课程介绍', 'gioi-thieu-khoa-hoc-nestjs', 'introduction-to-the-nestjs-course', 'nestjs-강의-소개', 'nestjsコースの紹介', 'nestjs-课程介绍', 123, 22, NULL, 89, 1, 0, 2, 173.00, NULL, NULL, NULL, NULL, NULL, 1, 'immediate', NULL, NULL, '2026-03-04 14:26:02', '2026-03-04 16:48:40', NULL),
(91, 'Chuẩn bị môi trường và cài đặt NestJS từ A đến Z', 'Environment Setup and NestJS Installation from A to Z', 'A부터 Z까지 NestJS 개발 환경 준비 및 설치', 'NestJSの開発環境準備とインストール（A〜Z）', '从 A 到 Z 准备环境并安装 NestJS', 'chuan-bi-moi-truong-va-cai-dat-nestjs-tu-a-den-z', 'environment-setup-and-nestjs-installation-from-a-to-z', 'a부터-z까지-nestjs-개발-환경-준비-및-설치', 'nestjsの開発環境準備とインストールaz', '从-a-到-z-准备环境并安装-nestjs', 124, 22, NULL, 89, 1, 0, 3, 212.00, NULL, NULL, NULL, NULL, NULL, 1, 'immediate', NULL, NULL, '2026-03-04 14:27:07', '2026-03-04 16:48:50', NULL),
(92, 'Module 02: Kiến thức nền tảng NestJS', 'Module 02: NestJS Fundamentals', '모듈 02: NestJS 기초 지식', 'モジュール 02: NestJS 基礎知識', '模块 02：NestJS 基础知识', 'module-02-kien-thuc-nen-tang-nestjs', 'module-02-nestjs-fundamentals', '모듈-02-nestjs-기초-지식', 'モジュール-02-nestjs-基礎知識', '模块-02nestjs-基础知识', NULL, 22, NULL, NULL, 0, 0, 4, 0.00, NULL, NULL, NULL, NULL, NULL, 1, 'immediate', NULL, NULL, '2026-03-04 14:27:49', '2026-03-04 14:27:49', NULL),
(93, 'Kiến trúc dự án NestJS', 'NestJS Project Architecture', 'NestJS 프로젝트 아키텍처', 'NestJSプロジェクトのアーキテクチャ', 'NestJS 项目架构', 'kien-truc-du-an-nestjs', 'nestjs-project-architecture', 'nestjs-프로젝트-아키텍처', 'nestjsプロジェクトのアーキテクチャ', 'nestjs-项目架构', 125, 22, NULL, 92, 0, 0, 5, 199.00, NULL, NULL, NULL, NULL, NULL, 1, 'immediate', NULL, NULL, '2026-03-04 14:28:17', '2026-03-04 16:49:03', NULL),
(94, 'Module 01: CRUD Laravel + NextJS', 'Module 01: CRUD with Laravel + NextJS', '모듈 01: Laravel + NextJS CRUD', 'モジュール 01: Laravel + NextJS CRUD', '模块 01：Laravel + NextJS CRUD', 'module-01-crud-laravel-nextjs', 'module-01-crud-with-laravel-nextjs', '모듈-01-laravel-nextjs-crud', 'モジュール-01-laravel-nextjs-crud', '模块-01laravel-nextjs-crud', NULL, 21, NULL, NULL, 0, 0, 1, 0.00, NULL, NULL, NULL, NULL, NULL, 1, 'immediate', NULL, NULL, '2026-03-04 14:29:08', '2026-03-04 14:29:08', NULL),
(95, 'Xây dựng ứng dụng Fullstack CRUD Laravel 11 + NextJS 14 (RESTful API, App Router)', 'Building a Fullstack CRUD App with Laravel 11 + NextJS 14 (RESTful API, App Router)', 'Laravel 11 + NextJS 14로 풀스택 CRUD 앱 만들기 (RESTful API, App Router)', 'Laravel 11 + NextJS 14でフルスタックCRUDアプリ開発（RESTful API、App Router）', '使用 Laravel 11 + NextJS 14 构建全栈 CRUD 应用（RESTful API、App Router）', 'xay-dung-ung-dung-fullstack-crud-laravel-11-nextjs-14-restful-api-app-router', 'building-a-fullstack-crud-app-with-laravel-11-nextjs-14-restful-api-app-router', 'laravel-11-nextjs-14로-풀스택-crud-앱-만들기-restful-api-app-router', 'laravel-11-nextjs-14でフルスタックcrudアプリ開発restful-apiapp-router', '使用-laravel-11-nextjs-14-构建全栈-crud-应用restful-apiapp-router', 126, 21, NULL, 94, 1, 0, 2, 338.00, NULL, NULL, NULL, NULL, NULL, 1, 'immediate', NULL, NULL, '2026-03-04 14:29:41', '2026-03-04 16:49:22', NULL),
(96, 'Tích hợp ReactJS vào dự án Laravel sử dụng InertiaJS (CRUD - Routing - Pagination - Link)', 'Integrating ReactJS into a Laravel Project Using InertiaJS (CRUD - Routing - Pagination - Links)', 'InertiaJS를 사용해 Laravel 프로젝트에 ReactJS 통합하기 (CRUD - 라우팅 - 페이지네이션 - 링크)', 'InertiaJSを使ってLaravelプロジェクトにReactJSを統合（CRUD・ルーティング・ページネーション・リンク）', '使用 InertiaJS 将 ReactJS 集成到 Laravel 项目中（CRUD - 路由 - 分页 - 链接）', 'tich-hop-reactjs-vao-du-an-laravel-su-dung-inertiajs-crud-routing-pagination-link', 'integrating-reactjs-into-a-laravel-project-using-inertiajs-crud-routing-pagination-links', 'inertiajs를-사용해-laravel-프로젝트에-reactjs-통합하기-crud-라우팅-페이지네이션-링크', 'inertiajsを使ってlaravelプロジェクトにreactjsを統合crudルーティングページネーションリンク', '使用-inertiajs-将-reactjs-集成到-laravel-项目中crud-路由-分页-链接', 127, 21, NULL, 94, 1, 0, 3, 338.00, NULL, NULL, NULL, NULL, NULL, 1, 'immediate', NULL, NULL, '2026-03-04 14:30:21', '2026-03-04 16:49:39', NULL),
(97, 'Xây dựng ứng dụng Single Page Application bằng Laravel Livewire (CRUD - Pagination - Filter)', 'Building a Single Page Application with Laravel Livewire (CRUD - Pagination - Filter)', 'Laravel Livewire로 싱글 페이지 애플리케이션 구축 (CRUD - 페이지네이션 - 필터)', 'Laravel Livewireでシングルページアプリ開発（CRUD・ページネーション・フィルター）', '使用 Laravel Livewire 构建单页应用（CRUD - 分页 - 筛选）', 'xay-dung-ung-dung-single-page-application-bang-laravel-livewire-crud-pagination-filter', 'building-a-single-page-application-with-laravel-livewire-crud-pagination-filter', 'laravel-livewire로-싱글-페이지-애플리케이션-구축-crud-페이지네이션-필터', 'laravel-livewireでシングルページアプリ開発crudページネーションフィルター', '使用-laravel-livewire-构建单页应用crud-分页-筛选', 128, 21, NULL, 94, 1, 0, 4, 192.00, NULL, NULL, NULL, NULL, NULL, 1, 'immediate', NULL, NULL, '2026-03-04 14:30:54', '2026-03-04 16:49:53', NULL),
(98, 'Module 02: Authentication - Authorization', 'Module 02: Authentication - Authorization', '모듈 02: 인증(Authentication) - 인가(Authorization)', 'モジュール 02: 認証（Authentication）・認可（Authorization）', '模块 02：身份验证（Authentication）与授权（Authorization）', 'module-02-authentication-authorization', 'module-02-authentication-authorization', '모듈-02-인증authentication-인가authorization', 'モジュール-02-認証authentication認可authorization', '模块-02身份验证authentication与授权authorization', NULL, 21, NULL, NULL, 0, 0, 5, 0.00, NULL, NULL, NULL, NULL, NULL, 1, 'immediate', NULL, NULL, '2026-03-04 14:31:26', '2026-03-04 14:31:26', NULL),
(99, 'Xây dựng ứng dụng Authentication Laravel 11 + NextJS 14 (RESTful API, App Router)', 'Building an Authentication App with Laravel 11 + NextJS 14 (RESTful API, App Router)', 'Laravel 11 + NextJS 14로 인증(Authentication) 앱 만들기 (RESTful API, App Router)', 'Laravel 11 + NextJS 14で認証アプリ開発（RESTful API、App Router）', '使用 Laravel 11 + NextJS 14 构建身份认证应用（RESTful API、App Router）', 'xay-dung-ung-dung-authentication-laravel-11-nextjs-14-restful-api-app-router', 'building-an-authentication-app-with-laravel-11-nextjs-14-restful-api-app-router', 'laravel-11-nextjs-14로-인증authentication-앱-만들기-restful-api-app-router', 'laravel-11-nextjs-14で認証アプリ開発restful-apiapp-router', '使用-laravel-11-nextjs-14-构建身份认证应用restful-apiapp-router', 130, 21, NULL, 98, 0, 0, 6, 289.00, NULL, NULL, NULL, NULL, NULL, 1, 'immediate', NULL, NULL, '2026-03-04 14:31:55', '2026-03-04 16:50:23', NULL),
(100, 'Module 01: Giới thiệu và cài đặt', 'Module 01: Introduction and Installation', '모듈 01: 소개 및 설치', 'モジュール 01: 紹介とインストール', '模块 01：介绍与安装', 'module-01-gioi-thieu-va-cai-dat', 'module-01-introduction-and-installation', '모듈-01-소개-및-설치', 'モジュール-01-紹介とインストール', '模块-01介绍与安装', NULL, 20, NULL, NULL, 0, 0, 1, 0.00, NULL, NULL, NULL, NULL, NULL, 1, 'immediate', NULL, NULL, '2026-03-04 14:34:42', '2026-03-04 14:34:42', NULL),
(101, 'Giới thiệu khóa học và thành quả sau khi hoàn thành', 'Course Introduction and Outcomes After Completion', '강의 소개 및 수강 후 성과', 'コース紹介と修了後に得られる成果', '课程介绍与完成后的成果', 'gioi-thieu-khoa-hoc-va-thanh-qua-sau-khi-hoan-thanh', 'course-introduction-and-outcomes-after-completion', '강의-소개-및-수강-후-성과', 'コース紹介と修了後に得られる成果', '课程介绍与完成后的成果', 131, 20, NULL, 100, 1, 0, 2, 202.00, NULL, NULL, NULL, NULL, NULL, 1, 'immediate', NULL, NULL, '2026-03-04 14:35:32', '2026-03-04 16:50:57', NULL),
(102, 'Tổng quan về Headless CMS và Strapi CMS', 'Overview of Headless CMS and Strapi CMS', 'Headless CMS와 Strapi CMS 개요', 'Headless CMS と Strapi CMS の概要', 'Headless CMS 与 Strapi CMS 概述', 'tong-quan-ve-headless-cms-va-strapi-cms', 'overview-of-headless-cms-and-strapi-cms', 'headless-cms와-strapi-cms-개요', 'headless-cms-と-strapi-cms-の概要', 'headless-cms-与-strapi-cms-概述', 132, 20, NULL, 100, 1, 0, 3, 166.00, NULL, NULL, NULL, NULL, NULL, 1, 'immediate', NULL, NULL, '2026-03-04 14:36:00', '2026-03-04 16:51:09', NULL),
(103, 'Cài đặt Strapi CMS và tìm hiểu cấu trúc folder Strapi', 'Installing Strapi CMS and Exploring the Strapi Folder Structure', 'Strapi CMS 설치 및 폴더 구조 이해', 'Strapi CMSのインストールとフォルダ構造の理解', '安装 Strapi CMS 并了解 Strapi 的文件夹结构', 'cai-dat-strapi-cms-va-tim-hieu-cau-truc-folder-strapi', 'installing-strapi-cms-and-exploring-the-strapi-folder-structure', 'strapi-cms-설치-및-폴더-구조-이해', 'strapi-cmsのインストールとフォルダ構造の理解', '安装-strapi-cms-并了解-strapi-的文件夹结构', 133, 20, NULL, 100, 1, 0, 4, 221.00, NULL, NULL, NULL, NULL, NULL, 1, 'immediate', NULL, NULL, '2026-03-04 14:37:19', '2026-03-04 16:51:25', NULL),
(104, 'Module 02: Làm quen với Strapi', 'Module 02: Getting Started with Strapi', '모듈 02: Strapi 시작하기', 'モジュール 02: Strapi入門', '模块 02：Strapi 入门', 'module-02-lam-quen-voi-strapi', 'module-02-getting-started-with-strapi', '모듈-02-strapi-시작하기', 'モジュール-02-strapi入門', '模块-02strapi-入门', NULL, 20, NULL, NULL, 0, 0, 5, 0.00, NULL, NULL, NULL, NULL, NULL, 1, 'immediate', NULL, NULL, '2026-03-04 14:37:51', '2026-03-04 14:37:51', NULL),
(105, 'Module 01: Giới thiệu tổng quan', 'Module 01: General Overview', '모듈 01: 전체 개요', 'モジュール 01: 概要紹介', '模块 01：总体概述', 'module-01-gioi-thieu-tong-quan', 'module-01-general-overview', '모듈-01-전체-개요', 'モジュール-01-概要紹介', '模块-01总体概述', NULL, 19, NULL, NULL, 0, 0, 1, 0.00, NULL, NULL, NULL, NULL, NULL, 1, 'immediate', NULL, NULL, '2026-03-04 14:43:51', '2026-03-04 14:43:51', NULL),
(106, 'Tổng quan về NodeJS', 'Overview of NodeJS', 'NodeJS 개요', 'NodeJSの概要', 'NodeJS 概述', 'tong-quan-ve-nodejs', 'overview-of-nodejs', 'nodejs-개요', 'nodejsの概要', 'nodejs-概述', 135, 19, NULL, 105, 1, 0, 2, 221.00, NULL, NULL, NULL, NULL, NULL, 1, 'immediate', NULL, NULL, '2026-03-04 14:48:18', '2026-03-04 16:53:07', NULL),
(107, 'Cài đặt môi trường phát triển NodeJS', 'Setting Up the NodeJS Development Environment', 'NodeJS 개발 환경 설정', 'NodeJS開発環境のセットアップ', 'NodeJS 开发环境配置', 'cài-dặt-môi-trường-phát-triển-nodejs', 'setting-up-the-nodejs-development-environment', 'nodejs-개발-환경-설정', 'nodejs開発環境のセットアップ', 'nodejs-开发环境配置', 136, 19, NULL, 105, 1, 0, 3, 154.00, NULL, NULL, NULL, NULL, NULL, 1, 'immediate', NULL, NULL, '2026-03-04 14:48:48', '2026-03-04 16:53:21', NULL),
(108, 'Module 02: Module System & Các Modules Cốt lõi', 'Module 02: Module System & Core Modules', '모듈 02: 모듈 시스템 및 핵심 모듈', 'モジュール 02: モジュールシステムとコアモジュール', '模块 02：模块系统与核心模块', 'module-02-module-system-cac-modules-cot-loi', 'module-02-module-system-core-modules', '모듈-02-모듈-시스템-및-핵심-모듈', 'モジュール-02-モジュールシステムとコアモジュール', '模块-02模块系统与核心模块', NULL, 19, NULL, NULL, 1, 0, 4, 0.00, NULL, NULL, NULL, NULL, NULL, 1, 'immediate', NULL, NULL, '2026-03-04 14:49:45', '2026-03-04 14:49:45', NULL),
(109, 'CommonJS và ES Modules trong NodeJS', 'CommonJS and ES Modules in NodeJS', 'NodeJS의 CommonJS와 ES Modules', 'NodeJSにおけるCommonJSとES Modules', 'NodeJS 中的 CommonJS 与 ES Modules', 'commonjs-và-es-modules-trong-nodejs', 'commonjs-and-es-modules-in-nodejs', 'nodejs의-commonjs와-es-modules', 'nodejsにおけるcommonjsとes-modules', 'nodejs-中的-commonjs-与-es-modules', 137, 19, NULL, 108, 0, 0, 5, 174.00, NULL, NULL, NULL, NULL, NULL, 1, 'immediate', NULL, NULL, '2026-03-04 14:50:16', '2026-03-04 16:53:34', NULL),
(110, 'Module 01: Kiến thức căn bản', 'Module 01: Basic Concepts', '모듈 01: 기본 지식', 'モジュール 01: 基礎知識', '模块 01：基础知识', 'module-01-kien-thuc-can-ban', 'module-01-basic-concepts', '모듈-01-기본-지식', 'モジュール-01-基礎知識', '模块-01基础知识', NULL, 18, NULL, NULL, 0, 0, 1, 0.00, NULL, NULL, NULL, NULL, NULL, 1, 'immediate', NULL, NULL, '2026-03-04 14:51:03', '2026-03-04 14:51:03', NULL),
(111, 'Tổng quan về Laravel Livewire - Xây dựng Component đầu tiên bằng Livewire', 'Overview of Laravel Livewire – Building Your First Livewire Component', 'Laravel Livewire 개요 - 첫 번째 Livewire 컴포넌트 만들기', 'Laravel Livewireの概要 - 最初のLivewireコンポーネント作成', 'Laravel Livewire 概述 - 构建第一个 Livewire 组件', 'tong-quan-ve-laravel-livewire-xay-dung-component-dau-tien-bang-livewire', 'overview-of-laravel-livewire-building-your-first-livewire-component', 'laravel-livewire-개요-첫-번째-livewire-컴포넌트-만들기', 'laravel-livewireの概要-最初のlivewireコンポーネント作成', 'laravel-livewire-概述-构建第一个-livewire-组件', 138, 18, NULL, 110, 1, 0, 2, 227.00, NULL, NULL, NULL, NULL, NULL, 1, 'immediate', NULL, NULL, '2026-03-04 14:51:42', '2026-03-04 16:53:54', NULL),
(112, 'Component trong Laravel Livewire - Phần 1', 'Components in Laravel Livewire - Part 1', 'Laravel Livewire의 컴포넌트 - 1부', 'Laravel Livewireのコンポーネント - パート1', 'Laravel Livewire 组件 - 第1部分', 'component-trong-laravel-livewire-phan-1', 'components-in-laravel-livewire-part-1', 'laravel-livewire의-컴포넌트-1부', 'laravel-livewireのコンポーネント-パート1', 'laravel-livewire-组件-第1部分', 139, 18, NULL, 110, 1, 0, 3, 244.00, NULL, NULL, NULL, NULL, NULL, 1, 'immediate', NULL, NULL, '2026-03-04 14:52:21', '2026-03-04 16:54:09', NULL),
(113, 'Module 02: Form & Action', 'Module 02: Form & Action', '모듈 02: 폼(Form) 및 액션(Action)', 'モジュール 02: フォームとアクション', '模块 02：表单与操作', 'module-02-form-action', 'module-02-form-action', '모듈-02-폼form-및-액션action', 'モジュール-02-フォームとアクション', '模块-02表单与操作', NULL, 18, NULL, NULL, 0, 0, 4, 0.00, NULL, NULL, NULL, NULL, NULL, 1, 'immediate', NULL, NULL, '2026-03-04 14:52:51', '2026-03-04 14:52:51', NULL),
(114, 'Kỹ thuật xử lý Form trong Livewire', 'Form Handling Techniques in Livewire', 'Livewire에서 폼 처리 기법', 'Livewireでのフォーム処理テクニック', 'Livewire 表单处理技巧', 'ky-thuat-xu-ly-form-trong-livewire', 'form-handling-techniques-in-livewire', 'livewire에서-폼-처리-기법', 'livewireでのフォーム処理テクニック', 'livewire-表单处理技巧', 140, 18, NULL, 113, 0, 0, 5, 177.00, NULL, NULL, NULL, NULL, NULL, 1, 'immediate', NULL, NULL, '2026-03-04 14:53:24', '2026-03-04 16:54:22', NULL),
(115, 'Module 01: Nhập môn lập trình Laravel Framewor', 'Module 01: Introduction to Laravel Framework Programming', '모듈 01: Laravel Framework 프로그래밍 입문', 'モジュール 01: Laravelフレームワークプログラミング入門', '模块 01：Laravel 框架编程入门', 'module-01-nhap-mon-lap-trinh-laravel-framewor', 'module-01-introduction-to-laravel-framework-programming', '모듈-01-laravel-framework-프로그래밍-입문', 'モジュール-01-laravelフレームワークプログラミング入門', '模块-01laravel-框架编程入门', NULL, 17, NULL, NULL, 0, 0, 1, 0.00, NULL, NULL, NULL, NULL, NULL, 1, 'immediate', NULL, NULL, '2026-03-04 14:54:25', '2026-03-04 14:54:25', NULL),
(116, 'Giới thiệu Laravel Framework - Cài đặt Laravel', 'Introduction to the Laravel Framework – Installing Laravel', 'Laravel Framework 소개 - Laravel 설치', 'Laravelフレームワークの紹介 - Laravelのインストール', 'Laravel 框架介绍 - 安装 Laravel', 'gioi-thieu-laravel-framework-cai-dat-laravel', 'introduction-to-the-laravel-framework-installing-laravel', 'laravel-framework-소개-laravel-설치', 'laravelフレームワークの紹介-laravelのインストール', 'laravel-框架介绍-安装-laravel', 141, 17, NULL, 115, 1, 0, 2, 159.00, NULL, NULL, NULL, NULL, NULL, 1, 'immediate', NULL, NULL, '2026-03-04 14:54:55', '2026-03-04 16:54:44', NULL),
(117, 'Cấu trúc thư mục và luồng Request trong Laravel Framework', 'Folder Structure and Request Flow in Laravel Framework', 'Laravel Framework의 폴더 구조와 요청 흐름', 'Laravelフレームワークのフォルダ構造とリクエストフロー', 'Laravel 框架的目录结构与请求流程', 'cau-truc-thu-muc-va-luong-request-trong-laravel-framework', 'folder-structure-and-request-flow-in-laravel-framework', 'laravel-framework의-폴더-구조와-요청-흐름', 'laravelフレームワークのフォルダ構造とリクエストフロー', 'laravel-框架的目录结构与请求流程', 142, 17, NULL, 115, 1, 0, 3, 267.00, NULL, NULL, NULL, NULL, NULL, 1, 'immediate', NULL, NULL, '2026-03-04 14:55:56', '2026-03-04 16:54:58', NULL),
(118, 'Thiết lập cấu hình cần thiết cho Laravel', 'Setting Up Essential Configuration for Laravel', 'Laravel 필수 설정 구성', 'Laravelの必須設定の構成', '配置 Laravel 的必要设置', 'thiet-lap-cau-hinh-can-thiet-cho-laravel', 'setting-up-essential-configuration-for-laravel', 'laravel-필수-설정-구성', 'laravelの必須設定の構成', '配置-laravel-的必要设置', 143, 17, NULL, 115, 1, 0, 4, 220.00, NULL, NULL, NULL, NULL, NULL, 1, 'immediate', NULL, NULL, '2026-03-04 14:56:30', '2026-03-04 16:55:10', NULL),
(119, 'Module 02: Lập trình Laravel Framework cơ bản', 'Module 02: Basic Laravel Framework Programming', '모듈 02: Laravel Framework 기초 프로그래밍', 'モジュール 02: Laravelフレームワーク基礎プログラミング', '模块 02：Laravel 框架基础编程', 'module-02-lap-trinh-laravel-framework-co-ban', 'module-02-basic-laravel-framework-programming', '모듈-02-laravel-framework-기초-프로그래밍', 'モジュール-02-laravelフレームワーク基礎プログラミング', '模块-02laravel-框架基础编程', NULL, 17, NULL, NULL, 0, 0, 5, 0.00, NULL, NULL, NULL, NULL, NULL, 1, 'immediate', NULL, NULL, '2026-03-04 14:57:37', '2026-03-04 14:57:37', NULL),
(120, 'Route trong Laravel - Phần 1', 'Routes in Laravel - Part 1', 'Laravel의 라우트(Route) - 1부', 'Laravelのルート(Route) - パート1', 'Laravel 路由 - 第1部分', 'route-trong-laravel-phan-1', 'routes-in-laravel-part-1', 'laravel의-라우트route-1부', 'laravelのルートroute-パート1', 'laravel-路由-第1部分', 144, 17, NULL, 119, 0, 0, 6, 269.00, NULL, NULL, NULL, NULL, NULL, 1, 'immediate', NULL, NULL, '2026-03-04 14:58:27', '2026-03-04 16:55:24', NULL),
(121, 'Route trong Laravel - Phần 2', 'Routes in Laravel - Part 2', 'Laravel의 라우트(Route) - 2부', 'Laravelのルート(Route) - パート2', 'Laravel 路由 - 第2部分', 'route-trong-laravel-phan-2', 'routes-in-laravel-part-2', 'laravel의-라우트route-2부', 'laravelのルートroute-パート2', 'laravel-路由-第2部分', 145, 17, NULL, 119, 0, 0, 7, 185.00, NULL, NULL, NULL, NULL, NULL, 1, 'immediate', NULL, NULL, '2026-03-04 14:58:59', '2026-03-04 16:55:37', NULL),
(122, 'Module 01: Biểu thức chính quy (Regular Expression)', 'Module 01: Regular Expressions (Regex)', '모듈 01: 정규 표현식 (Regular Expression)', 'モジュール 01: 正規表現 (Regular Expression)', '模块 01：正则表达式 (Regular Expression)', 'module-01-bieu-thuc-chinh-quy-regular-expression', 'module-01-regular-expressions-regex', '모듈-01-정규-표현식-regular-expression', 'モジュール-01-正規表現-regular-expression', '模块-01正则表达式-regular-expression', NULL, 16, NULL, NULL, 0, 0, 1, 0.00, NULL, NULL, NULL, NULL, NULL, 1, 'immediate', NULL, NULL, '2026-03-04 15:00:17', '2026-03-04 15:00:17', NULL),
(123, 'Regular Expression là gì? Ý nghĩa của Regular Expression', 'What is a Regular Expression? The Meaning and Purpose of Regular Expressions', '정규 표현식이란 무엇인가? 정규 표현식의 의미와 역할', '正規表現とは？正規表現の意味と役割', '什么是正则表达式？正则表达式的含义与作用', 'regular-expression-la-gi-y-nghia-cua-regular-expression', 'what-is-a-regular-expression-the-meaning-and-purpose-of-regular-expressions', '정규-표현식이란-무엇인가-정규-표현식의-의미와-역할', '正規表現とは正規表現の意味と役割', '什么是正则表达式正则表达式的含义与作用', 146, 16, NULL, 122, 1, 0, 2, 159.00, NULL, NULL, NULL, NULL, NULL, 1, 'immediate', NULL, NULL, '2026-03-04 15:01:03', '2026-03-04 16:56:24', NULL),
(124, 'Website kiểm tra Regular Expression', 'Websites for Testing Regular Expressions', '정규 표현식 테스트 웹사이트', '正規表現をテストするためのウェブサイト', '正则表达式测试网站', 'website-kiem-tra-regular-expression', 'websites-for-testing-regular-expressions', '정규-표현식-테스트-웹사이트', '正規表現をテストするためのウェブサイト', '正则表达式测试网站', 147, 16, NULL, 122, 1, 0, 3, 180.00, NULL, NULL, NULL, NULL, NULL, 1, 'immediate', NULL, NULL, '2026-03-04 15:01:36', '2026-03-04 16:56:38', NULL),
(125, 'Module 02: Lập trình hướng đối tượng (OOP)', 'Module 02: Object-Oriented Programming (OOP)', '모듈 02: 객체 지향 프로그래밍 (OOP)', 'モジュール 02: オブジェクト指向プログラミング (OOP)', '模块 02：面向对象编程 (OOP)', 'module-02-lap-trinh-huong-doi-tuong-oop', 'module-02-object-oriented-programming-oop', '모듈-02-객체-지향-프로그래밍-oop', 'モジュール-02-オブジェクト指向プログラミング-oop', '模块-02面向对象编程-oop', NULL, 16, NULL, NULL, 0, 0, 4, 0.00, NULL, NULL, NULL, NULL, NULL, 1, 'immediate', NULL, NULL, '2026-03-04 15:02:06', '2026-03-04 15:02:06', NULL),
(126, 'Tổng quan về lập trình truyền thống và lập trình hướng đối tượng', 'Overview of Procedural Programming and Object-Oriented Programming', '절차적 프로그래밍과 객체 지향 프로그래밍 개요', '手続き型プログラミングとオブジェクト指向プログラミングの概要', '传统编程与面向对象编程概述', 'tong-quan-ve-lap-trinh-truyen-thong-va-lap-trinh-huong-doi-tuong', 'overview-of-procedural-programming-and-object-oriented-programming', '절차적-프로그래밍과-객체-지향-프로그래밍-개요', '手続き型プログラミングとオブジェクト指向プログラミングの概要', '传统编程与面向对象编程概述', 148, 16, NULL, 125, 0, 0, 5, 226.00, NULL, NULL, NULL, NULL, NULL, 1, 'immediate', NULL, NULL, '2026-03-04 15:02:45', '2026-03-04 16:56:51', NULL),
(127, 'Module 02: PHP căn bản', 'Module 02: Basic PHP', '모듈 02: PHP 기초', 'モジュール 02: PHP 基礎', '模块 02：PHP 基础', 'module-02-php-can-ban', 'module-02-basic-php', '모듈-02-php-기초', 'モジュール-02-php-基礎', '模块-02php-基础', NULL, 15, NULL, NULL, 0, 0, 5, 0.00, NULL, NULL, NULL, NULL, NULL, 1, 'immediate', NULL, NULL, '2026-03-04 15:07:46', '2026-03-04 15:07:46', NULL),
(128, 'Kiểm tra thông tin PHP - phpinfo()', 'Checking PHP Information – phpinfo()', 'PHP 정보 확인 – phpinfo()', 'PHP情報の確認 – phpinfo()', '查看 PHP 信息 – phpinfo()', 'kiem-tra-thong-tin-php-phpinfo', 'checking-php-information-phpinfo', 'php-정보-확인-phpinfo', 'php情報の確認-phpinfo', '查看-php-信息-phpinfo', 150, 15, NULL, 127, 0, 1, 6, 192.00, NULL, NULL, NULL, NULL, NULL, 1, 'immediate', NULL, NULL, '2026-03-04 15:09:18', '2026-03-04 16:57:21', NULL),
(129, 'Biến - Comment - Debug trong PHP', 'Variables, Comments, and Debugging in PHP', 'PHP에서 변수, 주석, 디버깅', 'PHPの変数・コメント・デバッグ', 'PHP 中的变量、注释与调试', 'bien-comment-debug-trong-php', 'variables-comments-and-debugging-in-php', 'php에서-변수-주석-디버깅', 'phpの変数コメントデバッグ', 'php-中的变量注释与调试', 152, 15, NULL, 127, 0, 0, 7, 190.00, NULL, NULL, NULL, NULL, NULL, 1, 'immediate', NULL, NULL, '2026-03-04 15:09:51', '2026-03-04 16:58:30', NULL),
(130, 'Module 01: TypeScript cơ bản', 'Module 01: Basic TypeScript', '모듈 01: TypeScript 기초', 'モジュール 01: TypeScript 基礎', '模块 01：TypeScript 基础', 'module-01-typescript-co-ban', 'module-01-basic-typescript', '모듈-01-typescript-기초', 'モジュール-01-typescript-基礎', '模块-01typescript-基础', NULL, 14, NULL, NULL, 0, 0, 1, 0.00, NULL, NULL, NULL, NULL, NULL, 1, 'immediate', NULL, NULL, '2026-03-04 15:12:01', '2026-03-04 15:12:01', NULL),
(131, 'Tổng quan về TypeScript - Cài đặt môi trường chạy ứng dụng TypeScript', 'Overview of TypeScript – Setting Up the Environment to Run TypeScript Applications', 'TypeScript 개요 - TypeScript 실행 환경 설정', 'TypeScriptの概要 - TypeScript実行環境のセットアップ', 'TypeScript 概述 - 配置 TypeScript 运行环境', 'tong-quan-ve-typescript-cai-dat-moi-truong-chay-ung-dung-typescript', 'overview-of-typescript-setting-up-the-environment-to-run-typescript-applications', 'typescript-개요-typescript-실행-환경-설정', 'typescriptの概要-typescript実行環境のセットアップ', 'typescript-概述-配置-typescript-运行环境', 153, 14, NULL, 130, 1, 0, 2, 148.00, NULL, NULL, NULL, NULL, NULL, 1, 'immediate', NULL, NULL, '2026-03-04 15:13:15', '2026-03-04 16:58:49', NULL),
(132, 'Cấu hình Output và Prettier để tự động Format code', 'Configuring Output and Prettier for Automatic Code Formatting', '자동 코드 포맷팅을 위한 Output 및 Prettier 설정', '自動コードフォーマットのためのOutputとPrettierの設定', '配置 Output 和 Prettier 实现代码自动格式化', 'cau-hinh-output-va-prettier-de-tu-dong-format-code', 'configuring-output-and-prettier-for-automatic-code-formatting', '자동-코드-포맷팅을-위한-output-및-prettier-설정', '自動コードフォーマットのためのoutputとprettierの設定', '配置-output-和-prettier-实现代码自动格式化', 154, 14, NULL, 130, 1, 0, 3, 186.00, NULL, NULL, NULL, NULL, NULL, 1, 'immediate', NULL, NULL, '2026-03-04 15:13:51', '2026-03-04 16:59:03', NULL),
(133, 'Module 02: Lập trình hướng đối tượng (OOP)', 'Module 02: Object-Oriented Programming (OOP)', '모듈 02: 객체 지향 프로그래밍 (OOP)', 'モジュール 02: オブジェクト指向プログラミング (OOP)', '模块 02：面向对象编程 (OOP)', 'module-02-lap-trinh-huong-doi-tuong-oop', 'module-02-object-oriented-programming-oop', '모듈-02-객체-지향-프로그래밍-oop', 'モジュール-02-オブジェクト指向プログラミング-oop', '模块-02面向对象编程-oop', NULL, 14, NULL, NULL, 0, 0, 4, 0.00, NULL, NULL, NULL, NULL, NULL, 1, 'immediate', NULL, NULL, '2026-03-04 15:14:30', '2026-03-04 15:14:30', NULL),
(134, 'Tư duy lập trình hướng đối tượng', 'Object-Oriented Programming Mindset', '객체 지향 프로그래밍 사고방식', 'オブジェクト指向プログラミングの思考', '面向对象编程思维', 'tu-duy-lap-trinh-huong-doi-tuong', 'object-oriented-programming-mindset', '객체-지향-프로그래밍-사고방식', 'オブジェクト指向プログラミングの思考', '面向对象编程思维', 155, 14, NULL, 133, 0, 0, 5, 188.00, NULL, NULL, NULL, NULL, NULL, 1, 'immediate', NULL, NULL, '2026-03-04 15:15:32', '2026-03-04 16:59:16', NULL),
(135, 'Module 01: Bắt đầu', 'Module 01: Getting Started', '모듈 01: 시작하기', 'モジュール 01: はじめに', '模块 01：开始', 'module-01-bat-dau', 'module-01-getting-started', '모듈-01-시작하기', 'モジュール-01-はじめに', '模块-01开始', NULL, 13, NULL, NULL, 0, 0, 1, 0.00, NULL, NULL, NULL, NULL, NULL, 1, 'immediate', NULL, NULL, '2026-03-04 15:17:28', '2026-03-04 15:17:28', NULL),
(136, 'Tổng quan về khóa học NextJS + TypeScript', 'Overview of the NextJS + TypeScript Course', 'NextJS + TypeScript 강의 개요', 'NextJS + TypeScriptコースの概要', 'NextJS + TypeScript 课程概述', 'tong-quan-ve-khoa-hoc-nextjs-typescript', 'overview-of-the-nextjs-typescript-course', 'nextjs-typescript-강의-개요', 'nextjs-typescriptコースの概要', 'nextjs-typescript-课程概述', 156, 13, NULL, 135, 1, 0, 2, 107.00, NULL, NULL, NULL, NULL, NULL, 1, 'immediate', NULL, NULL, '2026-03-04 15:17:58', '2026-03-04 16:59:43', NULL),
(137, 'Kiến thức cần chuẩn bị trước khi học NextJS', 'Prerequisites Before Learning NextJS', 'NextJS 학습 전에 준비해야 할 지식', 'NextJSを学ぶ前に必要な知識', '学习 NextJS 前需要准备的知识', 'kien-thuc-can-chuan-bi-truoc-khi-hoc-nextjs', 'prerequisites-before-learning-nextjs', 'nextjs-학습-전에-준비해야-할-지식', 'nextjsを学ぶ前に必要な知識', '学习-nextjs-前需要准备的知识', 157, 13, NULL, 135, 1, 0, 3, 203.00, NULL, NULL, NULL, NULL, NULL, 1, 'immediate', NULL, NULL, '2026-03-04 16:16:33', '2026-03-04 16:59:57', NULL),
(138, 'Module 02: TypeScript cơ bản', 'Module 02: Basic TypeScript', '모듈 02: TypeScript 기초', 'モジュール 02: TypeScript 基礎', '模块 02：TypeScript 基础', 'module-02-typescript-co-ban', 'module-02-basic-typescript', '모듈-02-typescript-기초', 'モジュール-02-typescript-基礎', '模块-02typescript-基础', NULL, 13, NULL, NULL, 0, 0, 4, 0.00, NULL, NULL, NULL, NULL, NULL, 1, 'immediate', NULL, NULL, '2026-03-04 16:17:27', '2026-03-04 16:17:27', NULL),
(139, 'Tổng quan về TypeScript - Cài đặt môi trường chạy ứng dụng TypeScript', 'Overview of TypeScript – Setting Up the Environment to Run TypeScript Applications', 'TypeScript 개요 - TypeScript 실행 환경 설정', 'TypeScriptの概要 - TypeScript実行環境のセットアップ', 'TypeScript 概述 - 配置 TypeScript 运行环境', 'tong-quan-ve-typescript-cai-dat-moi-truong-chay-ung-dung-typescript', 'overview-of-typescript-setting-up-the-environment-to-run-typescript-applications', 'typescript-개요-typescript-실행-환경-설정', 'typescriptの概要-typescript実行環境のセットアップ', 'typescript-概述-配置-typescript-运行环境', 158, 13, NULL, 138, 0, 0, 5, 194.00, NULL, NULL, NULL, NULL, NULL, 1, 'immediate', NULL, NULL, '2026-03-04 16:18:02', '2026-03-04 17:00:10', NULL),
(140, 'Module 01: Cài đặt và kiến thức căn bản', 'Module 01: Installation and Basic Concepts', '모듈 01: 설치 및 기본 지식', 'モジュール 01: インストールと基礎知識', '模块 01：安装与基础知识', 'module-01-cai-dat-va-kien-thuc-can-ban', 'module-01-installation-and-basic-concepts', '모듈-01-설치-및-기본-지식', 'モジュール-01-インストールと基礎知識', '模块-01安装与基础知识', NULL, 12, NULL, NULL, 0, 0, 1, 0.00, NULL, NULL, NULL, NULL, NULL, 1, 'immediate', NULL, NULL, '2026-03-04 16:18:52', '2026-03-04 16:18:52', NULL),
(141, 'Kiến thức cần chuẩn bị trước khi học ReactJS', 'Prerequisites Before Learning ReactJS', 'ReactJS 학습 전에 준비해야 할 지식', 'ReactJSを学ぶ前に必要な知識', '学习 ReactJS 前需要准备的知识', 'kien-thuc-can-chuan-bi-truoc-khi-hoc-reactjs', 'prerequisites-before-learning-reactjs', 'reactjs-학습-전에-준비해야-할-지식', 'reactjsを学ぶ前に必要な知識', '学习-reactjs-前需要准备的知识', 159, 12, NULL, 140, 1, 1, 2, 195.00, NULL, NULL, NULL, NULL, NULL, 1, 'immediate', NULL, NULL, '2026-03-04 16:19:19', '2026-04-27 09:01:25', NULL),
(142, 'Tổng quan và hướng dẫn cài đặt ReactJS', 'Overview and Installation Guide for ReactJS', 'ReactJS 개요 및 설치 가이드', 'ReactJSの概要とインストールガイド', 'ReactJS 概述与安装指南', 'tong-quan-va-huong-dan-cai-dat-reactjs', 'overview-and-installation-guide-for-reactjs', 'reactjs-개요-및-설치-가이드', 'reactjsの概要とインストールガイド', 'reactjs-概述与安装指南', 160, 12, NULL, 140, 1, 0, 3, 188.00, NULL, NULL, NULL, NULL, NULL, 1, 'immediate', NULL, NULL, '2026-03-04 16:19:51', '2026-03-04 17:01:48', NULL),
(143, 'Module 02: Fetch API trong React JS', 'Module 02: Fetch API in ReactJS', '모듈 02: ReactJS에서 Fetch API', 'モジュール 02: ReactJSのFetch API', '模块 02：ReactJS 中的 Fetch API', 'module-02-fetch-api-trong-react-js', 'module-02-fetch-api-in-reactjs', '모듈-02-reactjs에서-fetch-api', 'モジュール-02-reactjsのfetch-api', '模块-02reactjs-中的-fetch-api', NULL, 12, NULL, NULL, 0, 0, 4, 0.00, NULL, NULL, NULL, NULL, NULL, 1, 'immediate', NULL, NULL, '2026-03-04 16:21:33', '2026-03-04 16:21:41', NULL),
(144, 'Danh sách API miễn phí cho anh em học Front-End', 'List of Free APIs for Front-End Learners', '프론트엔드 학습자를 위한 무료 API 목록', 'フロントエンド学習者向けの無料API一覧', '适合前端学习者的免费 API 列表', 'danh-sach-api-mien-phi-cho-anh-em-hoc-front-end', 'list-of-free-apis-for-front-end-learners', '프론트엔드-학습자를-위한-무료-api-목록', 'フロントエンド学習者向けの無料api一覧', '适合前端学习者的免费-api-列表', 161, 12, NULL, 143, 0, 2, 5, 232.00, NULL, NULL, NULL, NULL, NULL, 1, 'immediate', NULL, NULL, '2026-03-04 16:22:16', '2026-04-27 09:01:40', NULL),
(145, 'Module 01: Giới thiệu JavaScript', 'Module 01: Introduction to JavaScript', '모듈 01: JavaScript 소개', 'モジュール 01: JavaScriptの紹介', '模块 01：JavaScript 介绍', 'module-01-gioi-thieu-javascript', 'module-01-introduction-to-javascript', '모듈-01-javascript-소개', 'モジュール-01-javascriptの紹介', '模块-01javascript-介绍', NULL, 11, NULL, NULL, 0, 0, 1, 0.00, NULL, NULL, NULL, NULL, NULL, 1, 'immediate', NULL, NULL, '2026-03-04 16:23:13', '2026-03-04 16:23:13', NULL),
(146, 'Giới thiệu ngôn ngữ lập trình JavaScript', 'Introduction to the JavaScript Programming Language', 'JavaScript 프로그래밍 언어 소개', 'JavaScriptプログラミング言語の紹介', 'JavaScript 编程语言介绍', 'gioi-thieu-ngon-ngu-lap-trinh-javascript', 'introduction-to-the-javascript-programming-language', 'javascript-프로그래밍-언어-소개', 'javascriptプログラミング言語の紹介', 'javascript-编程语言介绍', 162, 11, NULL, 145, 1, 4, 2, 196.00, NULL, NULL, NULL, NULL, NULL, 1, 'immediate', NULL, NULL, '2026-03-04 16:23:41', '2026-05-27 08:17:49', NULL),
(147, 'Công cụ cần chuẩn bị trước khi học JavaScript', 'Tools to Prepare Before Learning JavaScript', 'JavaScript 학습 전에 준비해야 할 도구', 'JavaScriptを学ぶ前に準備するツール', '学习 JavaScript 前需要准备的工具', 'cong-cu-can-chuan-bi-truoc-khi-hoc-javascript', 'tools-to-prepare-before-learning-javascript', 'javascript-학습-전에-준비해야-할-도구', 'javascriptを学ぶ前に準備するツール', '学习-javascript-前需要准备的工具', 163, 11, NULL, 145, 1, 1, 3, 298.00, NULL, NULL, NULL, NULL, NULL, 1, 'immediate', NULL, NULL, '2026-03-04 16:24:24', '2026-04-27 08:10:31', NULL),
(148, 'Dev Tools là gì? Cách làm việc với Chrome Dev Tools', 'What Are Dev Tools? How to Work with Chrome DevTools', 'Dev Tools란 무엇인가? Chrome DevTools 사용 방법', 'DevToolsとは？Chrome DevToolsの使い方', '什么是 Dev Tools？如何使用 Chrome DevTools', 'dev-tools-la-gi-cach-lam-viec-voi-chrome-dev-tools', 'what-are-dev-tools-how-to-work-with-chrome-devtools', 'dev-tools란-무엇인가-chrome-devtools-사용-방법', 'devtoolsとはchrome-devtoolsの使い方', '什么是-dev-tools如何使用-chrome-devtools', 164, 11, NULL, 145, 1, 0, 4, 266.00, NULL, NULL, NULL, NULL, NULL, 1, 'immediate', NULL, NULL, '2026-03-04 16:25:12', '2026-03-04 17:03:02', NULL),
(149, 'Module 02: JavaScript cơ bản', 'Module 02: Basic JavaScript', '모듈 02: JavaScript 기초', 'モジュール 02: JavaScript 基礎', '模块 02：JavaScript 基础', 'module-02-javascript-co-ba', 'module-02-basic-javascript', '모듈-02-javascript-기초', 'モジュール-02-javascript-基礎', '模块-02javascript-基础', NULL, 11, NULL, NULL, 0, 0, 5, 0.00, NULL, NULL, NULL, NULL, NULL, 1, 'immediate', NULL, NULL, '2026-03-04 16:25:45', '2026-03-04 16:26:29', NULL),
(150, 'Viết chương trình JavaScript đầu tiên', 'Writing Your First JavaScript Program', '첫 번째 JavaScript 프로그램 작성하기', '最初のJavaScriptプログラムを書く', '编写你的第一个 JavaScript 程序', 'viet-chuong-trinh-javascript-dau-tien', 'writing-your-first-javascript-program', '첫-번째-javascript-프로그램-작성하기', '最初のjavascriptプログラムを書く', '编写你的第一个-javascript-程序', 165, 11, NULL, 149, 0, 2, 6, 147.00, NULL, NULL, NULL, NULL, NULL, 1, 'immediate', NULL, NULL, '2026-03-04 16:26:22', '2026-04-27 09:10:14', NULL),
(151, 'Module 01: Nhập môn lập trình web', 'Module 01: Introduction to Web Programming', '모듈 01: 웹 프로그래밍 입문', 'モジュール 01: Webプログラミング入門', '模块 01：Web 编程入门', 'module-01-nhap-mon-lap-trinh-web', 'module-01-introduction-to-web-programming', '모듈-01-웹-프로그래밍-입문', 'モジュール-01-webプログラミング入門', '模块-01web-编程入门', NULL, 10, NULL, NULL, 0, 0, 1, 0.00, NULL, NULL, NULL, NULL, NULL, 1, 'immediate', NULL, NULL, '2026-03-04 16:27:22', '2026-03-04 16:27:22', NULL),
(152, 'Nhập môn lập trình web - Phần 1', 'Introduction to Web Programming - Part 1', '웹 프로그래밍 입문 - 1부', 'Webプログラミング入門 - パート1', 'Web 编程入门 - 第1部分', 'nhap-mon-lap-trinh-web-phan-1', 'introduction-to-web-programming-part-1', '웹-프로그래밍-입문-1부', 'webプログラミング入門-パート1', 'web-编程入门-第1部分', 180, 10, NULL, 151, 1, 2, 2, 306.00, NULL, NULL, NULL, NULL, NULL, 1, 'immediate', NULL, NULL, '2026-03-04 16:28:21', '2026-05-27 07:56:13', NULL),
(153, 'Nhập môn lập trình web - Phần 2', 'Introduction to Web Programming - Part 2', '웹 프로그래밍 입문 - 2부', 'Webプログラミング入門 - パート2', 'Web 编程入门 - 第2部分', 'nhap-mon-lap-trinh-web-phan-2', 'introduction-to-web-programming-part-2', '웹-프로그래밍-입문-2부', 'webプログラミング入門-パート2', 'web-编程入门-第2部分', 168, 10, NULL, 151, 1, 1, 3, 159.00, NULL, NULL, NULL, NULL, NULL, 1, 'immediate', NULL, NULL, '2026-03-04 16:29:10', '2026-04-27 09:19:22', NULL),
(154, 'Công cụ - Phần mềm cần chuẩn bị', 'Tools and Software to Prepare', '준비해야 할 도구 및 소프트웨어', '準備するツール・ソフトウェア', '需要准备的工具和软件', 'cong-cu-phan-mem-can-chuan-bi', 'tools-and-software-to-prepare', '준비해야-할-도구-및-소프트웨어', '準備するツールソフトウェア', '需要准备的工具和软件', 169, 10, NULL, 151, 1, 0, 4, 269.00, NULL, NULL, NULL, NULL, NULL, 1, 'immediate', NULL, NULL, '2026-03-04 16:29:48', '2026-03-04 17:04:25', NULL),
(155, 'Module 02: Ngôn ngữ HTML', 'Module 02: HTML Language', '모듈 02: HTML 언어', 'モジュール 02: HTML言語', '模块 02：HTML 语言', 'module-02-ngon-ngu-html', 'module-02-html-language', '모듈-02-html-언어', 'モジュール-02-html言語', '模块-02html-语言', NULL, 10, NULL, NULL, 0, 0, 5, 0.00, NULL, NULL, NULL, NULL, NULL, 1, 'immediate', NULL, NULL, '2026-03-04 16:30:27', '2026-03-04 16:30:27', NULL),
(156, 'HTML cơ bản - Phần 1', 'Basic HTML - Part 1', 'TML 기초 - 1부', 'HTML 基礎 - パート1', 'HTML 基础 - 第1部分', 'html-co-ban-phan-1', 'basic-html-part-1', 'tml-기초-1부', 'html-基礎-パート1', 'html-基础-第1部分', 170, 10, NULL, 155, 0, 0, 6, 204.00, NULL, NULL, NULL, NULL, NULL, 1, 'immediate', NULL, NULL, '2026-03-04 16:31:13', '2026-03-04 17:04:37', NULL),
(157, 'HTML cơ bản - Phần 2', 'Basic HTML - Part 2', 'HTML 기초 - 2부', 'HTML 基礎 - パート2', 'HTML 基础 - 第2部分', 'html-co-ban-phan-2', 'basic-html-part-2', 'html-기초-2부', 'html-基礎-パート2', 'html-基础-第2部分', 171, 10, NULL, 155, 0, 0, 7, 283.00, NULL, NULL, NULL, NULL, NULL, 1, 'immediate', NULL, NULL, '2026-03-04 16:31:51', '2026-03-04 17:04:51', NULL),
(158, 'Tạo Content Type và cách làm việc với Content Manager', 'Creating Content Types and Working with the Content Manager', 'Content Type 생성 및 Content Manager 사용 방법', 'Content Typeの作成とContent Managerの使い方', '创建 Content Type 并使用 Content Manager', 'tao-content-type-va-cach-lam-viec-voi-content-manager', 'creating-content-types-and-working-with-the-content-manager', 'content-type-생성-및-content-manager-사용-방법', 'content-typeの作成とcontent-managerの使い方', '创建-content-type-并使用-content-manager', 134, 20, NULL, 104, 0, 0, 6, 312.00, NULL, NULL, NULL, NULL, NULL, 1, 'immediate', NULL, NULL, '2026-03-04 16:52:45', '2026-03-04 16:52:45', NULL),
(159, '123', NULL, NULL, NULL, NULL, '123', NULL, NULL, NULL, NULL, 172, 30, NULL, NULL, 0, 0, 1, 5355.00, '123', NULL, NULL, NULL, NULL, 1, 'immediate', NULL, NULL, '2026-04-03 15:28:42', '2026-04-03 15:28:42', NULL),
(160, '123', NULL, NULL, NULL, NULL, '123-2', NULL, NULL, NULL, NULL, NULL, 31, NULL, NULL, 0, 0, 1, 0.00, '123', NULL, NULL, NULL, NULL, 1, 'immediate', NULL, NULL, '2026-04-16 05:37:44', '2026-04-16 05:37:44', NULL),
(161, '123', NULL, NULL, NULL, NULL, '123-3', NULL, NULL, NULL, NULL, 175, 31, NULL, 160, 0, 0, 1, 6943.00, '123', NULL, NULL, NULL, NULL, 1, 'immediate', NULL, NULL, '2026-04-16 05:39:19', '2026-04-18 14:06:54', NULL),
(162, '123 (Bản sao)', NULL, NULL, NULL, NULL, '123-2-copy-q6eo', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, 1, 0.00, '123', NULL, NULL, NULL, NULL, 0, 'immediate', NULL, NULL, '2026-04-19 07:29:02', '2026-04-19 07:29:02', NULL),
(163, '123 (Bản sao)', NULL, NULL, NULL, NULL, '123-3-copy-bpmz', NULL, NULL, NULL, NULL, 175, NULL, NULL, 162, 0, 0, 1, 6943.00, '123', NULL, NULL, NULL, NULL, 0, 'immediate', NULL, NULL, '2026-04-19 07:29:02', '2026-04-19 07:29:02', NULL),
(164, '123', NULL, NULL, NULL, NULL, '123-4', NULL, NULL, NULL, NULL, NULL, 33, NULL, NULL, 0, 0, 1, 0.00, NULL, NULL, NULL, NULL, NULL, 1, 'immediate', NULL, NULL, '2026-04-27 08:09:24', '2026-04-27 08:09:24', NULL),
(166, '123', NULL, NULL, NULL, NULL, '123-5', NULL, NULL, NULL, NULL, 177, 33, NULL, 164, 1, 9, 2, 2375.00, NULL, NULL, NULL, NULL, NULL, 1, 'immediate', NULL, NULL, '2026-04-27 08:10:03', '2026-05-27 08:19:32', NULL),
(167, 'TEST', NULL, NULL, NULL, NULL, 'test', NULL, NULL, NULL, NULL, 178, 33, NULL, 164, 1, 2, 3, 19.31, '<p>123</p>', NULL, NULL, NULL, NULL, 1, 'immediate', NULL, NULL, '2026-05-09 05:52:41', '2026-05-09 06:35:29', NULL),
(168, 'TEST', NULL, NULL, NULL, NULL, 'test', NULL, NULL, NULL, NULL, 179, 33, NULL, 164, 1, 0, 3, 19.31, '<p>123</p>', NULL, NULL, NULL, NULL, 1, 'immediate', NULL, NULL, '2026-05-09 05:56:21', '2026-05-09 05:56:26', '2026-05-09 05:56:26');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `lesson_notes`
--

CREATE TABLE `lesson_notes` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `student_id` bigint(20) UNSIGNED NOT NULL,
  `lesson_id` bigint(20) UNSIGNED NOT NULL,
  `time_at` int(11) NOT NULL COMMENT 'Time in seconds',
  `content` text NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '2014_10_12_000000_create_users_table', 1),
(2, '2014_10_12_100000_create_password_reset_tokens_table', 1),
(3, '2014_10_12_100000_create_password_resets_table', 1),
(4, '2019_12_14_000001_create_personal_access_tokens_table', 1),
(5, '2025_12_23_041055_create_categories_table', 1),
(6, '2025_12_24_105809_create_courses_table', 1),
(7, '2025_12_24_221502_create_categories_courses_table', 1),
(8, '2025_12_25_092207_create_teacher_table', 1),
(9, '2025_12_25_235541_add_foreign_courses_table', 1),
(10, '2025_12_30_221216_create_video_table', 1),
(11, '2025_12_30_221723_create_documents_table', 1),
(12, '2025_12_30_222232_create_lessons_table', 2),
(13, '2026_01_02_132413_create_students_table', 3),
(14, '2026_01_02_211736_add_view_column_courses_table', 4),
(16, '2026_01_11_143806_add_status_column_Lessions_table', 5),
(17, '2026_01_12_113131_add_remember_column_students_table', 6),
(18, '2026_01_12_204106_add_email_verified_at_column_students_table', 7),
(19, '2026_01_12_215741_create_jobs_table', 8),
(20, '2026_01_13_205520_create_student_password_rests_table', 9),
(21, '2026_01_16_101424_create_students_course_table', 10),
(22, '2026_01_16_235153_create_orders_status_table', 11),
(23, '2026_01_16_235228_create_orders_table', 11),
(24, '2026_01_17_000021_create_orders_detail_table', 11),
(27, '2026_01_19_101621_add_payment_date_column_orders_table', 12),
(40, '2026_01_19_135527_create_coupons_table', 13),
(41, '2026_01_19_135937_create_coupons_usage_table', 13),
(42, '2026_01_19_140503_create_coupons_courses_table', 13),
(43, '2026_01_19_140635_create_coupons_student_table', 13),
(44, '2026_01_20_131225_add_discount_column_table', 14),
(45, '2026_01_24_112855_create_sessions_table', 15),
(46, '2026_01_26_214404_create_contacts_table', 16),
(48, '2026_01_31_111649_create_settings_table', 17),
(49, '2026_01_31_160550_create_notifications_table', 18),
(52, '2026_02_06_114630_create_activity_logs_table', 19),
(53, '2026_02_11_222514_create_failed_jobs_table', 20),
(54, '2026_02_12_094614_add_field_to_course_table', 21),
(55, '2026_02_12_141033_add_field_to_teacher_table', 22),
(56, '2026_02_12_220352_add_field_lesson_tabel', 23),
(57, '2026_03_04_000001_add_korean_fields_to_courses_table', 24),
(58, '2026_03_04_000002_add_korean_fields_to_lessons_table', 24),
(59, '2026_03_04_000003_add_korean_fields_to_teacher_table', 24),
(60, '2026_03_04_000004_add_localized_fields_to_categories_table', 24),
(61, '2026_03_04_000005_add_localized_name_slug_to_teacher_table', 24),
(62, '2026_03_04_000006_add_japanese_fields_to_courses_table', 24),
(63, '2026_03_04_000007_add_japanese_fields_to_lessons_table', 24),
(64, '2026_03_04_000008_add_japanese_fields_to_categories_table', 24),
(65, '2026_03_04_000009_add_japanese_fields_to_teacher_table', 24),
(66, '2026_03_04_000010_add_chinese_fields_to_courses_table', 24),
(67, '2026_03_04_000011_add_chinese_fields_to_lessons_table', 24),
(68, '2026_03_04_000011_create_course_comments_table', 24),
(69, '2026_03_04_000012_add_chinese_fields_to_categories_table', 24),
(70, '2026_03_04_000013_add_chinese_fields_to_teacher_table', 24),
(71, '2026_03_19_000001_add_email_two_factor_to_students_table', 25),
(72, '2026_03_19_000002_add_login_security_to_students_table', 25),
(73, '2026_03_19_000004_drop_pending_profile_change_from_students_table', 25),
(74, '2026_03_20_150000_add_customer_snapshot_to_orders_table', 25),
(75, '2026_03_21_090000_add_locale_columns_to_orders_status_table', 25),
(76, '2026_03_21_140000_create_student_lesson_progress_table', 25),
(77, '2026_03_26_000001_add_soft_deletes_to_courses_table', 25),
(78, '2026_03_27_000001_create_groups_table', 25),
(79, '2026_03_27_000002_create_permissions_table', 25),
(80, '2026_03_27_000003_prepare_users_group_id_for_roles', 25),
(81, '2026_03_28_000001_add_is_locked_to_users_table', 25),
(82, '2026_03_28_000001_add_payment_method_to_orders_table', 25),
(83, '2026_03_28_000002_add_admin_security_columns_to_users_table', 25),
(84, '2026_03_28_000002_add_is_learning_locked_to_courses_table', 25),
(85, '2026_03_28_000003_create_admin_session_trackers_table', 25),
(86, '2026_03_28_000010_add_soft_deletes_to_admin_resources_tables', 25),
(87, '2026_03_29_140000_create_chatbot_knowledge_table', 25),
(88, '2026_03_29_140100_create_chatbot_unresolved_questions_table', 26),
(89, '2026_03_29_000100_add_soft_deletes_to_groups_and_permissions_tables', 27),
(90, '2026_03_30_000200_add_soft_deletes_to_lessons_table', 28),
(91, '2026_03_30_120000_add_per_student_once_to_coupons_table', 29),
(92, '2026_03_30_130000_add_indexes_to_active_logs_table', 30),
(93, '2026_04_01_000001_create_teacher_packages_table', 31),
(94, '2026_04_01_000002_create_teacher_applications_table', 31),
(95, '2026_04_01_000003_add_portal_fields_to_teacher_table', 31),
(96, '2026_04_01_000004_create_teacher_payout_requests_table', 32),
(97, '2026_04_01_000005_add_admin_note_to_teacher_payout_requests_table', 32),
(98, '2026_04_01_000006_update_free_teacher_package_course_limit', 33),
(99, '2026_04_01_000007_update_teacher_applications_for_public_flow', 33),
(100, '2026_04_01_000008_add_coupon_fields_to_teacher_applications_table', 34),
(101, '2026_04_02_000008_add_translatable_fields_to_teacher_packages_table', 35),
(102, '2026_04_02_000009_add_hidden_mode_to_teacher_packages_table', 36),
(103, '2026_04_02_000010_add_locale_to_teacher_applications_table', 37),
(104, '2026_04_02_000001_add_preferred_locale_to_students_table', 37),
(105, '2026_04_03_000011_add_package_lifecycle_fields_to_teachers_and_teacher_applications', 38),
(106, '2026_04_04_000012_create_teacher_student_notes_table', 39),
(107, '2026_04_04_000013_add_tag_to_teacher_student_notes_table', 40),
(108, '2026_04_04_000014_create_teacher_course_grants_table', 41),
(109, '2026_04_04_000015_add_invitation_fields_to_teacher_course_grants_table', 42),
(110, '2026_04_05_000001_add_teacher_id_to_coupons_table', 43),
(111, '2026_04_06_000016_create_teacher_payout_accounts_table', 44),
(112, '2026_04_06_000017_create_teacher_payout_account_change_requests_table', 45),
(113, '2026_04_08_000018_add_feature_flags_to_teacher_packages_table', 45),
(114, '2026_04_08_000019_add_duplicate_course_feature_to_teacher_packages_table', 46),
(115, '2026_04_08_000020_add_limits_to_teacher_packages_table', 47),
(116, '2026_04_08_000021_create_course_ratings_table', 48),
(117, '2026_04_08_000022_create_teacher_ratings_table', 48),
(118, '2026_04_08_000023_add_support_fields_to_contacts_table', 49),
(119, '2026_04_09_000023_create_teacher_announcements_tables', 50),
(120, '2026_04_09_000024_create_teacher_announcement_reads_table', 51),
(121, '2026_04_09_000025_create_teacher_notification_reads_table', 52),
(122, '2026_04_09_000026_add_package_lock_fields_to_coupons_table', 53),
(123, '2026_04_09_000027_add_package_priority_to_coupons_table', 54),
(124, '2026_04_09_000028_add_package_controls_to_courses_table', 55),
(125, '2026_04_09_000029_add_activity_tracking_to_teacher_table', 56),
(126, '2026_04_10_000024_add_manage_students_feature_to_teacher_packages_table', 57),
(127, '2026_04_10_000025_add_view_student_progress_feature_to_teacher_packages_table', 58),
(128, '2026_04_10_000026_add_view_activity_logs_feature_to_teacher_packages_table', 59),
(129, '2026_04_10_000027_add_growth_features_to_teacher_packages_table', 60),
(130, '2026_04_10_000028_create_teacher_promotions_table', 61),
(131, '2026_04_10_000030_add_filters_to_teacher_promotions_table', 62),
(132, '2026_04_10_000031_create_teacher_course_bundles_tables', 63),
(133, '2026_04_11_000032_add_bundle_id_to_orders_table', 64),
(134, '2026_04_11_000033_create_coupons_teacher_course_bundles_table', 65),
(135, '2026_04_12_000034_create_teacher_affiliate_links_tables', 66),
(136, '2026_04_12_000035_add_affiliate_link_id_to_orders_table', 67),
(137, '2026_04_12_000036_add_custom_links_to_teacher_applications_table', 68),
(138, '2026_04_12_000037_create_teacher_course_certificates_table', 69),
(139, '2026_04_12_000038_add_revocation_columns_to_teacher_course_certificates_table', 70),
(140, '2026_04_12_000039_expand_order_money_columns', 71),
(141, '2026_04_12_000040_add_release_schedule_to_lessons_table', 72),
(142, '2026_04_13_000039_add_lesson_import_export_feature_to_teacher_packages_table', 73),
(143, '2026_04_14_000022_create_course_view_trackings_table', 74),
(144, '2026_04_14_000040_add_badges_to_teacher_table', 75),
(145, '2026_04_14_000041_add_custom_badge_fields_to_teacher_table', 76),
(146, '2026_04_16_000050_create_course_quizzes_table', 77),
(147, '2026_04_16_000051_create_course_quiz_questions_table', 77),
(148, '2026_04_16_000052_create_course_quiz_choices_table', 77),
(149, '2026_04_16_000053_create_course_quiz_assignments_table', 77),
(150, '2026_04_16_000054_create_course_quiz_submissions_table', 77),
(151, '2026_04_16_000055_create_course_quiz_submission_answers_table', 77),
(152, '2026_04_16_000055_add_manage_quizzes_feature_to_teacher_packages_table', 78),
(153, '2026_04_16_000056_add_deadline_at_to_course_quizzes_table', 79),
(154, '2026_04_16_000055_add_quiz_feature_to_teacher_packages_table', 80),
(155, '2026_04_16_234720_add_ai_quiz_limit_to_teacher_packages_table', 81),
(156, '2026_04_17_003246_add_can_use_ai_quiz_to_teacher_packages_table', 82),
(157, '2026_04_17_110404_create_teacher_cancellation_requests_table', 83),
(158, '2026_04_17_125430_add_can_import_export_to_teacher_packages_table', 84),
(159, '2026_04_17_133500_add_can_verify_certificates_to_teacher_packages_table', 85),
(160, '2026_04_22_215923_create_teacher_package_features_table', 86),
(161, '2026_04_22_220000_add_badge_tone_to_teacher_packages_table', 86),
(162, '2026_04_24_000001_add_localized_prices_to_courses', 87),
(163, '2026_04_24_000002_create_exchange_rates_table', 87),
(164, '2026_04_24_000003_add_currency_tracking_to_orders', 87),
(165, '2026_04_24_150000_add_created_by_to_coupons_table', 88),
(166, '2026_04_25_000001_add_is_exclusive_to_teacher_packages_table', 89),
(167, '2026_04_25_000001_add_locking_fields_to_teacher_table', 89),
(168, '2026_04_25_000002_add_granted_by_to_teacher_applications_table', 89),
(169, '2026_04_25_000002_add_is_hot_to_teacher_course_bundles_table', 89),
(170, '2026_04_25_000003_add_status_to_course_ratings_table', 89),
(171, '2026_04_25_000004_add_status_to_teacher_ratings_table', 89),
(172, '2026_04_26_114500_add_coming_soon_to_courses_table', 89),
(173, '2026_04_26_120343_add_stock_and_coming_soon_to_teacher_course_bundles_table', 89),
(174, '2026_04_26_120628_add_end_at_to_teacher_course_bundles_table', 89),
(175, '2026_04_26_123800_add_sale_price_to_teacher_course_bundles_table', 89),
(176, '2026_04_26_130500_add_stock_to_courses_table', 89),
(177, '2026_04_26_220000_create_announcements_tables', 90),
(178, '2026_04_27_000001_create_teacher_badges_table', 91),
(179, '2026_04_28_214453_add_max_payout_per_day_to_teacher_packages_table', 92),
(180, '2026_04_28_214932_add_currency_payout_fields_to_payout_requests_table', 93),
(181, '2026_04_28_221634_add_can_request_payouts_to_teacher_packages_table', 94),
(182, '2026_04_29_144500_create_ip_blacklists_table', 95),
(183, '2026_04_30_123129_add_performance_indexes_to_core_tables', 96),
(184, '2026_04_30_124800_add_cleanup_permission', 97),
(185, '2026_04_30_130000_add_maintenance_permissions', 98),
(186, '2026_05_01_000001_add_type_and_note_to_teacher_applications_table', 99),
(187, '2026_05_01_000001_make_orders_polymorphic', 100),
(188, '2026_05_05_000001_add_file_paths_to_teacher_applications_table', 101),
(189, '2026_05_06_000001_add_category_to_teacher_packages_table', 102),
(190, '2026_05_06_151800_add_localized_categories_to_teacher_packages_table', 103),
(191, '2026_05_06_152400_create_teacher_package_categories_table', 104),
(192, '2026_05_10_132814_add_completion_condition_to_courses_table', 105),
(193, '2026_05_10_135820_add_telegram_fields_to_teacher_table', 106),
(194, '2026_05_07_000001_create_lesson_notes_table', 107),
(195, '2026_05_10_231049_create_telegram_packages_table', 107),
(196, '2026_05_11_030100_add_localized_fields_to_telegram_packages_table', 108),
(197, '2026_05_11_212100_add_claim_fields_to_teacher_telegram_subscriptions_table', 109);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `notifications`
--

CREATE TABLE `notifications` (
  `id` char(36) NOT NULL,
  `type` varchar(255) NOT NULL,
  `notifiable_type` varchar(255) NOT NULL,
  `notifiable_id` bigint(20) UNSIGNED NOT NULL,
  `data` text NOT NULL,
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `orders`
--

CREATE TABLE `orders` (
  `id` int(10) UNSIGNED NOT NULL,
  `code` varchar(100) NOT NULL,
  `student_id` int(10) UNSIGNED DEFAULT NULL,
  `orderable_id` bigint(20) UNSIGNED DEFAULT NULL,
  `orderable_type` varchar(255) DEFAULT NULL,
  `type` varchar(255) NOT NULL DEFAULT 'course_purchase',
  `bundle_id` bigint(20) UNSIGNED DEFAULT NULL,
  `affiliate_link_id` bigint(20) UNSIGNED DEFAULT NULL,
  `customer_name_snapshot` varchar(255) DEFAULT NULL,
  `customer_email_snapshot` varchar(255) DEFAULT NULL,
  `customer_phone_snapshot` varchar(255) DEFAULT NULL,
  `customer_address_snapshot` varchar(255) DEFAULT NULL,
  `total` decimal(15,2) NOT NULL DEFAULT 0.00,
  `currency` varchar(3) NOT NULL DEFAULT 'VND',
  `exchange_rate` decimal(20,8) DEFAULT NULL,
  `conversion_fee_pct` decimal(5,2) DEFAULT NULL,
  `base_total` decimal(15,2) DEFAULT NULL,
  `discount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `coupon` varchar(255) DEFAULT NULL,
  `status_id` int(10) UNSIGNED DEFAULT NULL,
  `payment_date` timestamp NULL DEFAULT NULL,
  `payment_complete_date` timestamp NULL DEFAULT NULL,
  `payment_method` varchar(50) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `orders_detail`
--

CREATE TABLE `orders_detail` (
  `id` int(10) UNSIGNED NOT NULL,
  `order_id` int(10) UNSIGNED NOT NULL,
  `course_id` int(10) UNSIGNED DEFAULT NULL,
  `price` decimal(15,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `orders_status`
--

CREATE TABLE `orders_status` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(200) NOT NULL,
  `name_en` varchar(200) DEFAULT NULL,
  `name_ko` varchar(200) DEFAULT NULL,
  `name_ja` varchar(200) DEFAULT NULL,
  `name_zh` varchar(200) DEFAULT NULL,
  `color` varchar(100) DEFAULT NULL,
  `is_success` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `orders_status`
--

INSERT INTO `orders_status` (`id`, `name`, `name_en`, `name_ko`, `name_ja`, `name_zh`, `color`, `is_success`, `created_at`, `updated_at`) VALUES
(1, 'Chờ thanh toán', 'Pending payment', '결제 대기', '支払い待ち', '待付款', 'warning', 0, '2026-06-13 04:57:07', '2026-06-13 04:57:07'),
(2, 'Đã thanh toán', 'Paid', '결제 완료', '支払い完了', '已付款', 'success', 1, '2026-06-13 04:57:07', '2026-06-13 04:57:07'),
(3, 'Thanh toán thất bại', 'Payment failed', '결제 실패', '支払い失敗', '支付失败', 'danger', 0, '2026-06-13 04:57:07', '2026-06-13 04:57:07'),
(4, 'Hủy thanh toán', 'Payment cancelled', '결제 취소', '支払いキャンセル', '取消支付', 'danger', 0, '2026-06-13 04:57:07', '2026-06-13 04:57:07');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `password_resets`
--

CREATE TABLE `password_resets` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `permissions`
--

CREATE TABLE `permissions` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `module` varchar(255) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `permissions`
--

INSERT INTO `permissions` (`id`, `name`, `slug`, `module`, `description`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'Xem dashboard', 'dashboard.view', 'dashboard', NULL, '2026-03-29 16:21:59', '2026-03-29 16:21:59', NULL),
(2, 'Xem khóa học', 'courses.view', 'courses', NULL, '2026-03-29 16:21:59', '2026-03-29 16:21:59', NULL),
(3, 'Tạo khóa học', 'courses.create', 'courses', NULL, '2026-03-29 16:21:59', '2026-03-29 16:21:59', NULL),
(4, 'Sửa khóa học', 'courses.edit', 'courses', NULL, '2026-03-29 16:21:59', '2026-03-29 16:21:59', NULL),
(5, 'Xuất bản khóa học', 'courses.publish', 'courses', NULL, '2026-03-29 16:21:59', '2026-03-29 16:21:59', NULL),
(6, 'Xóa mềm khóa học', 'courses.soft_delete', 'courses', NULL, '2026-03-29 16:21:59', '2026-03-30 00:18:52', NULL),
(7, 'Khôi phục khóa học', 'courses.restore', 'courses', NULL, '2026-03-29 16:21:59', '2026-03-30 00:18:52', NULL),
(8, 'Xóa vĩnh viễn khóa học', 'courses.force_delete', 'courses', NULL, '2026-03-29 16:21:59', '2026-03-29 16:21:59', NULL),
(9, 'Xem bài giảng', 'lessons.view', 'lessons', NULL, '2026-03-29 16:21:59', '2026-03-29 16:21:59', NULL),
(10, 'Thêm bài giảng', 'lessons.create', 'lessons', NULL, '2026-03-29 16:21:59', '2026-03-29 16:21:59', NULL),
(11, 'Sửa bài giảng', 'lessons.edit', 'lessons', NULL, '2026-03-29 16:21:59', '2026-03-29 16:21:59', NULL),
(12, 'Xóa bài giảng', 'lessons.delete', 'lessons', NULL, '2026-03-29 16:21:59', '2026-03-29 16:21:59', NULL),
(13, 'Sắp xếp bài giảng', 'lessons.sort', 'lessons', NULL, '2026-03-29 16:21:59', '2026-03-29 16:21:59', NULL),
(14, 'Xem danh mục', 'categories.view', 'categories', NULL, '2026-03-29 16:21:59', '2026-03-29 16:21:59', NULL),
(15, 'Tạo danh mục', 'categories.create', 'categories', NULL, '2026-03-29 16:21:59', '2026-03-29 16:21:59', NULL),
(16, 'Sửa danh mục', 'categories.edit', 'categories', NULL, '2026-03-29 16:21:59', '2026-03-29 16:21:59', NULL),
(17, 'Xóa danh mục', 'categories.delete', 'categories', NULL, '2026-03-29 16:21:59', '2026-03-29 16:21:59', NULL),
(18, 'Xóa mềm danh mục', 'categories.soft_delete', 'categories', NULL, '2026-03-29 16:21:59', '2026-03-30 00:18:52', NULL),
(19, 'Khôi phục danh mục', 'categories.restore', 'categories', NULL, '2026-03-29 16:21:59', '2026-03-30 00:18:52', NULL),
(20, 'Xóa vĩnh viễn danh mục', 'categories.force_delete', 'categories', NULL, '2026-03-29 16:21:59', '2026-03-29 16:21:59', NULL),
(21, 'Xem lịch sử danh mục', 'categories.logs', 'categories', NULL, '2026-03-29 16:21:59', '2026-03-29 16:21:59', NULL),
(22, 'Xem giảng viên', 'teachers.view', 'teachers', NULL, '2026-03-29 16:21:59', '2026-03-29 16:21:59', NULL),
(23, 'Tạo giảng viên', 'teachers.create', 'teachers', NULL, '2026-03-29 16:21:59', '2026-03-29 16:21:59', NULL),
(24, 'Sửa giảng viên', 'teachers.edit', 'teachers', NULL, '2026-03-29 16:21:59', '2026-03-29 16:21:59', NULL),
(25, 'Xóa giảng viên', 'teachers.delete', 'teachers', NULL, '2026-03-29 16:21:59', '2026-03-29 16:21:59', NULL),
(26, 'Xóa mềm giảng viên', 'teachers.soft_delete', 'teachers', NULL, '2026-03-29 16:21:59', '2026-03-30 00:18:52', NULL),
(27, 'Khôi phục giảng viên', 'teachers.restore', 'teachers', NULL, '2026-03-29 16:21:59', '2026-03-30 00:18:52', NULL),
(28, 'Xóa vĩnh viễn giảng viên', 'teachers.force_delete', 'teachers', NULL, '2026-03-29 16:21:59', '2026-03-29 16:21:59', NULL),
(29, 'Xem lịch sử giảng viên', 'teachers.logs', 'teachers', NULL, '2026-03-29 16:21:59', '2026-03-29 16:21:59', NULL),
(30, 'Xem đơn hàng', 'orders.view', 'orders', NULL, '2026-03-29 16:21:59', '2026-03-29 16:21:59', NULL),
(31, 'Cập nhật đơn hàng', 'orders.update', 'orders', NULL, '2026-03-29 16:21:59', '2026-03-29 16:21:59', NULL),
(32, 'Xóa đơn hàng', 'orders.delete', 'orders', NULL, '2026-03-29 16:21:59', '2026-03-29 16:21:59', NULL),
(33, 'Xóa mềm đơn hàng', 'orders.soft_delete', 'orders', NULL, '2026-03-29 16:21:59', '2026-03-30 00:18:52', NULL),
(34, 'Khôi phục đơn hàng', 'orders.restore', 'orders', NULL, '2026-03-29 16:21:59', '2026-03-30 00:18:52', NULL),
(35, 'Xóa vĩnh viễn đơn hàng', 'orders.force_delete', 'orders', NULL, '2026-03-29 16:21:59', '2026-03-29 16:21:59', NULL),
(36, 'Xem học viên', 'students.view', 'students', NULL, '2026-03-29 16:21:59', '2026-03-29 16:21:59', NULL),
(37, 'Tạo học viên', 'students.create', 'students', NULL, '2026-03-29 16:21:59', '2026-03-29 16:21:59', NULL),
(38, 'Sửa học viên', 'students.edit', 'students', NULL, '2026-03-29 16:21:59', '2026-03-29 16:21:59', NULL),
(39, 'Xóa học viên', 'students.delete', 'students', NULL, '2026-03-29 16:21:59', '2026-03-29 16:21:59', NULL),
(40, 'Xóa mềm học viên', 'students.soft_delete', 'students', NULL, '2026-03-29 16:21:59', '2026-03-30 00:18:52', NULL),
(41, 'Khôi phục học viên', 'students.restore', 'students', NULL, '2026-03-29 16:21:59', '2026-03-30 00:18:52', NULL),
(42, 'Xóa vĩnh viễn học viên', 'students.force_delete', 'students', NULL, '2026-03-29 16:21:59', '2026-03-29 16:21:59', NULL),
(43, 'Xem lịch sử học viên', 'students.logs', 'students', NULL, '2026-03-29 16:21:59', '2026-03-29 16:21:59', NULL),
(44, 'Xem người dùng', 'users.view', 'users', NULL, '2026-03-29 16:21:59', '2026-03-29 16:21:59', NULL),
(45, 'Tạo người dùng', 'users.create', 'users', NULL, '2026-03-29 16:21:59', '2026-03-29 16:21:59', NULL),
(46, 'Sửa người dùng', 'users.edit', 'users', NULL, '2026-03-29 16:21:59', '2026-03-29 16:21:59', NULL),
(47, 'Xóa người dùng', 'users.delete', 'users', NULL, '2026-03-29 16:21:59', '2026-03-29 16:21:59', NULL),
(48, 'Xóa mềm người dùng', 'users.soft_delete', 'users', NULL, '2026-03-29 16:21:59', '2026-03-30 00:18:52', NULL),
(49, 'Khôi phục người dùng', 'users.restore', 'users', NULL, '2026-03-29 16:21:59', '2026-03-30 00:18:52', NULL),
(50, 'Xóa vĩnh viễn người dùng', 'users.force_delete', 'users', NULL, '2026-03-29 16:21:59', '2026-03-29 16:21:59', NULL),
(51, 'Xem lịch sử người dùng', 'users.logs', 'users', NULL, '2026-03-29 16:21:59', '2026-03-29 16:21:59', NULL),
(52, 'Xem nhóm quyền', 'groups.view', 'groups', NULL, '2026-03-29 16:21:59', '2026-03-29 16:21:59', NULL),
(53, 'Tạo nhóm quyền', 'groups.create', 'groups', NULL, '2026-03-29 16:21:59', '2026-03-29 16:21:59', NULL),
(54, 'Sửa nhóm quyền', 'groups.edit', 'groups', NULL, '2026-03-29 16:21:59', '2026-03-29 16:21:59', NULL),
(55, 'Xóa nhóm quyền', 'groups.delete', 'groups', NULL, '2026-03-29 16:21:59', '2026-03-29 16:21:59', NULL),
(56, 'Quản lý nhóm quyền', 'groups.manage', 'groups', NULL, '2026-03-29 16:21:59', '2026-03-29 16:21:59', NULL),
(57, 'Xem quyền chi tiết', 'permissions.view', 'permissions', NULL, '2026-03-29 16:21:59', '2026-03-29 16:21:59', NULL),
(58, 'Tạo quyền chi tiết', 'permissions.create', 'permissions', NULL, '2026-03-29 16:21:59', '2026-03-29 16:21:59', NULL),
(59, 'Sửa quyền chi tiết', 'permissions.edit', 'permissions', NULL, '2026-03-29 16:21:59', '2026-03-29 16:21:59', NULL),
(60, 'Xóa quyền chi tiết', 'permissions.delete', 'permissions', NULL, '2026-03-29 16:21:59', '2026-03-29 16:21:59', NULL),
(61, 'Quản lý quyền chi tiết', 'permissions.manage', 'permissions', NULL, '2026-03-29 16:21:59', '2026-03-29 16:21:59', NULL),
(62, 'Xem mã giảm giá', 'coupons.view', 'coupons', NULL, '2026-03-29 16:21:59', '2026-03-29 16:21:59', NULL),
(63, 'Tạo mã giảm giá', 'coupons.create', 'coupons', NULL, '2026-03-29 16:21:59', '2026-03-29 16:21:59', NULL),
(64, 'Sửa mã giảm giá', 'coupons.edit', 'coupons', NULL, '2026-03-29 16:21:59', '2026-03-29 16:21:59', NULL),
(65, 'Xóa mã giảm giá', 'coupons.delete', 'coupons', NULL, '2026-03-29 16:21:59', '2026-03-29 16:21:59', NULL),
(66, 'Xóa mềm mã giảm giá', 'coupons.soft_delete', 'coupons', NULL, '2026-03-29 16:21:59', '2026-03-30 00:18:52', NULL),
(67, 'Khôi phục mã giảm giá', 'coupons.restore', 'coupons', NULL, '2026-03-29 16:21:59', '2026-03-30 00:18:52', NULL),
(68, 'Xóa vĩnh viễn mã giảm giá', 'coupons.force_delete', 'coupons', NULL, '2026-03-29 16:21:59', '2026-03-29 16:21:59', NULL),
(69, 'Gán mã giảm giá', 'coupons.assign', 'coupons', NULL, '2026-03-29 16:21:59', '2026-03-29 16:21:59', NULL),
(70, 'Xem lịch sử mã giảm giá', 'coupons.logs', 'coupons', NULL, '2026-03-29 16:21:59', '2026-03-29 16:21:59', NULL),
(71, 'Xem liên hệ', 'contacts.view', 'contacts', NULL, '2026-03-29 16:21:59', '2026-03-29 16:21:59', NULL),
(72, 'Tiếp nhận liên hệ', 'contacts.update', 'contacts', NULL, '2026-03-29 16:21:59', '2026-03-29 16:21:59', NULL),
(73, 'Xóa liên hệ', 'contacts.delete', 'contacts', NULL, '2026-03-29 16:21:59', '2026-03-29 16:21:59', NULL),
(74, 'Xóa mềm liên hệ', 'contacts.soft_delete', 'contacts', NULL, '2026-03-29 16:21:59', '2026-03-30 00:18:52', NULL),
(75, 'Khôi phục liên hệ', 'contacts.restore', 'contacts', NULL, '2026-03-29 16:21:59', '2026-03-30 00:18:52', NULL),
(76, 'Xóa vĩnh viễn liên hệ', 'contacts.force_delete', 'contacts', NULL, '2026-03-29 16:21:59', '2026-03-29 16:21:59', NULL),
(77, 'Xem lịch sử liên hệ', 'contacts.logs', 'contacts', NULL, '2026-03-29 16:21:59', '2026-03-29 16:21:59', NULL),
(78, 'Kiểm duyệt bình luận', 'comments.moderate', 'comments', NULL, '2026-03-29 16:21:59', '2026-03-29 16:21:59', NULL),
(79, 'Xem cấu hình', 'settings.view', 'settings', NULL, '2026-03-29 16:21:59', '2026-03-29 16:21:59', NULL),
(80, 'Cập nhật cấu hình', 'settings.update', 'settings', NULL, '2026-03-29 16:21:59', '2026-03-29 16:21:59', NULL),
(81, 'Xem lịch sử cấu hình', 'settings.logs', 'settings', NULL, '2026-03-29 16:21:59', '2026-03-29 16:21:59', NULL),
(82, 'Xem tri thức chatbot', 'chatbot.view', 'chatbot', NULL, '2026-03-29 16:21:59', '2026-03-30 00:19:17', NULL),
(83, 'Thêm tri thức chatbot', 'chatbot.create', 'chatbot', NULL, '2026-03-29 16:21:59', '2026-03-30 00:19:17', NULL),
(84, 'Sửa tri thức chatbot', 'chatbot.edit', 'chatbot', NULL, '2026-03-29 16:21:59', '2026-03-30 00:19:17', NULL),
(85, 'Xóa tri thức chatbot', 'chatbot.delete', 'chatbot', NULL, '2026-03-29 16:21:59', '2026-03-30 00:19:17', NULL),
(86, 'Xem log chatbot', 'chatbot.logs', 'chatbot', NULL, '2026-03-29 16:21:59', '2026-03-29 16:21:59', NULL),
(87, 'Xem nhật ký hệ thống', 'logs.view', 'logs', NULL, '2026-03-29 16:21:59', '2026-03-29 16:21:59', NULL),
(88, 'Xóa mềm nhóm quyền', 'groups.soft_delete', 'groups', NULL, '2026-03-29 16:26:18', '2026-03-30 00:18:52', NULL),
(89, 'Khôi phục nhóm quyền', 'groups.restore', 'groups', NULL, '2026-03-29 16:26:18', '2026-03-30 00:18:52', NULL),
(90, 'Xóa vĩnh viễn nhóm quyền', 'groups.force_delete', 'groups', NULL, '2026-03-29 16:26:18', '2026-03-30 00:18:52', NULL),
(91, 'Xóa mềm quyền chi tiết', 'permissions.soft_delete', 'permissions', NULL, '2026-03-29 16:26:18', '2026-03-30 00:18:52', NULL),
(92, 'Khôi phục quyền chi tiết', 'permissions.restore', 'permissions', NULL, '2026-03-29 16:26:18', '2026-03-30 00:18:52', NULL),
(93, 'Xoa vinh vien quyền chi tiết', 'permissions.force_delete', 'permissions', NULL, '2026-03-29 16:26:18', '2026-03-30 00:18:52', NULL),
(94, 'Xóa mềm bài giảng', 'lessons.soft_delete', 'lessons', NULL, '2026-03-30 00:45:17', '2026-03-30 00:45:17', NULL),
(95, 'Khôi phục bài giảng', 'lessons.restore', 'lessons', NULL, '2026-03-30 00:45:17', '2026-03-30 00:45:17', NULL),
(96, 'Xóa vĩnh viễn bài giảng', 'lessons.force_delete', 'lessons', NULL, '2026-03-30 00:45:17', '2026-03-30 00:45:17', NULL),
(97, 'Duyệt giảng viên', 'teachers.approve', 'teachers', NULL, '2026-04-29 17:42:16', '2026-04-29 17:42:16', NULL),
(98, 'Từ chối giảng viên', 'teachers.reject', 'teachers', NULL, '2026-04-29 17:42:16', '2026-04-29 17:42:16', NULL),
(99, 'Khóa/Mở khóa giảng viên', 'teachers.lock', 'teachers', NULL, '2026-04-29 17:42:16', '2026-04-29 17:42:16', NULL),
(100, 'Xem danh sách gói', 'packages.view', 'packages', NULL, '2026-04-29 17:42:16', '2026-04-29 17:42:16', NULL),
(101, 'Quản lý gói giảng viên', 'packages.manage', 'packages', NULL, '2026-04-29 17:42:16', '2026-04-29 17:42:16', NULL),
(102, 'Xem khuyến mại', 'promotions.view', 'promotions', NULL, '2026-04-29 17:42:16', '2026-04-29 17:42:16', NULL),
(103, 'Gửi khuyến mại hàng loạt', 'promotions.send', 'promotions', NULL, '2026-04-29 17:42:16', '2026-04-29 17:42:16', NULL),
(104, 'Cấp khóa học thủ công', 'students.grant_course', 'students', NULL, '2026-04-29 17:42:16', '2026-04-29 17:42:16', NULL),
(105, 'Khóa tài khoản học viên', 'students.lock', 'students', NULL, '2026-04-29 17:42:16', '2026-04-29 17:42:16', NULL),
(106, 'Xem báo cáo vi phạm', 'reports.view', 'reports', NULL, '2026-04-29 17:42:16', '2026-04-29 17:42:16', NULL),
(107, 'Xử lý báo cáo vi phạm', 'reports.resolve', 'reports', NULL, '2026-04-29 17:42:16', '2026-04-29 17:42:16', NULL),
(108, 'Duyệt xuất bản khóa học', 'courses.approve', 'courses', NULL, '2026-04-29 17:42:16', '2026-04-29 17:42:16', NULL),
(109, 'Xem chứng chỉ', 'certificates.view', 'certificates', NULL, '2026-04-29 17:42:16', '2026-04-29 17:42:16', NULL),
(110, 'Xem thông báo hệ thống', 'announcements.view', 'announcements', NULL, '2026-04-29 17:50:53', '2026-04-29 17:50:53', NULL),
(111, 'Gửi thông báo hệ thống', 'announcements.create', 'announcements', NULL, '2026-04-29 17:50:53', '2026-04-29 17:50:53', NULL),
(112, 'Xóa thông báo hệ thống', 'announcements.delete', 'announcements', NULL, '2026-04-29 17:50:53', '2026-04-29 17:50:53', NULL),
(113, 'Xem đánh giá', 'ratings.view', 'ratings', NULL, '2026-04-30 05:18:34', '2026-04-30 05:18:34', NULL),
(114, 'Kiểm duyệt đánh giá', 'ratings.moderate', 'ratings', NULL, '2026-04-30 05:18:34', '2026-04-30 05:18:34', NULL),
(115, 'Xóa đánh giá', 'ratings.delete', 'ratings', NULL, '2026-04-30 05:18:34', '2026-04-30 05:18:34', NULL),
(116, 'Xem tài chính', 'finances.view', 'finances', NULL, '2026-04-30 05:18:34', '2026-04-30 05:18:34', NULL),
(117, 'Quản lý tài chính', 'finances.manage', 'finances', NULL, '2026-04-30 05:18:34', '2026-04-30 06:20:03', NULL),
(118, 'Xuất báo cáo', 'finances.export', 'finances', NULL, '2026-04-30 05:18:34', '2026-04-30 06:20:03', NULL),
(119, 'Xem danh sách huy hiệu', 'badges.view', 'badges', NULL, '2026-04-30 05:18:34', '2026-04-30 05:18:34', NULL),
(120, 'Tạo huy hiệu', 'badges.create', 'badges', NULL, '2026-04-30 05:18:34', '2026-04-30 05:18:34', NULL),
(121, 'Sửa huy hiệu', 'badges.edit', 'badges', NULL, '2026-04-30 05:18:34', '2026-04-30 05:18:34', NULL),
(122, 'Xóa huy hiệu', 'badges.delete', 'badges', NULL, '2026-04-30 05:18:34', '2026-04-30 05:18:34', NULL),
(123, 'Quản lý yêu cầu hủy hợp tác', 'teachers.cancel_manage', 'teachers', NULL, '2026-04-30 05:18:34', '2026-04-30 05:18:34', NULL),
(124, 'Đăng nhập với tư cách học viên', 'students.impersonate', 'students', NULL, '2026-04-30 05:23:58', '2026-04-30 05:23:58', NULL),
(125, 'Toàn quyền Media', 'media.manage', 'media', 'Cho phép truy cập và quản lý thư viện ảnh/video', '2026-04-30 06:03:08', '2026-04-30 06:20:03', NULL),
(126, 'Dọn dẹp hệ thống', 'settings.cleanup', 'settings', 'Cho phép thực hiện dọn dẹp log và dữ liệu rác thủ công', '2026-04-30 05:50:17', '2026-04-30 06:20:03', NULL),
(127, 'Bảo trì hệ thống', 'settings.maintenance', 'settings', 'Cho phép xóa Cache, dọn dẹp và bảo trì hệ thống', '2026-04-30 06:03:08', '2026-04-30 06:20:03', NULL),
(128, 'Sức khỏe web', 'settings.health', 'settings', 'Cho phép xem trạng thái ổ đĩa, database, mail server', '2026-04-30 06:03:08', '2026-04-30 06:20:03', NULL),
(129, 'Xóa tệp tin', 'media.delete', 'media', NULL, '2026-04-30 06:14:30', '2026-04-30 06:14:30', NULL);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `personal_access_tokens`
--

CREATE TABLE `personal_access_tokens` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tokenable_type` varchar(255) NOT NULL,
  `tokenable_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `token` varchar(64) NOT NULL,
  `abilities` text DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('3pHua75tLdbAOyZKN5W0anoj0CTLQ4RSVEKrc5KE', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'YTo1OntzOjY6Il90b2tlbiI7czo0MDoiMmZVVmwyQjYxcDE0cHRHdEtHcEpQNEI3b0dwQkN0NkxLY2VOb0RDciI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MjQ6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC92aSI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fXM6NjoibG9jYWxlIjtzOjI6InZpIjtzOjU1OiJsb2dpbl9zdHVkZW50c181OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtpOjE7fQ==', 1781326821),
('F7rYisDTWcEFrWZ76VcFUO4pJ7go7pNnaCtUIW1E', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'YTo2OntzOjY6Il90b2tlbiI7czo0MDoiSjZVZElDMTlZaDJ5WnAyR3FHMlJIdnUyN2Y5N0RJMFZQdVdxeU43UyI7czo2OiJsb2NhbGUiO3M6MjoidmkiO3M6OToiX3ByZXZpb3VzIjthOjE6e3M6MzoidXJsIjtzOjY3OiJodHRwOi8vMTI3LjAuMC4xOjgwMDAvdmkva2hvYS1ob2MvamF2YXNjcmlwdC10dS1jby1iYW4tZGVuLW5hbmctY2FvIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo1NToibG9naW5fc3R1ZGVudHNfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI7aToxMztzOjg6InN0dWRlbnRzIjthOjE6e3M6MTA6InR3b19mYWN0b3IiO2E6MTp7czoxMToidmVyaWZpZWRfYXQiO2k6MTc3OTg2NjA1Mzt9fX0=', 1779871300),
('U9fJYBQZFDJU40BoB90QOIafxRssQmPIkeILR5e8', 2, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'YTo5OntzOjY6Il90b2tlbiI7czo0MDoieUVDSFhFcFozU2M5RlBDMDFYSGdaWTFEY09NcmZtemxzUnZOUHhzWCI7czozOiJ1cmwiO2E6MDp7fXM6OToiX3ByZXZpb3VzIjthOjE6e3M6MzoidXJsIjtzOjM4OiJodHRwOi8vMTI3LjAuMC4xOjgwMDAvYWRtaW4vYWN0aXZlbG9ncyI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fXM6NjoibG9jYWxlIjtzOjI6InZpIjtzOjUwOiJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI7aToyO3M6NDoiYXV0aCI7YToxOntzOjIxOiJwYXNzd29yZF9jb25maXJtZWRfYXQiO2k6MTc3OTg2NjA4NDt9czoyMzoibGFzdF92aWV3ZWRfY29tbWVudHNfYXQiO3M6MTk6IjIwMjYtMDUtMjcgMTU6MzQ6MTEiO3M6Mjc6Imxhc3Rfdmlld2VkX3RlYWNoZXJfYXBwc19hdCI7czoxOToiMjAyNi0wNS0yNyAxNTozNDoyOSI7fQ==', 1779871012),
('ZhJOXbkaNJD6hCsaTt2Cuse1SWRL89LFtcrR8PM5', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'YTo3OntzOjY6Il90b2tlbiI7czo0MDoiT3Uyd3dLMjhCWHdqUGU1cm41bjQzYlRqa1ZrN0lGUFZkSmp4T04xTCI7czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czozODoiaHR0cDovLzEyNy4wLjAuMTo4MDAwL3RlYWNoZXIvdGVsZWdyYW0iO31zOjk6Il9wcmV2aW91cyI7YToxOntzOjM6InVybCI7czozNToiaHR0cDovLzEyNy4wLjAuMTo4MDAwL3RlYWNoZXIvaG8tc28iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX1zOjY6ImxvY2FsZSI7czoyOiJ2aSI7czo1NToibG9naW5fc3R1ZGVudHNfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI7aToxO3M6ODoic3R1ZGVudHMiO2E6MTp7czoxMDoidHdvX2ZhY3RvciI7YToxOntzOjExOiJ2ZXJpZmllZF9hdCI7aToxNzc5ODY2MDM0O319fQ==', 1779870723);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `settings`
--

CREATE TABLE `settings` (
  `id` int(10) UNSIGNED NOT NULL,
  `key` varchar(255) NOT NULL,
  `value` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `settings`
--

INSERT INTO `settings` (`id`, `key`, `value`, `created_at`, `updated_at`) VALUES
(1, 'site_name', 'ABC Udemy', NULL, '2026-06-13 04:57:07'),
(2, 'email', 'abcudemy@gmail.com', NULL, '2026-06-13 04:57:07'),
(3, 'phone', '0123456789', NULL, '2026-06-13 04:57:07'),
(4, 'address', 'Việt Nam', NULL, '2026-06-13 04:57:07'),
(5, 'facebook', '#', NULL, '2026-06-13 04:57:07'),
(6, 'instagram', '#', NULL, '2026-06-13 04:57:07'),
(7, 'youtube', '#', NULL, '2026-06-13 04:57:07'),
(8, 'tiktok', '#', NULL, '2026-06-13 04:57:07'),
(11, 'banner_slider', '[\"banners\\/slider\\/CmuCrlfrfNJIj9ZyowYNZ0jsN2ISpOBR26O72rSL.png\",\"banners\\/slider\\/w0ZJbiiWDbTwkuwJIqQinIOEBiD8NhZs6H5B67G1.png\",\"banners\\/slider\\/ZbIttMpwlLILR18ywik5kQhBtvdUqfRnjCMHL95B.png\"]', '2026-02-03 09:45:18', '2026-03-29 15:11:18'),
(12, 'banner_right', '[\"banners\\/right\\/ceIJGz23fIlcYRfUFK5IkQNd0EVp3oWFZpE3jPm8.png\",\"banners\\/right\\/xp0UzMCnuev6p49MCsbv1xdq26Q3gQdYiieFDlar.png\",\"banners\\/right\\/gXgn64e9NTdIv913ckoCOEwCJzN014IX5ovhuRjK.png\"]', '2026-02-03 09:54:31', '2026-03-29 15:11:18'),
(13, 'banner_full', 'banners/full/Mzh4FxhZBjEHowWipvYCcvpI4ecnXdDyyRfgo60g.png', '2026-02-03 09:55:23', '2026-03-29 15:11:18'),
(15, 'logo', NULL, '2026-02-03 10:15:42', '2026-02-03 10:35:59'),
(16, 'currency_rate_usd', '25000', '2026-03-29 10:25:09', '2026-06-13 04:57:07'),
(17, 'currency_rate_krw', '18', '2026-03-29 10:25:09', '2026-06-13 04:57:07'),
(18, 'currency_rate_jpy', '170', '2026-03-29 10:25:09', '2026-06-13 04:57:07'),
(19, 'currency_rate_cny', '3500', '2026-03-29 10:25:09', '2026-06-13 04:57:07'),
(28, 'checkout_countdown_minutes', '5', '2026-03-29 10:27:03', '2026-03-29 10:27:03'),
(29, 'student_two_factor_timeout', '600', '2026-03-29 10:27:03', '2026-03-29 10:27:03'),
(30, 'student_two_factor_code_expire', '600', '2026-03-29 10:27:03', '2026-03-29 10:27:03'),
(31, 'student_two_factor_resend_cooldown', '60', '2026-03-29 10:27:03', '2026-03-29 10:27:03'),
(32, 'chatbot_message_ttl_minutes', '5', '2026-03-29 10:27:03', '2026-03-29 10:27:03'),
(33, 'chatbot_widget_enabled', '1', '2026-03-29 10:27:03', '2026-03-29 16:03:38'),
(34, 'chatbot_enabled', '1', '2026-03-29 10:27:03', '2026-05-27 08:29:58'),
(41, 'global_notice_title', 'Đừng quên sử dụng mã giảm giá để tiết kiệm chi phí học tập', '2026-03-29 10:27:03', '2026-03-29 15:21:51'),
(42, 'global_notice_content', '<p><strong>Đừng qu&ecirc;n sử dụng m&atilde; giảm gi&aacute; 50% để tiết kiệm chi ph&iacute; học tập</strong></p>', '2026-03-29 10:27:03', '2026-03-29 15:21:51'),
(43, 'global_notice_link_label', 'Xem danh sách khuyến mãi', '2026-03-29 10:27:03', '2026-03-29 15:21:51'),
(44, 'global_notice_link_url', 'http://127.0.0.1:8000/vi/ma-giam-gia', '2026-03-29 10:27:03', '2026-03-29 15:21:51'),
(45, 'global_notice_title_en', 'Don’t forget to use the discount code to save on your learning costs.', '2026-03-29 10:27:03', '2026-03-29 15:21:51'),
(46, 'global_notice_content_en', '<p><strong>Don&rsquo;t forget to use the 50% discount code to save on your learning costs.</strong></p>', '2026-03-29 10:27:03', '2026-03-29 15:21:51'),
(47, 'global_notice_link_label_en', 'View promotions list', '2026-03-29 10:27:03', '2026-03-29 15:21:51'),
(48, 'global_notice_link_url_en', 'http://127.0.0.1:8000/vi/ma-giam-gia', '2026-03-29 10:27:03', '2026-03-29 15:21:51'),
(49, 'global_notice_title_ko', '학습 비용을 절약하려면 할인 코드를 사용하는 것을 잊지 마세요.', '2026-03-29 10:27:03', '2026-03-29 15:21:51'),
(50, 'global_notice_content_ko', '<p><strong>학습 비용을 절약하려면 50% 할인 코드를 사용하는 것을 잊지 마세요.</strong></p>', '2026-03-29 10:27:03', '2026-03-29 15:21:51'),
(51, 'global_notice_link_label_ko', '프로모션 목록 보기', '2026-03-29 10:27:03', '2026-03-29 15:21:51'),
(52, 'global_notice_link_url_ko', 'http://127.0.0.1:8000/vi/ma-giam-gia', '2026-03-29 10:27:03', '2026-03-29 15:21:51'),
(53, 'global_notice_title_ja', '学習費用を節約するために、割引コードの使用をお忘れなく。', '2026-03-29 10:27:03', '2026-03-29 15:21:51'),
(54, 'global_notice_content_ja', '<p><strong>学習費用を節約するために、50％割引コードの使用をお忘れなく。</strong></p>', '2026-03-29 10:27:03', '2026-03-29 15:21:51'),
(55, 'global_notice_link_label_ja', 'プロモーション一覧を見る', '2026-03-29 10:27:03', '2026-03-29 15:21:51'),
(56, 'global_notice_link_url_ja', 'http://127.0.0.1:8000/vi/ma-giam-gia', '2026-03-29 10:27:03', '2026-03-29 15:21:51'),
(57, 'global_notice_title_zh', '别忘了使用优惠码来节省学习费用。', '2026-03-29 10:27:03', '2026-03-29 15:21:51'),
(58, 'global_notice_content_zh', '<p><strong>别忘了使用50%的优惠码来节省学习费用。</strong></p>', '2026-03-29 10:27:03', '2026-03-29 15:21:51'),
(59, 'global_notice_link_label_zh', '查看优惠列表**', '2026-03-29 10:27:03', '2026-03-29 15:21:51'),
(60, 'global_notice_link_url_zh', 'http://127.0.0.1:8000/vi/ma-giam-gia', '2026-03-29 10:27:03', '2026-03-29 15:21:51'),
(61, 'popup_notice_title', '✨ Ưu đãi học tập dành riêng cho bạn', '2026-03-29 10:27:03', '2026-03-29 15:21:51'),
(62, 'popup_notice_content', '<p style=\"text-align:center\"><img alt=\"\" src=\"/storage/photos/2/popup.png\" style=\"height:419px; width:550px\" /></p>\r\n\r\n<p style=\"text-align:center\">&nbsp;</p>\r\n\r\n<p style=\"text-align:center\"><strong>Đừng qu&ecirc;n sử dụng m&atilde; giảm gi&aacute; 50% để tiết kiệm chi ph&iacute; học tập</strong></p>', '2026-03-29 10:27:03', '2026-03-29 15:22:34'),
(63, 'popup_notice_link_label', 'Xem ưu đãi ngay', '2026-03-29 10:27:03', '2026-03-29 15:21:51'),
(64, 'popup_notice_link_url', 'http://127.0.0.1:8000/vi/ma-giam-gia', '2026-03-29 10:27:03', '2026-03-29 15:21:51'),
(65, 'popup_notice_title_en', '✨ Exclusive Learning Offers Just for You', '2026-03-29 10:27:03', '2026-03-29 15:21:51'),
(66, 'popup_notice_content_en', '<p style=\"text-align:center\"><img alt=\"\" src=\"/storage/photos/2/popup.png\" style=\"height:419px; width:550px\" /></p>\r\n\r\n<p style=\"text-align:center\">&nbsp;</p>\r\n\r\n<p style=\"text-align:center\"><strong>Don&rsquo;t forget to use the 50% discount code to save on your learning costs.</strong></p>', '2026-03-29 10:27:03', '2026-03-29 15:22:34'),
(67, 'popup_notice_link_label_en', 'View Offers Now', '2026-03-29 10:27:03', '2026-03-29 15:21:51'),
(68, 'popup_notice_link_url_en', 'http://127.0.0.1:8000/vi/ma-giam-gia', '2026-03-29 10:27:03', '2026-03-29 15:21:51'),
(69, 'popup_notice_title_ko', '✨ 당신만을 위한 특별 학습 혜택', '2026-03-29 10:27:03', '2026-03-29 15:21:51'),
(70, 'popup_notice_content_ko', '<p style=\"text-align:center\"><img alt=\"\" src=\"/storage/photos/2/popup.png\" style=\"height:419px; width:550px\" /></p>\r\n\r\n<p style=\"text-align:center\">&nbsp;</p>\r\n\r\n<p style=\"text-align:center\"><strong>학습 비용을 절약하려면 50% 할인 코드를 사용하는 것을 잊지 마세요.</strong></p>', '2026-03-29 10:27:03', '2026-03-29 15:22:34'),
(71, 'popup_notice_link_label_ko', '지금 혜택 보기', '2026-03-29 10:27:03', '2026-03-29 15:21:51'),
(72, 'popup_notice_link_url_ko', 'http://127.0.0.1:8000/vi/ma-giam-gia', '2026-03-29 10:27:03', '2026-03-29 15:21:51'),
(73, 'popup_notice_title_ja', '✨ あなたのための特別な学習特典', '2026-03-29 10:27:03', '2026-03-29 15:21:51'),
(74, 'popup_notice_content_ja', '<p style=\"text-align:center\"><img alt=\"\" src=\"/storage/photos/2/popup.png\" style=\"height:419px; width:550px\" /></p>\r\n\r\n<p style=\"text-align:center\">&nbsp;</p>\r\n\r\n<p style=\"text-align:center\"><strong>学習費用を節約するために、50％割引コードの使用をお忘れなく。</strong></p>', '2026-03-29 10:27:03', '2026-03-29 15:22:34'),
(75, 'popup_notice_link_label_ja', '今すぐ特典を見る', '2026-03-29 10:27:03', '2026-03-29 15:21:51'),
(76, 'popup_notice_link_url_ja', 'http://127.0.0.1:8000/vi/ma-giam-gia', '2026-03-29 10:27:03', '2026-03-29 15:21:51'),
(77, 'popup_notice_title_zh', '✨ 专属于你的学习优惠', '2026-03-29 10:27:03', '2026-03-29 15:21:51'),
(78, 'popup_notice_content_zh', '<p style=\"text-align:center\"><img alt=\"\" src=\"/storage/photos/2/popup.png\" style=\"height:419px; width:550px\" /></p>\r\n\r\n<p style=\"text-align:center\">&nbsp;</p>\r\n\r\n<p style=\"text-align:center\"><strong>别忘了使用50%的优惠码来节省学习费用。</strong></p>', '2026-03-29 10:27:03', '2026-03-29 15:22:34'),
(79, 'popup_notice_link_label_zh', '立即查看优惠', '2026-03-29 10:27:03', '2026-03-29 15:21:51'),
(80, 'popup_notice_link_url_zh', 'http://127.0.0.1:8000/vi/ma-giam-gia', '2026-03-29 10:27:03', '2026-03-29 15:21:51'),
(81, 'popup_notice_snooze_minutes', '10', '2026-03-29 10:27:03', '2026-03-29 15:21:51'),
(82, 'global_notice_enabled', '1', '2026-03-29 10:27:03', '2026-03-29 15:21:51'),
(83, 'popup_notice_enabled', '1', '2026-03-29 10:27:03', '2026-03-29 15:21:51'),
(84, 'mail_enabled', '0', '2026-03-29 10:27:03', '2026-04-30 16:54:09'),
(87, 'max_devices', '2', '2026-03-29 15:36:42', '2026-03-29 15:36:49'),
(88, 'ai_quiz_enabled', '1', '2026-04-16 16:40:53', '2026-05-27 08:30:33'),
(89, 'exchange_rate_api_key', 'fx_placeholder', '2026-04-24 05:55:44', '2026-06-13 04:56:54'),
(90, 'currency_conversion_fee', '2', '2026-04-24 05:55:44', '2026-04-24 14:21:14'),
(91, 'payment_bank_enabled', '1', '2026-04-26 13:41:19', '2026-05-11 05:21:34'),
(92, 'payment_momo_enabled', '1', '2026-04-26 13:41:19', '2026-05-11 05:21:41'),
(93, 'payment_vnpay_enabled', '1', '2026-04-26 13:41:19', '2026-05-11 05:21:41'),
(94, 'bank_transfer_bank_bin', '970407', '2026-04-26 13:57:44', '2026-04-26 13:57:44'),
(95, 'bank_transfer_bank_name', 'Techcombank', '2026-04-26 13:57:44', '2026-04-26 13:57:44'),
(96, 'bank_transfer_account_number', '61043040524', '2026-04-26 13:57:44', '2026-04-26 14:09:05'),
(97, 'bank_transfer_account_name', 'NGUYEN DUY KHANH', '2026-04-26 13:57:44', '2026-04-26 14:09:05'),
(98, 'bank_transfer_note_prefix', 'KH', '2026-04-26 13:57:44', '2026-04-26 14:09:05'),
(99, 'teacher_badge_notification_email_enabled', '0', '2026-04-27 13:36:08', '2026-04-27 13:36:08'),
(100, 'min_payout_amount', '5000', '2026-04-28 14:55:47', '2026-04-28 15:17:46'),
(101, 'admin_max_devices', '3', '2026-04-29 15:35:35', '2026-04-29 15:35:35'),
(102, 'maintenance_mode', '0', '2026-04-29 15:35:35', '2026-04-30 04:53:56'),
(103, 'maintenance_whitelist_ips', '127.0.0.1', '2026-04-29 15:35:35', '2026-04-29 15:35:35'),
(104, 'maintenance_message', 'Website đang bảo trì định kỳ. Vui lòng quay lại sau.', '2026-04-29 15:35:35', '2026-04-29 15:35:35'),
(105, 'theme_primary_color', '#0d6efd', '2026-04-29 15:35:35', '2026-04-29 15:35:35'),
(106, 'telegram_bot_enabled', '1', '2026-04-29 16:18:58', '2026-04-30 04:53:10'),
(107, 'payment_wallet_enabled', '1', '2026-05-11 05:27:48', '2026-05-11 06:51:22');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `students`
--

CREATE TABLE `students` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(50) DEFAULT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `password` varchar(100) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `two_factor_email_enabled` tinyint(1) NOT NULL DEFAULT 0,
  `two_factor_email_enabled_at` timestamp NULL DEFAULT NULL,
  `two_factor_email_code` varchar(255) DEFAULT NULL,
  `two_factor_email_purpose` varchar(40) DEFAULT NULL,
  `two_factor_email_code_expires_at` timestamp NULL DEFAULT NULL,
  `two_factor_email_code_sent_at` timestamp NULL DEFAULT NULL,
  `last_login_at` timestamp NULL DEFAULT NULL,
  `last_login_ip` varchar(64) DEFAULT NULL,
  `last_login_user_agent` text DEFAULT NULL,
  `last_login_browser` varchar(255) DEFAULT NULL,
  `last_login_platform` varchar(255) DEFAULT NULL,
  `last_login_device` varchar(255) DEFAULT NULL,
  `address` varchar(200) DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 0,
  `preferred_locale` varchar(5) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `students_courses`
--

CREATE TABLE `students_courses` (
  `id` int(10) UNSIGNED NOT NULL,
  `student_id` int(10) UNSIGNED NOT NULL,
  `course_id` int(10) UNSIGNED NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `student_lesson_progress`
--

CREATE TABLE `student_lesson_progress` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `student_id` bigint(20) UNSIGNED NOT NULL,
  `course_id` bigint(20) UNSIGNED NOT NULL,
  `lesson_id` bigint(20) UNSIGNED NOT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `student_password_rests`
--

CREATE TABLE `student_password_rests` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `teacher`
--

CREATE TABLE `teacher` (
  `id` int(10) UNSIGNED NOT NULL,
  `student_id` int(10) UNSIGNED DEFAULT NULL,
  `telegram_chat_id` varchar(255) DEFAULT NULL,
  `is_telegram_notifications_enabled` tinyint(1) NOT NULL DEFAULT 0,
  `telegram_feature_expires_at` timestamp NULL DEFAULT NULL,
  `application_id` int(10) UNSIGNED DEFAULT NULL,
  `name` varchar(100) DEFAULT NULL,
  `name_en` varchar(225) DEFAULT NULL,
  `name_ko` varchar(225) DEFAULT NULL,
  `name_ja` varchar(225) DEFAULT NULL,
  `name_zh` varchar(225) DEFAULT NULL,
  `slug` varchar(100) DEFAULT NULL,
  `slug_en` varchar(225) DEFAULT NULL,
  `slug_ko` varchar(225) DEFAULT NULL,
  `slug_ja` varchar(225) DEFAULT NULL,
  `slug_zh` varchar(225) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `description_en` text DEFAULT NULL,
  `description_ko` text DEFAULT NULL,
  `description_ja` text DEFAULT NULL,
  `description_zh` text DEFAULT NULL,
  `exp` int(11) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `status` varchar(30) NOT NULL DEFAULT 'pending',
  `is_locked` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Trạng thái khóa giáo viên (không khóa học viên)',
  `lock_reason` text DEFAULT NULL COMMENT 'Lý do khóa tài khoản giáo viên',
  `locked_at` timestamp NULL DEFAULT NULL,
  `locked_by` bigint(20) UNSIGNED DEFAULT NULL,
  `is_verified_badge` tinyint(1) NOT NULL DEFAULT 0,
  `is_premium_badge` tinyint(1) NOT NULL DEFAULT 0,
  `badge_key` varchar(50) DEFAULT NULL,
  `badge_label` varchar(100) DEFAULT NULL,
  `badge_tone` varchar(30) DEFAULT NULL,
  `commission_rate` decimal(5,2) NOT NULL DEFAULT 50.00,
  `approved_at` timestamp NULL DEFAULT NULL,
  `approved_by` int(10) UNSIGNED DEFAULT NULL,
  `package_started_at` datetime DEFAULT NULL,
  `package_expires_at` datetime DEFAULT NULL,
  `last_active_at` timestamp NULL DEFAULT NULL,
  `inactive_teacher_notified_at` timestamp NULL DEFAULT NULL,
  `inactive_admin_notified_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `teacher_affiliate_links`
--

CREATE TABLE `teacher_affiliate_links` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `teacher_id` int(10) UNSIGNED NOT NULL,
  `name` varchar(120) NOT NULL,
  `code` varchar(40) NOT NULL,
  `target_type` varchar(20) NOT NULL,
  `target_id` bigint(20) UNSIGNED DEFAULT NULL,
  `clicks_count` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `last_clicked_at` timestamp NULL DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `teacher_affiliate_link_clicks`
--

CREATE TABLE `teacher_affiliate_link_clicks` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `affiliate_link_id` bigint(20) UNSIGNED NOT NULL,
  `teacher_id` int(10) UNSIGNED NOT NULL,
  `student_id` int(10) UNSIGNED DEFAULT NULL,
  `target_type` varchar(20) NOT NULL,
  `target_id` bigint(20) UNSIGNED DEFAULT NULL,
  `locale` varchar(5) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `referer_url` varchar(500) DEFAULT NULL,
  `target_url` varchar(500) DEFAULT NULL,
  `clicked_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `teacher_announcements`
--

CREATE TABLE `teacher_announcements` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `title_en` varchar(255) DEFAULT NULL,
  `title_ko` varchar(255) DEFAULT NULL,
  `title_ja` varchar(255) DEFAULT NULL,
  `title_zh` varchar(255) DEFAULT NULL,
  `message` text NOT NULL,
  `message_en` text DEFAULT NULL,
  `message_ko` text DEFAULT NULL,
  `message_ja` text DEFAULT NULL,
  `message_zh` text DEFAULT NULL,
  `action_url` varchar(255) DEFAULT NULL,
  `action_label` varchar(255) DEFAULT NULL,
  `action_label_en` varchar(255) DEFAULT NULL,
  `action_label_ko` varchar(255) DEFAULT NULL,
  `action_label_ja` varchar(255) DEFAULT NULL,
  `action_label_zh` varchar(255) DEFAULT NULL,
  `icon` varchar(255) DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `is_pinned` tinyint(1) NOT NULL DEFAULT 0,
  `starts_at` timestamp NULL DEFAULT NULL,
  `ends_at` timestamp NULL DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `teacher_announcement_package`
--

CREATE TABLE `teacher_announcement_package` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `announcement_id` bigint(20) UNSIGNED NOT NULL,
  `package_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `teacher_announcement_reads`
--

CREATE TABLE `teacher_announcement_reads` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `announcement_id` bigint(20) UNSIGNED NOT NULL,
  `student_id` int(10) UNSIGNED NOT NULL,
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `teacher_applications`
--

CREATE TABLE `teacher_applications` (
  `id` int(10) UNSIGNED NOT NULL,
  `student_id` int(10) UNSIGNED DEFAULT NULL,
  `applicant_type` varchar(20) NOT NULL DEFAULT 'student',
  `teacher_id` int(10) UNSIGNED DEFAULT NULL,
  `package_id` int(10) UNSIGNED DEFAULT NULL,
  `type` varchar(20) NOT NULL DEFAULT 'new',
  `payment_method` varchar(30) DEFAULT NULL,
  `coupon_code` varchar(50) DEFAULT NULL,
  `discount_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `status` varchar(30) NOT NULL DEFAULT 'draft',
  `full_name` varchar(100) NOT NULL,
  `display_name` varchar(100) DEFAULT NULL,
  `headline` varchar(255) DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `experience_years` int(11) DEFAULT NULL,
  `specialties` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`specialties`)),
  `phone` varchar(30) DEFAULT NULL,
  `email` varchar(100) NOT NULL,
  `locale` varchar(5) DEFAULT NULL,
  `portfolio_url` varchar(255) DEFAULT NULL,
  `facebook_url` varchar(255) DEFAULT NULL,
  `youtube_url` varchar(255) DEFAULT NULL,
  `linkedin_url` varchar(255) DEFAULT NULL,
  `custom_links` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`custom_links`)),
  `intro_video_url` varchar(255) DEFAULT NULL,
  `cv_file` varchar(255) DEFAULT NULL,
  `cv_file_path` varchar(255) DEFAULT NULL,
  `identity_file` varchar(255) DEFAULT NULL,
  `identity_file_path` varchar(255) DEFAULT NULL,
  `submitted_at` timestamp NULL DEFAULT NULL,
  `reviewed_at` timestamp NULL DEFAULT NULL,
  `activates_at` datetime DEFAULT NULL,
  `package_started_at` datetime DEFAULT NULL,
  `package_expires_at` datetime DEFAULT NULL,
  `activated_at` datetime DEFAULT NULL,
  `account_created_at` timestamp NULL DEFAULT NULL,
  `account_credentials_sent_at` timestamp NULL DEFAULT NULL,
  `reviewed_by` int(10) UNSIGNED DEFAULT NULL,
  `granted_by` bigint(20) UNSIGNED DEFAULT NULL COMMENT 'Admin ID đã tặng gói. NULL = đăng ký/nâng cấp thông thường',
  `claim_token` varchar(64) DEFAULT NULL COMMENT 'Token bảo mật trong link email để giáo viên xác nhận nhận gói',
  `claim_expires_at` timestamp NULL DEFAULT NULL COMMENT 'Thời hạn xác nhận nhận gói',
  `claimed_at` timestamp NULL DEFAULT NULL COMMENT 'Thời điểm giáo viên đã bấm Nhận gói',
  `admin_note` text DEFAULT NULL,
  `note` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `teacher_badges`
--

CREATE TABLE `teacher_badges` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`name`)),
  `code` varchar(255) NOT NULL,
  `icon` varchar(255) DEFAULT NULL,
  `color_bg` varchar(255) NOT NULL DEFAULT '#f1f5f9',
  `color_text` varchar(255) NOT NULL DEFAULT '#0f172a',
  `description` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`description`)),
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `teacher_cancellation_requests`
--

CREATE TABLE `teacher_cancellation_requests` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `teacher_id` int(10) UNSIGNED NOT NULL,
  `reason` text NOT NULL,
  `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `admin_note` text DEFAULT NULL,
  `processed_by` int(10) UNSIGNED DEFAULT NULL,
  `processed_at` timestamp NULL DEFAULT NULL,
  `otp_code` varchar(10) DEFAULT NULL,
  `otp_expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `teacher_course_bundles`
--

CREATE TABLE `teacher_course_bundles` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `teacher_id` int(10) UNSIGNED NOT NULL,
  `name` varchar(160) NOT NULL,
  `slug` varchar(190) NOT NULL,
  `description` text DEFAULT NULL,
  `thumbnail` varchar(255) DEFAULT NULL,
  `price` decimal(12,2) NOT NULL DEFAULT 0.00,
  `sale_price` double(8,2) DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL COMMENT 'Số lượng giới hạn, null là không giới hạn',
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `is_coming_soon` tinyint(1) NOT NULL DEFAULT 0,
  `coming_soon_start_at` timestamp NULL DEFAULT NULL,
  `end_at` timestamp NULL DEFAULT NULL COMMENT 'Ngày giờ kết thúc bán',
  `is_hot` tinyint(1) NOT NULL DEFAULT 0,
  `position` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `teacher_course_bundle_items`
--

CREATE TABLE `teacher_course_bundle_items` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `bundle_id` bigint(20) UNSIGNED NOT NULL,
  `course_id` int(10) UNSIGNED NOT NULL,
  `position` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `teacher_course_certificates`
--

CREATE TABLE `teacher_course_certificates` (
  `id` int(10) UNSIGNED NOT NULL,
  `teacher_id` int(10) UNSIGNED NOT NULL,
  `student_id` int(10) UNSIGNED NOT NULL,
  `course_id` int(10) UNSIGNED NOT NULL,
  `issued_by_student_id` int(10) UNSIGNED DEFAULT NULL,
  `code` varchar(40) NOT NULL,
  `student_name_snapshot` varchar(225) NOT NULL,
  `course_name_snapshot` varchar(255) NOT NULL,
  `teacher_name_snapshot` varchar(225) NOT NULL,
  `completed_lessons` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `total_lessons` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `progress_percent` tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
  `note` text DEFAULT NULL,
  `issued_at` timestamp NULL DEFAULT NULL,
  `revoked_at` timestamp NULL DEFAULT NULL,
  `revoked_by_student_id` int(10) UNSIGNED DEFAULT NULL,
  `revoke_reason` varchar(500) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `teacher_course_grants`
--

CREATE TABLE `teacher_course_grants` (
  `id` int(10) UNSIGNED NOT NULL,
  `teacher_id` int(10) UNSIGNED NOT NULL,
  `student_id` int(10) UNSIGNED NOT NULL,
  `course_id` int(10) UNSIGNED NOT NULL,
  `reason` varchar(50) NOT NULL DEFAULT 'gift',
  `note` text DEFAULT NULL,
  `status` varchar(30) NOT NULL DEFAULT 'accepted',
  `token` varchar(100) DEFAULT NULL,
  `locale` varchar(10) DEFAULT NULL,
  `invited_at` timestamp NULL DEFAULT NULL,
  `accepted_at` timestamp NULL DEFAULT NULL,
  `revoked_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `teacher_has_badges`
--

CREATE TABLE `teacher_has_badges` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `teacher_id` int(10) UNSIGNED NOT NULL,
  `badge_id` int(10) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `teacher_notification_reads`
--

CREATE TABLE `teacher_notification_reads` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `student_id` int(10) UNSIGNED NOT NULL,
  `notification_key` varchar(191) NOT NULL,
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `teacher_packages`
--

CREATE TABLE `teacher_packages` (
  `id` int(10) UNSIGNED NOT NULL,
  `category_id` bigint(20) UNSIGNED DEFAULT NULL,
  `code` varchar(50) NOT NULL,
  `badge_tone` varchar(255) DEFAULT NULL,
  `name` varchar(100) NOT NULL,
  `name_en` varchar(100) DEFAULT NULL,
  `name_ko` varchar(100) DEFAULT NULL,
  `name_ja` varchar(100) DEFAULT NULL,
  `name_zh` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `description_en` text DEFAULT NULL,
  `description_ko` text DEFAULT NULL,
  `description_ja` text DEFAULT NULL,
  `description_zh` text DEFAULT NULL,
  `tagline` varchar(190) DEFAULT NULL,
  `tagline_en` varchar(190) DEFAULT NULL,
  `tagline_ko` varchar(190) DEFAULT NULL,
  `tagline_ja` varchar(190) DEFAULT NULL,
  `tagline_zh` varchar(190) DEFAULT NULL,
  `badge_text` varchar(100) DEFAULT NULL,
  `badge_text_en` varchar(100) DEFAULT NULL,
  `badge_text_ko` varchar(100) DEFAULT NULL,
  `badge_text_ja` varchar(100) DEFAULT NULL,
  `badge_text_zh` varchar(100) DEFAULT NULL,
  `price` decimal(12,2) NOT NULL DEFAULT 0.00,
  `max_payout_per_day` decimal(18,2) DEFAULT NULL COMMENT 'Hạn mức rút tiền tối đa / ngày (VND), null là không giới hạn',
  `billing_cycle` varchar(20) NOT NULL DEFAULT 'one_time',
  `category` varchar(50) NOT NULL DEFAULT 'standard',
  `category_en` varchar(255) DEFAULT NULL,
  `category_ko` varchar(255) DEFAULT NULL,
  `category_ja` varchar(255) DEFAULT NULL,
  `category_zh` varchar(255) DEFAULT NULL,
  `course_limit` int(11) DEFAULT NULL,
  `payout_account_limit` tinyint(3) UNSIGNED NOT NULL DEFAULT 3,
  `commission_rate` decimal(5,2) NOT NULL DEFAULT 50.00,
  `priority_review` tinyint(1) NOT NULL DEFAULT 0,
  `can_request_payouts` tinyint(1) NOT NULL DEFAULT 1,
  `can_duplicate_courses` tinyint(1) NOT NULL DEFAULT 0,
  `can_manage_comments` tinyint(1) NOT NULL DEFAULT 0,
  `can_manage_coupons` tinyint(1) NOT NULL DEFAULT 0,
  `can_manage_students` tinyint(1) NOT NULL DEFAULT 0,
  `can_view_student_progress` tinyint(1) NOT NULL DEFAULT 0,
  `can_view_activity_logs` tinyint(1) NOT NULL DEFAULT 0,
  `can_import_export` tinyint(1) NOT NULL DEFAULT 0,
  `can_manage_quizzes` tinyint(1) NOT NULL DEFAULT 0,
  `can_use_ai_quiz` tinyint(1) NOT NULL DEFAULT 0,
  `coupon_limit` int(10) UNSIGNED DEFAULT NULL,
  `ai_quiz_limit` int(11) DEFAULT NULL COMMENT 'Giới hạn số lần AI sinh Quiz mỗi ngày',
  `can_grant_courses` tinyint(1) NOT NULL DEFAULT 0,
  `can_export_orders` tinyint(1) NOT NULL DEFAULT 0,
  `can_export_students` tinyint(1) NOT NULL DEFAULT 0,
  `can_import_export_lessons` tinyint(1) NOT NULL DEFAULT 0,
  `can_sell_bundles` tinyint(1) NOT NULL DEFAULT 0,
  `can_schedule_content` tinyint(1) NOT NULL DEFAULT 0,
  `can_send_promotions` tinyint(1) NOT NULL DEFAULT 0,
  `can_issue_certificates` tinyint(1) NOT NULL DEFAULT 0,
  `can_verify_certificates` tinyint(1) NOT NULL DEFAULT 0,
  `can_customize_teacher_landing` tinyint(1) NOT NULL DEFAULT 0,
  `can_use_affiliate_links` tinyint(1) NOT NULL DEFAULT 0,
  `support_level` varchar(50) DEFAULT NULL,
  `support_level_en` varchar(50) DEFAULT NULL,
  `support_level_ko` varchar(50) DEFAULT NULL,
  `support_level_ja` varchar(50) DEFAULT NULL,
  `support_level_zh` varchar(50) DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `hidden_mode` varchar(20) NOT NULL DEFAULT 'unavailable',
  `is_featured` tinyint(1) NOT NULL DEFAULT 0,
  `is_exclusive` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Gói đặc quyền tặng riêng – không hiển thị trên trang bảng giá công khai',
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `teacher_package_categories`
--

CREATE TABLE `teacher_package_categories` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `name_en` varchar(255) DEFAULT NULL,
  `name_ko` varchar(255) DEFAULT NULL,
  `name_ja` varchar(255) DEFAULT NULL,
  `name_zh` varchar(255) DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 1,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `teacher_package_categories`
--

INSERT INTO `teacher_package_categories` (`id`, `name`, `name_en`, `name_ko`, `name_ja`, `name_zh`, `sort_order`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Khởi đầu', 'Getting Started', '시작하기', 'はじめに', '开始', 1, 1, '2026-05-07 06:12:51', '2026-05-07 06:12:51'),
(2, 'IT TEST', 'IT TEST', 'IT TEST', 'IT TEST', 'IT TEST', 2, 1, '2026-05-07 06:13:01', '2026-05-07 06:13:01');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `teacher_package_features`
--

CREATE TABLE `teacher_package_features` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `key` varchar(255) NOT NULL,
  `name_vi` varchar(255) DEFAULT NULL,
  `name_en` varchar(255) DEFAULT NULL,
  `name_ko` varchar(255) DEFAULT NULL,
  `name_ja` varchar(255) DEFAULT NULL,
  `name_zh` varchar(255) DEFAULT NULL,
  `description_vi` text DEFAULT NULL,
  `description_en` text DEFAULT NULL,
  `description_ko` text DEFAULT NULL,
  `description_ja` text DEFAULT NULL,
  `description_zh` text DEFAULT NULL,
  `group` varchar(255) DEFAULT NULL,
  `icon` varchar(255) DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `is_enabled` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `teacher_payout_accounts`
--

CREATE TABLE `teacher_payout_accounts` (
  `id` int(10) UNSIGNED NOT NULL,
  `teacher_id` int(10) UNSIGNED NOT NULL,
  `bank_name` varchar(100) NOT NULL,
  `bank_account_name` varchar(120) NOT NULL,
  `bank_account_number` varchar(50) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `teacher_payout_account_change_requests`
--

CREATE TABLE `teacher_payout_account_change_requests` (
  `id` int(10) UNSIGNED NOT NULL,
  `teacher_id` int(10) UNSIGNED NOT NULL,
  `replace_payout_account_id` int(10) UNSIGNED DEFAULT NULL,
  `replace_bank_name` varchar(100) DEFAULT NULL,
  `replace_bank_account_name` varchar(120) DEFAULT NULL,
  `replace_bank_account_number` varchar(50) DEFAULT NULL,
  `bank_name` varchar(100) NOT NULL,
  `bank_account_name` varchar(120) NOT NULL,
  `bank_account_number` varchar(50) NOT NULL,
  `note` text DEFAULT NULL,
  `admin_note` text DEFAULT NULL,
  `status` varchar(30) NOT NULL DEFAULT 'pending',
  `processed_at` timestamp NULL DEFAULT NULL,
  `processed_by` int(10) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `teacher_payout_requests`
--

CREATE TABLE `teacher_payout_requests` (
  `id` int(10) UNSIGNED NOT NULL,
  `teacher_id` int(10) UNSIGNED NOT NULL,
  `amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `currency_code` varchar(10) NOT NULL DEFAULT 'VND',
  `exchange_rate` decimal(15,6) NOT NULL DEFAULT 1.000000,
  `original_amount` decimal(18,2) DEFAULT NULL,
  `converted_amount_vnd` decimal(18,2) DEFAULT NULL,
  `fee_percentage` decimal(5,2) NOT NULL DEFAULT 0.00,
  `fee_amount_vnd` decimal(18,2) NOT NULL DEFAULT 0.00,
  `bank_name` varchar(100) NOT NULL,
  `bank_account_name` varchar(120) NOT NULL,
  `bank_account_number` varchar(50) NOT NULL,
  `note` text DEFAULT NULL,
  `admin_note` text DEFAULT NULL,
  `status` varchar(30) NOT NULL DEFAULT 'requested',
  `processed_at` timestamp NULL DEFAULT NULL,
  `processed_by` int(10) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `teacher_promotions`
--

CREATE TABLE `teacher_promotions` (
  `id` int(10) UNSIGNED NOT NULL,
  `teacher_id` int(10) UNSIGNED NOT NULL,
  `course_id` int(10) UNSIGNED DEFAULT NULL,
  `created_by_student_id` int(10) UNSIGNED DEFAULT NULL,
  `title` varchar(160) NOT NULL,
  `message` text NOT NULL,
  `audience_type` varchar(30) NOT NULL DEFAULT 'all_students',
  `recipient_count` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `filters` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`filters`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `teacher_ratings`
--

CREATE TABLE `teacher_ratings` (
  `id` int(10) UNSIGNED NOT NULL,
  `teacher_id` int(10) UNSIGNED NOT NULL,
  `student_id` int(10) UNSIGNED NOT NULL,
  `rating` decimal(2,1) NOT NULL,
  `status` tinyint(4) NOT NULL DEFAULT 1 COMMENT '1: Show, 0: Hide',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `teacher_student_notes`
--

CREATE TABLE `teacher_student_notes` (
  `id` int(10) UNSIGNED NOT NULL,
  `teacher_id` int(10) UNSIGNED NOT NULL,
  `student_id` int(10) UNSIGNED NOT NULL,
  `tag` varchar(30) DEFAULT NULL,
  `note` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `teacher_telegram_subscriptions`
--

CREATE TABLE `teacher_telegram_subscriptions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `teacher_id` bigint(20) UNSIGNED NOT NULL,
  `telegram_package_id` bigint(20) UNSIGNED NOT NULL,
  `started_at` datetime DEFAULT NULL,
  `expires_at` datetime DEFAULT NULL,
  `amount` decimal(15,2) NOT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'active',
  `claim_token` varchar(255) DEFAULT NULL,
  `claimed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `telegram_packages`
--

CREATE TABLE `telegram_packages` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `name_en` varchar(255) DEFAULT NULL,
  `name_ja` varchar(255) DEFAULT NULL,
  `name_ko` varchar(255) DEFAULT NULL,
  `name_zh` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `description_en` text DEFAULT NULL,
  `description_ja` text DEFAULT NULL,
  `description_ko` text DEFAULT NULL,
  `description_zh` text DEFAULT NULL,
  `price` decimal(15,2) NOT NULL DEFAULT 0.00,
  `sale_price` decimal(15,2) DEFAULT NULL,
  `duration_value` int(11) NOT NULL DEFAULT 1,
  `duration_unit` varchar(255) NOT NULL DEFAULT 'day',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `users`
--

CREATE TABLE `users` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `group_id` int(10) UNSIGNED NOT NULL DEFAULT 1,
  `is_locked` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Khoa tai khoan admin, khong cho truy cap admin panel',
  `two_factor_email_enabled` tinyint(1) NOT NULL DEFAULT 0,
  `two_factor_email_enabled_at` timestamp NULL DEFAULT NULL,
  `two_factor_email_code` varchar(255) DEFAULT NULL,
  `two_factor_email_code_expires_at` timestamp NULL DEFAULT NULL,
  `two_factor_email_code_sent_at` timestamp NULL DEFAULT NULL,
  `last_login_at` timestamp NULL DEFAULT NULL,
  `last_login_ip` varchar(45) DEFAULT NULL,
  `last_login_user_agent` text DEFAULT NULL,
  `last_login_browser` varchar(255) DEFAULT NULL,
  `last_login_platform` varchar(255) DEFAULT NULL,
  `last_login_device` varchar(255) DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `group_id`, `is_locked`, `two_factor_email_enabled`, `two_factor_email_enabled_at`, `two_factor_email_code`, `two_factor_email_code_expires_at`, `two_factor_email_code_sent_at`, `last_login_at`, `last_login_ip`, `last_login_user_agent`, `last_login_browser`, `last_login_platform`, `last_login_device`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`, `deleted_at`) VALUES
(2, 'Super Admin', 'khanhndph39625@fpt.edu.vn', 1, 0, 0, NULL, NULL, NULL, NULL, '2026-05-27 07:14:44', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'Google Chrome', 'Windows', 'Desktop', NULL, '$2y$12$p397JSGLdL8ItAt5m4DxyOsuSROhZWAwonFXv1y5booq8LdoPCan6', 'aV0AYZ76jy4FwhGh2f6JcmiLABq2GLBwaBMARXRvhzFxc1kMX0Hd6a5PF8y2', '2026-01-12 14:40:15', '2026-05-27 07:14:45', NULL),
(8, 'Admin', 'admin@gmail.com', 1, 0, 0, NULL, NULL, NULL, NULL, '2026-05-14 06:41:11', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'Google Chrome', 'Windows', 'Desktop', NULL, '$2y$12$e6zd1eA1eeAoaxWZAas4juTOfCRPbkI699nvsWtd8UbQhOHLXBk/m', NULL, '2026-04-19 06:48:43', '2026-06-13 04:57:07', NULL);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `videos`
--

CREATE TABLE `videos` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `url` varchar(255) DEFAULT NULL,
  `size` double(8,2) DEFAULT 0.00,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `videos`
--

INSERT INTO `videos` (`id`, `name`, `url`, `size`, `created_at`, `updated_at`) VALUES
(102, 'Cài đặt công cụ - môi trường cần thiết', 'https://www.youtube.com/embed/x_0d54sbVKg?si=vwwyd_wRy1dPw8Na', 0.00, '2026-02-12 16:02:29', '2026-02-12 16:02:29'),
(103, 'Giới thiệu ngôn ngữ PHP', 'https://www.youtube.com/embed/CfnM02dPsDU?si=shpvfPH-5-3cTnA4', 0.00, '2026-02-24 06:32:22', '2026-02-24 06:32:22'),
(104, 'Lộ trình - Phương pháp học PHP & MySQL hiệu quả', 'https://www.youtube.com/embed/jhLIxDHXJFI?si=skFCg_N9aqebZ6pg', 0.00, '2026-02-24 06:35:15', '2026-02-24 06:35:15'),
(105, 'Lời giới thiệu khóa học Docker', 'https://www.youtube.com/embed/qsiO_vC7MEQ?si=kkYSSocw9jbw0g8_', 0.00, '2026-03-04 16:35:25', '2026-03-04 16:35:25'),
(106, 'Tổng quan về Docker và các lệnh cơ bản', 'https://www.youtube.com/embed/aZtg_HmwxiQ?si=16bn9A0LcyUz-tnf', 0.00, '2026-03-04 16:35:45', '2026-03-04 16:35:45'),
(107, 'Cài đặt Docker trên Windows - MacOS - Ubuntu', 'https://www.youtube.com/embed/zC5SdK6m-4A?si=cGeUBMJrN1JyWpR2', 0.00, '2026-03-04 16:36:26', '2026-03-04 16:36:26'),
(108, 'Kiến trúc và thành phần của Docker', 'https://www.youtube.com/embed/CsCgX0Cm44g?si=MYFmiJFQ6MgfcPxB', 0.00, '2026-03-04 16:36:42', '2026-03-04 16:36:42'),
(109, 'Giới thiệu tổng quan về khóa học', 'https://www.youtube.com/embed/ealfjTQEI4Q?si=POjkPBi85r1TtijS', 0.00, '2026-03-04 16:37:04', '2026-03-04 16:37:04'),
(110, 'Tại sao lập trình viên cần Git?', 'https://www.youtube.com/embed/EvPEeSBfB3E?si=p94kve0qARcDP_On', 0.00, '2026-03-04 16:37:24', '2026-03-04 16:37:24'),
(111, 'Cài đặt & Cấu hình Môi trường', 'https://www.youtube.com/embed/EvPEeSBfB3E?si=DN_HNaEmEdLes9Yx', 0.00, '2026-03-04 16:37:42', '2026-03-04 16:37:42'),
(112, 'Khởi tạo kho chứa (Repository)', 'https://youtu.be/-DgNA7nKtD8?si=U9UEa63xNcOVzwFf', 0.00, '2026-03-04 16:37:55', '2026-03-04 16:37:55'),
(113, 'Tổng quan về khóa học Deploy Web', 'https://youtu.be/YdNZ8Yv-dJs?si=PbElL46PWdKj7jW4', 0.00, '2026-03-04 16:38:31', '2026-03-04 16:38:31'),
(114, 'Tổng quan về khóa học Deploy Web', 'https://www.youtube.com/embed/YdNZ8Yv-dJs?si=AKa5ydjjw6nqIMeN', 0.00, '2026-03-04 16:39:18', '2026-03-04 16:39:18'),
(115, 'Mô hình Client - Server và cách website hoạt động', 'https://www.youtube.com/embed/OerAX-zKyvg?si=6Sik9q3Ak_NE7Ycb', 0.00, '2026-03-04 16:39:36', '2026-03-04 16:39:36'),
(116, 'Tên miền là gì? Cách mua và chọn nhà cung cấp tên miền', 'https://www.youtube.com/embed/GKxkw0FQBm0?si=6kshDlNnWdbDHbk3', 0.00, '2026-03-04 16:39:58', '2026-03-04 16:39:58'),
(117, 'Tổng quan về khóa học Deploy Web', 'https://www.youtube.com/embed/GKxkw0FQBm0?si=g_-Qv_w-9IHaKCHi', 0.00, '2026-03-04 16:40:33', '2026-03-04 16:40:33'),
(118, 'Cách đăng ký và sở hữu hosting Cpanel miễn phí', 'https://www.youtube.com/embed/dzdJEc3vu_E?si=kOKz6pXDq3QC8UKI', 0.00, '2026-03-04 16:40:49', '2026-03-04 16:40:49'),
(119, 'Giới thiệu về React Native và cách hoạt động', 'https://www.youtube.com/embed/kZnpzY7A1u4?si=C7rJ9Bwviy_55U-u', 0.00, '2026-03-04 16:45:08', '2026-03-04 16:45:08'),
(120, 'Cài đặt môi trường và cài đặt React Native', 'https://www.youtube.com/embed/cKBRHaPdjbc?si=cbgAvk3q6nbvs1m7', 0.00, '2026-03-04 16:46:41', '2026-03-04 16:46:41'),
(121, 'Chạy dự án trên thiết bị thật và thiết bị giả lập', 'https://www.youtube.com/embed/cKBRHaPdjbc?si=QdoPlUZ5mc5fXPDm', 0.00, '2026-03-04 16:48:00', '2026-03-04 16:48:00'),
(122, 'Basic Component: View, Text', 'https://www.youtube.com/embed/kJw-XWgfPm8?si=ZXwqx8uVCrkKy6PE', 0.00, '2026-03-04 16:48:17', '2026-03-04 16:48:17'),
(123, 'Giới thiệu khóa học NestJS', 'https://www.youtube.com/embed/kxuyoJZ5S4w?si=J-s0tV9CS5XLKFUS', 0.00, '2026-03-04 16:48:39', '2026-03-04 16:48:39'),
(124, 'Chuẩn bị môi trường và cài đặt NestJS từ A đến Z', 'https://www.youtube.com/embed/cnHzo18EFzw?si=9yNy8oNy0T6RkfeH', 0.00, '2026-03-04 16:48:50', '2026-03-04 16:48:50'),
(125, 'Kiến trúc dự án NestJS', 'https://www.youtube.com/embed/cthtCRmTcgA?si=2KExURnTt3fMdKnF', 0.00, '2026-03-04 16:49:02', '2026-03-04 16:49:02'),
(126, 'Xây dựng ứng dụng Fullstack CRUD Laravel 11 + NextJS 14 (RESTful API, App Router)', 'https://www.youtube.com/embed/QJ5BiZUCb4M?si=fcj04Vu2hxfDReHO', 0.00, '2026-03-04 16:49:21', '2026-03-04 16:49:21'),
(127, 'Tích hợp ReactJS vào dự án Laravel sử dụng InertiaJS (CRUD - Routing - Pagination - Link)', 'https://www.youtube.com/embed/QJ5BiZUCb4M?si=tIFEdwcl4MXbnbkh', 0.00, '2026-03-04 16:49:38', '2026-03-04 16:49:38'),
(128, 'Xây dựng ứng dụng Single Page Application bằng Laravel Livewire (CRUD - Pagination - Filter)', 'https://www.youtube.com/embed/L_z9YHX5ous?si=jLUzwOON2GcBNfKl', 0.00, '2026-03-04 16:49:52', '2026-03-04 16:49:52'),
(129, 'Xây dựng ứng dụng Authentication Laravel 11 + NextJS 14 (RESTful API, App Router)', 'https://www.youtube.com/embed/L_z9YHX5ous?si=6wTPEfHs8VlsrNOA', 0.00, '2026-03-04 16:50:09', '2026-03-04 16:50:09'),
(130, 'Xây dựng ứng dụng Authentication Laravel 11 + NextJS 14 (RESTful API, App Router)', 'https://www.youtube.com/embed/Bb5Qb7ziIig?si=lORyC9J4Yyz9VKI-', 0.00, '2026-03-04 16:50:22', '2026-03-04 16:50:22'),
(131, 'Giới thiệu khóa học và thành quả sau khi hoàn thành', 'https://www.youtube.com/embed/eukA1NGSM5w?si=jPX3BvcJxEQT2biQ', 0.00, '2026-03-04 16:50:56', '2026-03-04 16:50:56'),
(132, 'Tổng quan về Headless CMS và Strapi CMS', 'https://www.youtube.com/embed/GHs_RYQIog8?si=Qd19FsFik5zq4abK', 0.00, '2026-03-04 16:51:08', '2026-03-04 16:51:08'),
(133, 'Cài đặt Strapi CMS và tìm hiểu cấu trúc folder Strapi', 'https://www.youtube.com/embed/W4UeUJA9wLU?si=Ojrp3QXnn9Cd5DYN', 0.00, '2026-03-04 16:51:24', '2026-03-04 16:51:24'),
(134, 'Tạo Content Type và cách làm việc với Content Manager', 'https://www.youtube.com/embed/Hc5edo4R5Oc?si=z8tbCEcC5rOeO8ey', 0.00, '2026-03-04 16:52:44', '2026-03-04 16:52:44'),
(135, 'Tổng quan về NodeJS', 'https://www.youtube.com/embed/f-VsoLm4i5c?si=9d3DOzqS9FEoNSi9', 0.00, '2026-03-04 16:53:06', '2026-03-04 16:53:06'),
(136, 'Cài đặt môi trường phát triển NodeJS', 'https://www.youtube.com/embed/iFoLKvdqXk8?si=XaaHZsYn4EWdSV-w', 0.00, '2026-03-04 16:53:20', '2026-03-04 16:53:20'),
(137, 'CommonJS và ES Modules trong NodeJS', 'https://www.youtube.com/embed/xPXgebIDE6M?si=2IfvFm2xFaFW4WHZ', 0.00, '2026-03-04 16:53:33', '2026-03-04 16:53:33'),
(138, 'Tổng quan về Laravel Livewire - Xây dựng Component đầu tiên bằng Livewire', 'https://www.youtube.com/embed/vPz8ftK_4bk?si=Zr0u8LNve1HY2nMb', 0.00, '2026-03-04 16:53:53', '2026-03-04 16:53:53'),
(139, 'Component trong Laravel Livewire - Phần 1', 'https://www.youtube.com/embed/PNhYz6RmIr4?si=qlJZ5FwZZAOtbyIu', 0.00, '2026-03-04 16:54:08', '2026-03-04 16:54:08'),
(140, 'Kỹ thuật xử lý Form trong Livewire', 'https://www.youtube.com/embed/p40OWOxAeSw?si=f5yuN4xVSxVh-_09', 0.00, '2026-03-04 16:54:21', '2026-03-04 16:54:21'),
(141, 'Giới thiệu Laravel Framework - Cài đặt Laravel', 'https://www.youtube.com/embed/Yw9Ra2UiVLw?si=XygoRCtDYrUR_O1y', 0.00, '2026-03-04 16:54:43', '2026-03-04 16:54:43'),
(142, 'Cấu trúc thư mục và luồng Request trong Laravel Framework', 'https://www.youtube.com/embed/XGrvLJG8tuM?si=T4SZFWhRtggrOY-L', 0.00, '2026-03-04 16:54:57', '2026-03-04 16:54:57'),
(143, 'Thiết lập cấu hình cần thiết cho Laravel', 'https://www.youtube.com/embed/i54avTdUqwU?si=A8U3kBsmCUbbzeK9', 0.00, '2026-03-04 16:55:09', '2026-03-04 16:55:09'),
(144, 'Route trong Laravel - Phần 1', 'https://www.youtube.com/embed/h8fbQROn_tQ?si=5ZhT9tc6O8Z-TZCP', 0.00, '2026-03-04 16:55:23', '2026-03-04 16:55:23'),
(145, 'Route trong Laravel - Phần 2', 'https://www.youtube.com/embed/xRL2BspFnOs?si=k6hobLKtjcoQPSUC', 0.00, '2026-03-04 16:55:36', '2026-03-04 16:55:36'),
(146, 'Regular Expression là gì? Ý nghĩa của Regular Expression', 'https://www.youtube.com/embed/LPx7ERqFYM0?si=YzsSfckE8J05dxnV', 0.00, '2026-03-04 16:56:23', '2026-03-04 16:56:23'),
(147, 'Website kiểm tra Regular Expression', 'https://www.youtube.com/embed/mbEZ_9dhM_Y?si=JShAetboj8QJ0yvs', 0.00, '2026-03-04 16:56:37', '2026-03-04 16:56:37'),
(148, 'Tổng quan về lập trình truyền thống và lập trình hướng đối tượng', 'https://www.youtube.com/embed/cm0C1c2UuQA?si=QmaZuoMYY4ajvrmc', 0.00, '2026-03-04 16:56:49', '2026-03-04 16:56:49'),
(149, 'Kiểm tra thông tin PHP - phpinfo()', 'https://www.youtube.com/embed/dm5-tn1Rug0?si=zKAr7PGkQ2dYvqQw\\', 0.00, '2026-03-04 16:57:19', '2026-03-04 16:57:19'),
(150, 'Kiểm tra thông tin PHP - phpinfo()', 'https://www.youtube.com/embed/dm5-tn1Rug0?si=zKAr7PGkQ2dYvqQw', 0.00, '2026-03-04 16:57:20', '2026-03-04 16:57:20'),
(151, 'Biến - Comment - Debug trong PHP', 'https://www.youtube.com/embed/dm5-tn1Rug0?si=82XU9QFpAYXH4RPG', 0.00, '2026-03-04 16:57:36', '2026-03-04 16:57:36'),
(152, 'Biến - Comment - Debug trong PHP', 'https://www.youtube.com/embed/NG3YyVNyKUk?si=Fj5MqBYVFH7dCXWS', 0.00, '2026-03-04 16:58:28', '2026-03-04 16:58:28'),
(153, 'Tổng quan về TypeScript - Cài đặt môi trường chạy ứng dụng TypeScript', 'https://www.youtube.com/embed/r5JEgaDK7H0?si=K64YjSlv3YatperC', 0.00, '2026-03-04 16:58:48', '2026-03-04 16:58:48'),
(154, 'Cấu hình Output và Prettier để tự động Format code', 'https://www.youtube.com/embed/GtmrOux7rgg?si=DRFrdMWvztnEFSIF', 0.00, '2026-03-04 16:59:02', '2026-03-04 16:59:02'),
(155, 'Tư duy lập trình hướng đối tượng', 'https://www.youtube.com/embed/U1usrHw-GvI?si=935_qT1I8q1YWivq', 0.00, '2026-03-04 16:59:15', '2026-03-04 16:59:15'),
(156, 'Tổng quan về khóa học NextJS + TypeScript', 'https://www.youtube.com/embed/VLvBlG49BKA?si=gNI_VRD2N7eGGO20', 0.00, '2026-03-04 16:59:42', '2026-03-04 16:59:42'),
(157, 'Kiến thức cần chuẩn bị trước khi học NextJS', 'https://www.youtube.com/embed/dfeGNIfSyGk?si=VpgD5Ziunqsepi14', 0.00, '2026-03-04 16:59:56', '2026-03-04 16:59:56'),
(158, 'Tổng quan về TypeScript - Cài đặt môi trường chạy ứng dụng TypeScript', 'https://www.youtube.com/embed/9f1u_U3Hvf4?si=qKY6gTz7EmiM7Gl2', 0.00, '2026-03-04 17:00:09', '2026-03-04 17:00:09'),
(159, 'Kiến thức cần chuẩn bị trước khi học ReactJS', 'https://www.youtube.com/embed/9bDsiTYQLs0?si=8ELRsZuVAYzila79', 0.00, '2026-03-04 17:01:33', '2026-03-04 17:01:33'),
(160, 'Tổng quan và hướng dẫn cài đặt ReactJS', 'https://www.youtube.com/embed/qBsiWmEdj0c?si=3bm-DOG3VBBl_tSN', 0.00, '2026-03-04 17:01:47', '2026-03-04 17:01:47'),
(161, 'Danh sách API miễn phí cho anh em học Front-End', 'https://www.youtube.com/embed/U0Vr1zotKIo?si=SmEokTTC2LDXaEer', 0.00, '2026-03-04 17:02:02', '2026-03-04 17:02:02'),
(162, 'Giới thiệu ngôn ngữ lập trình JavaScript', 'https://www.youtube.com/embed/i_ec-92cdJI?si=be4Uga5IOUmKFFcF', 0.00, '2026-03-04 17:02:31', '2026-03-04 17:02:31'),
(163, 'Công cụ cần chuẩn bị trước khi học JavaScript', 'https://www.youtube.com/embed/d1sfT-FglD8?si=EozhdbI4v5ayJZZU', 0.00, '2026-03-04 17:02:45', '2026-03-04 17:02:45'),
(164, 'Dev Tools là gì? Cách làm việc với Chrome Dev Tools', 'https://www.youtube.com/embed/_XX248bq6Pw?si=WPapszsk6RNV96oM', 0.00, '2026-03-04 17:03:01', '2026-03-04 17:03:01'),
(165, 'Viết chương trình JavaScript đầu tiên', 'https://www.youtube.com/embed/1dlTWaiBZDw?si=vVsr0issJpcPMJ56', 0.00, '2026-03-04 17:03:21', '2026-03-04 17:03:21'),
(166, 'Nhập môn lập trình web - Phần 1', 'https://www.youtube.com/embed/E27ET_lINRo?si=iAJZ9QPo_1bzfCoT', 0.00, '2026-03-04 17:03:45', '2026-03-04 17:03:45'),
(167, 'Nhập môn lập trình web - Phần 1', 'https://www.youtube.com/embed/E27ET_lINRo?si=l7fQ82Oz-RHsg-tB', 0.00, '2026-03-04 17:03:59', '2026-03-04 17:03:59'),
(168, 'Nhập môn lập trình web - Phần 2', 'https://www.youtube.com/embed/vqEoE3ABR-k?si=_YLsi4D_4KzMnWCK', 0.00, '2026-03-04 17:04:11', '2026-03-04 17:04:11'),
(169, 'Công cụ - Phần mềm cần chuẩn bị', 'https://www.youtube.com/embed/RarQCMLLIOg?si=mRyp2WwgtC7Z1qlL', 0.00, '2026-03-04 17:04:24', '2026-03-04 17:04:24'),
(170, 'HTML cơ bản - Phần 1', 'https://www.youtube.com/embed/9UcQ7ddVjoc?si=xXlO1PfW9tHl_vdr', 0.00, '2026-03-04 17:04:36', '2026-03-04 17:04:36'),
(171, 'HTML cơ bản - Phần 2', 'https://www.youtube.com/embed/rLNvDu59ffI?si=fO6Jq4on6IyL2V72', 0.00, '2026-03-04 17:04:50', '2026-03-04 17:04:50'),
(172, '123', 'https://www.youtube.com/embed/ZBxCLg659vE?si=nQUfd-eH4fTs8f7g', 0.00, '2026-04-03 15:28:40', '2026-04-03 15:28:40'),
(173, '123', 'https://www.youtube.com/embed/3p5NDuPQsso?si=JoAUW_u5Vd7lOG8E', 0.00, '2026-04-16 17:17:15', '2026-04-16 17:17:15'),
(174, '123', 'https://www.youtube.com/watch?v=3p5NDuPQsso', 0.00, '2026-04-16 17:23:39', '2026-04-16 17:23:39'),
(175, '123', 'https://www.youtube.com/watch?v=Wy2dGikgdhw', 0.00, '2026-04-18 14:06:52', '2026-04-18 14:06:52'),
(176, '123', 'https://www.youtube.com/watch?v=d7MIyXYwtLQ&t=14126s', 0.00, '2026-04-27 08:09:35', '2026-04-27 08:09:35'),
(177, '123', 'https://www.youtube.com/embed/CfnM02dPsDU?si=shpvfPH-5-3cTnA4', 0.00, '2026-04-27 08:10:02', '2026-04-27 08:10:02'),
(178, '7586158-hd_1920_1080_24fps.mp4', '/storage/videos/2/7586158-hd_1920_1080_24fps.mp4', 19.31, '2026-05-09 05:52:41', '2026-05-09 05:52:41'),
(179, '7586158-hd_1920_1080_24fps.mp4', '/storage/videos/2/7586158-hd_1920_1080_24fps.mp4', 19.31, '2026-05-09 05:56:21', '2026-05-09 05:56:21'),
(180, 'Nhập môn lập trình web - Phần 1', 'https://www.youtube.com/watch?v=Kgb8kxcqafA&list=RDMMMmpY6UzM3w0&index=9', 0.00, '2026-05-27 07:56:12', '2026-05-27 07:56:12');

--
-- Chỉ mục cho các bảng đã đổ
--

--
-- Chỉ mục cho bảng `active_logs`
--
ALTER TABLE `active_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `active_logs_log_name_index` (`log_name`),
  ADD KEY `active_logs_action_index` (`action`),
  ADD KEY `active_logs_subject_index` (`subject_type`,`subject_id`),
  ADD KEY `active_logs_causer_id_index` (`causer_id`),
  ADD KEY `active_logs_ip_index` (`ip`),
  ADD KEY `active_logs_created_at_index` (`created_at`);

--
-- Chỉ mục cho bảng `admin_session_trackers`
--
ALTER TABLE `admin_session_trackers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `admin_session_trackers_session_id_unique` (`session_id`),
  ADD KEY `admin_session_trackers_user_id_last_activity_index` (`user_id`,`last_activity`);

--
-- Chỉ mục cho bảng `announcements`
--
ALTER TABLE `announcements`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `announcement_reads`
--
ALTER TABLE `announcement_reads`
  ADD PRIMARY KEY (`id`),
  ADD KEY `announcement_reads_announcement_id_foreign` (`announcement_id`);

--
-- Chỉ mục cho bảng `announcement_user`
--
ALTER TABLE `announcement_user`
  ADD PRIMARY KEY (`id`),
  ADD KEY `announcement_user_announcement_id_foreign` (`announcement_id`);

--
-- Chỉ mục cho bảng `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `categories_courses`
--
ALTER TABLE `categories_courses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `categories_courses_category_id_foreign` (`category_id`),
  ADD KEY `categories_courses_courses_id_foreign` (`courses_id`);

--
-- Chỉ mục cho bảng `chatbot_knowledge`
--
ALTER TABLE `chatbot_knowledge`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `chatbot_unresolved_questions`
--
ALTER TABLE `chatbot_unresolved_questions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `chatbot_unresolved_questions_student_id_foreign` (`student_id`),
  ADD KEY `chatbot_unresolved_questions_knowledge_id_foreign` (`knowledge_id`),
  ADD KEY `chatbot_unresolved_questions_status_last_asked_at_index` (`status`,`last_asked_at`),
  ADD KEY `chatbot_unresolved_questions_normalized_message_locale_index` (`normalized_message`,`locale`);

--
-- Chỉ mục cho bảng `contacts`
--
ALTER TABLE `contacts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `contacts_student_fk` (`student_id`),
  ADD KEY `contacts_teacher_fk` (`teacher_id`);

--
-- Chỉ mục cho bảng `coupons`
--
ALTER TABLE `coupons`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `coupons_code_unique` (`code`),
  ADD KEY `coupons_teacher_id_foreign` (`teacher_id`),
  ADD KEY `coupons_created_by_foreign` (`created_by`);

--
-- Chỉ mục cho bảng `coupons_courses`
--
ALTER TABLE `coupons_courses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `coupons_courses_coupon_id_foreign` (`coupon_id`),
  ADD KEY `coupons_courses_course_id_foreign` (`course_id`);

--
-- Chỉ mục cho bảng `coupons_students`
--
ALTER TABLE `coupons_students`
  ADD PRIMARY KEY (`id`),
  ADD KEY `coupons_students_coupon_id_foreign` (`coupon_id`),
  ADD KEY `coupons_students_student_id_foreign` (`student_id`);

--
-- Chỉ mục cho bảng `coupons_teacher_course_bundles`
--
ALTER TABLE `coupons_teacher_course_bundles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `coupon_bundle_unique` (`coupon_id`,`bundle_id`),
  ADD KEY `coupons_teacher_course_bundles_bundle_id_foreign` (`bundle_id`);

--
-- Chỉ mục cho bảng `coupons_usage`
--
ALTER TABLE `coupons_usage`
  ADD PRIMARY KEY (`id`),
  ADD KEY `coupons_usage_coupon_id_foreign` (`coupon_id`),
  ADD KEY `coupons_usage_order_id_foreign` (`order_id`),
  ADD KEY `coupons_usage_student_id_foreign` (`student_id`);

--
-- Chỉ mục cho bảng `courses`
--
ALTER TABLE `courses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `courses_teacher_id_foreign` (`teacher_id`),
  ADD KEY `courses_status_index` (`status`),
  ADD KEY `courses_created_at_index` (`created_at`);

--
-- Chỉ mục cho bảng `course_comments`
--
ALTER TABLE `course_comments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `course_comments_course_id_foreign` (`course_id`),
  ADD KEY `course_comments_parent_id_foreign` (`parent_id`),
  ADD KEY `course_comments_student_id_foreign` (`student_id`),
  ADD KEY `course_comments_user_id_foreign` (`user_id`);

--
-- Chỉ mục cho bảng `course_quizzes`
--
ALTER TABLE `course_quizzes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `course_quizzes_lesson_fk` (`lesson_id`),
  ADD KEY `course_quizzes_course_lesson_idx` (`course_id`,`lesson_id`),
  ADD KEY `course_quizzes_course_idx` (`course_id`),
  ADD KEY `course_quizzes_slug_idx` (`slug`);

--
-- Chỉ mục cho bảng `course_quiz_assignments`
--
ALTER TABLE `course_quiz_assignments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `quiz_assignments_unique` (`quiz_id`,`student_id`),
  ADD KEY `quiz_assignments_student_fk` (`student_id`),
  ADD KEY `quiz_assignments_quiz_student_idx` (`quiz_id`,`student_id`);

--
-- Chỉ mục cho bảng `course_quiz_choices`
--
ALTER TABLE `course_quiz_choices`
  ADD PRIMARY KEY (`id`),
  ADD KEY `quiz_choices_question_position_idx` (`question_id`,`position`),
  ADD KEY `quiz_choices_question_idx` (`question_id`);

--
-- Chỉ mục cho bảng `course_quiz_questions`
--
ALTER TABLE `course_quiz_questions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `quiz_questions_quiz_position_idx` (`quiz_id`,`position`),
  ADD KEY `quiz_questions_quiz_idx` (`quiz_id`);

--
-- Chỉ mục cho bảng `course_quiz_submissions`
--
ALTER TABLE `course_quiz_submissions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `quiz_submissions_assignment_fk` (`assignment_id`),
  ADD KEY `quiz_submissions_quiz_student_idx` (`quiz_id`,`student_id`),
  ADD KEY `quiz_submissions_student_submitted_idx` (`student_id`,`submitted_at`);

--
-- Chỉ mục cho bảng `course_quiz_submission_answers`
--
ALTER TABLE `course_quiz_submission_answers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `quiz_submission_answers_unique` (`submission_id`,`question_id`),
  ADD KEY `quiz_submission_answers_question_fk` (`question_id`),
  ADD KEY `quiz_submission_answers_choice_fk` (`choice_id`);

--
-- Chỉ mục cho bảng `course_ratings`
--
ALTER TABLE `course_ratings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `course_ratings_unique` (`course_id`,`student_id`),
  ADD KEY `course_ratings_student_fk` (`student_id`);

--
-- Chỉ mục cho bảng `course_view_trackings`
--
ALTER TABLE `course_view_trackings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `course_view_trackings_unique_daily` (`course_id`,`view_date`,`visitor_hash`),
  ADD KEY `course_view_trackings_course_date_index` (`course_id`,`view_date`),
  ADD KEY `course_view_trackings_student_index` (`student_id`);

--
-- Chỉ mục cho bảng `documents`
--
ALTER TABLE `documents`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `exchange_rates`
--
ALTER TABLE `exchange_rates`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `exchange_rates_code_unique` (`code`);

--
-- Chỉ mục cho bảng `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Chỉ mục cho bảng `groups`
--
ALTER TABLE `groups`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `groups_slug_unique` (`slug`);

--
-- Chỉ mục cho bảng `group_permission`
--
ALTER TABLE `group_permission`
  ADD PRIMARY KEY (`group_id`,`permission_id`),
  ADD KEY `group_permission_permission_id_index` (`permission_id`);

--
-- Chỉ mục cho bảng `ip_blacklists`
--
ALTER TABLE `ip_blacklists`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ip_blacklists_ip_address_unique` (`ip_address`);

--
-- Chỉ mục cho bảng `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Chỉ mục cho bảng `lessons`
--
ALTER TABLE `lessons`
  ADD PRIMARY KEY (`id`),
  ADD KEY `lessons_video_id_foreign` (`video_id`),
  ADD KEY `lessons_document_id_foreign` (`document_id`),
  ADD KEY `lessons_course_id_foreign` (`course_id`),
  ADD KEY `lessons_parent_id_foreign` (`parent_id`),
  ADD KEY `lessons_created_at_index` (`created_at`);

--
-- Chỉ mục cho bảng `lesson_notes`
--
ALTER TABLE `lesson_notes`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `notifications_notifiable_type_notifiable_id_index` (`notifiable_type`,`notifiable_id`);

--
-- Chỉ mục cho bảng `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `orders_status_id_foreign` (`status_id`),
  ADD KEY `order_student_id_foreign` (`student_id`),
  ADD KEY `orders_bundle_id_foreign` (`bundle_id`),
  ADD KEY `orders_affiliate_link_id_index` (`affiliate_link_id`),
  ADD KEY `orders_payment_complete_date_index` (`payment_complete_date`),
  ADD KEY `orders_status_id_index` (`status_id`),
  ADD KEY `orders_currency_index` (`currency`),
  ADD KEY `orders_created_at_index` (`created_at`),
  ADD KEY `orders_orderable_id_orderable_type_index` (`orderable_id`,`orderable_type`),
  ADD KEY `orders_type_index` (`type`);

--
-- Chỉ mục cho bảng `orders_detail`
--
ALTER TABLE `orders_detail`
  ADD PRIMARY KEY (`id`),
  ADD KEY `orders_detail_course_id_foreign` (`course_id`),
  ADD KEY `orders_detail_order_id_foreign` (`order_id`);

--
-- Chỉ mục cho bảng `orders_status`
--
ALTER TABLE `orders_status`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `password_resets`
--
ALTER TABLE `password_resets`
  ADD KEY `password_resets_email_index` (`email`);

--
-- Chỉ mục cho bảng `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Chỉ mục cho bảng `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `permissions_slug_unique` (`slug`);

--
-- Chỉ mục cho bảng `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  ADD KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`);

--
-- Chỉ mục cho bảng `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Chỉ mục cho bảng `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `settings_key_unique` (`key`);

--
-- Chỉ mục cho bảng `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `students_email_unique` (`email`),
  ADD KEY `students_status_index` (`status`),
  ADD KEY `students_created_at_index` (`created_at`);

--
-- Chỉ mục cho bảng `students_courses`
--
ALTER TABLE `students_courses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `students_courses_student_id_foreign` (`student_id`),
  ADD KEY `students_courses_course_id_foreign` (`course_id`);

--
-- Chỉ mục cho bảng `student_lesson_progress`
--
ALTER TABLE `student_lesson_progress`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `student_lesson_progress_unique` (`student_id`,`lesson_id`),
  ADD KEY `student_lesson_progress_course_index` (`student_id`,`course_id`);

--
-- Chỉ mục cho bảng `student_password_rests`
--
ALTER TABLE `student_password_rests`
  ADD PRIMARY KEY (`email`);

--
-- Chỉ mục cho bảng `teacher`
--
ALTER TABLE `teacher`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `teacher_student_id_unique` (`student_id`),
  ADD KEY `teachers_status_index` (`status`),
  ADD KEY `teachers_created_at_index` (`created_at`);

--
-- Chỉ mục cho bảng `teacher_affiliate_links`
--
ALTER TABLE `teacher_affiliate_links`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `teacher_affiliate_links_code_unique` (`code`),
  ADD KEY `teacher_affiliate_links_teacher_id_target_type_index` (`teacher_id`,`target_type`);

--
-- Chỉ mục cho bảng `teacher_affiliate_link_clicks`
--
ALTER TABLE `teacher_affiliate_link_clicks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `teacher_affiliate_link_clicks_affiliate_link_id_foreign` (`affiliate_link_id`),
  ADD KEY `teacher_affiliate_link_clicks_teacher_id_target_type_index` (`teacher_id`,`target_type`);

--
-- Chỉ mục cho bảng `teacher_announcements`
--
ALTER TABLE `teacher_announcements`
  ADD PRIMARY KEY (`id`),
  ADD KEY `teacher_announcements_active_idx` (`status`,`starts_at`,`ends_at`);

--
-- Chỉ mục cho bảng `teacher_announcement_package`
--
ALTER TABLE `teacher_announcement_package`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `teacher_announcement_package_unique` (`announcement_id`,`package_id`);

--
-- Chỉ mục cho bảng `teacher_announcement_reads`
--
ALTER TABLE `teacher_announcement_reads`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `teacher_announcement_reads_unique` (`announcement_id`,`student_id`),
  ADD KEY `teacher_announcement_reads_student_fk` (`student_id`);

--
-- Chỉ mục cho bảng `teacher_applications`
--
ALTER TABLE `teacher_applications`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `teacher_applications_claim_token_unique` (`claim_token`),
  ADD KEY `teacher_applications_teacher_id_foreign` (`teacher_id`),
  ADD KEY `teacher_applications_package_id_foreign` (`package_id`),
  ADD KEY `teacher_applications_student_id_foreign` (`student_id`);

--
-- Chỉ mục cho bảng `teacher_badges`
--
ALTER TABLE `teacher_badges`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `teacher_badges_code_unique` (`code`);

--
-- Chỉ mục cho bảng `teacher_cancellation_requests`
--
ALTER TABLE `teacher_cancellation_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `teacher_cancellation_requests_teacher_id_foreign` (`teacher_id`),
  ADD KEY `teacher_cancellation_requests_processed_by_foreign` (`processed_by`);

--
-- Chỉ mục cho bảng `teacher_course_bundles`
--
ALTER TABLE `teacher_course_bundles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `teacher_course_bundles_slug_unique` (`slug`),
  ADD KEY `teacher_course_bundles_teacher_id_foreign` (`teacher_id`);

--
-- Chỉ mục cho bảng `teacher_course_bundle_items`
--
ALTER TABLE `teacher_course_bundle_items`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `teacher_course_bundle_items_bundle_id_course_id_unique` (`bundle_id`,`course_id`),
  ADD KEY `teacher_course_bundle_items_course_id_foreign` (`course_id`);

--
-- Chỉ mục cho bảng `teacher_course_certificates`
--
ALTER TABLE `teacher_course_certificates`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `teacher_course_certificates_unique` (`teacher_id`,`student_id`,`course_id`),
  ADD UNIQUE KEY `teacher_course_certificates_code_unique` (`code`),
  ADD KEY `teacher_course_certificates_student_id_foreign` (`student_id`),
  ADD KEY `teacher_course_certificates_course_id_foreign` (`course_id`),
  ADD KEY `teacher_course_certificates_issuer_fk` (`issued_by_student_id`),
  ADD KEY `teacher_course_certificates_revoked_by_fk` (`revoked_by_student_id`);

--
-- Chỉ mục cho bảng `teacher_course_grants`
--
ALTER TABLE `teacher_course_grants`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `teacher_course_grants_unique` (`teacher_id`,`student_id`,`course_id`),
  ADD UNIQUE KEY `teacher_course_grants_token_unique` (`token`),
  ADD KEY `teacher_course_grants_student_id_foreign` (`student_id`),
  ADD KEY `teacher_course_grants_course_id_foreign` (`course_id`);

--
-- Chỉ mục cho bảng `teacher_has_badges`
--
ALTER TABLE `teacher_has_badges`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `teacher_has_badges_teacher_id_badge_id_unique` (`teacher_id`,`badge_id`),
  ADD KEY `teacher_has_badges_badge_id_foreign` (`badge_id`);

--
-- Chỉ mục cho bảng `teacher_notification_reads`
--
ALTER TABLE `teacher_notification_reads`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `teacher_notification_reads_unique` (`student_id`,`notification_key`);

--
-- Chỉ mục cho bảng `teacher_packages`
--
ALTER TABLE `teacher_packages`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `teacher_packages_code_unique` (`code`),
  ADD KEY `teacher_packages_category_id_foreign` (`category_id`);

--
-- Chỉ mục cho bảng `teacher_package_categories`
--
ALTER TABLE `teacher_package_categories`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `teacher_package_features`
--
ALTER TABLE `teacher_package_features`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `teacher_package_features_key_unique` (`key`);

--
-- Chỉ mục cho bảng `teacher_payout_accounts`
--
ALTER TABLE `teacher_payout_accounts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `teacher_payout_accounts_teacher_id_foreign` (`teacher_id`);

--
-- Chỉ mục cho bảng `teacher_payout_account_change_requests`
--
ALTER TABLE `teacher_payout_account_change_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tpacr_teacher_fk` (`teacher_id`),
  ADD KEY `tpacr_replace_account_fk` (`replace_payout_account_id`);

--
-- Chỉ mục cho bảng `teacher_payout_requests`
--
ALTER TABLE `teacher_payout_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `teacher_payout_requests_teacher_id_foreign` (`teacher_id`);

--
-- Chỉ mục cho bảng `teacher_promotions`
--
ALTER TABLE `teacher_promotions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `teacher_promotions_teacher_id_foreign` (`teacher_id`),
  ADD KEY `teacher_promotions_course_id_foreign` (`course_id`),
  ADD KEY `teacher_promotions_created_by_student_id_foreign` (`created_by_student_id`);

--
-- Chỉ mục cho bảng `teacher_ratings`
--
ALTER TABLE `teacher_ratings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `teacher_ratings_unique` (`teacher_id`,`student_id`),
  ADD KEY `teacher_ratings_student_fk` (`student_id`);

--
-- Chỉ mục cho bảng `teacher_student_notes`
--
ALTER TABLE `teacher_student_notes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `teacher_student_notes_teacher_id_student_id_unique` (`teacher_id`,`student_id`),
  ADD KEY `teacher_student_notes_student_id_foreign` (`student_id`);

--
-- Chỉ mục cho bảng `teacher_telegram_subscriptions`
--
ALTER TABLE `teacher_telegram_subscriptions`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `telegram_packages`
--
ALTER TABLE `telegram_packages`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`),
  ADD KEY `users_group_id_index` (`group_id`);

--
-- Chỉ mục cho bảng `videos`
--
ALTER TABLE `videos`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT cho các bảng đã đổ
--

--
-- AUTO_INCREMENT cho bảng `active_logs`
--
ALTER TABLE `active_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `admin_session_trackers`
--
ALTER TABLE `admin_session_trackers`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `announcements`
--
ALTER TABLE `announcements`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `announcement_reads`
--
ALTER TABLE `announcement_reads`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `announcement_user`
--
ALTER TABLE `announcement_user`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT cho bảng `categories_courses`
--
ALTER TABLE `categories_courses`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;

--
-- AUTO_INCREMENT cho bảng `chatbot_knowledge`
--
ALTER TABLE `chatbot_knowledge`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT cho bảng `chatbot_unresolved_questions`
--
ALTER TABLE `chatbot_unresolved_questions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT cho bảng `contacts`
--
ALTER TABLE `contacts`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT cho bảng `coupons`
--
ALTER TABLE `coupons`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `coupons_courses`
--
ALTER TABLE `coupons_courses`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `coupons_students`
--
ALTER TABLE `coupons_students`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `coupons_teacher_course_bundles`
--
ALTER TABLE `coupons_teacher_course_bundles`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `coupons_usage`
--
ALTER TABLE `coupons_usage`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `courses`
--
ALTER TABLE `courses`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;

--
-- AUTO_INCREMENT cho bảng `course_comments`
--
ALTER TABLE `course_comments`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `course_quizzes`
--
ALTER TABLE `course_quizzes`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `course_quiz_assignments`
--
ALTER TABLE `course_quiz_assignments`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `course_quiz_choices`
--
ALTER TABLE `course_quiz_choices`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `course_quiz_questions`
--
ALTER TABLE `course_quiz_questions`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `course_quiz_submissions`
--
ALTER TABLE `course_quiz_submissions`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `course_quiz_submission_answers`
--
ALTER TABLE `course_quiz_submission_answers`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `course_ratings`
--
ALTER TABLE `course_ratings`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `course_view_trackings`
--
ALTER TABLE `course_view_trackings`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `documents`
--
ALTER TABLE `documents`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `exchange_rates`
--
ALTER TABLE `exchange_rates`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `groups`
--
ALTER TABLE `groups`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT cho bảng `ip_blacklists`
--
ALTER TABLE `ip_blacklists`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=375;

--
-- AUTO_INCREMENT cho bảng `lessons`
--
ALTER TABLE `lessons`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=169;

--
-- AUTO_INCREMENT cho bảng `lesson_notes`
--
ALTER TABLE `lesson_notes`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=198;

--
-- AUTO_INCREMENT cho bảng `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `orders_detail`
--
ALTER TABLE `orders_detail`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `orders_status`
--
ALTER TABLE `orders_status`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT cho bảng `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=130;

--
-- AUTO_INCREMENT cho bảng `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=132;

--
-- AUTO_INCREMENT cho bảng `students`
--
ALTER TABLE `students`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `students_courses`
--
ALTER TABLE `students_courses`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `student_lesson_progress`
--
ALTER TABLE `student_lesson_progress`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT cho bảng `teacher`
--
ALTER TABLE `teacher`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `teacher_affiliate_links`
--
ALTER TABLE `teacher_affiliate_links`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `teacher_affiliate_link_clicks`
--
ALTER TABLE `teacher_affiliate_link_clicks`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `teacher_announcements`
--
ALTER TABLE `teacher_announcements`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `teacher_announcement_package`
--
ALTER TABLE `teacher_announcement_package`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `teacher_announcement_reads`
--
ALTER TABLE `teacher_announcement_reads`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `teacher_applications`
--
ALTER TABLE `teacher_applications`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `teacher_badges`
--
ALTER TABLE `teacher_badges`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `teacher_cancellation_requests`
--
ALTER TABLE `teacher_cancellation_requests`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `teacher_course_bundles`
--
ALTER TABLE `teacher_course_bundles`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `teacher_course_bundle_items`
--
ALTER TABLE `teacher_course_bundle_items`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `teacher_course_certificates`
--
ALTER TABLE `teacher_course_certificates`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `teacher_course_grants`
--
ALTER TABLE `teacher_course_grants`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `teacher_has_badges`
--
ALTER TABLE `teacher_has_badges`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `teacher_notification_reads`
--
ALTER TABLE `teacher_notification_reads`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `teacher_packages`
--
ALTER TABLE `teacher_packages`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `teacher_package_categories`
--
ALTER TABLE `teacher_package_categories`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT cho bảng `teacher_package_features`
--
ALTER TABLE `teacher_package_features`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `teacher_payout_accounts`
--
ALTER TABLE `teacher_payout_accounts`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `teacher_payout_account_change_requests`
--
ALTER TABLE `teacher_payout_account_change_requests`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `teacher_payout_requests`
--
ALTER TABLE `teacher_payout_requests`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `teacher_promotions`
--
ALTER TABLE `teacher_promotions`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `teacher_ratings`
--
ALTER TABLE `teacher_ratings`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `teacher_student_notes`
--
ALTER TABLE `teacher_student_notes`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `teacher_telegram_subscriptions`
--
ALTER TABLE `teacher_telegram_subscriptions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `telegram_packages`
--
ALTER TABLE `telegram_packages`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `users`
--
ALTER TABLE `users`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT cho bảng `videos`
--
ALTER TABLE `videos`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=181;

--
-- Các ràng buộc cho các bảng đã đổ
--

--
-- Các ràng buộc cho bảng `admin_session_trackers`
--
ALTER TABLE `admin_session_trackers`
  ADD CONSTRAINT `admin_session_trackers_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `announcement_reads`
--
ALTER TABLE `announcement_reads`
  ADD CONSTRAINT `announcement_reads_announcement_id_foreign` FOREIGN KEY (`announcement_id`) REFERENCES `announcements` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `announcement_user`
--
ALTER TABLE `announcement_user`
  ADD CONSTRAINT `announcement_user_announcement_id_foreign` FOREIGN KEY (`announcement_id`) REFERENCES `announcements` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `categories_courses`
--
ALTER TABLE `categories_courses`
  ADD CONSTRAINT `categories_courses_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `categories_courses_courses_id_foreign` FOREIGN KEY (`courses_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `chatbot_unresolved_questions`
--
ALTER TABLE `chatbot_unresolved_questions`
  ADD CONSTRAINT `chatbot_unresolved_questions_knowledge_id_foreign` FOREIGN KEY (`knowledge_id`) REFERENCES `chatbot_knowledge` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `chatbot_unresolved_questions_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE SET NULL;

--
-- Các ràng buộc cho bảng `contacts`
--
ALTER TABLE `contacts`
  ADD CONSTRAINT `contacts_student_fk` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `contacts_teacher_fk` FOREIGN KEY (`teacher_id`) REFERENCES `teacher` (`id`) ON DELETE SET NULL;

--
-- Các ràng buộc cho bảng `coupons`
--
ALTER TABLE `coupons`
  ADD CONSTRAINT `coupons_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `coupons_teacher_id_foreign` FOREIGN KEY (`teacher_id`) REFERENCES `teacher` (`id`) ON DELETE SET NULL;

--
-- Các ràng buộc cho bảng `coupons_courses`
--
ALTER TABLE `coupons_courses`
  ADD CONSTRAINT `coupons_courses_coupon_id_foreign` FOREIGN KEY (`coupon_id`) REFERENCES `coupons` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `coupons_courses_course_id_foreign` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `coupons_students`
--
ALTER TABLE `coupons_students`
  ADD CONSTRAINT `coupons_students_coupon_id_foreign` FOREIGN KEY (`coupon_id`) REFERENCES `coupons` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `coupons_students_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `coupons_teacher_course_bundles`
--
ALTER TABLE `coupons_teacher_course_bundles`
  ADD CONSTRAINT `coupons_teacher_course_bundles_bundle_id_foreign` FOREIGN KEY (`bundle_id`) REFERENCES `teacher_course_bundles` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `coupons_teacher_course_bundles_coupon_id_foreign` FOREIGN KEY (`coupon_id`) REFERENCES `coupons` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `coupons_usage`
--
ALTER TABLE `coupons_usage`
  ADD CONSTRAINT `coupons_usage_coupon_id_foreign` FOREIGN KEY (`coupon_id`) REFERENCES `coupons` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `coupons_usage_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `coupons_usage_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `courses`
--
ALTER TABLE `courses`
  ADD CONSTRAINT `courses_teacher_id_foreign` FOREIGN KEY (`teacher_id`) REFERENCES `teacher` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `course_comments`
--
ALTER TABLE `course_comments`
  ADD CONSTRAINT `course_comments_course_id_foreign` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `course_comments_parent_id_foreign` FOREIGN KEY (`parent_id`) REFERENCES `course_comments` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `course_comments_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `course_comments_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Các ràng buộc cho bảng `course_quizzes`
--
ALTER TABLE `course_quizzes`
  ADD CONSTRAINT `course_quizzes_course_fk` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `course_quizzes_lesson_fk` FOREIGN KEY (`lesson_id`) REFERENCES `lessons` (`id`) ON DELETE SET NULL;

--
-- Các ràng buộc cho bảng `course_quiz_assignments`
--
ALTER TABLE `course_quiz_assignments`
  ADD CONSTRAINT `quiz_assignments_quiz_fk` FOREIGN KEY (`quiz_id`) REFERENCES `course_quizzes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `quiz_assignments_student_fk` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `course_quiz_choices`
--
ALTER TABLE `course_quiz_choices`
  ADD CONSTRAINT `quiz_choices_question_fk` FOREIGN KEY (`question_id`) REFERENCES `course_quiz_questions` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `course_quiz_questions`
--
ALTER TABLE `course_quiz_questions`
  ADD CONSTRAINT `quiz_questions_quiz_fk` FOREIGN KEY (`quiz_id`) REFERENCES `course_quizzes` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `course_quiz_submissions`
--
ALTER TABLE `course_quiz_submissions`
  ADD CONSTRAINT `quiz_submissions_assignment_fk` FOREIGN KEY (`assignment_id`) REFERENCES `course_quiz_assignments` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `quiz_submissions_quiz_fk` FOREIGN KEY (`quiz_id`) REFERENCES `course_quizzes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `quiz_submissions_student_fk` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `course_quiz_submission_answers`
--
ALTER TABLE `course_quiz_submission_answers`
  ADD CONSTRAINT `quiz_submission_answers_choice_fk` FOREIGN KEY (`choice_id`) REFERENCES `course_quiz_choices` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `quiz_submission_answers_question_fk` FOREIGN KEY (`question_id`) REFERENCES `course_quiz_questions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `quiz_submission_answers_submission_fk` FOREIGN KEY (`submission_id`) REFERENCES `course_quiz_submissions` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `course_ratings`
--
ALTER TABLE `course_ratings`
  ADD CONSTRAINT `course_ratings_course_fk` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `course_ratings_student_fk` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `lessons`
--
ALTER TABLE `lessons`
  ADD CONSTRAINT `lessons_course_id_foreign` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `lessons_document_id_foreign` FOREIGN KEY (`document_id`) REFERENCES `documents` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `lessons_parent_id_foreign` FOREIGN KEY (`parent_id`) REFERENCES `lessons` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `lessons_video_id_foreign` FOREIGN KEY (`video_id`) REFERENCES `videos` (`id`) ON DELETE SET NULL;

--
-- Các ràng buộc cho bảng `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `order_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `orders_affiliate_link_id_foreign` FOREIGN KEY (`affiliate_link_id`) REFERENCES `teacher_affiliate_links` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `orders_bundle_id_foreign` FOREIGN KEY (`bundle_id`) REFERENCES `teacher_course_bundles` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `orders_status_id_foreign` FOREIGN KEY (`status_id`) REFERENCES `orders_status` (`id`) ON DELETE SET NULL;

--
-- Các ràng buộc cho bảng `orders_detail`
--
ALTER TABLE `orders_detail`
  ADD CONSTRAINT `orders_detail_course_id_foreign` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `orders_detail_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `students_courses`
--
ALTER TABLE `students_courses`
  ADD CONSTRAINT `students_courses_course_id_foreign` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `students_courses_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `teacher_affiliate_links`
--
ALTER TABLE `teacher_affiliate_links`
  ADD CONSTRAINT `teacher_affiliate_links_teacher_id_foreign` FOREIGN KEY (`teacher_id`) REFERENCES `teacher` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `teacher_affiliate_link_clicks`
--
ALTER TABLE `teacher_affiliate_link_clicks`
  ADD CONSTRAINT `teacher_affiliate_link_clicks_affiliate_link_id_foreign` FOREIGN KEY (`affiliate_link_id`) REFERENCES `teacher_affiliate_links` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `teacher_affiliate_link_clicks_teacher_id_foreign` FOREIGN KEY (`teacher_id`) REFERENCES `teacher` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `teacher_announcement_package`
--
ALTER TABLE `teacher_announcement_package`
  ADD CONSTRAINT `teacher_announcement_package_announcement_fk` FOREIGN KEY (`announcement_id`) REFERENCES `teacher_announcements` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `teacher_announcement_reads`
--
ALTER TABLE `teacher_announcement_reads`
  ADD CONSTRAINT `teacher_announcement_reads_announcement_fk` FOREIGN KEY (`announcement_id`) REFERENCES `teacher_announcements` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `teacher_announcement_reads_student_fk` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `teacher_applications`
--
ALTER TABLE `teacher_applications`
  ADD CONSTRAINT `teacher_applications_package_id_foreign` FOREIGN KEY (`package_id`) REFERENCES `teacher_packages` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `teacher_applications_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `teacher_applications_teacher_id_foreign` FOREIGN KEY (`teacher_id`) REFERENCES `teacher` (`id`) ON DELETE SET NULL;

--
-- Các ràng buộc cho bảng `teacher_cancellation_requests`
--
ALTER TABLE `teacher_cancellation_requests`
  ADD CONSTRAINT `teacher_cancellation_requests_processed_by_foreign` FOREIGN KEY (`processed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `teacher_cancellation_requests_teacher_id_foreign` FOREIGN KEY (`teacher_id`) REFERENCES `teacher` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `teacher_course_bundles`
--
ALTER TABLE `teacher_course_bundles`
  ADD CONSTRAINT `teacher_course_bundles_teacher_id_foreign` FOREIGN KEY (`teacher_id`) REFERENCES `teacher` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `teacher_course_bundle_items`
--
ALTER TABLE `teacher_course_bundle_items`
  ADD CONSTRAINT `teacher_course_bundle_items_bundle_id_foreign` FOREIGN KEY (`bundle_id`) REFERENCES `teacher_course_bundles` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `teacher_course_bundle_items_course_id_foreign` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `teacher_course_certificates`
--
ALTER TABLE `teacher_course_certificates`
  ADD CONSTRAINT `teacher_course_certificates_course_id_foreign` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `teacher_course_certificates_issuer_fk` FOREIGN KEY (`issued_by_student_id`) REFERENCES `students` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `teacher_course_certificates_revoked_by_fk` FOREIGN KEY (`revoked_by_student_id`) REFERENCES `students` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `teacher_course_certificates_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `teacher_course_certificates_teacher_id_foreign` FOREIGN KEY (`teacher_id`) REFERENCES `teacher` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `teacher_course_grants`
--
ALTER TABLE `teacher_course_grants`
  ADD CONSTRAINT `teacher_course_grants_course_id_foreign` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `teacher_course_grants_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `teacher_course_grants_teacher_id_foreign` FOREIGN KEY (`teacher_id`) REFERENCES `teacher` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `teacher_has_badges`
--
ALTER TABLE `teacher_has_badges`
  ADD CONSTRAINT `teacher_has_badges_badge_id_foreign` FOREIGN KEY (`badge_id`) REFERENCES `teacher_badges` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `teacher_has_badges_teacher_id_foreign` FOREIGN KEY (`teacher_id`) REFERENCES `teacher` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `teacher_notification_reads`
--
ALTER TABLE `teacher_notification_reads`
  ADD CONSTRAINT `teacher_notification_reads_student_fk` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `teacher_packages`
--
ALTER TABLE `teacher_packages`
  ADD CONSTRAINT `teacher_packages_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `teacher_package_categories` (`id`) ON DELETE SET NULL;

--
-- Các ràng buộc cho bảng `teacher_payout_accounts`
--
ALTER TABLE `teacher_payout_accounts`
  ADD CONSTRAINT `teacher_payout_accounts_teacher_id_foreign` FOREIGN KEY (`teacher_id`) REFERENCES `teacher` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `teacher_payout_account_change_requests`
--
ALTER TABLE `teacher_payout_account_change_requests`
  ADD CONSTRAINT `tpacr_replace_account_fk` FOREIGN KEY (`replace_payout_account_id`) REFERENCES `teacher_payout_accounts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `tpacr_teacher_fk` FOREIGN KEY (`teacher_id`) REFERENCES `teacher` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `teacher_payout_requests`
--
ALTER TABLE `teacher_payout_requests`
  ADD CONSTRAINT `teacher_payout_requests_teacher_id_foreign` FOREIGN KEY (`teacher_id`) REFERENCES `teacher` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `teacher_promotions`
--
ALTER TABLE `teacher_promotions`
  ADD CONSTRAINT `teacher_promotions_course_id_foreign` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `teacher_promotions_created_by_student_id_foreign` FOREIGN KEY (`created_by_student_id`) REFERENCES `students` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `teacher_promotions_teacher_id_foreign` FOREIGN KEY (`teacher_id`) REFERENCES `teacher` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `teacher_ratings`
--
ALTER TABLE `teacher_ratings`
  ADD CONSTRAINT `teacher_ratings_student_fk` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `teacher_ratings_teacher_fk` FOREIGN KEY (`teacher_id`) REFERENCES `teacher` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `teacher_student_notes`
--
ALTER TABLE `teacher_student_notes`
  ADD CONSTRAINT `teacher_student_notes_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `teacher_student_notes_teacher_id_foreign` FOREIGN KEY (`teacher_id`) REFERENCES `teacher` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
