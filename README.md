
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


🧑‍💻 Geliştirici
👩‍💻  Zehra Efsa Eryeşil
📫 GitHub: https://github.com/EfsaEryesil


✅ Kurulum Sonrası:

👑 Admin Hesabı
E-posta: admin@example.com
Şifre: 123456
Admin tarafından oluşturulan %50 indirimli genel kupon kodu: 7899
Yetkiler: Yeni firmalar oluşturabilir, düzenleyebilir, silebilir.
Firma admin kullanıcılarını oluşturabilir ve firmalara atayabilir.


🏢 Örnek Firma hesabı:
E-posta: metro@example.com  

Şifre: 123456

Metro Firma Kupon Kodu : 777

Yetkiler:Sadece kendi firmasına ait seferleri yönetebilir.

Yeni seferler oluşturabilir, düzenleyebilir veya silebilir. 

Kendi firmasına özel indirim kuponları tanımlayabilir.


🏢 Örnek Firma hesabı 2 :
E-posta: murat@murat.com

Şifre: 123456

Elazığ Murat Turizm Firma Kupon Kodu : 2323


🏢 Örnek Firma hesabı 3 :
E-posta: yavuzlar@team.com

Şifre: 123456

Yavuzlar Turizm Firma Kupon Kodu : 2025


👤 Örnek User hesabı:
E-posta: ornekkullanici@ornek.com 

Şifre: 123456

NOT:Her User'a otomatik 20.000 sanal kredi verilmiştir.

Yetkiler:Seferleri arayabilir ve bilet satın alabilir.

Kupon kodu varsa indirimden yararlanabilir.

Satın aldığı biletleri görüntüleyebilir ve iptal edebilir.

Biletini PDF olarak indirebilir.

Kalkışa 1 saatten az kaldıysa iptal işlemi yapılamaz.



 









