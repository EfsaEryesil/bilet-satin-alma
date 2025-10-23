
# 🚌 Bilet Satın Alma Platformu

Bu proje, **PHP (8.2) - SQLite - Docker** teknolojileri kullanılarak geliştirilmiş çok rollü bir **otobüs bileti satın alma platformudur.**  
Proje, kullanıcı rolleri ve işlevleriyle birlikte tam entegre CRUD yapısına sahiptir.

## 🚀 Özellikler

### 👤 Ziyaretçi
- Kalkış ve varış şehirlerine göre sefer arayabilir.
- Sefer detaylarını görebilir ancak bilet satın alamaz.
- Satın alma denemesinde “Giriş yapın” uyarısı alır.

### 🧳 Kullanıcı (Yolcu)
- Sisteme kayıt olabilir, giriş yapabilir ve çıkış yapabilir.
- Hesabına bakiye ekleyebilir, bilet satın alabilir veya iptal edebilir.
- Seferin kalkış saatine 1 saatten az kalmışsa iptal yapılamaz.
- Başarılı iptallerde ücret hesabına otomatik iade edilir.
- Satın alınan biletler PDF olarak indirilebilir.

### 🏢 Firma Admin
- Sadece kendi firmasına ait seferleri yönetebilir.
- Yeni sefer oluşturabilir, düzenleyebilir ve silebilir (CRUD).
- Firma özelinde kupon kodları ekleyebilir, düzenleyebilir ve silebilir.

### 🛠️ Admin
- Yeni otobüs firmaları ekleyebilir, düzenleyebilir veya silebilir.
- Firma admin kullanıcıları oluşturup firmalara atayabilir.
- Tüm firmalar için genel indirim kuponlarını yönetebilir.

---

## 🗄️ Kullanılan Teknolojiler

| Teknoloji | Açıklama |
|------------|----------|
| **PHP 8.2 + Apache** | Uygulama sunucusu |
| **SQLite** | Hafif veritabanı |
| **Docker** | Proje konteynerizasyonu |
| **HTML + CSS (Bootstrap benzeri custom theme)** | Arayüz tasarımı |
| **Session tabanlı auth sistemi** | Giriş / çıkış yönetimi |

---

## 🐳 Docker ile Çalıştırma

Projeyi çalıştırmak için aşağıdaki adımları izleyiniz:

# 1. Proje klasörüne giriniz
cd bilet-satin-alma

# 2. Docker imajını oluşturunuz
docker compose build

# 3. Konteyneri başlatınız
docker compose up -d

# 4. Tarayıcıdan açınız
http://localhost:8080

🟢 Docker ortamı aktif olduğunda, proje Apache üzerinde /app/public dizininden otomatik olarak yüklenir.






## ✅ Kurulum Sonrası

| Rol | E-posta | Şifre | Kupon Kodu | Yetkiler |
|------|----------|--------|--------------|-----------|
| 👑 **Admin** | `admin@example.com` | `123456` | **7899**(%50 indirimli) | - Yeni firmalar oluşturabilir, düzenleyebilir, silebilir. <br> - Firma admin kullanıcılarını oluşturabilir ve firmalara atayabilir. <br> - Tüm firmalarda geçerli kupon işlemlerini düzenleyebilir. |
| 🏢 **Firma Admin (Metro)** | `metro@example.com` | `123456` | **777** | - Sadece kendi firmasına ait seferleri yönetebilir. <br> - Yeni sefer oluşturabilir, düzenleyebilir veya silebilir. <br> - Firma özelinde indirim kuponları tanımlayabilir. |
| 🏢 **Firma Admin (Elazığ Murat Turizm)** | `murat@murat.com` | `123456` | **2323** | - Kendi firmasına ait seferleri yönetebilir. <br> - Yeni sefer oluşturabilir, düzenleyebilir veya silebilir. <br> - Firma kuponlarını düzenleyebilir. |
| 🏢 **Firma Admin (Yavuzlar Turizm)** | `yavuzlar@team.com` | `123456` | **2025** | - Yalnızca kendi firmasına ait seferleri yönetebilir. <br> - Sefer ve kupon işlemlerini düzenleyebilir. |
| 👤 **User (Yolcu)** | `ornekkullanici@ornek.com` | `123456` | — | - Seferleri arayabilir ve bilet satın alabilir. <br> - Kupon kodu varsa indirimden yararlanabilir. <br> - Satın aldığı biletleri görüntüleyebilir, iptal edebilir. <br> - Biletini **PDF** olarak indirebilir. <br> - Kalkışa 1 saatten az kaldıysa iptal yapılamaz. <br> - Her kullanıcıya otomatik olarak **20.000₺ sanal kredi** tanımlanmıştır. |

---

💡 **Not:**  
- Admin tarafından tanımlanan kuponlar tüm firmalarda geçerlidir.  
- Firma adminleri yalnızca kendi firmalarına ait kupon ve seferleri yönetebilir.  






## 🧑‍💻 Geliştirici :

👩‍💻  Zehra Efsa Eryeşil

📫 GitHub: https://github.com/EfsaEryesil











 















