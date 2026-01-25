# Sinjai Email Management System

A centralized platform for managing official employee email accounts for the Government of Sinjai Regency. This system integrates with cPanel, BSrE (Digital Signatures), and internal HR systems to provide a seamless experience for provisioning and maintaining official digital identities.

## Key Features

### 📧 Email Account Management
- **Comprehensive CRUD:** Manage email accounts with detailed employee profiles (NIK, NIP, Name, Job Title).
- **Batch Operations:** 
  - **Batch Creation:** Create multiple accounts at once with automatic username generation and smart duplicate resolution (prioritizing NIP characters, then NIK).
  - **Batch Update:** Update employee profiles and account details for hundreds of accounts simultaneously using CSV/Excel-like interfaces.
  - **Batch Update PK:** Dedicated interface for updating "Perjanjian Kerja" (Work Agreement) details specifically for Non-ASN staff.
- **cPanel Sync:** Real-time two-way synchronization with the mail server to track usage, quota, and status.

### 🏢 Organizational Structure
- **Unit Kerja Management:** Hierarchical management of work units (OPD) with parent-child relationships.
  - Supports batch creation of units.
- **Role & Rank Tracking:**
  - **Eselon:** Management of official ranks.
  - **Status ASN:** Classification of employees (PNS, PPPK, Non-ASN, etc.).
  - **Leadership Flags:** Dedicated tracking and reporting for "Pimpinan" (Heads of Agencies) and "Pimpinan Desa" (Village Heads).

### 🔐 Security & Integration
- **BSrE Integration:** Automated checking of Electronic Signature (TTE) status for each account (Issue, Expired, Revoked, etc.).
- **HR Sync:** Integration with **Simpegnas** API to pull accurate employee data and ensure consistency.

### 🌐 Web Desa & Kelurahan Tracking
- **Centralized Database:** Comprehensive listing of all Village (Desa) and Sub-district (Kelurahan) websites within Sinjai Regency.
- **Automated Expiration Fetching:** Integration with **PANDI RDAP** to automatically retrieve domain expiration dates for village websites.
- **Smart Expiration Rules:** Specialized logic to handle fixed expiration dates for Kelurahan websites.
- **Analytics Dashboard:** Visual tracking of website status (Active/Inactive) with percentage-based statistics and platform distribution (SIDEKA-NG, OpenSID, etc.).
- **Indonesian PDF Reports:** Generate professional reports with Indonesian month names, color-coded rows (Desa vs Kelurahan), and comprehensive platform statistics.

### 🏢 Website OPD Tracking
- **Agency Website Management:** Centralized tracking for all official websites of local government agencies (OPD).
- **Domain Expiration Monitoring:** Automated fetching of expiration data from PANDI RDAP.
- **Platform Analytics:** Track the technology stack used by each agency (Custom CMS, Pihak Ketiga, etc.).
- **Professional Exports:** Specialized PDF reports for OPD websites with Indonesian formatting and status visualization.

### 📄 Reporting & Documents
- **PDF Exports:** Generate formatted lists of emails, leadership directories, and individual account detail sheets.
- **CSV Exports:** Export Unit Kerja data in CSV format for bulk processing or external use.
- **Work Agreements (PK):** Automated generation of "Perjanjian Kerja" documents for Non-ASN staff, including batch ZIP downloads.

## Tech Stack

- **Framework:** CodeIgniter 4 (PHP)
- **Database:** MySQL / MariaDB
- **Frontend:** Bootstrap 5, Vanilla JS (with async batch processing)
- **PDF Generation:** Dompdf
- **Integrations:**
  - cPanel UAPI
  - BSrE API
  - Simpegnas API

## Environment

- PHP 8.1+
- Composer
