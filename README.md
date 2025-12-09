# Sinjai Emails Management System

This application is a specialized employee and email management system tailored for the Sinjai Regency Government. It serves as a central hub for managing official email accounts hosted on a cPanel server, linking them to comprehensive employee data.

## Key Features

### 1. Email & Employee Management

The core of the system is managing email accounts that serve as unique identifiers for employees.

- **CRUD Operations:** Create, Read, Update, and Delete email accounts.
- **Rich Employee Data:** Stores detailed information including NIK (National ID), NIP (Civil Servant ID), full name with academic degrees, job titles, and status (ASN/Non-ASN).
- **Batch Processing:** Supports batch creation and updates of email accounts to handle bulk changes efficiently.

### 2. cPanel Integration

Tightly integrated with the cPanel API to manage the mail server directly.

- **Real-time Actions:** Create, delete, and change passwords for email accounts directly on the cPanel server.
- **Synchronization:** Sync functionality to fetch current server-side data (disk usage, suspension status) and ensure the local database matches the mail server.

### 3. Work Unit (Unit Kerja) Organization

Employees are organized into a hierarchical structure of Work Units.

- **Hierarchy:** Supports departments and sub-departments (parent-child relationships).
- **Filtering & Reporting:** Use work units to filter employee lists and generate specific reports.

### 4. BSrE Integration

Integrates with the Balai Sertifikasi Elektronik (BSrE) API.

- **Digital Certificate Check:** Verifies the status of an employee's digital certificate (issued/not issued), which is crucial for digital signature workflows.

### 5. Document Generation & Export

Automates the creation of administrative documents.

- **Work Agreements:** Generates "Perjanjian Kerja" (Work Agreements) in PDF format for employees.
- **Exports:** Export employee lists per work unit to PDF and CSV formats.
- **Bulk Export:** Automatically creates ZIP archives for large batch exports.
