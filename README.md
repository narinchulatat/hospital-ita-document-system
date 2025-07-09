# ระบบจัดเก็บและเผยแพร่เอกสาร ITA โรงพยาบาล

## ภาพรวมโปรเจค

ระบบจัดการเอกสารสำหรับโรงพยาบาลที่สมบูรณ์แบบ ใช้โครงสร้างฐานข้อมูล MySQL พร้อมระบบ RBAC และ workflow การอนุมัติเอกสาร

## คุณสมบัติหลัก

### 🔐 ระบบความปลอดภัย
- **Authentication & Authorization**: ระบบล็อกอินที่ปลอดภัย
- **Role-based Access Control (RBAC)**: 4 บทบาท (Admin, Staff, Approver, Visitor)
- **Session Management**: จัดการเซสชันและ timeout
- **CSRF Protection**: ป้องกันการโจมตี CSRF
- **Input Validation**: ตรวจสอบข้อมูลป้อนเข้า

### 👥 บทบาทผู้ใช้

#### 🛠️ Admin (ผู้ดูแลระบบ)
- จัดการผู้ใช้และสิทธิ์
- ดูสถิติและรายงานทั้งหมด
- จัดการหมวดหมู่เอกสาร
- สำรองข้อมูลและตั้งค่าระบบ
- ดูประวัติการใช้งาน (Activity Logs)

#### 📝 Staff (เจ้าหน้าที่)
- อัปโหลดเอกสารใหม่
- แก้ไขเอกสารที่อัปโหลด
- ติดตามสถานะการอนุมัติ
- จัดการเอกสารส่วนตัว

#### ✅ Approver (ผู้อนุมัติ)
- อนุมัติ/ปฏิเสธเอกสาร
- เพิ่มความเห็นในการอนุมัติ
- ดูประวัติการอนุมัติ
- รับการแจ้งเตือนเอกสารรออนุมัติ

#### 👁️ Visitor (ผู้เยี่ยมชม)
- เรียกดูเอกสารสาธารณะ
- ค้นหาและดาวน์โหลดเอกสาร
- ไม่ต้องลงทะเบียน

### 📁 ระบบจัดการเอกสาร
- **หมวดหมู่ 3 ระดับ**: จัดเรียงแบบ Tree Structure
- **Version Control**: ติดตามเวอร์ชันเอกสาร
- **Approval Workflow**: ร่าง → รออนุมัติ → อนุมัติ/ปฏิเสธ
- **ไฟล์ที่รองรับ**: PDF, DOCX, XLSX, JPG, PNG
- **ข้อมูลปีงบประมาณ**: ผูกเอกสารกับปีงบและไตรมาส
- **สถิติการใช้งาน**: นับการดูและดาวน์โหลด

### 🔍 ระบบค้นหาและเรียกดู
- **Advanced Search**: ค้นหาแบบละเอียด
- **Category Navigation**: เรียกดูตามหมวดหมู่
- **Filter System**: กรองตามปีงบ, ไตรมาส, สถานะ
- **Document Preview**: ดูตัวอย่างไฟล์ PDF
- **Mobile Responsive**: ใช้งานได้บนมือถือ

### 🔔 ระบบแจ้งเตือน
- **Real-time Notifications**: แจ้งเตือนแบบ Real-time
- **Email Integration**: ส่งอีเมลแจ้งเตือน
- **Activity Alerts**: แจ้งเตือนการอนุมัติ/ปฏิเสธ
- **System Announcements**: ประกาศระบบ

### 💾 ระบบสำรองข้อมูล
- **Auto & Manual Backup**: สำรองอัตโนมัติและด้วยตนเอง
- **Database + Files**: สำรองทั้งฐานข้อมูลและไฟล์
- **Restore Function**: กู้คืนข้อมูล
- **Retention Policy**: นโยบายเก็บข้อมูล

## เทคโนโลยีที่ใช้

### Backend
- **PHP 8+**: ภาษาหลัก
- **MySQL 5.7+**: ฐานข้อมูล
- **PDO**: การเชื่อมต่อฐานข้อมูล

### Frontend
- **TailwindCSS 3.0**: CSS Framework
- **JavaScript (Vanilla)**: ฟังก์ชันพื้นฐาน
- **SweetAlert2**: Dialog และ Alert
- **DataTables**: ตารางข้อมูล
- **Font Awesome 6**: Icons
- **Sarabun Font**: ฟอนต์ภาษาไทย

### Security
- **bcrypt**: การเข้ารหัสรหัสผ่าน
- **Prepared Statements**: ป้องกัน SQL Injection
- **CSRF Tokens**: ป้องกัน CSRF Attack
- **Input Sanitization**: ทำความสะอาดข้อมูล

## โครงสร้างไฟล์

```
/
├── ita_hospital_db.sql                  # โครงสร้างฐานข้อมูลหลัก
├── config/
│   ├── config.php                   # การตั้งค่าหลัก
│   ├── constants.php                # ค่าคงที่
│   └── database.php                 # การเชื่อมต่อฐานข้อมูล
├── includes/
│   ├── auth.php                     # ระบบ Authentication
│   ├── functions.php                # ฟังก์ชันช่วย
│   ├── header.php                   # Header template
│   └── footer.php                   # Footer template
├── classes/
│   ├── Database.php                 # คลาสฐานข้อมูล
│   ├── User.php                     # คลาสผู้ใช้
│   ├── Document.php                 # คลาสเอกสาร
│   ├── Category.php                 # คลาสหมวดหมู่
│   ├── Notification.php             # คลาสแจ้งเตือน
│   └── Backup.php                   # คลาสสำรองข้อมูล
├── admin/                           # ส่วน Admin
├── staff/                           # ส่วน Staff
├── approver/                        # ส่วน Approver
├── public/                          # ส่วน Public Portal
├── api/                             # REST API endpoints
├── assets/                          # CSS, JS, Images
├── uploads/                         # ไฟล์ที่อัปโหลด
├── backups/                         # ไฟล์สำรองข้อมูล
├── login.php                        # หน้าล็อกอิน
├── logout.php                       # หน้าล็อกเอาท์
└── index.php                        # หน้าหลัก
```

## การติดตั้งและใช้งาน

### ข้อกำหนดระบบ
- PHP 8.0 หรือสูงกว่า
- MySQL 5.7 หรือสูงกว่า
- Web Server (Apache/Nginx)
- Extension: PDO, GD, ZIP

### ขั้นตอนการติดตั้ง

1. **Clone โปรเจค**
   ```bash
   git clone https://github.com/narinchulatat/hospital-ita-document-system.git
   ```

2. **Import ฐานข้อมูล**
   ```sql
   mysql -u root -p < ita_hospital_db.sql
   ```

3. **ตั้งค่าฐานข้อมูล**
   ```php
   // config/database.php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'ita_hospital_db');
   define('DB_USER', 'your_username');
   define('DB_PASS', 'your_password');
   ```

4. **สร้าง Directories**
   ```bash
   mkdir uploads/documents
   mkdir backups
   chmod 755 uploads backups
   ```

5. **เข้าใช้งาน**
   - URL: `http://your-domain/`
   - Username: `admin`
   - Password: `admin123`

## Default Login Credentials
- **Username**: admin
- **Password**: admin123
- **Role**: Administrator (สิทธิ์เต็ม)

⚠️ **สำคัญ**: กรุณาเปลี่ยนรหัสผ่านเริ่มต้นหลังจากติดตั้งเสร็จสิ้น

## การแก้ไขปัญหาทั่วไป

### ปัญหาการเชื่อมต่อฐานข้อมูล
```php
// ตรวจสอบการตั้งค่าใน config/database.php
define('DB_HOST', 'localhost');
define('DB_NAME', 'ita_hospital_db');
define('DB_USER', 'root');          // เปลี่ยนตามการตั้งค่าของคุณ
define('DB_PASS', '');              // เปลี่ยนตามการตั้งค่าของคุณ
```

### ปัญหาการเข้าสู่ระบบ
- ตรวจสอบว่าใช้ username: `admin` และ password: `admin123`
- ตรวจสอบว่าฐานข้อมูลมีข้อมูลผู้ใช้
- ตรวจสอบสิทธิ์การเข้าถึงไฟล์และโฟลเดอร์

### การตั้งค่า BASE_URL
```php
// ใน config/config.php ปรับ BASE_URL ให้ตรงกับที่อยู่เว็บไซต์
define('BASE_URL', 'http://localhost/hospital-ita-document-system');
```

### สิทธิ์ไฟล์และโฟลเดอร์
```bash
chmod 755 uploads/ backups/ temp/
chmod 644 config/*.php
```

## การใช้งานแต่ละบทบาท

### สำหรับ Admin
1. เข้าสู่ระบบด้วย admin/admin123
2. จัดการผู้ใช้ในเมนู "จัดการผู้ใช้"
3. ตั้งค่าหมวดหมู่ในเมนู "จัดการหมวดหมู่"
4. ดูสถิติในหน้า Dashboard

### สำหรับ Staff
1. อัปโหลดเอกสารใหม่
2. เลือกหมวดหมู่และปีงบประมาณ
3. รอการอนุมัติจาก Approver
4. ติดตามสถานะในหน้า Dashboard

### สำหรับ Approver
1. ดูรายการเอกสารรออนุมัติ
2. คลิกดูรายละเอียดเอกสาร
3. อนุมัติหรือปฏิเสธพร้อมความเห็น
4. ระบบจะแจ้งเตือนไปยัง Staff

### สำหรับ Visitor
1. เข้าชมหน้าเว็บโดยไม่ต้องล็อกอิน
2. เรียกดูเอกสารตามหมวดหมู่
3. ค้นหาเอกสารด้วยคำสำคัญ
4. ดาวน์โหลดเอกสารที่ต้องการ

**ระบบจัดเก็บและเผยแพร่เอกสาร ITA โรงพยาบาล** - ระบบจัดการเอกสารที่ทันสมัยและใช้งานง่าย สำหรับโรงพยาบาลและหน่วยงานขนาดกลางถึงใหญ่