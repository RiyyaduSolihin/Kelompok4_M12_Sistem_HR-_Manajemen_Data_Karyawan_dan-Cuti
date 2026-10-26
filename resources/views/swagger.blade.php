<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Swagger UI - API Dokumentasi Sistem HR (Araya Business Hub)</title>
    <link rel="stylesheet" href="https://unpkg.com/swagger-ui-dist@5.18.2/swagger-ui.css">
    <style>
        body { margin: 0; background: #fafafa; }
        .topbar { background-color: #1F3F7A !important; padding: 12px 24px !important; }
        .topbar-wrapper {
            display: flex;
            align-items: center;
            justify-content: space-between;
            color: #ffffff;
            font-family: sans-serif;
        }
        .topbar-wrapper a.back-btn {
            color: #ffffff;
            text-decoration: none;
            background: rgba(255, 255, 255, 0.15);
            padding: 6px 14px;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 600;
        }
        .topbar-wrapper a.back-btn:hover { background: rgba(255, 255, 255, 0.25); }
    </style>
</head>
<body>
    <div class="topbar">
        <div class="topbar-wrapper">
            <div>
                <strong>Araya Business Hub — Dokumentasi REST API & RBAC Lintas 4 Modul (M12, M13, M14)</strong>
            </div>
            <a href="{{ url('/') }}" class="back-btn">&larr; Kembali ke Dashboard</a>
        </div>
    </div>

    <div id="swagger-ui"></div>

    <script src="https://unpkg.com/swagger-ui-dist@5.18.2/swagger-ui-bundle.js"></script>
    <script>
        const spec = {
            openapi: "3.0.3",
            info: {
                title: "API Sistem HR - Modul M12 (Login, RBAC HR vs Employee, & Kontrak Data)",
                version: "2.0.0",
                description: "Dokumentasi RESTful API terpadu yang menyelaraskan `/api/employees` dan `/api/karyawan` dengan kontrol akses berbasis role (**RBAC Lintas 4 Modul**):\n\n" +
                    "### Akun Uji Coba Default (Database Neon):\n" +
                    "- **Akun HR (`Admin HR`)**: Email `hr@araya.com` | Password `password123` (Bisa akses seluruh karyawan, karyawan aktif, tambah/edit, dan deaktivasi)\n" +
                    "- **Akun Karyawan Biasa (`Employee`)**: Email `andi@araya.com` | Password `password123` (Hanya bisa melihat data kepegawaian miliknya sendiri)\n\n" +
                    "### Cara Autentikasi di Swagger / Modul Lain:\n" +
                    "1. Jalankan **`POST /api/login`**, salin nilai `token`, lalu klik tombol hijau **`Authorize`** di kanan atas dan masukkan token tersebut, **ATAU**\n" +
                    "2. Gunakan Header **`X-User-Role`** (`Admin HR` atau `Employee`) dan **`X-Employee-Id`**."
            },
            servers: [
                { url: window.location.origin, description: "Server Aktif Saat Ini" },
                { url: "https://kelompok4-m12-sistem-hr-manajemen-d.vercel.app", description: "Production Server (Vercel + Neon PostgreSQL)" },
                { url: "http://127.0.0.1:8000", description: "Local Development Server" }
            ],
            security: [
                { BearerAuth: [] }
            ],
            tags: [
                { name: "1. Autentikasi & Perjanjian User (4 Modul)", description: "Endpoint Login, Cek Profil/Role, dan Logout" },
                { name: "2. Karyawan Aktif (Hanya HR)", description: "Endpoint kontrak data karyawan aktif (Diselaraskan: /api/employees/active, /api/employees/aktif, /api/karyawan/aktif)" },
                { name: "3. Data Karyawan / Employees (Identik)", description: "HR dapat mengambil seluruh data karyawan; User biasa hanya dapat mengambil datanya sendiri" },
                { name: "4. Deaktivasi Karyawan (Hanya HR)", description: "Menonaktifkan karyawan dan mencatat tanggal berhenti tanpa hard delete" }
            ],
            paths: {
                "/api/login": {
                    post: {
                        tags: ["1. Autentikasi & Perjanjian User (4 Modul)"],
                        security: [],
                        summary: "Login pengguna (HR / Employee) menggunakan Email atau NIK",
                        requestBody: {
                            required: true,
                            content: {
                                "application/json": {
                                    schema: {
                                        type: "object",
                                        properties: {
                                            email: { type: "string", example: "hr@araya.com" },
                                            password: { type: "string", example: "password123" }
                                        }
                                    }
                                }
                            }
                        },
                        responses: {
                            "200": { description: "Login berhasil dan mengembalikan Bearer token beserta role & employee_id" },
                            "401": { description: "Email/NIK atau password salah" }
                        }
                    }
                },
                "/api/me": {
                    get: {
                        tags: ["1. Autentikasi & Perjanjian User (4 Modul)"],
                        summary: "Melihat profil user, role, dan data karyawan yang sedang login",
                        responses: {
                            "200": { description: "Profil pengguna berhasil diambil" },
                            "401": { description: "Unauthenticated" }
                        }
                    }
                },
                "/api/employees/active": {
                    get: {
                        tags: ["2. Karyawan Aktif (Hanya HR)"],
                        summary: "Mengambil daftar seluruh karyawan aktif — HANYA HR",
                        description: "Alias identik yang juga tersedia:\n- `GET /api/employees/aktif`\n- `GET /api/karyawan/aktif`\n- `GET /api/karyawan/active`\n- `GET /api/employees?status_kerja=aktif`",
                        responses: {
                            "200": { description: "Berhasil mengambil daftar karyawan aktif (Khusus HR)" },
                            "403": { description: "Akses ditolak jika diakses oleh role Employee biasa" }
                        }
                    }
                },
                "/api/karyawan/aktif": {
                    get: {
                        tags: ["2. Karyawan Aktif (Hanya HR)"],
                        summary: "Mengambil daftar seluruh karyawan aktif (Alias Kontrak M13 & M14) — HANYA HR",
                        responses: {
                            "200": { description: "Berhasil mengambil daftar karyawan aktif (Khusus HR)" },
                            "403": { description: "Akses ditolak jika diakses oleh role Employee biasa" }
                        }
                    }
                },
                "/api/employees": {
                    get: {
                        tags: ["3. Data Karyawan / Employees (Identik)"],
                        summary: "Mengambil daftar karyawan (HR: seluruh karyawan | Employee: hanya data diri sendiri)",
                        description: "Identik dengan `GET /api/karyawan`.",
                        parameters: [
                            {
                                name: "status_kerja",
                                in: "query",
                                required: false,
                                description: "Filter status (`aktif` / `active` atau `nonaktif` — hanya untuk HR)",
                                schema: { type: "string", enum: ["aktif", "nonaktif"] }
                            }
                        ],
                        responses: {
                            "200": { description: "Berhasil mengambil data karyawan sesuai hak akses role" },
                            "401": { description: "Belum login" },
                            "403": { description: "User biasa mencoba memfilter seluruh karyawan aktif" }
                        }
                    },
                    post: {
                        tags: ["3. Data Karyawan / Employees (Identik)"],
                        summary: "Menambahkan karyawan baru — HANYA HR",
                        description: "Identik dengan `POST /api/karyawan`. Mendukung field Indonesia (`nama`, `jabatan`, `divisi`) maupun Inggris (`name`, `position`, `department`).",
                        requestBody: {
                            required: true,
                            content: {
                                "application/json": {
                                    schema: { $ref: "#/components/schemas/EmployeeInput" }
                                }
                            }
                        },
                        responses: {
                            "201": { description: "Karyawan berhasil disimpan" },
                            "403": { description: "Akses ditolak (Bukan HR)" },
                            "422": { description: "Validasi gagal / NIK duplikat" }
                        }
                    }
                },
                "/api/employees/{id}": {
                    get: {
                        tags: ["3. Data Karyawan / Employees (Identik)"],
                        summary: "Detail 1 karyawan (HR: bebas | Employee: hanya ID miliknya sendiri)",
                        description: "Identik dengan `GET /api/karyawan/{id}`.",
                        parameters: [
                            { name: "id", in: "path", required: true, schema: { type: "integer", example: 1 } }
                        ],
                        responses: {
                            "200": { description: "Detail karyawan berhasil diambil" },
                            "403": { description: "User biasa mencoba melihat data karyawan lain" },
                            "404": { description: "Data karyawan tidak ditemukan" }
                        }
                    },
                    put: {
                        tags: ["3. Data Karyawan / Employees (Identik)"],
                        summary: "Memperbarui data karyawan — HANYA HR",
                        description: "Identik dengan `PUT /api/karyawan/{id}`.",
                        parameters: [
                            { name: "id", in: "path", required: true, schema: { type: "integer", example: 1 } }
                        ],
                        requestBody: {
                            required: true,
                            content: {
                                "application/json": {
                                    schema: { $ref: "#/components/schemas/EmployeeInput" }
                                }
                            }
                        },
                        responses: {
                            "200": { description: "Data karyawan berhasil diperbarui" },
                            "403": { description: "Akses ditolak (Bukan HR)" }
                        }
                    }
                },
                "/api/employees/{id}/deactivate": {
                    patch: {
                        tags: ["4. Deaktivasi Karyawan (Hanya HR)"],
                        summary: "Menonaktifkan karyawan & mencatat tanggal berhenti — HANYA HR",
                        description: "Identik dengan `PATCH /api/karyawan/{id}/deactivate`.",
                        parameters: [
                            { name: "id", in: "path", required: true, schema: { type: "integer", example: 2 } }
                        ],
                        requestBody: {
                            required: true,
                            content: {
                                "application/json": {
                                    schema: {
                                        type: "object",
                                        required: ["tanggal_berhenti"],
                                        properties: {
                                            tanggal_berhenti: { type: "string", format: "date", example: "2026-10-26" }
                                        }
                                    }
                                }
                            }
                        },
                        responses: {
                            "200": { description: "Karyawan berhasil dinonaktifkan" },
                            "403": { description: "Akses ditolak: Hanya HR yang boleh menonaktifkan karyawan" }
                        }
                    }
                }
            },
            components: {
                securitySchemes: {
                    BearerAuth: {
                        type: "http",
                        scheme: "bearer",
                        description: "Masukkan token dari hasil POST /api/login"
                    }
                },
                schemas: {
                    EmployeeInput: {
                        type: "object",
                        required: ["nik", "nama", "jabatan", "divisi", "status_kerja"],
                        properties: {
                            nik: { type: "string", example: "2026005" },
                            nama: { type: "string", example: "Siti Aminah" },
                            jabatan: { type: "string", example: "Analis SDM" },
                            divisi: { type: "string", example: "HRD" },
                            status_kerja: { type: "string", enum: ["aktif", "nonaktif"], example: "aktif" }
                        }
                    }
                }
            }
        };

        window.onload = function() {
            SwaggerUIBundle({
                spec: spec,
                dom_id: '#swagger-ui',
                deepLinking: true,
                presets: [SwaggerUIBundle.presets.apis],
                layout: "BaseLayout"
            });
        };
    </script>
</body>
</html>
