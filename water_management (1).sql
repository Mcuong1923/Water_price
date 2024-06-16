-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Máy chủ: 127.0.0.1
-- Thời gian đã tạo: Th6 16, 2024 lúc 03:50 PM
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
-- Cơ sở dữ liệu: `water_management`
--

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `invoices`
--

CREATE TABLE `invoices` (
  `id` int(11) NOT NULL,
  `id_user` int(11) DEFAULT NULL,
  `total_amount` decimal(10,2) DEFAULT NULL,
  `status` enum('paid','unpaid','archived') DEFAULT 'unpaid',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `invoices`
--

INSERT INTO `invoices` (`id`, `id_user`, `total_amount`, `status`, `created_at`) VALUES
(8, 2, 2500000.00, 'paid', '2024-06-11 22:35:48'),
(12, 2, 440000.00, 'unpaid', '2024-06-14 16:46:32'),
(16, 2, 0.00, 'unpaid', '2024-06-14 22:12:05'),
(17, 2, 0.00, 'unpaid', '2024-06-14 22:12:06'),
(23, 5, 220000.00, 'unpaid', '2024-06-15 19:37:46'),
(24, 4, 4440000.00, 'unpaid', '2024-06-15 19:38:52');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `settings`
--

CREATE TABLE `settings` (
  `id` int(11) NOT NULL,
  `key_name` varchar(100) NOT NULL,
  `key_value` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `settings`
--

INSERT INTO `settings` (`id`, `key_name`, `key_value`) VALUES
(1, 'water_price_per_unit', '20000');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `address` varchar(255) NOT NULL,
  `role` enum('admin','user') DEFAULT 'user'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `address`, `role`) VALUES
(2, 'Bui Vuong Truong', 'buitruong132100@gmail.com', '$2y$10$xB6oHWoVzEtcI2skMDl0RuYXwgarPHMG/Qr7vkqxEQm96QghzQsa6', 'HẢI PHÒNG', 'user'),
(3, 'Admin', 'admin@gmail.com', '$2y$10$obp1XVms/yZeqVWf4Bm0ae5X/ghJA279SE/h3feIOaqQOIB/XRmwC', 'Hải Phòng', 'admin'),
(4, 'test1', 'test01@gmail.com', '', 'ha noi', 'user'),
(5, 'Mạnh Cường Nguyễn', '21011583@st.phenikaa-uni.edu.vn', '$2y$10$PsNWeRC2RF4wwuZLZ133ZuOkgk/ayiLUeJzU1uLR1.HR9rtBmWwL2', 'ha noi', 'user'),
(6, 'dung', 'd@gmail.com', '$2y$10$rbsvhh5PRtsWZ3mQgFkGO.PWnh4DSzZoYE/e9tybZFipdMAX4/6uG', 'ha noi', 'user'),
(7, 'cuong', 'nvancuong792@gmail.com', '$2y$10$C7tchBWNZzqHz1weOLaqkOFFnsSbQyNw9rt2ahrBVKjyJydJpyFd2', 'ha noi', 'user');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `water`
--

CREATE TABLE `water` (
  `id` int(11) NOT NULL,
  `number_water` int(11) NOT NULL,
  `date` date NOT NULL,
  `id_user` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `water`
--

INSERT INTO `water` (`id`, `number_water`, `date`, `id_user`) VALUES
(16, 11111, '2024-06-22', 4),
(20, 111, '2024-06-13', 5),
(21, 11, '2024-06-16', 5),
(23, 11, '2024-06-22', 4),
(24, 2222, '2024-06-14', 5),
(26, 12, '2024-06-14', 7),
(27, 3, '2024-06-17', 7),
(28, 22, '2024-06-14', 6),
(29, 1, '2024-06-26', 6),
(30, 11, '2024-06-26', 7),
(31, 2, '2024-06-16', 7);

--
-- Chỉ mục cho các bảng đã đổ
--

--
-- Chỉ mục cho bảng `invoices`
--
ALTER TABLE `invoices`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_user` (`id_user`);

--
-- Chỉ mục cho bảng `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `key_name` (`key_name`);

--
-- Chỉ mục cho bảng `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Chỉ mục cho bảng `water`
--
ALTER TABLE `water`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_user` (`id_user`);

--
-- AUTO_INCREMENT cho các bảng đã đổ
--

--
-- AUTO_INCREMENT cho bảng `invoices`
--
ALTER TABLE `invoices`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT cho bảng `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT cho bảng `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT cho bảng `water`
--
ALTER TABLE `water`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- Các ràng buộc cho các bảng đã đổ
--

--
-- Các ràng buộc cho bảng `invoices`
--
ALTER TABLE `invoices`
  ADD CONSTRAINT `invoices_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `users` (`id`);

--
-- Các ràng buộc cho bảng `water`
--
ALTER TABLE `water`
  ADD CONSTRAINT `water_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
