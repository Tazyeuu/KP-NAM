<p align="center">
  <img src="https://img.shields.io/badge/Flutter-3.x-02569B?logo=flutter&logoColor=white" alt="Flutter" />
  <img src="https://img.shields.io/badge/Dart-3.11+-0175C2?logo=dart&logoColor=white" alt="Dart" />
  <img src="https://img.shields.io/badge/Firebase-FCM-FFCA28?logo=firebase&logoColor=black" alt="Firebase" />
  <img src="https://img.shields.io/badge/Architecture-Clean-brightgreen" alt="Clean Architecture" />
  <img src="https://img.shields.io/badge/License-Private-red" alt="License" />
</p>

# 🏥 Helpdesk Teknisi — RSUD dr. Soedarso

> **Aplikasi mobile helpdesk untuk teknisi RSUD dr. Soedarso Pontianak.**  
> Dibangun sebagai bagian dari proyek Kerja Praktik (KP) di RSUD dr. Soedarso Pontianak.

Aplikasi ini memungkinkan teknisi rumah sakit untuk menerima, mengelola, dan menyelesaikan tiket pekerjaan langsung dari perangkat mobile mereka — lengkap dengan **GPS check-in**, **push notification real-time**, dan **pelaporan tugas** yang terintegrasi.

---

## 📋 Daftar Isi

- [Fitur Utama](#-fitur-utama)
- [Arsitektur](#-arsitektur)
- [Tech Stack](#-tech-stack)
- [Struktur Project](#-struktur-project)
- [Prasyarat](#-prasyarat)
- [Instalasi & Setup](#-instalasi--setup)
- [Konfigurasi Environment](#-konfigurasi-environment)
- [Menjalankan Aplikasi](#-menjalankan-aplikasi)
- [Tim Pengembang](#-tim-pengembang)

---

## ✨ Fitur Utama

| Fitur                     | Deskripsi                                                                  |
| ------------------------- | -------------------------------------------------------------------------- |
| 🔐 **Autentikasi**        | Login aman dengan token-based authentication & secure storage              |
| 📋 **Manajemen Tugas**    | Daftar tugas real-time dengan filter status (Ditugaskan, Diproses)         |
| 📍 **GPS Check-in**       | Verifikasi lokasi teknisi sebelum memulai pekerjaan menggunakan Geolocator |
| 📝 **Pelaporan Tugas**    | Submit laporan penyelesaian tugas langsung dari aplikasi                   |
| 📜 **Riwayat Tugas**      | Akses riwayat tugas yang telah diselesaikan beserta log aktivitas          |
| 🔔 **Push Notification**  | Notifikasi real-time via Firebase Cloud Messaging (FCM)                    |
| 👤 **Profil Teknisi**     | Halaman profil dengan pengaturan preferensi notifikasi                     |
| 🌐 **Connectivity Aware** | Deteksi koneksi internet otomatis dengan UI fallback                       |
| 🔒 **Secure Storage**     | Penyimpanan kredensial terenkripsi menggunakan EncryptedSharedPreferences  |

---

## 🏗 Arsitektur

Aplikasi ini menggunakan **Clean Architecture** dengan pemisahan layer yang jelas:

```
┌──────────────────────────────────────────────────┐
│                 PRESENTATION                      │
│         (Pages, Providers, Widgets)               │
├──────────────────────────────────────────────────┤
│                    DOMAIN                         │
│           (Models, Repositories)                  │
├──────────────────────────────────────────────────┤
│                     DATA                          │
│        (Datasources, Repositories Impl)           │
├──────────────────────────────────────────────────┤
│                     CORE                          │
│  (Network, Storage, Services, Constants, Widgets) │
└──────────────────────────────────────────────────┘
```

### Prinsip yang Diterapkan

- ✅ **Clean Architecture** — Separation of Concerns antar layer
- ✅ **SOLID Principles** — Single Responsibility, Dependency Inversion
- ✅ **No Hardcode** — Semua konfigurasi melalui environment variable (`.env`)
- ✅ **Secure by Design** — Token disimpan di Encrypted Storage, bukan SharedPreferences biasa
- ✅ **State Management** — Provider pattern untuk reactive UI

---

## 🛠 Tech Stack

| Kategori              | Teknologi                                           |
| --------------------- | --------------------------------------------------- |
| **Framework**         | Flutter 3.x (Dart ≥ 3.11)                           |
| **State Management**  | Provider                                            |
| **Networking**        | http                                                |
| **Push Notification** | Firebase Cloud Messaging (FCM)                      |
| **Secure Storage**    | flutter_secure_storage (EncryptedSharedPreferences) |
| **Geolocation**       | Geolocator                                          |
| **Connectivity**      | connectivity_plus                                   |
| **Environment**       | flutter_dotenv                                      |
| **Design System**     | Material Design 3                                   |

---

## 📁 Struktur Project

```
mobile-apps/
├── lib/
│   ├── main.dart                          # Entry point aplikasi
│   ├── firebase_options.dart              # Firebase config (auto-generated)
│   │
│   ├── core/                              # Shared / Cross-cutting concerns
│   │   ├── constants/
│   │   │   └── app_constants.dart         # Base URL, API endpoints
│   │   ├── network/
│   │   │   └── api_client.dart            # HTTP client wrapper + interceptor
│   │   ├── services/
│   │   │   ├── connectivity_service.dart  # Cek koneksi internet
│   │   │   ├── fcm_service.dart           # Firebase push notification handler
│   │   │   └── session_service.dart       # Session management
│   │   ├── storage/
│   │   │   └── secure_storage.dart        # Encrypted local storage
│   │   └── widgets/
│   │       ├── connectivity_wrapper.dart  # Widget pembungkus koneksi
│   │       └── no_internet_widget.dart    # UI saat offline
│   │
│   └── features/                          # Feature-based modules
│       ├── auth/                          # 🔐 Autentikasi
│       │   ├── data/
│       │   │   ├── datasource/            # API calls untuk auth
│       │   │   └── repository/            # Implementasi repository
│       │   ├── domain/
│       │   │   ├── model/
│       │   │   │   └── user_model.dart    # Model user
│       │   │   └── repository/            # Abstract repository
│       │   └── presentation/
│       │       ├── auth_provider.dart      # State management auth
│       │       ├── login_page.dart         # Halaman login
│       │       ├── splash_page.dart        # Splash screen + auto-login
│       │       └── notification_landing_page.dart  # Deep-link dari notifikasi
│       │
│       ├── task/                           # 📋 Manajemen Tugas
│       │   ├── data/
│       │   │   ├── datasource/            # API calls untuk task
│       │   │   └── repository/            # Implementasi repository
│       │   ├── domain/
│       │   │   ├── model/
│       │   │   │   └── task_model.dart     # Model task & ticket log
│       │   │   └── repository/            # Abstract repository
│       │   └── presentation/
│       │       ├── task_provider.dart      # State management task
│       │       ├── task_list_page.dart     # Daftar tugas + filter
│       │       ├── checkin_page.dart       # GPS check-in & mulai tugas
│       │       ├── task_report_page.dart   # Form laporan penyelesaian
│       │       └── task_history_page.dart  # Riwayat tugas selesai
│       │
│       └── profile/                       # 👤 Profil Teknisi
│           └── presentation/
│               └── profile_page.dart      # Profil & pengaturan notifikasi
│
├── assets/
│   └── icon/                              # App icon assets
├── android/                               # Android native config
├── ios/                                   # iOS native config
├── pubspec.yaml                           # Dependencies & project config
├── firebase.json                          # Firebase project config
└── .env                                   # Environment variables (git-ignored)
```

---

## 📌 Prasyarat

Pastikan tools berikut sudah terinstal:

- [Flutter SDK](https://docs.flutter.dev/get-started/install) ≥ 3.x (Dart ≥ 3.11)
- [Android Studio](https://developer.android.com/studio) atau [VS Code](https://code.visualstudio.com/) dengan Flutter extension
- [Git](https://git-scm.com/)
- Device Android / Emulator (API Level ≥ 21)
- Akses ke **Backend API** Helpdesk yang sudah berjalan

---

## ⚙ Instalasi & Setup

```bash
# 1. Clone repository
git clone https://github.com/<username>/KP-NAM.git
cd KP-NAM/mobile-apps

# 2. Install dependencies
flutter pub get

# 3. Buat file environment
cp .env.example .env
# → Edit .env dan isi API_BASE_URL sesuai backend

# 4. Setup Firebase credentials
cp lib/firebase_options.dart.example lib/firebase_options.dart
cp android/app/google-services.json.example android/app/google-services.json
# → Isi kedua file dengan kredensial dari Firebase Console
# → Atau jalankan: flutterfire configure
```

> [!IMPORTANT]
> File `.env`, `firebase_options.dart`, dan `google-services.json` **tidak di-track oleh Git** demi keamanan. Anda harus membuat file-file ini secara manual dari template `.example` yang tersedia.

---

## 🔧 Konfigurasi Environment

Buat file `.env` di root folder `mobile-apps/`:

```env
API_BASE_URL=http://<backend-ip>:<port>/api
```

| Variable       | Deskripsi            | Contoh                           |
| -------------- | -------------------- | -------------------------------- |
| `API_BASE_URL` | Base URL backend API | `http://10.220.108.112:8000/api` |

### Firebase Credentials

| File                    | Lokasi         | Cara Mendapatkan                                                    |
| ----------------------- | -------------- | ------------------------------------------------------------------- |
| `firebase_options.dart` | `lib/`         | Jalankan `flutterfire configure` atau isi manual dari template      |
| `google-services.json`  | `android/app/` | Download dari **Firebase Console** → Project Settings → Android app |

> ⚠️ **Penting:** File `.env`, `firebase_options.dart`, dan `google-services.json` **tidak boleh di-commit** ke repository.

---

## 🚀 Menjalankan Aplikasi

```bash
# Development mode
flutter run

# Build APK (release)
flutter build apk --release

# Build App Bundle (untuk Play Store)
flutter build appbundle --release
```

---

## 🔗 API Endpoints

Aplikasi ini berkomunikasi dengan backend melalui endpoint berikut:

| Method | Endpoint                   | Deskripsi                                |
| ------ | -------------------------- | ---------------------------------------- |
| `POST` | `/mobile/login`            | Login teknisi                            |
| `GET`  | `/mobile/tasks`            | Ambil daftar tugas                       |
| `POST` | `/mobile/tickets/start`    | Mulai pengerjaan tiket (check-in)        |
| `POST` | `/mobile/tickets/resolve`  | Selesaikan tiket + kirim laporan         |
| `POST` | `/mobile/update-fcm-token` | Update FCM token untuk push notification |

---

## 🔒 Keamanan

- **Token Authentication** — Setiap request API menggunakan Bearer Token
- **Encrypted Storage** — Token & data sensitif disimpan menggunakan `flutter_secure_storage` dengan `EncryptedSharedPreferences` (Android)
- **Environment Variables** — Tidak ada hardcode URL atau secret di source code
- **Session Management** — Auto-logout saat token expired, clear session saat logout

---

## 👥 Tim Pengembang

| Nama                       | Role              |
| -------------------------- | ----------------- |
| **Tan Rafly**              | Mobile Developer  |
| **Muhammad Fauzi**         | Web Developer     |
| **Muhammad Iqbal Maulana** | Chatbot Developer |

> 📍 Dikembangkan sebagai proyek **Kerja Praktik (KP)** untuk **RSUD dr. Soedarso Pontianak**.

---

## 📄 Lisensi

Proyek ini bersifat **private** dan dikembangkan khusus untuk keperluan internal RSUD dr. Soedarso Pontianak.

---

<p align="center">
  <sub>Rafly Tan</sub>
</p>
