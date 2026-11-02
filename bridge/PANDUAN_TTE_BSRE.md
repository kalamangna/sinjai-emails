# Panduan Integrasi API TTE BSrE — Pemkab Sinjai

Panduan teknis integrasi dan pemanggilan endpoint Tanda Tangan Elektronik (TTE) BSrE BSSN melalui Reverse Proxy server `apl.sinjaikab.go.id`.

---

## 1. Spesifikasi Umum

- **Base URL Akses Publik:** `http://apl.sinjaikab.go.id/tte/`
- **Backend Server Lokal:** `http://10.91.162.6/`
- **Protokol:** HTTP/HTTPS REST API
- **Metode Autentikasi:** HTTP Basic Authentication (`username:password`)

---

## 2. Autentikasi

Setiap request ke endpoint API TTE wajib menyertakan header:
```http
Authorization: Basic <base64(username:password)>
```
*Username dan password diperoleh dari akun sistem yang telah didaftarkan pada eSign Client Service instansi.*

---

## 3. Daftar Endpoint API

### A. Cek Status Layanan
- **Method:** `GET`
- **Endpoint:** `/api/status`
- **Deskripsi:** Memeriksa status operasional eSign Client Service dan konektivitas ke server pusat BSrE BSSN.

### B. Cek Status Sertifikat User
- **Method:** `GET`
- **Endpoint:** `/api/user/status/{nik}`
- **Parameter URL:** `{nik}` (16 digit NIK pemilik sertifikat)
- **Deskripsi:** Mengetahui status sertifikat elektronik penandatangan (aktif/belum ada/kedaluwarsa).

### C. Penandatanganan Dokumen PDF
- **Method:** `POST`
- **Endpoint:** `/api/sign/pdf`
- **Content-Type:** `multipart/form-data`
- **Parameter Form:**

| Parameter | Tipe Data | Wajib | Keterangan |
| :--- | :--- | :---: | :--- |
| `file` | File Binary | Ya | Berkas PDF yang akan ditandatangani |
| `nik` | String | Ya | 16 digit NIK penandatangan |
| `passphrase`| String | Ya | Passphrase / PIN keamanan sertifikat |
| `tampilan` | String | Ya | `visible` (dengan visual QR) atau `invisible` |
| `halaman` | String/Int | * | Nomor halaman tanda tangan (misal: `1` atau `terakhir`) |
| `xAxis` | Float | * | Posisi koordinat horizontal (X) |
| `yAxis` | Float | * | Posisi koordinat vertikal (Y) |
| `width` | Float | * | Lebar area tanda tangan / QR (standar: 100 - 150) |
| `height` | Float | * | Tinggi area tanda tangan / QR (standar: 100 - 150) |

*\*Wajib jika parameter `tampilan` bernilai `visible`.*

- **Response:**
  - **HTTP 200:** File stream PDF hasil penandatanganan (`application/pdf`).
  - **HTTP 4xx / 5xx:** JSON pesan error (misal passphrase salah, ukuran file terlalu besar, dll).

### D. Verifikasi Dokumen PDF
- **Method:** `POST`
- **Endpoint:** `/api/sign/verify`
- **Content-Type:** `multipart/form-data`
- **Parameter Form:**

| Parameter | Tipe Data | Wajib | Keterangan |
| :--- | :--- | :---: | :--- |
| `file` | File Binary | Ya | Dokumen PDF yang akan diverifikasi integritasnya |

- **Response:** JSON detail penandatangan, timestamp penandatanganan, dan validitas sertifikat.

---

## 4. Contoh Kode Pemanggilan

### A. Contoh cURL (Terminal / Bash)

```bash
# 1. Cek Status Service
curl -X GET "http://apl.sinjaikab.go.id/tte/api/status" \
     -u "username:password"

# 2. Penandatanganan PDF
curl -X POST "http://apl.sinjaikab.go.id/tte/api/sign/pdf" \
     -u "username:password" \
     -F "file=@/path/to/dokumen.pdf" \
     -F "nik=730XXXXXXXXXXXXX" \
     -F "passphrase=Rahasia123!" \
     -F "tampilan=visible" \
     -F "halaman=1" \
     -F "xAxis=100" \
     -F "yAxis=100" \
     -F "width=120" \
     -F "height=120" \
     --output "dokumen_signed.pdf"
```

### B. Contoh PHP (cURL Native)

```php
<?php
$url = 'http://apl.sinjaikab.go.id/tte/api/sign/pdf';
$username = 'username_tte';
$password = 'password_tte';

$postData = [
    'file'       => new CURLFile('/path/ke/surat.pdf', 'application/pdf', 'surat.pdf'),
    'nik'        => '730XXXXXXXXXXXXX',
    'passphrase' => 'PasswordTTE123',
    'tampilan'   => 'visible',
    'halaman'    => '1',
    'xAxis'      => '100',
    'yAxis'      => '150',
    'width'      => '120',
    'height'     => '120',
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
curl_setopt($ch, CURLOPT_USERPWD, "$username:$password");
curl_setopt($ch, CURLOPT_TIMEOUT, 120);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode === 200) {
    file_put_contents('/path/ke/surat_signed.pdf', $response);
    echo "TTE Berhasil disimpan!";
} else {
    echo "Gagal: HTTP " . $httpCode . " - " . $response;
}
?>
```

### C. Contoh Python (Requests)

```python
import requests
from requests.auth import HTTPBasicAuth

url = "http://apl.sinjaikab.go.id/tte/api/sign/pdf"
auth = HTTPBasicAuth("username_tte", "password_tte")

files = {
    'file': ('surat.pdf', open('/path/ke/surat.pdf', 'rb'), 'application/pdf')
}

data = {
    'nik': '730XXXXXXXXXXXXX',
    'passphrase': 'PasswordTTE123',
    'tampilan': 'visible',
    'halaman': '1',
    'xAxis': '100',
    'yAxis': '100',
    'width': '120',
    'height': '120'
}

response = requests.post(url, auth=auth, files=files, data=data, timeout=120)

if response.status_code == 200:
    with open('/path/ke/surat_signed.pdf', 'wb') as f:
        f.write(response.content)
    print("PDF Berhasil ditandatangani!")
else:
    print(f"Gagal ({response.status_code}): {response.text}")
```

---

## 5. Troubleshooting

1. **HTTP 401 Unauthorized:** Kredensial username/password Basic Auth tidak cocok dengan yang ada pada eSign Client Service.
2. **Connection Timeout:** Proses enkripsi dan verifikasi ke BSSN membutuhkan waktu. Pastikan timeout aplikasi client minimal 60-120 detik.
3. **Payload Too Large / 413:** Dokumen PDF terlalu besar, perkecil resolusi lampiran atau kompres PDF.
