Beres Sir Helmy, jom kita bedah konsep dan susun **Product Requirement Document (PRD)** untuk MVP platform *vertical mini-drama streaming* ni. Konsep macam DramaBox atau ReelShort dalam screenshot tu memang tengah meletup sekarang, dan model *freemium with rewarded interstitial ads* campur langganan RM5/bulan ni sangat praktikal untuk pasaran tempatan.

---

## 1. Ringkasan Projek & Objektif MVP

* **Nama Konsep:** MiniDrama Hub (Nama kerja)
* **Objektif:** Menyediakan platform video penstriman drama pendek (format menegak 9:16 atau standard) di mana *creators* boleh muat naik siri/filem, penonton tonton percuma dengan selingan iklan pendek (10–20 saat), atau langgan pas *Ad-Free* RM5 sebulan.
* **Fokus MVP:** Aliran asas muat naik kandungan, penstriman video stabil, integrasi iklan *interstitial*, dan pembayaran langganan bulanan tanpa *bloatware*.

---

## 2. Pengurusan Peranan Pengguna (User Roles)

1. **Super Admin:**
* Urus dan pantau akaun pengguna serta *filmmakers*.
* Moderasi kandungan (lulus/tolak video sebelum *live*).
* Pantau transaksi langganan dan analitik tontonan.
* Urus tetapan iklan platform (URL iklan video, tempoh masa *countdown*).


2. **Content Creator / Filmmaker:**
* Daftar akaun dan akses *Creator Dashboard*.
* Cipta tajuk drama/filem (Metadata: Tajuk, Sinopsis, *Thumbnail*, Genre).
* Muat naik episod mengikut urutan (Episod 1, 2, 3...).
* Pantau jumlah tontonan (*views counter*).


3. **Viewer (Penonton):**
* Daftar/Log masuk akaun.
* *Browse* senarai drama (Trending, Terbaharu, Kategori).
* Tonton video secara percuma dengan *ad modal countdown* (10–20 saat) sebelum episod seterusnya dimainkan.
* Langgan pelan Premium RM5/bulan untuk langkau terus semua iklan.



---

## 3. Spesifikasi Ciri & Keperluan Fungsian (Functional Requirements)

### Modul 1: Autentikasi & Pengurusan Akaun

* Pendaftaran menggunakan emel/kata laluan atau *Social Login* (Google).
* Pengasingan peranan menggunakan *middleware* (`admin`, `creator`, `viewer`).
* Profil pengguna ringkas (status langganan: Free / Premium, tarikh luput).

### Modul 2: Pengurusan Video & Muat Naik (Creator Portal)

* Borang cipta Siri Baru: Tajuk, poster menegak (cover), sinopsis, kategori.
* Muat naik fail video:
* Format: MP4 / WebM (disyorkan simpan ke *object storage* seperti Cloudflare R2 / AWS S3 / BunnyCDN bagi mengelakkan *load* berat pada MariaDB/server utama).
* Sokongan *chunk upload* atau *background queue* (Laravel Queue) untuk fail bersaiz besar.


* Status video: *Draft*, *Pending Review*, *Published*.

### Modul 3: Pemain Video & Logik Iklan (Video Player & Ad Engine)

* Pemain video menggunakan **Video.js** atau pemain HTML5 kustom mesra peranti mudah alih.
* **Logik Iklan Interstitial (Free User):**
1. Apabila episod selesai dimainkan atau penonton klik butang "Episod Seterusnya", sistem memaparkan *Ad Modal Overlay*.
2. Iklan video pendek (10–20 saat) dimainkan dengan pemasa *countdown* yang tidak boleh di-*skip*.
3. Butang "Langkau / Tonton Seterusnya" hanya aktif selepas pemasa mencapai 0 saat.
4. Video episod baharu dimainkan secara automatik sejurus iklan selesai.


* **Logik Premium User:**
1. Semakan status langganan dibuat pada paras API/middleware.
2. Jika akaun berstatus *Premium Active*, langkau terus paparan iklan dan teruskan ke episod seterusnya.



### Modul 4: Sistem Langganan & Gerbang Pembayaran

* Integrasi *payment gateway* tempatan (contoh: ToyyibPay, Chip In, atau Stripe FPX).
* Tetapan pelan: RM5 untuk akses 30 hari.
* *Webhook handler* untuk mengemas kini tarikh `subscription_expires_at` sebaik pembayaran berjaya disahkan.

---

## 4. Cadangan Skema Pangkalan Data (MariaDB)

```sql
-- Jadual Pengguna
CREATE TABLE users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'creator', 'viewer') DEFAULT 'viewer',
    subscription_status ENUM('free', 'premium') DEFAULT 'free',
    subscription_expires_at TIMESTAMP NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);

-- Jadual Siri / Filem
CREATE TABLE dramas (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    creator_id BIGINT UNSIGNED NOT NULL,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) UNIQUE NOT NULL,
    synopsis TEXT NULL,
    poster_url VARCHAR(255) NOT NULL,
    type ENUM('series', 'movie') DEFAULT 'series',
    status ENUM('pending', 'published', 'rejected') DEFAULT 'pending',
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (creator_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Jadual Episod
CREATE TABLE episodes (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    drama_id BIGINT UNSIGNED NOT NULL,
    episode_number INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    video_url VARCHAR(255) NOT NULL,
    duration INT NULL, -- dalam saat
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (drama_id) REFERENCES dramas(id) ON DELETE CASCADE
);

-- Jadual Iklan Dalaman (In-house Ads)
CREATE TABLE ads (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    video_url VARCHAR(255) NOT NULL,
    duration_seconds INT DEFAULT 15,
    click_url VARCHAR(255) NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);

-- Jadual Transaksi Langganan
CREATE TABLE subscriptions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    amount DECIMAL(8,2) NOT NULL DEFAULT 5.00,
    payment_channel VARCHAR(50) NOT NULL,
    transaction_ref VARCHAR(255) UNIQUE NOT NULL,
    status ENUM('pending', 'success', 'failed') DEFAULT 'pending',
    paid_at TIMESTAMP NULL,
    created_at TIMESTAMP NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

```

---

## 5. Senibina Teknikal & Stack Cadangan

* **Backend:** Laravel 11.x (Menggunakan Laravel Breeze untuk scaffolding autentikasi yang ringan).
* **Database:** MariaDB (Optimum untuk transaksi ACID dan struktur relasi siri-ke-episod).
* **Frontend:** Blade + Alpine.js + Tailwind CSS (Cepat dibina, interaktif, dan sangat ringan untuk peranti mudah alih).
* **Storan Media & CDN:** Simpan fail video di storan objek pihak ketiga (contohnya Bunny.net CDN atau Cloudflare R2). Jangan simpan fail video terus ke pelayan web tempatan untuk memastikan kelajuan *buffering* tidak terganggu.
* **Queues:** Redis atau MariaDB database driver untuk menguruskan notifikasi emel dan pemprosesan metadata video.

---

## 6. Fasa Seterusnya (Post-MVP Backlog)

Berdasarkan paparan pada gambar rujukan, elemen yang sesuai dimasukkan selepas MVP stabil:

* **Gamification & Daily Check-in:** Sistem syiling harian seperti dalam gambar bagi pengguna yang tidak melanggan tetapi rajin melawat web.
* **Coin Unlocking System:** Pilihan membayar sen-sen menggunakan baki syiling untuk *unlock* terus episod tertentu tanpa menonton iklan.
* **Revenue Sharing Portal:** Modul pengiraan komisen untuk *creators* berdasarkan jumlah tontonan episod mereka.

mariadb
user: filemai
password: cxA2t051ZEmUOPM0
database: filemai
localhost:3306