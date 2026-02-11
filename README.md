# Sinjai Emails - Portal Manajemen Identitas Digital

A centralized management platform designed to streamline digital identity provisioning, institutional asset tracking, and technical support activities for the Government of Sinjai Regency.

## Core Features

### 🆔 Manajemen Identitas (Email)
- **Digital Account Provisioning:** Full lifecycle management of official electronic identities (@sinjaikab.go.id) integrated with cPanel API.
- **Bulk Processing (Layanan Batch):** High-volume account creation, profile updates, and automated PK (Perjanjian Kerja) document generation.
- **Service Integration:** Real-time synchronization with mail servers to monitor usage, disk quota, and account health.

### 🏢 Direktori Institusi
- **Unit Kerja Hierarchy:** Manage agency structures with multi-level relationships (OPD, UPTD, and Kecamatan).
- **Classification & Roles:** Track employee status (ASN/PPPK), official ranks (Eselon), and leadership positions.
- **Data Pimpinan Hub:** Specialized tracking for OPD leaders, Village heads (Kepala Desa), and Ward leaders (Lurah).

### 🔐 Compliance & Digital Signatures (TTE)
- **BSrE Integration:** Automated monitoring of electronic signature certification status via BSrE API.
- **Standardized Status Monitoring:** Consistent color-coded status tracking (ISSUE, EXPIRED, NO_CERTIFICATE, RENEW, etc.) across all platforms.
- **Human Resource Sync:** Connects with local HR systems (Simpegnas) to maintain accurate employee information.

### 📊 Monitoring Website
- **Centralized Tracking:** Monitor the status and availability of official government websites (OPD and Desa).
- **Automated Metadata:** Intelligent checks for domain expiration and management status (Dikelola Kominfo/Mandiri).
- **Visual Analytics:** Interactive dashboards providing insights into asset distribution and status using ApexCharts.

### 🤝 Log Pendampingan (Technical Support)
- **Activity Logging:** Document technical assistance and mentoring provided to various government units.
- **Service Categorization:** Specialized tracking for system applications, email support, or website management.
- **Method Tracking:** Log interactions based on support delivery (Remote/Online or On-site).

### 📄 Pelaporan Profesional
- **Standardized Exports:** Professional PDF and CSV reports with unified branding and localized formatting.
- **Batch Document Generation:** Automated generation of PK documents for PPPK Paruh Waktu with ZIP export capabilities.
- **Filtered Insights:** Targeted report generation based on specific Unit Kerja, Eselon, or Certification status.

## Technology Stack

- **Backend:** PHP 8.1+ with CodeIgniter 4 Framework
- **Database:** Relational Database (MySQL / MariaDB)
- **Frontend:** Tailwind CSS & Modern JavaScript (Async/Fetch)
- **Visualization:** ApexCharts.js
- **Reporting:** Dompdf for advanced PDF generation
- **API Integrations:** cPanel UAPI, BSrE API V2, Simpegnas API

## Requirements

- PHP 8.1 or higher
- Composer Dependency Manager
- MySQL 5.7+ or MariaDB 10.3+
- Node.js (for Tailwind CSS build process)
