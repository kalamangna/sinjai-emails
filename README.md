# Email Management System

A comprehensive web application designed to manage official email accounts, employee data, and digital signature statuses efficiently. It serves as a central hub for linking email accounts hosted on a cPanel server with detailed employee records and external services.

## Key Features

### 1. Email & Employee Management

Centralized management of email accounts serving as unique identifiers for employees.

- **CRUD Operations:** Create, Read, Update, and Delete email accounts.
- **Rich Employee Data:** Stores detailed information including NIK (National ID), NIP (Civil Servant ID), full name with academic degrees, job titles, and employment status.
- **Batch Processing:** Supports batch creation and updates of email accounts to handle bulk changes efficiently.

### 2. cPanel Integration

Direct integration with the cPanel API for seamless mail server management.

- **Real-time Actions:** Perform actions like account creation, deletion, and password resets directly on the cPanel server.
- **Synchronization:** Sync functionality to fetch current server-side data (such as disk usage and suspension status) to ensure the local database remains up-to-date.

### 3. Organizational Structure Management

Organizes employees into a hierarchical structure of Work Units (Unit Kerja).

- **Hierarchy:** Supports departments and sub-departments (parent-child relationships).
- **Filtering & Reporting:** Allows filtering employee lists by work unit and generating specific reports based on organizational structure.

### 4. Digital Signature Integration (Status TTE)

Integrates with external Digital Signature Certification Authorities (e.g., BSrE) to manage electronic signature statuses.

- **Status Verification:** Checks the current status of an employee's digital signature certificate (e.g., Active, Expired, Not Registered).
- **Database Synchronization:** Syncs certificate statuses from the API directly to the local database for persistent storage and quick access.
- **Batch Synchronization:** Provides tools to sync statuses individually or in batches for entire work units.
- **Status Filtering:** Enables filtering of email lists based on their digital signature status.

### 5. External HR Data Integration

Connects with external Human Resources APIs (e.g., Simpegnas) to enrich employee profiles.

- **Data Retrieval:** Fetches detailed employee data based on unique identifiers (like NIP) from external systems.
- **Secure Access:** Configurable via environment variables for secure API communication.

### 6. Document Generation & Export

Automates the creation of administrative documents and reports.

- **Work Agreements:** Generates standardized "Work Agreements" (Perjanjian Kerja) in PDF format.
- **Comprehensive Exports:** Exports employee lists per work unit to PDF and CSV formats.
- **Enhanced PDF Reports:** PDF exports include detailed columns such as "Status TTE" with color-coded status indicators and legends.
- **Bulk Exporting:** Automatically generates ZIP archives for large batch export operations.
