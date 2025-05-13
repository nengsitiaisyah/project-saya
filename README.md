## Cara Menggunakan Database

Untuk menggunakan database yang ada di repositori ini, ikuti langkah-langkah berikut:

1. **Download file database**:
   - Ambil file `mydatabase.sql` dari repositori ini.
   - Klik file `.sql` di repositori, lalu pilih **"Download"**.

2. **Impor file SQL ke MySQL atau Database Lain**:
   - **Jika menggunakan MySQL di komputer**:
     - Buka terminal dan jalankan perintah berikut untuk mengimpor database:
       ```bash
       mysql -u username -p nama_database < mydatabase.sql
       ```
     - Gantilah `username` dengan nama pengguna MySQL kamu dan `nama_database` dengan nama database yang ingin digunakan.

3. **Alternatif dengan phpMyAdmin**:
   - Jika kamu menggunakan **phpMyAdmin**, lakukan langkah-langkah berikut:
     1. Login ke phpMyAdmin.
     2. Pilih database tempat kamu ingin mengimpor.
     3. Klik tab **"Import"**.
     4. Pilih file `mydatabase.sql` yang sudah kamu download.
     5. Klik tombol **"Go"** untuk mengimpor file.

4. **Setelah itu, kamu bisa mulai menggunakan aplikasi ini** dengan database yang sudah terisi.
