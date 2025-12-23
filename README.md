# Email Management System

A centralized platform for managing official employee email accounts, integrated with government HR systems and digital signature authorities.

## Key Features

- **Employee Email Management:** Full CRUD operations for email accounts linked to detailed employee records (ASN/Non-ASN), including batch processing for bulk updates and creation.
- **cPanel Integration:** Real-time synchronization with mail servers for account management, usage tracking, and automated provisioning.
- **Organizational Hierarchy:** Management of Work Units (Unit Kerja) and Ranks (Eselon) with support for hierarchical structures.
- **Digital Signature (TTE) Integration:** Automated status verification and synchronization with BSrE (Balai Sertifikasi Elektronik).
- **HR Data Synchronization:** Seamless integration with external HR APIs (Simpegnas) to maintain accurate employee profiles.
- **Automated Document Generation:** Exporting of employee lists (PDF/CSV) and batch generation of Work Agreements (Perjanjian Kerja) in ZIP archives.

## Tech Stack

- **Framework:** CodeIgniter 4
- **PDF Generation:** Dompdf
- **APIs:** cPanel, BSrE, Simpegnas
- **Environment:** PHP 8.1+