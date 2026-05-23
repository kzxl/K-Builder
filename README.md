# KBuilder CMS

KBuilder là một nền tảng quản trị nội dung (CMS) và Page Builder hệ thống mới, được thiết kế theo kiến trúc Headless-Ready, giao diện quản trị React SPA (Single Page Application) mượt mà và hệ thống kéo-thả (Drag & Drop) thông minh. 

Hệ thống được phát triển với triết lý tối ưu hoá hiệu năng, mở rộng vô hạn qua Plugin và mang lại trải nghiệm chuẩn "SaaS cao cấp".

## 🌟 Tính năng nổi bật
- **Visual Page Builder:** Xây dựng trang web bằng thao tác kéo thả trực quan, xem trước (Live Preview) theo thời gian thực.
- **Nested Layouts:** Hỗ trợ lồng ghép Component không giới hạn (ví dụ: Chia cột, lồng khối Text, Image bên trong các cột).
- **Dynamic Content:** Liên kết dữ liệu động trực tiếp vào giao diện (Query Loop cho Posts/Products) chỉ với một cú click chuột.
- **Glassmorphism Admin UI:** Bảng điều khiển quản trị siêu mượt, được thiết kế theo phong cách hiện đại với React.
- **Extensible Plugin System:** Kiến trúc PHP lõi hỗ trợ tạo Component/Plugin mới cực kỳ dễ dàng.

## 🚀 Hướng dẫn cài đặt

### Yêu cầu hệ thống
- PHP >= 8.2
- MySQL / MariaDB
- Composer
- Node.js >= 18 (Để build React Admin)

### Các bước triển khai

1. **Clone mã nguồn và cài đặt PHP Dependencies:**
   ```bash
   composer install
   ```

2. **Cấu hình CSDL:**
   Tạo Database MySQL (ví dụ: `kbuilder`). 
   Sửa thông tin kết nối trong file `config/database.php` (hoặc `.env` nếu có) và `phinx.php`.

3. **Chạy Migration & Seeding (Tạo cấu trúc và dữ liệu mẫu):**
   Chạy các lệnh Phinx để tạo bảng và tài khoản mặc định:
   ```bash
   vendor/bin/phinx migrate
   vendor/bin/phinx seed:run
   ```

4. **Build giao diện Admin (React SPA):**
   Chuyển vào thư mục `apps/admin` và tiến hành build frontend:
   ```bash
   cd apps/admin
   npm install
   npm run build
   ```

5. **Cấu hình Web Server:**
   - Trỏ thư mục gốc (Document Root) của domain ảo (Virtual Host) vào thư mục `public` của project.
   - Nếu chạy trong thư mục con (Sub-directory) qua XAMPP (VD: `localhost/kbuilder/public`), hệ thống hỗ trợ auto-detect path, nhưng tối ưu nhất vẫn là dùng Virtual Host.

## 🔐 Tài khoản Quản trị mặc định

Sau khi chạy lệnh `seed:run`, một tài khoản Super Admin sẽ được khởi tạo tự động.

*   **Đường dẫn đăng nhập:** `http://your-domain.com/admin` (hoặc `http://localhost/kbuilder/public/admin`)
*   **Email:** `admin@kbuilder.local`
*   **Mật khẩu:** `Admin@12345`

*(Vui lòng đổi mật khẩu ngay sau khi đăng nhập thành công vào hệ thống!)*

## 📂 Cấu trúc thư mục lõi

```
kbuilder/
├── apps/admin/          # Mã nguồn Frontend React SPA cho Admin Dashboard
├── config/              # Các file cấu hình hệ thống (DB, App, Cache...)
├── database/            # Phinx Migrations & Seeders
├── plugins/             # Thư mục chứa các Plugin & Component mở rộng
├── public/              # Thư mục gốc Web (index.php, CSS, JS tĩnh)
├── src/                 # Mã nguồn Backend PHP (Domain, Http, Core)
└── templates/           # Twig templates cho giao diện Public
```

## 🛠 Cách tạo nội dung mẫu nhanh (Demo Data)
Bên trong Admin Dashboard:
1. Đăng nhập vào trang quản trị.
2. Truy cập menu **Cài đặt** -> Chuyển sang tab **Công cụ hệ thống**.
3. Bấm vào nút **"Bắt đầu khởi tạo Demo"**.
Hệ thống sẽ tự động tạo cấu trúc Trang chủ, Tin tức, Danh mục... giúp bạn trải nghiệm tính năng Builder ngay lập tức.

## 🗺 Roadmap (Các tính năng cần hoàn thiện)
Dưới đây là các hạng mục dự kiến sẽ tiếp tục được nâng cấp trong các phiên bản tiếp theo:

- [ ] **Hệ thống Đa ngôn ngữ (i18n)**: Hỗ trợ dịch nội dung bài viết, trang và giao diện ra nhiều ngôn ngữ.
- [ ] **Lịch sử chỉnh sửa (Revisions)**: Cải tiến Builder cho phép Undo/Redo và khôi phục các phiên bản cũ của Trang/Bài viết.
- [ ] **Tối ưu SEO & Performance Frontend**: Tự động Minify CSS/JS cho giao diện người dùng cuối, Lazy-load ảnh.
- [ ] **Trình quản lý Form (Form Builder)**: Khối Component kéo thả cho phép tạo Contact Form tùy chỉnh và lưu cấu hình vào hệ thống.
- [ ] **Hệ thống Caching toàn diện**: Áp dụng Redis/Memcached mạnh mẽ cho việc Render View và Query Database.
- [ ] **Theme Customizer mở rộng**: Cho phép tải lên cấu hình Typography, Palette màu nâng cao từ file định dạng chuẩn.
