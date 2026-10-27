<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    /**
     * Membuat signed Bearer token yang bisa diverifikasi lintas modul & di Vercel Serverless
     */
    public static function generateToken(User $user): string
    {
        $payload = base64_encode(json_encode([
            'user_id' => $user->id,
            'email' => $user->email,
            'role' => $user->role,
            'employee_id' => $user->employee_id,
            'iat' => time(),
        ]));

        $signature = hash_hmac('sha256', $payload, config('app.key', 'sistemhr-secret-key'));

        return $payload.'.'.$signature;
    }

    /**
     * Memverifikasi request (dari Bearer Token, Header X-User-Role / X-Employee-Id, atau Query/Body)
     *
     * @return array{authenticated: bool, is_hr: bool, role: ?string, user_id: ?int, employee_id: ?int, user: ?User}
     */
    public static function resolveAuth(Request $request): array
    {
        $bearer = $request->bearerToken() ?: $request->input('token');

        if ($bearer) {
            // 1. Cek jika token berupa signed token (payload.signature)
            if (str_contains($bearer, '.')) {
                [$payload, $sig] = explode('.', $bearer, 2);
                $expectedSig = hash_hmac('sha256', $payload, config('app.key', 'sistemhr-secret-key'));

                if (hash_equals($expectedSig, $sig)) {
                    $data = json_decode((string) base64_decode($payload, true), true);
                    if (is_array($data) && isset($data['user_id'])) {
                        $user = User::with('employee')->find($data['user_id']);
                        $role = $user->role ?? ($data['role'] ?? 'Employee');
                        $empId = $user->employee_id ?? ($data['employee_id'] ?? null);

                        return [
                            'authenticated' => true,
                            'is_hr' => self::isHrRole($role),
                            'role' => $role,
                            'user_id' => (int) ($user->id ?? $data['user_id']),
                            'employee_id' => $empId ? (int) $empId : null,
                            'user' => $user,
                        ];
                    }
                }
            }

            // 2. Cek jika token disimpan langsung di kolom users.api_token
            $user = User::with('employee')->where('api_token', $bearer)->first();
            if ($user) {
                return [
                    'authenticated' => true,
                    'is_hr' => $user->isHr(),
                    'role' => $user->role,
                    'user_id' => $user->id,
                    'employee_id' => $user->employee_id ? (int) $user->employee_id : null,
                    'user' => $user,
                ];
            }
        }

        // 3. Fallback Perjanjian Antar-Modul (Header X-User-Role / X-Role & X-Employee-Id / X-User-Id)
        $headerRole = $request->header('X-User-Role')
            ?? $request->header('X-Role')
            ?? $request->input('user_role');

        $headerUserId = $request->header('X-User-Id') ?? $request->input('user_id');
        $headerEmpId = $request->header('X-Employee-Id') ?? $request->input('employee_id');

        if ($headerUserId && ! $headerRole) {
            $user = User::with('employee')->find($headerUserId);
            if ($user) {
                return [
                    'authenticated' => true,
                    'is_hr' => $user->isHr(),
                    'role' => $user->role,
                    'user_id' => $user->id,
                    'employee_id' => $user->employee_id ? (int) $user->employee_id : null,
                    'user' => $user,
                ];
            }
        }

        if ($headerRole) {
            $user = $headerUserId ? User::with('employee')->find($headerUserId) : null;
            $empId = $headerEmpId ?? ($user->employee_id ?? null);

            return [
                'authenticated' => true,
                'is_hr' => self::isHrRole($headerRole),
                'role' => $headerRole,
                'user_id' => $headerUserId ? (int) $headerUserId : null,
                'employee_id' => $empId ? (int) $empId : null,
                'user' => $user,
            ];
        }

        return [
            'authenticated' => false,
            'is_hr' => false,
            'role' => null,
            'user_id' => null,
            'employee_id' => null,
            'user' => null,
        ];
    }

    /**
     * Normalisasi pengecekan Role HR lintas 4 modul
     * Mendukung: 'Admin HR', 'HR', 'HRD', 'Admin', 'Superadmin' (case-insensitive)
     */
    public static function isHrRole(?string $role): bool
    {
        if (! $role) {
            return false;
        }

        $normalized = strtolower(trim($role));

        return in_array($normalized, ['admin hr', 'hr', 'hrd', 'admin', 'superadmin'], true);
    }

    /**
     * POST /api/login (atau POST /api/auth/login)
     * Endpoint Login Kesepakatan 4 Modul (M12, M13, M14, dll.)
     */
    public function login(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required_without:nik|string',
            'nik' => 'required_without:email|string',
            'password' => 'required|string',
        ], [
            'email.required_without' => 'Email atau NIK wajib diisi.',
            'nik.required_without' => 'NIK atau Email wajib diisi.',
            'password.required' => 'Password wajib diisi.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi login gagal.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = null;

        if ($request->filled('email')) {
            $user = User::with('employee')->where('email', $request->email)->first();
        } elseif ($request->filled('nik')) {
            $employee = Employee::where('nik', $request->nik)->first();
            if ($employee) {
                $user = User::with('employee')->where('employee_id', $employee->id)->first();
            }
        }

        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'Akun pengguna tidak ditemukan.',
            ], 401);
        }

        // Cek password (mendukung Hash Bcrypt Laravel maupun plain-text jika di-insert modul lain)
        $passwordValid = false;
        if ($user->password) {
            $passwordValid = Hash::check($request->password, $user->password)
                || $request->password === $user->password;
        }

        if (! $passwordValid) {
            return response()->json([
                'success' => false,
                'message' => 'Email/NIK atau password salah.',
            ], 401);
        }

        // Jika employee_id belum terhubung, coba hubungkan otomatis berdasarkan nama
        if (! $user->employee_id) {
            $matchedEmployee = Employee::where('nama', $user->name)
                ->orWhere('name', $user->name)
                ->first();
            if ($matchedEmployee) {
                $user->employee_id = $matchedEmployee->id;
            }
        }

        $token = self::generateToken($user);
        $user->api_token = $token;
        $user->save();
        $user->load('employee');

        $emp = $user->employee;

        return response()->json([
            'success' => true,
            'message' => 'Login berhasil.',
            // Top-level fields untuk kompatibilitas langsung M13-FE
            'token' => $token,
            'role' => $user->role,
            'employee_id' => $user->employee_id,
            'name' => $emp->nama ?? $user->name,
            'nik' => $emp->nik ?? null,
            'data' => [
                'token' => $token,
                'token_type' => 'Bearer',
                'role' => $user->role,
                'employee_id' => $user->employee_id,
                'name' => $emp->nama ?? $user->name,
                'nik' => $emp->nik ?? null,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                    'is_hr' => $user->isHr(),
                    'employee_id' => $user->employee_id,
                    'employee' => $emp,
                ],
            ],
        ], 200);
    }

    /**
     * GET /api/me (atau GET /api/auth/me)
     * Mengecek profil user & role yang sedang login
     */
    public function me(Request $request): JsonResponse
    {
        $auth = self::resolveAuth($request);

        if (! $auth['authenticated']) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated. Sertakan header Authorization: Bearer <token>.',
            ], 401);
        }

        $emp = $auth['user']->employee ?? ($auth['employee_id'] ? Employee::find($auth['employee_id']) : null);
        $empId = $emp->id ?? $auth['employee_id'];

        $profile = [
            'id' => $empId ? (int) $empId : (int) $auth['user_id'],
            'employee_id' => $empId ? (int) $empId : null,
            'user_id' => $auth['user_id'],
            'nama' => $emp->nama ?? ($auth['user']->name ?? null),
            'name' => $emp->nama ?? ($auth['user']->name ?? null),
            'email' => $auth['user']->email ?? null,
            'jabatan' => $emp->jabatan ?? null,
            'divisi' => $emp->divisi ?? null,
            'nik' => $emp->nik ?? null,
            'status_kerja' => $emp->status_kerja ?? 'aktif',
            'role' => $auth['role'],
            'is_hr' => $auth['is_hr'],
            'employee' => $emp,
        ];

        return response()->json(array_merge([
            'success' => true,
            'message' => 'Profil pengguna berhasil diambil.',
        ], $profile, [
            'data' => $profile,
        ]), 200);
    }

    /**
     * POST /api/logout
     */
    public function logout(Request $request): JsonResponse
    {
        $auth = self::resolveAuth($request);

        if ($auth['user']) {
            $auth['user']->api_token = null;
            $auth['user']->save();
        }

        return response()->json([
            'success' => true,
            'message' => 'Logout berhasil.',
        ], 200);
    }

    /**
     * GET /api/users
     * Menampilkan daftar user beserta role & employee_id (Hanya HR)
     */
    public function users(Request $request): JsonResponse
    {
        $auth = self::resolveAuth($request);

        if (! $auth['authenticated']) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated. Silakan login terlebih dahulu.',
            ], 401);
        }

        if (! $auth['is_hr']) {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak: Hanya HR yang dapat melihat seluruh akun pengguna.',
            ], 403);
        }

        $users = User::with('employee')->orderBy('id', 'asc')->get();

        return response()->json([
            'success' => true,
            'message' => 'Daftar akun pengguna berhasil diambil.',
            'data' => $users,
        ], 200);
    }
}
