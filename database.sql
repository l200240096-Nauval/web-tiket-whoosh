DROP TABLE IF EXISTS bookings;
DROP TABLE IF EXISTS schedules;
DROP TABLE IF EXISTS trains;
DROP TABLE IF EXISTS stations;
DROP TABLE IF EXISTS users;

CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nama VARCHAR(100) NOT NULL,
  email VARCHAR(120) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  role ENUM('admin','user') NOT NULL DEFAULT 'user',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE stations (
  id INT AUTO_INCREMENT PRIMARY KEY,
  kode VARCHAR(10) NOT NULL UNIQUE,
  nama VARCHAR(100) NOT NULL,
  kota VARCHAR(100) NOT NULL
);

CREATE TABLE trains (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nama VARCHAR(100) NOT NULL,
  kelas VARCHAR(50) NOT NULL
);

CREATE TABLE schedules (
  id INT AUTO_INCREMENT PRIMARY KEY,
  train_id INT NOT NULL,
  asal_id INT NOT NULL,
  tujuan_id INT NOT NULL,
  jam_berangkat TIME NOT NULL,
  jam_tiba TIME NOT NULL,
  harga INT NOT NULL,
  kursi_total INT NOT NULL DEFAULT 100,
  FOREIGN KEY (train_id) REFERENCES trains(id) ON DELETE CASCADE,
  FOREIGN KEY (asal_id) REFERENCES stations(id) ON DELETE CASCADE,
  FOREIGN KEY (tujuan_id) REFERENCES stations(id) ON DELETE CASCADE
);

CREATE TABLE bookings (
  id INT AUTO_INCREMENT PRIMARY KEY,
  kode_booking VARCHAR(30) NOT NULL UNIQUE,
  user_id INT NULL,
  schedule_id INT NOT NULL,
  tanggal_berangkat DATE NOT NULL,
  nama_penumpang VARCHAR(100) NOT NULL,
  no_identitas VARCHAR(30) NOT NULL,
  email VARCHAR(120) NOT NULL,
  telepon VARCHAR(30) NOT NULL,
  jumlah_tiket INT NOT NULL DEFAULT 1,
  total_harga INT NOT NULL,
  status ENUM('Booked','Paid','Cancelled') NOT NULL DEFAULT 'Booked',
  payment_method VARCHAR(50) NULL,
  payment_code VARCHAR(80) NULL,
  paid_at DATETIME NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
  FOREIGN KEY (schedule_id) REFERENCES schedules(id) ON DELETE CASCADE
);

INSERT INTO users (nama, email, password, role) VALUES
('Administrator', 'admin@kai.test', '$2y$10$ukOu2dFIrfLOwGWQefcBKuNS1cWONmQnSeMLHguTMn3AMF7oHi7Fu', 'admin');

INSERT INTO stations (kode, nama, kota) VALUES
('HLM', 'Halim', 'Jakarta'),
('KRW', 'Karawang', 'Karawang'),
('PDL', 'Padalarang', 'Bandung Barat'),
('TGL', 'Tegalluar Summarecon', 'Bandung');

INSERT INTO trains (nama, kelas) VALUES
('Whoosh G1051', 'Premium Economy'),
('Whoosh G1057', 'Business Class'),
('Whoosh G1063', 'First Class'),
('Whoosh G1068', 'Premium Economy');

INSERT INTO schedules (train_id, asal_id, tujuan_id, jam_berangkat, jam_tiba, harga, kursi_total) VALUES
(1, 1, 3, '07:30:00', '08:00:00', 250000, 600),
(2, 1, 4, '08:00:00', '08:46:00', 350000, 600),
(3, 4, 1, '10:30:00', '11:16:00', 600000, 600),
(4, 3, 1, '13:45:00', '14:15:00', 250000, 600),
(1, 1, 2, '15:00:00', '15:15:00', 125000, 600),
(4, 2, 4, '17:20:00', '17:55:00', 175000, 600);
