<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Swagger UI - API Dokumentasi M12 Sistem HR (Araya Business Hub)</title>
    <link rel="stylesheet" href="https://unpkg.com/swagger-ui-dist@5.18.2/swagger-ui.css">
    <style>
        body {
            margin: 0;
            background: #fafafa;
        }
        .topbar {
            background-color: #1F3F7A !important;
            padding: 12px 24px !important;
        }
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
        .topbar-wrapper a.back-btn:hover {
            background: rgba(255, 255, 255, 0.25);
        }
    </style>
</head>
<body>
    <div class="topbar">
        <div class="topbar-wrapper">
            <div>
                <strong>Araya Business Hub — Dokumentasi REST API Modul M12 (Manajemen Data Karyawan)</strong>
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
                title: "API Sistem HR - Modul M12 (Manajemen Data Karyawan)",
                version: "1.0.0",
                description: "Dokumentasi RESTful API untuk **Modul M12 (Manajemen Data Karyawan)** serta Endpoint Kontrak Data untuk **Modul M13 (Manajemen Cuti)** dan **Modul M14 (Pengelolaan Komponen Gaji)**.\n\nSeluruh endpoint di bawah ini terhubung langsung ke database **PostgreSQL (Neon Console)** dan dapat diuji secara interaktif melalui tombol **Try it out**."
            },
            servers: [
                {
                    url: window.location.origin,
                    description: "Server Aktif Saat Ini"
                },
                {
                    url: "https://kelompok4-m12-sistem-hr-manajemen-d.vercel.app",
                    description: "Production Server (Vercel + Neon PostgreSQL)"
                },
                {
                    url: "http://127.0.0.1:8000",
                    description: "Local Development Server"
                }
            ],
            tags: [
                {
                    name: "Kontrak Data (M13 & M14)",
                    description: "Endpoint penyedia daftar karyawan aktif untuk konsumsi Modul M13 dan M14"
                },
                {
                    name: "CRUD Karyawan (M12-US1)",
                    description: "Pengelolaan data master karyawan & validasi duplikasi NIK"
                },
                {
                    name: "Deaktivasi Karyawan (M12-US2)",
                    description: "Penonaktifan status kerja karyawan & pencatatan tanggal berhenti tanpa hard delete"
                }
            ],
            paths: {
                "/api/karyawan/aktif": {
                    get: {
                        tags: ["Kontrak Data (M13 & M14)"],
                        summary: "Mengambil daftar karyawan yang berstatus aktif",
                        description: "Digunakan oleh Modul M13 & M14 untuk mendapatkan daftar karyawan yang masih aktif bekerja.",
                        responses: {
                            "200": {
                                description: "Berhasil mengambil daftar karyawan aktif",
                                content: {
                                    "application/json": {
                                        schema: {
                                            type: "object",
                                            properties: {
                                                success: { type: "boolean", example: true },
                                                message: { type: "string", example: "Berhasil mengambil daftar karyawan aktif." },
                                                data: {
                                                    type: "array",
                                                    items: { $ref: "#/components/schemas/EmployeeActive" }
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                },
                "/api/employees": {
                    get: {
                        tags: ["CRUD Karyawan (M12-US1)"],
                        summary: "Menampilkan daftar seluruh karyawan",
                        description: "Mengambil semua data karyawan (aktif maupun nonaktif). Dapat difilter menggunakan query parameter `status_kerja`.",
                        parameters: [
                            {
                                name: "status_kerja",
                                in: "query",
                                required: false,
                                description: "Filter berdasarkan status kerja (`aktif` atau `nonaktif`)",
                                schema: {
                                    type: "string",
                                    enum: ["aktif", "nonaktif"]
                                }
                            }
                        ],
                        responses: {
                            "200": {
                                description: "Daftar data karyawan berhasil diambil",
                                content: {
                                    "application/json": {
                                        schema: {
                                            type: "object",
                                            properties: {
                                                success: { type: "boolean", example: true },
                                                message: { type: "string", example: "Daftar data karyawan berhasil diambil." },
                                                data: {
                                                    type: "array",
                                                    items: { $ref: "#/components/schemas/Employee" }
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    },
                    post: {
                        tags: ["CRUD Karyawan (M12-US1)"],
                        summary: "Menyimpan data karyawan baru (M12-US1: AC 1 & AC 2)",
                        description: "Menambahkan karyawan baru dengan validasi NIK unik (menolak duplikasi NIK).",
                        requestBody: {
                            required: true,
                            content: {
                                "application/json": {
                                    schema: { $ref: "#/components/schemas/EmployeeInput" }
                                }
                            }
                        },
                        responses: {
                            "201": {
                                description: "Informasi kepegawaian berhasil disimpan",
                                content: {
                                    "application/json": {
                                        schema: {
                                            type: "object",
                                            properties: {
                                                success: { type: "boolean", example: true },
                                                message: { type: "string", example: "Informasi kepegawaian berhasil disimpan." },
                                                data: { $ref: "#/components/schemas/Employee" }
                                            }
                                        }
                                    }
                                }
                            },
                            "422": {
                                description: "Validasi gagal (input kosong atau NIK duplikat)",
                                content: {
                                    "application/json": {
                                        schema: { $ref: "#/components/schemas/ValidationError" }
                                    }
                                }
                            }
                        }
                    }
                },
                "/api/employees/{id}": {
                    get: {
                        tags: ["CRUD Karyawan (M12-US1)"],
                        summary: "Menampilkan detail 1 karyawan berdasarkan ID",
                        parameters: [
                            {
                                name: "id",
                                in: "path",
                                required: true,
                                description: "ID unik karyawan",
                                schema: { type: "integer", example: 1 }
                            }
                        ],
                        responses: {
                            "200": {
                                description: "Detail karyawan berhasil diambil",
                                content: {
                                    "application/json": {
                                        schema: {
                                            type: "object",
                                            properties: {
                                                success: { type: "boolean", example: true },
                                                message: { type: "string", example: "Detail karyawan berhasil diambil." },
                                                data: { $ref: "#/components/schemas/Employee" }
                                            }
                                        }
                                    }
                                }
                            },
                            "404": {
                                description: "Data karyawan tidak ditemukan"
                            }
                        }
                    },
                    put: {
                        tags: ["CRUD Karyawan (M12-US1)"],
                        summary: "Memperbarui data karyawan berdasarkan ID",
                        parameters: [
                            {
                                name: "id",
                                in: "path",
                                required: true,
                                description: "ID unik karyawan",
                                schema: { type: "integer", example: 1 }
                            }
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
                            "200": {
                                description: "Data karyawan berhasil diperbarui",
                                content: {
                                    "application/json": {
                                        schema: {
                                            type: "object",
                                            properties: {
                                                success: { type: "boolean", example: true },
                                                message: { type: "string", example: "Data karyawan berhasil diperbarui." },
                                                data: { $ref: "#/components/schemas/Employee" }
                                            }
                                        }
                                    }
                                }
                            },
                            "404": {
                                description: "Data karyawan tidak ditemukan"
                            },
                            "422": {
                                description: "Validasi gagal (NIK sudah digunakan karyawan lain)",
                                content: {
                                    "application/json": {
                                        schema: { $ref: "#/components/schemas/ValidationError" }
                                    }
                                }
                            }
                        }
                    }
                },
                "/api/karyawan/{id}/deactivate": {
                    patch: {
                        tags: ["Deaktivasi Karyawan (M12-US2)"],
                        summary: "Menonaktifkan karyawan dan mencatat tanggal berhenti (M12-US2)",
                        description: "Mengubah `status_kerja` menjadi `nonaktif` dan menyimpan `tanggal_berhenti` tanpa menghapus riwayat data karyawan (tanpa hard delete).",
                        parameters: [
                            {
                                name: "id",
                                in: "path",
                                required: true,
                                description: "ID unik karyawan yang akan dinonaktifkan",
                                schema: { type: "integer", example: 1 }
                            }
                        ],
                        requestBody: {
                            required: true,
                            content: {
                                "application/json": {
                                    schema: {
                                        type: "object",
                                        required: ["tanggal_berhenti"],
                                        properties: {
                                            tanggal_berhenti: {
                                                type: "string",
                                                format: "date",
                                                example: "2026-10-26"
                                            }
                                        }
                                    }
                                }
                            }
                        },
                        responses: {
                            "200": {
                                description: "Karyawan berhasil dinonaktifkan dan riwayat tetap tersimpan",
                                content: {
                                    "application/json": {
                                        schema: {
                                            type: "object",
                                            properties: {
                                                success: { type: "boolean", example: true },
                                                message: { type: "string", example: "Karyawan berhasil dinonaktifkan dan riwayat kepegawaian tetap tersimpan." },
                                                data: { $ref: "#/components/schemas/Employee" }
                                            }
                                        }
                                    }
                                }
                            },
                            "422": {
                                description: "Tanggal berhenti kosong atau format tidak valid"
                            }
                        }
                    }
                }
            },
            components: {
                schemas: {
                    Employee: {
                        type: "object",
                        properties: {
                            id: { type: "integer", example: 1 },
                            nik: { type: "string", example: "EMP-2026-001" },
                            nama: { type: "string", example: "Budi Santoso" },
                            jabatan: { type: "string", example: "Staff HR" },
                            divisi: { type: "string", example: "HRD" },
                            status_kerja: { type: "string", enum: ["aktif", "nonaktif"], example: "aktif" },
                            tanggal_berhenti: { type: "string", format: "date", nullable: true, example: null },
                            created_at: { type: "string", format: "date-time", example: "2026-10-25T08:00:00.000000Z" },
                            updated_at: { type: "string", format: "date-time", example: "2026-10-25T08:00:00.000000Z" }
                        }
                    },
                    EmployeeActive: {
                        type: "object",
                        properties: {
                            id: { type: "integer", example: 1 },
                            nik: { type: "string", example: "EMP-2026-001" },
                            nama: { type: "string", example: "Budi Santoso" },
                            jabatan: { type: "string", example: "Staff HR" },
                            divisi: { type: "string", example: "HRD" },
                            status_kerja: { type: "string", example: "aktif" }
                        }
                    },
                    EmployeeInput: {
                        type: "object",
                        required: ["nik", "nama", "jabatan", "divisi", "status_kerja"],
                        properties: {
                            nik: { type: "string", maxLength: 30, example: "EMP-2026-001" },
                            nama: { type: "string", maxLength: 100, example: "Budi Santoso" },
                            jabatan: { type: "string", maxLength: 50, example: "Staff HR" },
                            divisi: { type: "string", maxLength: 50, example: "HRD" },
                            status_kerja: { type: "string", enum: ["aktif", "nonaktif"], example: "aktif" }
                        }
                    },
                    ValidationError: {
                        type: "object",
                        properties: {
                            success: { type: "boolean", example: false },
                            message: { type: "string", example: "Gagal menyimpan data karyawan. Periksa kembali input Anda." },
                            errors: {
                                type: "object",
                                properties: {
                                    nik: {
                                        type: "array",
                                        items: { type: "string" },
                                        example: ["Sistem menolak duplikasi: NIK sudah terdaftar."]
                                    }
                                }
                            }
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
                presets: [
                    SwaggerUIBundle.presets.apis
                ],
                layout: "BaseLayout"
            });
        };
    </script>
</body>
</html>

