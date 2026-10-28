-- Seeding Filemai.my database

-- Clear old data if any
SET FOREIGN_KEY_CHECKS = 0;
TRUNCATE TABLE subscriptions;
TRUNCATE TABLE ads;
TRUNCATE TABLE episodes;
TRUNCATE TABLE dramas;
TRUNCATE TABLE users;
SET FOREIGN_KEY_CHECKS = 1;

-- Users (password: 'password')
INSERT INTO users (id, name, email, password, role, subscription_status, subscription_expires_at) VALUES
(1, 'Super Admin', 'admin@filemai.my', '$2y$12$5zk99orTugUE2f1rHEL5f.3tPWZ0dmKYi.Dhk0P9HuUGutB3PC6kC', 'admin', 'premium', DATE_ADD(NOW(), INTERVAL 365 DAY)),
(2, 'Sir Helmy (Filmmaker)', 'creator@filemai.my', '$2y$12$5zk99orTugUE2f1rHEL5f.3tPWZ0dmKYi.Dhk0P9HuUGutB3PC6kC', 'creator', 'premium', DATE_ADD(NOW(), INTERVAL 90 DAY)),
(3, 'Siti Drama Studio', 'siti@filemai.my', '$2y$12$5zk99orTugUE2f1rHEL5f.3tPWZ0dmKYi.Dhk0P9HuUGutB3PC6kC', 'creator', 'free', NULL),
(4, 'Ali Penonton (Free)', 'viewer@filemai.my', '$2y$12$5zk99orTugUE2f1rHEL5f.3tPWZ0dmKYi.Dhk0P9HuUGutB3PC6kC', 'viewer', 'free', NULL),
(5, 'Sara VIP (Premium)', 'vip@filemai.my', '$2y$12$5zk99orTugUE2f1rHEL5f.3tPWZ0dmKYi.Dhk0P9HuUGutB3PC6kC', 'viewer', 'premium', DATE_ADD(NOW(), INTERVAL 30 DAY));

-- Dramas
INSERT INTO dramas (id, creator_id, title, slug, synopsis, poster_url, banner_url, genre, type, status, views_count, featured) VALUES
(1, 2, 'Jodoh Kontrak CEO Dingin', 'jodoh-kontrak-ceo-dingin', 'Seorang gadis biasa terpaksa menandatangani kontrak perkahwinan palsu bernilai RM1 Juta dengan CEO paling dingin di kota demi menyelamatkan perniagaan keluarganya. Namun, di sebalik sikap acuh tak acuh si jutawan, tersimpan rahsia besar dari masa silam mereka berdua.', '/uploads/posters/jodoh-kontrak-ceo.jpg', '/uploads/posters/jodoh-kontrak-ceo.jpg', 'Romance', 'series', 'published', 15420, 1),
(2, 2, 'Dendam Pewaris Yang Dibuang', 'dendam-pewaris-yang-dibuang', 'Dibuang oleh keluarga konglomeratnya 10 tahun lalu dan dihina sebagai pengemis jalanan. Kini, sang naga tidur bangkit kembali sebagai penguasa kewangan global terkuat untuk menuntut setiap titis air mata dan maruah yang telah dirampas!', '/uploads/posters/dendam-pewaris.jpg', '/uploads/posters/dendam-pewaris.jpg', 'Action', 'series', 'published', 28900, 1),
(3, 3, 'Rahsia Isteri Jutawan', 'rahsia-isteri-jutawan', 'Di mata orang ramai, dia hanyalah suri rumah yang tenang di dalam banglo mewah. Hakikatnya, dia memiliki kunci kepada empayar rahsia bawah tanah yang sedang diburu oleh musuh-musuh suaminya.', '/uploads/posters/rahsia-isteri.jpg', '/uploads/posters/rahsia-isteri.jpg', 'Mystery', 'series', 'published', 8450, 0),
(4, 3, 'Keluarga Mertua Dari Neraka', 'keluarga-mertua-dari-neraka', 'Drama realiti kekeluargaan tentang cabaran seorang menantu berdepan kerenah ibu mertua dan ipar duai yang tidak pernah berpuas hati.', '/uploads/posters/jodoh-kontrak-ceo.jpg', '/uploads/posters/jodoh-kontrak-ceo.jpg', 'Drama', 'series', 'pending', 0, 0);

-- Episodes for Drama 1 (Jodoh Kontrak CEO)
INSERT INTO episodes (drama_id, episode_number, title, video_url, duration, views_count, is_free_preview) VALUES
(1, 1, 'Episod 1: Pertemuan Tak Sengaja di Lif Korporat', '/uploads/videos/ep1.mp4', 75, 5820, 1),
(1, 2, 'Episod 2: Syarat Perkahwinan Rahsia RM1 Juta', '/uploads/videos/ep2.mp4', 82, 4210, 1),
(1, 3, 'Episod 3: Bekas Kekasih Menagih Kasih', '/uploads/videos/ep3.mp4', 68, 3110, 0),
(1, 4, 'Episod 4: Cemburu Sang Jutawan', '/uploads/videos/ep1.mp4', 90, 2280, 0);

-- Episodes for Drama 2 (Dendam Pewaris)
INSERT INTO episodes (drama_id, episode_number, title, video_url, duration, views_count, is_free_preview) VALUES
(2, 1, 'Episod 1: Dikhianati Pada Hari Pertunangan', '/uploads/videos/ep1.mp4', 85, 12400, 1),
(2, 2, 'Episod 2: Identiti Sebenar Sang Naga Terbongkar', '/uploads/videos/ep2.mp4', 78, 8900, 1),
(2, 3, 'Episod 3: Menggoncang Majlis Jamuan Korporat', '/uploads/videos/ep3.mp4', 92, 7600, 0);

-- Episodes for Drama 3 (Rahsia Isteri)
INSERT INTO episodes (drama_id, episode_number, title, video_url, duration, views_count, is_free_preview) VALUES
(3, 1, 'Episod 1: Kehidupan Di Sebalik Topeng Emas', '/uploads/videos/ep1.mp4', 64, 4350, 1),
(3, 2, 'Episod 2: Kunci Peti Besi Rahsia', '/uploads/videos/ep2.mp4', 70, 4100, 0);

-- In-house Ads
INSERT INTO ads (title, video_url, duration_seconds, click_url, sponsor_name, is_active, views_count, clicks_count) VALUES
('MiQa Cloud - Server Web Ultra Pantas', '/uploads/videos/ad1.mp4', 10, 'https://filemai.my', 'MiQaOS Cloud', 1, 1420, 85),
('Kopi Harimau Perkasa - Segar & Bersemangat', '/uploads/videos/ad1.mp4', 15, 'https://filemai.my', 'Harimau Beverages', 1, 980, 42),
('Pas VIP Filemai - Tonton Tanpa Iklan RM5/bulan', '/uploads/videos/ad1.mp4', 10, '/subscribe', 'Filemai VIP', 1, 2350, 310);

-- Subscriptions
INSERT INTO subscriptions (user_id, amount, payment_channel, transaction_ref, status, paid_at) VALUES
(5, 5.00, 'ToyyibPay FPX (Maybank2u)', 'TRX-20261028-09142', 'success', NOW()),
(2, 5.00, 'Stripe Card (Visa)', 'TRX-20261027-08231', 'success', DATE_SUB(NOW(), INTERVAL 1 DAY));
