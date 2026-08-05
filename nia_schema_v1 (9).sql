-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Aug 06, 2026 at 01:37 AM
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
-- Database: `nia_schema_v1`
--

-- --------------------------------------------------------

--
-- Table structure for table `assets`
--

CREATE TABLE `assets` (
  `asset_id` int(11) NOT NULL,
  `asset_code` varchar(50) NOT NULL,
  `asset_name` varchar(150) DEFAULT NULL,
  `qr_code_ref` varchar(100) NOT NULL,
  `description` varchar(255) NOT NULL,
  `brand` varchar(100) DEFAULT NULL,
  `model` varchar(100) DEFAULT NULL,
  `serial_number` varchar(100) DEFAULT NULL,
  `acquisition_cost` decimal(10,0) NOT NULL,
  `acquisition_date` date DEFAULT NULL,
  `status` enum('active','inactive','disposed','missing','pending_disposal') NOT NULL DEFAULT 'active',
  `condition` enum('good','fair','poor','unserviceable') NOT NULL DEFAULT 'good',
  `verification_status` enum('pending','verified','discrepancy') DEFAULT 'pending',
  `verified_at` datetime DEFAULT NULL,
  `verified_by` int(11) DEFAULT NULL,
  `inspection_remarks` text DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `disposal_reason` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `asset_accounts_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `assets`
--

INSERT INTO `assets` (`asset_id`, `asset_code`, `asset_name`, `qr_code_ref`, `description`, `brand`, `model`, `serial_number`, `acquisition_cost`, `acquisition_date`, `status`, `condition`, `verification_status`, `verified_at`, `verified_by`, `inspection_remarks`, `remarks`, `disposal_reason`, `created_at`, `updated_at`, `asset_accounts_id`) VALUES
(1, 'AST-001', 'Dell OptiPlex 7080', 'QR-bd37b60b7e8211f195d61068382e09fc', 'Desktop computer for admin use', 'Dell', 'OptiPlex 7080', 'SN-001-ABC', 45000, '2023-06-15', 'active', 'good', 'verified', NULL, NULL, '', 'Admin workstation', NULL, '2026-07-13 06:19:01', '2026-07-30 08:43:30', 1),
(2, 'AST-002', 'HP LaserJet Pro MFP', 'QR-bd37fade7e8211f195d61068382e09fc', 'Multi‑function printer', 'HP', 'LaserJet Pro M428fdw', 'SN-002-XYZ', 28000, '2023-07-20', 'active', 'good', 'verified', '2026-07-31 11:36:29', 3, '', 'Shared printer', NULL, '2026-07-13 06:19:01', '2026-07-31 09:36:29', 1),
(3, 'AST-003', 'Dell PowerEdge R740', 'QR-bd37fd1a7e8211f195d61068382e09fc', 'Main application server', 'Dell', 'PowerEdge R740', 'SN-003-456', 250000, '2023-08-10', 'active', 'fair', 'pending', NULL, NULL, NULL, 'Database server', NULL, '2026-07-13 06:19:01', '2026-07-13 06:19:01', 2),
(4, 'AST-004', 'Cisco Catalyst 9300', 'QR-bd37fe417e8211f195d61068382e09fc', 'Network core switch', 'Cisco', 'Catalyst 9300', 'SN-004-789', 180000, '2023-09-05', 'active', 'good', 'pending', NULL, NULL, NULL, 'Core network switch', NULL, '2026-07-13 06:19:01', '2026-07-13 06:19:01', 2),
(5, 'AST-005', 'John Deere 5055E Tractor', 'QR-bd37ff527e8211f195d61068382e09fc', 'Farm tractor', 'John Deere', '5055E', 'SN-005-321', 850000, '2023-09-05', 'active', 'good', 'pending', NULL, NULL, NULL, 'For field operations', NULL, '2026-07-13 06:19:01', '2026-07-13 06:19:01', 3),
(6, 'AST-006', 'Kubota L3560', 'QR-bd3800787e8211f195d61068382e09fc', 'Compact tractor', 'Kubota', 'L3560', 'SN-006-654', 650000, '2023-10-20', 'active', 'good', 'pending', NULL, NULL, NULL, 'Landscaping', NULL, '2026-07-13 06:19:01', '2026-07-13 06:19:01', 3),
(7, 'AST-007', 'Motorola DP4800 Radio', 'QR-bd38016a7e8211f195d61068382e09fc', 'Handheld two‑way radio', 'Motorola', 'DP4800', 'SN-007-987', 25000, '2023-11-01', 'active', 'good', 'pending', NULL, NULL, NULL, 'Field communication', NULL, '2026-07-13 06:19:01', '2026-07-13 06:19:01', 4),
(8, 'AST-008', 'Iridium Satellite Phone', 'QR-bd3802517e8211f195d61068382e09fc', 'Satellite phone for remote areas', 'Iridium', '9555', 'SN-008-654', 120000, '2023-12-15', 'active', 'good', 'pending', NULL, NULL, NULL, 'Emergency use', NULL, '2026-07-13 06:19:01', '2026-07-13 06:19:01', 4),
(9, 'AST-009', 'Caterpillar D6 Bulldozer', 'QR-bd3803967e8211f195d61068382e09fc', 'Heavy bulldozer', 'Caterpillar', 'D6', 'SN-009-321', 3500000, '2024-01-10', 'active', 'good', 'pending', NULL, NULL, NULL, 'Land clearing', NULL, '2026-07-13 06:19:01', '2026-07-13 06:19:01', 5),
(10, 'AST-010', 'Komatsu PC210 Excavator', 'QR-bd3804847e8211f195d61068382e09fc', 'Hydraulic excavator', 'Komatsu', 'PC210', 'SN-010-456', 2800000, '2024-02-05', 'active', 'good', 'pending', NULL, NULL, NULL, 'Excavation work', NULL, '2026-07-13 06:19:01', '2026-07-13 06:19:01', 5),
(11, 'AST-011', 'Heidelberg Speedmaster', 'QR-bd38056a7e8211f195d61068382e09fc', 'Offset printing press', 'Heidelberg', 'Speedmaster CD 102', 'SN-011-789', 12000000, '2023-05-10', 'active', 'fair', 'pending', NULL, NULL, NULL, 'High‑volume printing', NULL, '2026-07-13 06:19:01', '2026-07-13 06:19:01', 6),
(12, 'AST-012', 'HP Indigo 7900', 'QR-bd38093e7e8211f195d61068382e09fc', 'Digital press', 'HP', 'Indigo 7900', 'SN-012-123', 8500000, '2023-06-25', 'active', 'good', 'pending', NULL, NULL, NULL, 'Digital printing', NULL, '2026-07-13 06:19:01', '2026-07-13 06:19:01', 6),
(13, 'AST-013', 'Olympus BX53 Microscope', 'QR-bd380a927e8211f195d61068382e09fc', 'Research microscope', 'Olympus', 'BX53', 'SN-013-456', 150000, '2023-07-30', 'active', 'good', 'verified', '2026-07-31 11:49:00', 3, '', 'Lab research', NULL, '2026-07-13 06:19:01', '2026-07-31 09:49:00', 7),
(14, 'AST-014', 'Thermo Fisher Spectrometer', 'QR-bd380b727e8211f195d61068382e09fc', 'UV‑Vis spectrometer', 'Thermo Fisher', 'Genesys 150', 'SN-014-789', 220000, '2023-08-20', 'active', 'good', 'pending', NULL, NULL, NULL, 'Chemical analysis', NULL, '2026-07-13 06:19:01', '2026-07-13 06:19:01', 7),
(15, 'AST-015', 'Caterpillar C15 Generator', 'QR-bd380c507e8211f195d61068382e09fc', 'Diesel generator 500kW', 'Caterpillar', 'C15', 'SN-015-321', 1200000, '2023-09-15', 'active', 'good', 'pending', NULL, NULL, NULL, 'Backup power', NULL, '2026-07-13 06:19:01', '2026-07-13 06:19:01', 8),
(16, 'AST-016', 'Siemens Transformer 5MVA', 'QR-bd380d297e8211f195d61068382e09fc', 'Power transformer', 'Siemens', '5MVA', 'SN-016-654', 3000000, '2023-10-25', 'active', 'fair', 'pending', NULL, NULL, NULL, 'Substation', NULL, '2026-07-13 06:19:01', '2026-07-13 06:19:01', 8),
(17, 'AST-017', 'Ingersoll Rand Compressor', 'QR-bd380e1f7e8211f195d61068382e09fc', 'Air compressor 100HP', 'Ingersoll Rand', '100HP', 'SN-017-987', 450000, '2023-11-10', 'active', 'good', 'pending', NULL, NULL, NULL, 'Workshop air supply', NULL, '2026-07-13 06:19:01', '2026-07-13 06:19:01', 9),
(18, 'AST-018', 'Parker Hydraulic Pump', 'QR-bd380efd7e8211f195d61068382e09fc', 'Hydraulic pump unit', 'Parker', 'PV180', 'SN-018-123', 250000, '2023-12-05', 'active', 'good', 'pending', NULL, NULL, NULL, 'Hydraulic system', NULL, '2026-07-13 06:19:01', '2026-07-13 06:19:01', 9),
(19, 'AST-019', 'Toyota Hilux 4x4', 'QR-bd380fd97e8211f195d61068382e09fc', 'Pickup truck', 'Toyota', 'Hilux 4x4', 'SN-019-456', 1200000, '2024-01-20', 'active', 'good', 'pending', NULL, NULL, NULL, 'Field vehicle', NULL, '2026-07-13 06:19:01', '2026-07-13 06:19:01', 10),
(20, 'AST-020', 'Ford Ranger Raptor', 'QR-bd3810b97e8211f195d61068382e09fc', 'Off‑road pickup', 'Ford', 'Ranger Raptor', 'SN-020-789', 1500000, '2024-02-15', 'active', 'good', 'pending', NULL, NULL, NULL, 'Utility vehicle', NULL, '2026-07-13 06:19:01', '2026-07-13 06:19:01', 10),
(21, 'AST-021', 'Utility Trailer 20ft', 'QR-bd38119f7e8211f195d61068382e09fc', 'Cargo trailer', 'Utility', '20ft', 'SN-021-321', 150000, '2023-06-01', 'active', 'good', 'pending', NULL, NULL, NULL, 'Cargo transport', NULL, '2026-07-13 06:19:01', '2026-07-13 06:19:01', 11),
(22, 'AST-022', 'Forklift Toyota 8FGU25', 'QR-bd3812797e8211f195d61068382e09fc', 'Diesel forklift', 'Toyota', '8FGU25', 'SN-022-654', 350000, '2023-07-15', 'active', 'good', 'pending', NULL, NULL, NULL, 'Warehouse lifting', NULL, '2026-07-13 06:19:01', '2026-07-13 06:19:01', 11),
(23, 'AST-023', 'Executive Office Desk', 'QR-bd3813917e8211f195d61068382e09fc', 'Wooden executive desk', 'Woodcraft', 'Executive', 'SN-023-987', 25000, '2023-08-01', 'active', 'good', 'pending', NULL, NULL, NULL, 'CEO office', NULL, '2026-07-13 06:19:01', '2026-07-13 06:19:01', 12),
(24, 'AST-024', 'Steel Filing Cabinet', 'QR-bd3814ab7e8211f195d61068382e09fc', '4‑drawer filing cabinet', 'Steelco', '4‑drawer', 'SN-024-123', 12000, '2023-09-10', 'active', 'good', 'pending', NULL, NULL, NULL, 'Document storage', NULL, '2026-07-13 06:19:01', '2026-07-13 06:19:01', 12),
(25, 'ASST 456', 'ink', 'QR-6A72EC33D4FB9', '', 'brother', '', '010101', 50000, '2026-08-07', 'active', 'good', 'pending', NULL, NULL, NULL, '', NULL, '2026-08-05 07:54:27', '2026-08-05 07:54:27', 6),
(26, 'm', 'k', 'QR-6A73B98E754D0', ',', 'm', 'm', 'm', 70000, '0004-02-05', 'active', 'good', 'pending', NULL, NULL, NULL, 's', NULL, '2026-08-05 22:30:38', '2026-08-05 22:30:38', 2);

-- --------------------------------------------------------

--
-- Table structure for table `asset_accounts`
--

CREATE TABLE `asset_accounts` (
  `asset_accounts_id` int(11) NOT NULL,
  `account_code` varchar(30) NOT NULL,
  `account_name` varchar(150) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `asset_accounts`
--

INSERT INTO `asset_accounts` (`asset_accounts_id`, `account_code`, `account_name`, `created_at`) VALUES
(1, '05-020', 'Office Equipment', '2026-07-13 06:19:00'),
(2, '05-030', 'Information and Communications Technology Equipment', '2026-07-13 06:19:00'),
(3, '05-040', 'Agricultural and Forestry Equipment', '2026-07-13 06:19:00'),
(4, '05-070', 'Communication Equipment', '2026-07-13 06:19:00'),
(5, '05-080', 'Construction and Heavy Equipment', '2026-07-13 06:19:00'),
(6, '05-120', 'Printing Equipment', '2026-07-13 06:19:00'),
(7, '05-140', 'Technical and Scientific Equipment', '2026-07-13 06:19:00'),
(8, '05-170', 'Electrical Equipment', '2026-07-13 06:19:00'),
(9, '05-990', 'Other Machinery and Equipment', '2026-07-13 06:19:00'),
(10, '06-010', 'Motor Vehicles', '2026-07-13 06:19:00'),
(11, '06-990', 'Other Transportation Equipment', '2026-07-13 06:19:00'),
(12, '07-010', 'Furnitures and Fixtures', '2026-07-13 06:19:00');

-- --------------------------------------------------------

--
-- Table structure for table `asset_custodies`
--

CREATE TABLE `asset_custodies` (
  `asset_custodies_id` int(11) NOT NULL,
  `asset_id` int(11) NOT NULL,
  `custodian_id` int(11) NOT NULL,
  `office_id` int(11) NOT NULL,
  `property_number` varchar(50) NOT NULL,
  `effectivity_date` date NOT NULL,
  `end_date` date DEFAULT NULL,
  `status` enum('active','returned','transferred') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `asset_custodies`
--

INSERT INTO `asset_custodies` (`asset_custodies_id`, `asset_id`, `custodian_id`, `office_id`, `property_number`, `effectivity_date`, `end_date`, `status`, `created_at`) VALUES
(1, 1, 2, 1, 'PAR-2024-001', '2026-07-13', NULL, 'active', '2026-07-13 06:19:01'),
(2, 3, 2, 1, 'PAR-2024-001', '2026-07-13', NULL, 'active', '2026-07-13 06:19:01'),
(3, 5, 2, 1, 'PAR-2024-001', '2026-07-13', NULL, 'active', '2026-07-13 06:19:01'),
(4, 7, 2, 1, 'PAR-2024-001', '2026-07-13', NULL, 'active', '2026-07-13 06:19:01'),
(5, 9, 2, 1, 'PAR-2024-001', '2026-07-13', NULL, 'active', '2026-07-13 06:19:01'),
(6, 11, 2, 1, 'PAR-2024-001', '2026-07-13', NULL, 'active', '2026-07-13 06:19:01'),
(7, 13, 2, 1, 'PAR-2024-001', '2026-07-13', '2026-07-31', '', '2026-07-13 06:19:01'),
(8, 15, 2, 1, 'PAR-2024-001', '2026-07-13', NULL, 'active', '2026-07-13 06:19:01'),
(9, 17, 2, 1, 'PAR-2024-001', '2026-07-13', NULL, 'active', '2026-07-13 06:19:01'),
(10, 19, 2, 1, 'PAR-2024-001', '2026-07-13', NULL, 'active', '2026-07-13 06:19:01'),
(11, 21, 2, 1, 'PAR-2024-001', '2026-07-13', NULL, 'active', '2026-07-13 06:19:01'),
(12, 23, 2, 1, 'PAR-2024-001', '2026-07-13', NULL, 'active', '2026-07-13 06:19:01'),
(16, 6, 5, 18, '', '2026-07-13', NULL, 'active', '2026-07-13 06:33:59'),
(17, 10, 5, 11, '', '2026-07-13', NULL, 'active', '2026-07-13 06:34:14'),
(18, 13, 23, 3, 'TRANSFER-20260731', '2026-07-31', NULL, 'active', '2026-07-31 09:49:00'),
(19, 26, 6, 1, '', '2026-08-06', NULL, 'active', '2026-08-05 22:30:38');

-- --------------------------------------------------------

--
-- Table structure for table `asset_locations`
--

CREATE TABLE `asset_locations` (
  `id` int(11) NOT NULL,
  `asset_id` int(11) NOT NULL,
  `location_name` varchar(200) NOT NULL,
  `site_type` enum('office','field_project','warehouse','other') DEFAULT NULL,
  `description` text DEFAULT NULL,
  `recorded_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `recorded_by` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `asset_reports`
--

CREATE TABLE `asset_reports` (
  `asset_report_id` int(11) NOT NULL,
  `report_number` varchar(50) NOT NULL,
  `report_date` date NOT NULL,
  `office_id` int(11) NOT NULL,
  `prepared_by` int(11) NOT NULL,
  `status` enum('draft','submitted','approved') NOT NULL DEFAULT 'draft',
  `remarks` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `asset_report_items`
--

CREATE TABLE `asset_report_items` (
  `asset_report_item_id` int(11) NOT NULL,
  `asset_report_id` int(11) NOT NULL,
  `asset_id` int(11) NOT NULL,
  `verification_status` enum('found','missing','for_repair','disposed') NOT NULL,
  `asset_condition` enum('good','fair','poor','unserviceable') DEFAULT NULL,
  `verified_by` int(11) NOT NULL,
  `verified_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `remarks` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `asset_transfers`
--

CREATE TABLE `asset_transfers` (
  `id` int(11) NOT NULL,
  `asset_id` int(11) NOT NULL,
  `from_custodian_id` int(11) DEFAULT NULL,
  `to_custodian_id` int(11) NOT NULL,
  `from_office_id` int(11) DEFAULT NULL,
  `to_office_id` int(11) NOT NULL,
  `transfer_number` varchar(50) NOT NULL,
  `transfer_date` date NOT NULL,
  `approved_by` int(11) DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `status` enum('pending','approved','acknowledged','rejected') NOT NULL DEFAULT 'pending',
  `remarks` text DEFAULT NULL,
  `acknowledged_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `asset_transfers`
--

INSERT INTO `asset_transfers` (`id`, `asset_id`, `from_custodian_id`, `to_custodian_id`, `from_office_id`, `to_office_id`, `transfer_number`, `transfer_date`, `approved_by`, `approved_at`, `status`, `remarks`, `acknowledged_at`, `created_at`) VALUES
(1, 13, 2, 23, 1, 3, 'TR-20260731-C158D2', '2026-07-31', NULL, NULL, 'approved', NULL, NULL, '2026-07-31 09:49:00'),
(2, 26, NULL, 6, NULL, 1, 'TR-20260806-E78631', '2026-08-06', NULL, NULL, 'approved', NULL, NULL, '2026-08-05 22:30:38');

-- --------------------------------------------------------

--
-- Table structure for table `audit_trail`
--

CREATE TABLE `audit_trail` (
  `audit_trail_id` int(11) NOT NULL,
  `asset_id` int(11) DEFAULT NULL,
  `performed_by` int(11) NOT NULL,
  `action_type` varchar(60) NOT NULL,
  `module` varchar(60) NOT NULL,
  `previous_values` longtext DEFAULT NULL,
  `new_values` longtext DEFAULT NULL,
  `performed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `audit_trail`
--

INSERT INTO `audit_trail` (`audit_trail_id`, `asset_id`, `performed_by`, `action_type`, `module`, `previous_values`, `new_values`, `performed_at`) VALUES
(1, 1, 3, 'VERIFY', 'ASSET', '', '', '2026-07-30 08:32:44'),
(2, 1, 3, 'VERIFY', 'ASSET', '', '', '2026-07-30 08:38:17'),
(3, 1, 3, 'VERIFY', 'ASSET', '', '', '2026-07-30 08:43:30'),
(4, 2, 3, 'VERIFY', 'ASSET', '', '', '2026-07-31 09:36:29'),
(5, 13, 3, 'VERIFY', 'ASSET', '', '', '2026-07-31 09:49:00');

-- --------------------------------------------------------

--
-- Table structure for table `offices`
--

CREATE TABLE `offices` (
  `office_id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `office_code` varchar(20) NOT NULL,
  `location` varchar(200) DEFAULT NULL,
  `contact_person` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `offices`
--

INSERT INTO `offices` (`office_id`, `name`, `office_code`, `location`, `contact_person`, `created_at`) VALUES
(1, 'OFFICE OF THE REGIONAL IRRIGATION MANAGER - Region 9', 'R9-01', NULL, NULL, '2026-07-09 08:37:36'),
(2, 'Office of the Manager, ENGINEERING AND OPERATION DIVISION', 'R9-02', NULL, NULL, '2026-07-09 08:37:36'),
(3, 'Planning, Design and Construction Section', 'R9-03', NULL, NULL, '2026-07-09 08:37:36'),
(4, 'Operation, Institutional and Equipment Section', 'R9-04', NULL, NULL, '2026-07-09 08:37:36'),
(5, 'Office of the Manager, ADMINISTRATIVE AND FINANCE DIVISION', 'R9-05', NULL, NULL, '2026-07-09 08:37:36'),
(6, 'Finance Section', 'R9-06', NULL, NULL, '2026-07-09 08:37:36'),
(7, 'Administrative Section', 'R9-07', NULL, NULL, '2026-07-09 08:37:36'),
(8, 'NIA-COA-RO', 'R9-08', NULL, NULL, '2026-07-09 08:37:36'),
(9, 'Office of the Division Manager, ZAMBOANGA DEL NORTE REGIONAL SUB-OFFICE', 'R9-09', NULL, NULL, '2026-07-09 08:37:36'),
(10, 'Engineering, Operations and Maintenance Section', 'R9-10', NULL, NULL, '2026-07-09 08:37:36'),
(11, 'Administrative and Finance Section', 'R9-11', NULL, NULL, '2026-07-09 08:37:36'),
(12, 'Office of the Division Manager, ZAMBASULTA REGIONAL SUB-OFFICE', 'R9-12', NULL, NULL, '2026-07-09 08:37:36'),
(15, 'NIA-COA-ZAMBASULTA', 'R9-15', NULL, NULL, '2026-07-09 08:37:36'),
(16, 'Office of the Division Manager, ZAMBOANGA SIBUGAY IRRIGATION MANAGEMENT OFFICE', 'R9-16', NULL, NULL, '2026-07-09 08:37:36'),
(19, 'NIA-COA-ZAMBOANGA SIBUGAY', 'R9-19', NULL, NULL, '2026-07-09 08:37:36'),
(20, 'Office of the Division Manager, ZAMBOANGA DEL SUR IRRIGATION MANAGEMENT OFFICE', 'R9-20', NULL, NULL, '2026-07-09 08:37:36'),
(23, 'NIA-COA-ZAMBOANGA DEL SUR', 'R9-23', NULL, NULL, '2026-07-09 08:37:36'),
(24, 'SALUG-DIPOLO RIVER IRRIGATION SYSTEM', 'R9-24', NULL, NULL, '2026-07-09 08:37:36'),
(25, 'SIBUGUEY VALLEY RIVER IRRIGATION SYSTEM', 'R9-25', NULL, NULL, '2026-07-09 08:37:36'),
(26, 'LABANGAN RIVER IRRIGATION SYSTEM', 'R9-26', NULL, NULL, '2026-07-09 08:37:36');

-- --------------------------------------------------------

--
-- Table structure for table `personnel`
--

CREATE TABLE `personnel` (
  `personnel_id` int(11) NOT NULL,
  `employee_id` varchar(30) NOT NULL,
  `full_name` varchar(150) NOT NULL,
  `position` varchar(100) DEFAULT NULL,
  `designation` varchar(100) DEFAULT NULL,
  `salary_grade` tinyint(3) UNSIGNED NOT NULL DEFAULT 1,
  `employment_status` enum('active','retired','transferred','inactive') NOT NULL DEFAULT 'active',
  `office_id` int(11) NOT NULL,
  `is_active` tinyint(4) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `personnel`
--

INSERT INTO `personnel` (`personnel_id`, `employee_id`, `full_name`, `position`, `designation`, `salary_grade`, `employment_status`, `office_id`, `is_active`, `created_at`) VALUES
(1, 'NIA-001', 'Dr. Maria Santos', 'Regional Director', 'Chief Executive Officer', 1, 'active', 1, 1, '2026-07-20 07:01:47'),
(2, 'NIA-002', 'Engr. Juan Dela Cruz', 'Assistant Regional Director', 'Operations Head', 1, 'active', 1, 1, '2026-07-20 07:01:47'),
(3, 'NIA-003', 'Atty. Anna Gonzales', 'Legal Officer', 'Legal Affairs', 1, 'active', 1, 1, '2026-07-20 07:01:47'),
(4, 'NIA-004', 'Engr. Pedro Reyes', 'Division Chief', 'Engineering Division', 1, 'active', 1, 1, '2026-07-20 07:01:47'),
(5, 'NIA-005', 'Ms. Liza Mendoza', 'Administrative Officer V', 'HR & Admin', 1, 'active', 1, 1, '2026-07-20 07:01:47'),
(6, 'NIA-006', 'Mr. Carlo Villanueva', 'Supply Officer III', 'Property & Supply', 1, 'active', 1, 1, '2026-07-20 07:01:47'),
(7, 'NIA-007', 'Engr. Jose Mercado', 'Project Engineer', 'Infrastructure Projects', 1, 'active', 1, 1, '2026-07-20 07:01:47'),
(8, 'NIA-008', 'Ms. Grace Tan', 'Financial Analyst', 'Budget & Finance', 1, 'active', 1, 1, '2026-07-20 07:01:47'),
(9, 'NIA-009', 'Mr. Ramil Santos', 'IT Specialist', 'Information Systems', 1, 'active', 1, 1, '2026-07-20 07:01:47'),
(10, 'NIA-010', 'Engr. Mark Rivera', 'Field Engineer', 'Irrigation Systems', 1, 'active', 1, 1, '2026-07-20 07:01:47'),
(11, 'ZCIO-001', 'Engr. Fernando Lopez', 'Division Chief', 'Zamboanga City', 1, 'active', 2, 1, '2026-07-20 07:01:47'),
(12, 'ZCIO-002', 'Ms. Teresa Cruz', 'Administrative Officer IV', 'HR & Finance', 1, 'active', 2, 1, '2026-07-20 07:01:47'),
(13, 'ZCIO-003', 'Engr. Gilbert Tan', 'Senior Engineer', 'Irrigation Design', 1, 'active', 2, 1, '2026-07-20 07:01:47'),
(14, 'ZCIO-004', 'Mr. Rodel Fernandez', 'Technician', 'Field Operations', 17, 'active', 2, 1, '2026-07-20 07:01:47'),
(15, 'ZCIO-005', 'Ms. Jennifer Reyes', 'Supply Officer', 'Equipment & Materials', 1, 'active', 2, 1, '2026-07-20 07:01:47'),
(16, 'ZCIO-006', 'Engr. Allan Santos', 'Project Coordinator', 'Infrastructure', 1, 'active', 2, 1, '2026-07-20 07:01:47'),
(17, 'ZCIO-007', 'Mr. Dominador Cruz', 'Warehouse Manager', 'Inventory Control', 1, 'active', 2, 1, '2026-07-20 07:01:47'),
(18, 'ZCIO-008', 'Ms. Maricel Mendoza', 'Records Officer', 'Document Control', 1, 'active', 2, 1, '2026-07-20 07:01:47'),
(19, 'ZCIO-009', 'Engr. Paul Garcia', 'Field Engineer', 'Irrigation Systems', 1, 'active', 2, 1, '2026-07-20 07:01:47'),
(20, 'ZCIO-010', 'Mr. Rolando Paz', 'Driver/Mechanic', 'Transportation', 1, 'active', 2, 1, '2026-07-20 07:01:47'),
(21, 'PCIO-001', 'Engr. Rebecca Tan', 'Division Chief', 'Pagadian City', 1, 'active', 3, 1, '2026-07-20 07:01:47'),
(22, 'PCIO-002', 'Mr. Ricardo Santos', 'Administrative Officer IV', 'Admin & Finance', 1, 'active', 3, 1, '2026-07-20 07:01:47'),
(23, 'PCIO-003', 'Engr. Emmanuel Cruz', 'Senior Engineer', 'Irrigation Systems', 1, 'active', 3, 1, '2026-07-20 07:01:47'),
(24, 'PCIO-004', 'Ms. Virginia Reyes', 'Supply Officer', 'Materials Management', 1, 'active', 3, 1, '2026-07-20 07:01:47'),
(25, 'PCIO-005', 'Engr. Danilo Fernandez', 'Project Engineer', 'Construction', 1, 'active', 3, 1, '2026-07-20 07:01:47'),
(26, 'PCIO-006', 'Mr. Henry Tan', 'Technician', 'Field Maintenance', 1, 'active', 3, 1, '2026-07-20 07:01:47'),
(27, 'PCIO-007', 'Ms. Lourdes Sarmiento', 'Records Officer', 'Document Control', 1, 'active', 3, 1, '2026-07-20 07:01:47'),
(28, 'PCIO-008', 'Engr. Ronald Garcia', 'Field Engineer', 'Irrigation Operations', 1, 'active', 3, 1, '2026-07-20 07:01:47'),
(29, 'PCIO-009', 'Mr. Eddie Cruz', 'Warehouse Staff', 'Inventory', 1, 'active', 3, 1, '2026-07-20 07:01:47'),
(30, 'PCIO-010', 'Ms. Corazon Santos', 'Administrative Assistant', 'Office Support', 1, 'active', 3, 1, '2026-07-20 07:01:47');

-- --------------------------------------------------------

--
-- Table structure for table `qr_scans`
--

CREATE TABLE `qr_scans` (
  `qr_scans_id` int(11) NOT NULL,
  `asset_id` int(11) NOT NULL,
  `scanned_by` int(11) NOT NULL,
  `scanned_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `users_id` int(11) NOT NULL,
  `personnel_id` int(11) NOT NULL,
  `username` varchar(80) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('admin','encoder','asset_inspector') NOT NULL,
  `is_active` tinyint(4) DEFAULT 1,
  `last_login` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`users_id`, `personnel_id`, `username`, `password_hash`, `role`, `is_active`, `last_login`, `created_at`) VALUES
(1, 1, 'admin', '$2y$10$AR8kpRmwIEkmFtoQ40aqLe2VZh4gb1SAmynr7hn7uUZxTgfX5DlKO', 'admin', 1, '2026-08-05 22:24:36', '2026-07-04 04:54:18'),
(2, 2, 'supply_officer', '$2y$10$BoC/diZ4Z/a32pSH9fLHguiX0qCvHJpL1actmOG61vcifO3HAjh6K', 'encoder', 1, '2026-08-05 22:26:38', '2026-07-04 04:54:18'),
(3, 3, 'inspector', '$2y$10$BoC/diZ4Z/a32pSH9fLHguiX0qCvHJpL1actmOG61vcifO3HAjh6K', 'asset_inspector', 1, '2026-08-05 22:25:07', '2026-07-04 04:54:18');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `assets`
--
ALTER TABLE `assets`
  ADD PRIMARY KEY (`asset_id`),
  ADD UNIQUE KEY `asset_code` (`asset_code`),
  ADD UNIQUE KEY `qr_code_ref` (`qr_code_ref`),
  ADD UNIQUE KEY `serial_number` (`serial_number`) USING BTREE,
  ADD KEY `fk_asset_account` (`asset_accounts_id`),
  ADD KEY `verified_by` (`verified_by`);

--
-- Indexes for table `asset_accounts`
--
ALTER TABLE `asset_accounts`
  ADD PRIMARY KEY (`asset_accounts_id`),
  ADD UNIQUE KEY `account_code` (`account_code`);

--
-- Indexes for table `asset_custodies`
--
ALTER TABLE `asset_custodies`
  ADD PRIMARY KEY (`asset_custodies_id`),
  ADD KEY `asset_id` (`asset_id`),
  ADD KEY `custodian_id` (`custodian_id`),
  ADD KEY `office_id` (`office_id`);

--
-- Indexes for table `asset_locations`
--
ALTER TABLE `asset_locations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `asset_id` (`asset_id`),
  ADD KEY `asset_locations_ibfk_2` (`recorded_by`);

--
-- Indexes for table `asset_reports`
--
ALTER TABLE `asset_reports`
  ADD PRIMARY KEY (`asset_report_id`),
  ADD UNIQUE KEY `report_number` (`report_number`),
  ADD KEY `office_id` (`office_id`),
  ADD KEY `inventory_reports_ibfk_2` (`prepared_by`);

--
-- Indexes for table `asset_report_items`
--
ALTER TABLE `asset_report_items`
  ADD PRIMARY KEY (`asset_report_item_id`),
  ADD UNIQUE KEY `uk_line_report_asset` (`asset_report_id`,`asset_id`),
  ADD KEY `asset_id` (`asset_id`),
  ADD KEY `inventory_line_items_ibfk_3` (`verified_by`);

--
-- Indexes for table `asset_transfers`
--
ALTER TABLE `asset_transfers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `transfer_number` (`transfer_number`),
  ADD KEY `asset_id` (`asset_id`),
  ADD KEY `from_custodian_id` (`from_custodian_id`),
  ADD KEY `to_custodian_id` (`to_custodian_id`),
  ADD KEY `from_office_id` (`from_office_id`),
  ADD KEY `to_office_id` (`to_office_id`),
  ADD KEY `asset_transfers_ibfk_6` (`approved_by`);

--
-- Indexes for table `audit_trail`
--
ALTER TABLE `audit_trail`
  ADD PRIMARY KEY (`audit_trail_id`),
  ADD KEY `asset_id` (`asset_id`),
  ADD KEY `audit_trail_ibfk_2` (`performed_by`);

--
-- Indexes for table `offices`
--
ALTER TABLE `offices`
  ADD PRIMARY KEY (`office_id`),
  ADD UNIQUE KEY `office_code` (`office_code`) USING BTREE;

--
-- Indexes for table `personnel`
--
ALTER TABLE `personnel`
  ADD PRIMARY KEY (`personnel_id`),
  ADD UNIQUE KEY `employee_id` (`employee_id`),
  ADD KEY `personnel_ibfk_1` (`office_id`);

--
-- Indexes for table `qr_scans`
--
ALTER TABLE `qr_scans`
  ADD PRIMARY KEY (`qr_scans_id`),
  ADD KEY `asset_id` (`asset_id`),
  ADD KEY `qr_scans_ibfk_2` (`scanned_by`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`users_id`),
  ADD UNIQUE KEY `personnel_id` (`personnel_id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `assets`
--
ALTER TABLE `assets`
  MODIFY `asset_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `asset_accounts`
--
ALTER TABLE `asset_accounts`
  MODIFY `asset_accounts_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `asset_custodies`
--
ALTER TABLE `asset_custodies`
  MODIFY `asset_custodies_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `asset_locations`
--
ALTER TABLE `asset_locations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `asset_reports`
--
ALTER TABLE `asset_reports`
  MODIFY `asset_report_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `asset_report_items`
--
ALTER TABLE `asset_report_items`
  MODIFY `asset_report_item_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `asset_transfers`
--
ALTER TABLE `asset_transfers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `audit_trail`
--
ALTER TABLE `audit_trail`
  MODIFY `audit_trail_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `offices`
--
ALTER TABLE `offices`
  MODIFY `office_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `personnel`
--
ALTER TABLE `personnel`
  MODIFY `personnel_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `qr_scans`
--
ALTER TABLE `qr_scans`
  MODIFY `qr_scans_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `users_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `assets`
--
ALTER TABLE `assets`
  ADD CONSTRAINT `assets_ibfk_1` FOREIGN KEY (`verified_by`) REFERENCES `users` (`users_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_asset_account` FOREIGN KEY (`asset_accounts_id`) REFERENCES `asset_accounts` (`asset_accounts_id`) ON DELETE NO ACTION ON UPDATE CASCADE;

--
-- Constraints for table `asset_custodies`
--
ALTER TABLE `asset_custodies`
  ADD CONSTRAINT `asset_custodies_ibfk_1` FOREIGN KEY (`asset_id`) REFERENCES `assets` (`asset_id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `asset_custodies_ibfk_2` FOREIGN KEY (`custodian_id`) REFERENCES `personnel` (`personnel_id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `asset_custodies_ibfk_3` FOREIGN KEY (`office_id`) REFERENCES `offices` (`office_id`) ON UPDATE CASCADE;

--
-- Constraints for table `asset_locations`
--
ALTER TABLE `asset_locations`
  ADD CONSTRAINT `asset_locations_ibfk_1` FOREIGN KEY (`asset_id`) REFERENCES `assets` (`asset_id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `asset_locations_ibfk_2` FOREIGN KEY (`recorded_by`) REFERENCES `users` (`users_id`) ON UPDATE CASCADE;

--
-- Constraints for table `asset_reports`
--
ALTER TABLE `asset_reports`
  ADD CONSTRAINT `asset_reports_ibfk_1` FOREIGN KEY (`office_id`) REFERENCES `offices` (`office_id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `asset_reports_ibfk_2` FOREIGN KEY (`prepared_by`) REFERENCES `users` (`users_id`) ON UPDATE CASCADE;

--
-- Constraints for table `asset_report_items`
--
ALTER TABLE `asset_report_items`
  ADD CONSTRAINT `asset_report_items_ibfk_1` FOREIGN KEY (`asset_report_id`) REFERENCES `asset_reports` (`asset_report_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `asset_report_items_ibfk_2` FOREIGN KEY (`asset_id`) REFERENCES `assets` (`asset_id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `asset_report_items_ibfk_3` FOREIGN KEY (`verified_by`) REFERENCES `users` (`users_id`) ON UPDATE CASCADE;

--
-- Constraints for table `asset_transfers`
--
ALTER TABLE `asset_transfers`
  ADD CONSTRAINT `asset_transfers_ibfk_1` FOREIGN KEY (`asset_id`) REFERENCES `assets` (`asset_id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `asset_transfers_ibfk_2` FOREIGN KEY (`from_custodian_id`) REFERENCES `personnel` (`personnel_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `asset_transfers_ibfk_3` FOREIGN KEY (`to_custodian_id`) REFERENCES `personnel` (`personnel_id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `asset_transfers_ibfk_4` FOREIGN KEY (`from_office_id`) REFERENCES `offices` (`office_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `asset_transfers_ibfk_5` FOREIGN KEY (`to_office_id`) REFERENCES `offices` (`office_id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `asset_transfers_ibfk_6` FOREIGN KEY (`approved_by`) REFERENCES `users` (`users_id`) ON UPDATE CASCADE;

--
-- Constraints for table `audit_trail`
--
ALTER TABLE `audit_trail`
  ADD CONSTRAINT `audit_trail_ibfk_1` FOREIGN KEY (`asset_id`) REFERENCES `assets` (`asset_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `audit_trail_ibfk_2` FOREIGN KEY (`performed_by`) REFERENCES `users` (`users_id`) ON UPDATE CASCADE;

--
-- Constraints for table `personnel`
--
ALTER TABLE `personnel`
  ADD CONSTRAINT `personnel_ibfk_1` FOREIGN KEY (`office_id`) REFERENCES `offices` (`office_id`) ON UPDATE CASCADE;

--
-- Constraints for table `qr_scans`
--
ALTER TABLE `qr_scans`
  ADD CONSTRAINT `qr_scans_ibfk_1` FOREIGN KEY (`asset_id`) REFERENCES `assets` (`asset_id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `qr_scans_ibfk_2` FOREIGN KEY (`scanned_by`) REFERENCES `users` (`users_id`) ON UPDATE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`personnel_id`) REFERENCES `personnel` (`personnel_id`) ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
