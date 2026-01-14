# Implementation Plan: Redesign Modul Akademik

**Tanggal**: 2026-01-14
**Objective**: Merestrukturisasi modul akademik (Kurikulum, Mata Pelajaran, Jam Pelajaran, Jadwal Mengajar) agar sinkron, fleksibel, dan mengikuti best practice sistem akademik SMK.

---

## Ringkasan Perubahan

### Arsitektur Baru

```
PERIODE SEMESTER
├── Template Jam Pelajaran (per periode + hari + tipe slot)
└── Kurikulum Assignment per Tingkat
    └── Mata Pelajaran (per kurikulum)

KELAS
├── tingkat (X/XI/XII) → determines kurikulum
└── jurusan/konsentrasi

JADWAL MENGAJAR
├── periode_semester_id (FK)
├── template_jam_id (FK) → instead of jam_mulai/jam_selesai
├── kelas_id → auto-resolve kurikulum
└── mata_pelajaran_id → filtered by kurikulum

PERTEMUAN (unchanged logic)
└── jadwal_mengajar_id

ABSENSI (unchanged)
└── pertemuan_id
```

---

## Phase 1: Database Schema Changes

### 1.1 Create `kurikulum` Table

```sql
-- Tabel master kurikulum
CREATE TABLE kurikulum (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    kode VARCHAR(20) NOT NULL UNIQUE,     -- K13, MERDEKA, MERDEKA_SMK
    nama VARCHAR(100) NOT NULL,            -- Kurikulum 2013, Kurikulum Merdeka
    deskripsi TEXT NULL,
    tahun_berlaku YEAR NULL,               -- 2013, 2022
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);
```

### 1.2 Modify `mata_pelajaran` Table

```sql
-- Add kurikulum_id FK
ALTER TABLE mata_pelajaran 
    ADD COLUMN kurikulum_id BIGINT UNSIGNED NULL AFTER id,
    ADD COLUMN kelompok ENUM('A', 'B', 'C') NULL AFTER kode_mapel,  -- A=Umum, B=Kejuruan, C=Pilihan
    ADD CONSTRAINT fk_mapel_kurikulum FOREIGN KEY (kurikulum_id) REFERENCES kurikulum(id) ON DELETE SET NULL;
```

### 1.3 Create `tingkat_kurikulum` Table (Assignment per Periode)

```sql
-- Menentukan kurikulum apa yang dipakai oleh tingkat tertentu pada periode tertentu
CREATE TABLE tingkat_kurikulum (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    periode_semester_id BIGINT UNSIGNED NOT NULL,
    tingkat ENUM('X', 'XI', 'XII') NOT NULL,
    kurikulum_id BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    FOREIGN KEY (periode_semester_id) REFERENCES periode_semester(id) ON DELETE CASCADE,
    FOREIGN KEY (kurikulum_id) REFERENCES kurikulum(id) ON DELETE CASCADE,
    UNIQUE KEY unique_tingkat_periode (periode_semester_id, tingkat)
);
```

### 1.4 Refactor `jam_pelajaran` → `template_jam`

```sql
-- Drop existing jam_pelajaran (data is dummy)
DROP TABLE jam_pelajaran;

-- Create new template_jam with more flexibility
CREATE TABLE template_jam (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    periode_semester_id BIGINT UNSIGNED NOT NULL,
    hari ENUM('Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu') NOT NULL,
    urutan TINYINT UNSIGNED NOT NULL,       -- 1, 2, 3, 4...
    label VARCHAR(50) NOT NULL,             -- "Jam Ke-1", "Istirahat", "Ishoma"
    jam_mulai TIME NOT NULL,
    jam_selesai TIME NOT NULL,
    tipe ENUM('pelajaran', 'istirahat', 'ishoma', 'upacara', 'lainnya') DEFAULT 'pelajaran',
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    FOREIGN KEY (periode_semester_id) REFERENCES periode_semester(id) ON DELETE CASCADE,
    UNIQUE KEY unique_slot (periode_semester_id, hari, urutan)
);
```

### 1.5 Modify `jadwal_mengajar` Table

```sql
-- Simplify: remove duplicate time fields, reference template_jam instead
ALTER TABLE jadwal_mengajar
    ADD COLUMN periode_semester_id BIGINT UNSIGNED NULL AFTER kelas_id,
    ADD COLUMN template_jam_id BIGINT UNSIGNED NULL AFTER periode_semester_id,
    ADD CONSTRAINT fk_jadwal_periode FOREIGN KEY (periode_semester_id) REFERENCES periode_semester(id) ON DELETE CASCADE,
    ADD CONSTRAINT fk_jadwal_template FOREIGN KEY (template_jam_id) REFERENCES template_jam(id) ON DELETE CASCADE;

-- Migrate existing data if needed (or truncate since dummy)
-- TRUNCATE jadwal_mengajar, pertemuan, absensi;

-- After migration, drop old columns
ALTER TABLE jadwal_mengajar
    DROP COLUMN hari,
    DROP COLUMN jam_mulai,
    DROP COLUMN jam_selesai,
    DROP COLUMN semester,
    DROP COLUMN tahun_ajaran;
```

---

## Phase 2: Models & Migrations

### Files to Create:
- [ ] `database/migrations/xxxx_create_kurikulum_table.php`
- [ ] `database/migrations/xxxx_add_kurikulum_id_to_mata_pelajaran.php`
- [ ] `database/migrations/xxxx_create_tingkat_kurikulum_table.php`
- [ ] `database/migrations/xxxx_refactor_jam_pelajaran_to_template_jam.php`
- [ ] `database/migrations/xxxx_refactor_jadwal_mengajar.php`
- [ ] `app/Models/Kurikulum.php`
- [ ] `app/Models/TingkatKurikulum.php`
- [ ] `app/Models/TemplateJam.php` (rename from JamPelajaran)

### Files to Modify:
- [ ] `app/Models/MataPelajaran.php` - add kurikulum relation
- [ ] `app/Models/JadwalMengajar.php` - change relations, remove hari/jam fields
- [ ] `app/Models/Kelas.php` - add helper to get kurikulum from tingkat

---

## Phase 3: Controllers & Services

### Files to Create:
- [ ] `app/Http/Controllers/Admin/KurikulumController.php`
- [ ] `app/Http/Controllers/Admin/TingkatKurikulumController.php`

### Files to Modify:
- [ ] `app/Http/Controllers/Admin/MataPelajaranController.php` - filter by kurikulum
- [ ] `app/Http/Controllers/Admin/JamPelajaranController.php` → `TemplateJamController.php`
- [ ] `app/Http/Controllers/Admin/JadwalMengajarController.php` - update matrix logic
- [ ] `app/Services/Absensi/JadwalService.php` - update queries

---

## Phase 4: Views & UI

### Files to Create:
- [ ] `resources/views/admin/kurikulum/index.blade.php`
- [ ] `resources/views/admin/kurikulum/create.blade.php`
- [ ] `resources/views/admin/kurikulum/edit.blade.php`

### Files to Modify:
- [ ] `resources/views/admin/periode-semester/index.blade.php` - add tingkat-kurikulum config
- [ ] `resources/views/admin/mata-pelajaran/index.blade.php` - add kurikulum filter
- [ ] `resources/views/admin/mata-pelajaran/create.blade.php` - add kurikulum select
- [ ] `resources/views/admin/jam-pelajaran/index.blade.php` → template-jam with hari tabs
- [ ] `resources/views/admin/jadwal-mengajar/matrix.blade.php` - load template_jam per hari
- [ ] `resources/views/components/sidebar.blade.php` - add Kurikulum menu

---

## Phase 5: Routes

### Files to Modify:
- [ ] `routes/absensi.php` - add kurikulum routes, update jam-pelajaran to template-jam

---

## Phase 6: Seeders & Data Reset

### Files to Create/Modify:
- [ ] `database/seeders/KurikulumSeeder.php`
- [ ] `database/seeders/TemplateJamSeeder.php`
- [ ] `database/seeders/DatabaseSeeder.php` - update order

---

## Execution Checklist

### Pre-Flight
- [x] Audit database schema
- [x] Audit existing models
- [x] Confirm data is safe to reset

### Phase 1: Database (Priority: HIGH) ✅ COMPLETED
- [x] Create migration: kurikulum table
- [x] Create migration: add kurikulum_id to mata_pelajaran
- [x] Create migration: tingkat_kurikulum table
- [x] Create migration: template_jam table (replace jam_pelajaran)
- [x] Create migration: refactor jadwal_mengajar
- [x] Run: `php artisan migrate:fresh --seed` (reset all data)

### Phase 2: Models (Priority: HIGH) ✅ COMPLETED
- [x] Create Kurikulum model
- [x] Create TingkatKurikulum model
- [x] Create TemplateJam model
- [x] Update MataPelajaran model
- [x] Update JadwalMengajar model
- [x] Update Kelas model (add kurikulum helper)
- [x] Delete old JamPelajaran model

### Phase 3: Controllers (Priority: MEDIUM) ✅ COMPLETED
- [x] Create KurikulumController (CRUD)
- [x] Create TemplateJamController (replace JamPelajaranController)
- [x] Update MataPelajaranController
- [x] Update JadwalMengajarController (matrix logic)
- [ ] Update JadwalService (may need update)

### Phase 4: Views (Priority: MEDIUM) ✅ COMPLETED
- [x] Create Kurikulum views (index, create, edit)
- [ ] Update Periode Semester views (tingkat-kurikulum config) - TODO
- [x] Update Mata Pelajaran views
- [x] Create Template Jam views (with hari tabs)
- [x] Update Jadwal Matrix view
- [x] Update Sidebar

### Phase 5: Routes & Cleanup (Priority: LOW) ✅ COMPLETED
- [x] Update routes (absensi.php)
- [x] Create seeders (KurikulumSeeder, KonsentrasiSeeder)
- [x] Remove dead code (JamPelajaranController, JamPelajaran model)
- [ ] Test all flows - IN PROGRESS

---

## Expected UI Flow After Implementation

### 1. Setup Periode Semester
```
Buat Periode: Ganjil 2026/2027
└── Set Kurikulum per Tingkat:
    ├── Tingkat X  → [Dropdown: Kurikulum Merdeka]
    ├── Tingkat XI → [Dropdown: Kurikulum Merdeka]
    └── Tingkat XII → [Dropdown: Kurikulum 2013]
```

### 2. Setup Template Jam
```
Periode: Ganjil 2026/2027
Tabs: [Senin] [Selasa] [Rabu] [Kamis] [Jumat] [Sabtu]

Tab Senin:
┌─────┬──────────┬─────────────┬─────────────┬──────────┐
│ No  │ Label    │ Mulai       │ Selesai     │ Tipe     │
├─────┼──────────┼─────────────┼─────────────┼──────────┤
│ 1   │ Jam Ke-1 │ 07:00       │ 07:45       │ Pelajaran│
│ 2   │ Jam Ke-2 │ 07:45       │ 08:30       │ Pelajaran│
│ ... │ ...      │ ...         │ ...         │ ...      │
│ 5   │ Istirahat│ 09:15       │ 09:30       │ Istirahat│
│ ... │ ...      │ ...         │ ...         │ ...      │
└─────┴──────────┴─────────────┴─────────────┴──────────┘
[+ Tambah Slot]

Tab Jumat:
(Different schedule - 6 slots, includes Ishoma)
```

### 3. Input Jadwal Mengajar (Matrix)
```
Pilih Kelas: [X-A RPL ▾] → Auto: Tingkat X = Kurikulum Merdeka
Pilih Hari:  [Senin ▾]   → Load template jam Senin

┌─────────────┬─────────────────────┬─────────────────────┐
│ Slot        │ Mata Pelajaran      │ Guru                │
├─────────────┼─────────────────────┼─────────────────────┤
│ 07:00-07:45 │ [Dropdown Merdeka▾] │ [Select Guru▾]      │
│ 07:45-08:30 │ Matematika          │ Pak Budi            │
│ 09:15-09:30 │ ── ISTIRAHAT ──     │ (disabled)          │
│ ...         │ ...                 │ ...                 │
└─────────────┴─────────────────────┴─────────────────────┘
```

---

## Notes & Decisions

1. **Tingkat as ENUM**: Using X, XI, XII as ENUM for type safety
2. **Template Jam per Hari**: Allows different schedules per day
3. **Kurikulum Assignment per Periode**: Enables gradual transition between curricula
4. **Backward Compatibility**: Old pertemuan & absensi data will be cleared (dummy data)
5. **Tipe Slot**: pelajaran, istirahat, ishoma, upacara, lainnya - user defines duration

---

## Risks & Mitigations

| Risk | Mitigation |
|------|------------|
| Data loss | Data is dummy, confirmed safe to reset |
| Breaking changes to absensi | Will cascade delete; re-seed after |
| Complex migration | Using fresh migration, not alter |

---

**Ready to Execute**: Menunggu konfirmasi user untuk memulai Phase 1.
