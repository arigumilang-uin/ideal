# Backend Architecture Documentation

## Directory Structure & Responsibilities

This document defines the purpose and responsibilities of each folder in the Laravel `app/` directory,
following **Clean Architecture** and **Single Responsibility Principle (SRP)**.

---

## 📁 Console/Commands/

**Purpose:** Artisan CLI commands for automation and maintenance tasks.

**Usage:**

-   Cron jobs (scheduled tasks)
-   Queue workers
-   Database maintenance
-   Developer utilities

---

## 📁 Data/

**Purpose:** Data Transfer Objects (DTOs) for type-safe data passing between layers.

**Usage:**

-   Request data validation and transformation
-   Response data shaping
-   Domain-specific data structures
-   Separates data shape from Eloquent models

---

## 📁 Enums/

**Purpose:** Type-safe enumerations for fixed values.

**Usage:**

-   Status values (StatusPembinaan, StatusTindakLanjut)
-   Category types (KategoriPelanggaran)
-   Level definitions (TingkatPelanggaran)

---

## 📁 Exceptions/

**Purpose:** Custom exception classes for domain-specific error handling.

**Usage:**

-   Business logic validation errors
-   Authorization failures
-   Domain-specific exceptions

---

## 📁 Helpers/

**Purpose:** Global utility functions and static helper classes.

**Usage:**

-   Date/time formatting
-   String manipulation
-   Common calculations

---

## 📁 Http/Controllers/

**Purpose:** HTTP request handlers that act as "couriers" (thin controllers).

**Rules:**

-   NO business logic
-   NO database queries
-   NO complex data manipulation
-   Target: <20 lines per method

---

## 📁 Http/Middleware/

**Purpose:** Request/Response filters (auth, throttle, logging).

---

## 📁 Http/Requests/

**Purpose:** Form Request classes for input validation.

---

## 📁 Jobs/

**Purpose:** Queued jobs for background processing.

---

## 📁 Listeners/

**Purpose:** Event listeners that respond to application events.

---

## 📁 Models/

**Purpose:** Eloquent models representing database tables.

---

## 📁 Notifications/

**Purpose:** Notification classes for multi-channel alerts (email, database, SMS).

---

## 📁 Observers/

**Purpose:** Model lifecycle hooks (creating, created, updating, deleted).

---

## 📁 Policies/

**Purpose:** Model-based authorization policies.

---

## 📁 Providers/

**Purpose:** Service providers for dependency injection and bootstrapping.

---

## 📁 Repositories/

**Purpose:** Data access layer for database operations.

**Pattern:**

1. Define interface in `Contracts/`
2. Implement in repository class
3. Bind in `RepositoryServiceProvider`
4. Inject interface in Services

---

## 📁 Services/

**Purpose:** Business logic layer - the orchestrator.

**Rules:**

-   NO Request objects - only DTOs/primitives
-   NO HTTP concerns
-   Single Responsibility per service

### Services Folder Structure

```
Services/
├── Dashboard/
│   └── DashboardService.php        # Centralized statistics & chart data
│
├── MasterData/
│   ├── JurusanService.php          # Jurusan CRUD operations
│   └── KelasService.php            # Kelas CRUD operations
│
├── Pelanggaran/
│   ├── PelanggaranService.php      # Riwayat pelanggaran CRUD (Orchestrator)
│   ├── PelanggaranRulesEngine.php  # Frequency rules evaluation & surat trigger
│   ├── PoinCalculationService.php  # Poin calculation (extracted from RulesEngine)
│   ├── PelanggaranPreviewService.php # Preview impact before saving
│   └── FrequencyRuleService.php    # Frequency rule CRUD
│
├── Pembinaan/
│   └── PembinaanService.php        # Pembinaan internal workflow
│
├── Rules/
│   └── RulesEngineSettingsService.php # Rules engine settings management
│
├── Siswa/
│   ├── SiswaService.php            # Core Siswa CRUD
│   ├── SiswaBulkService.php        # Bulk import/delete
│   ├── SiswaArchiveService.php     # Soft-deleted management
│   ├── SiswaTransferService.php    # Kenaikan kelas
│   └── SiswaWaliService.php        # Wali murid management
│
├── TindakLanjut/
│   ├── TindakLanjutService.php          # Tindak lanjut CRUD
│   ├── TindakLanjutNotificationService.php # Notifications (approval, awareness)
│   └── SuratPanggilanService.php        # Surat panggilan data
│
└── User/
    ├── UserService.php             # User CRUD operations
    └── RoleService.php             # Role utilities
```

### Domain Responsibility Map

| Domain           | Service                  | Responsibility               |
| ---------------- | ------------------------ | ---------------------------- |
| **Siswa**        | `SiswaService`           | Core CRUD                    |
| **Siswa**        | `SiswaBulkService`       | Bulk import/delete           |
| **Siswa**        | `SiswaArchiveService`    | Soft-deleted management      |
| **Siswa**        | `SiswaTransferService`   | Kenaikan kelas               |
| **Pelanggaran**  | `PelanggaranService`     | Riwayat CRUD orchestrator    |
| **Pelanggaran**  | `PelanggaranRulesEngine` | Frequency evaluation + surat |
| **Pelanggaran**  | `PoinCalculationService` | Poin calculation             |
| **Pembinaan**    | `PembinaanService`       | Internal pembinaan workflow  |
| **TindakLanjut** | `TindakLanjutService`    | Case management CRUD         |
| **Dashboard**    | `DashboardService`       | Centralized statistics       |

---

## 📁 Traits/

**Purpose:** Reusable PHP traits for shared behavior.

---

## Data Flow

```
Request → Controller → FormRequest → DTO → Service → Repository → Model → Database
```

---

_Last updated: 2026-01-09_
