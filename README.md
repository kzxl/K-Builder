# KBuilder CMS

KBuilder là một nền tảng quản trị nội dung (CMS) và Page Builder hệ thống mới, được thiết kế theo kiến trúc Headless-Ready, giao diện quản trị React SPA (Single Page Application) mượt mà và hệ thống kéo-thả (Drag & Drop) thông minh. 

Hệ thống được phát triển với triết lý tối ưu hoá hiệu năng, mở rộng vô hạn qua Plugin và mang lại trải nghiệm chuẩn "SaaS cao cấp".

## 🌟 Tính năng nổi bật
- **Visual Page Builder:** Xây dựng trang web bằng thao tác kéo thả trực quan, xem trước (Live Preview) theo thời gian thực, kèm **Undo/Redo** và **Lịch sử phiên bản (Revisions)**.
- **Responsive Preview:** Xem trước Desktop / Tablet / Mobile ngay trong Builder.
- **Nested Layouts:** Hỗ trợ lồng ghép Component không giới hạn (ví dụ: Chia cột, lồng khối Text, Image bên trong các cột).
- **Dynamic Content:** Liên kết dữ liệu động trực tiếp vào giao diện (Query Loop cho Posts) chỉ với một cú click chuột.
- **Glassmorphism Admin UI:** Bảng điều khiển quản trị siêu mượt, được thiết kế theo phong cách hiện đại với React.
- **Extensible Plugin System:** Kiến trúc PHP lõi hỗ trợ tạo Component/Plugin mới cực kỳ dễ dàng qua hệ thống Hook (action/filter).
- **Bảo mật & Phân quyền (RBAC):** JWT (access + refresh token xoay vòng), middleware kiểm tra quyền theo vai trò, rate limiting và security headers.
- **SEO toàn diện:** `sitemap.xml` động, `robots.txt`, trình quản lý Redirect (301/302), thẻ Meta/OpenGraph/JSON-LD theo từng trang.
- **Form Builder:** Tạo biểu mẫu tùy biến bằng kéo thả, lưu submission linh hoạt và quản lý trong Admin.
- **Caching:** Cache HTML trang công khai (driver File hoặc Redis) với tự động vô hiệu hóa khi nội dung thay đổi.
- **Multi-site sẵn sàng:** `site_id` được nhúng trong JWT, hạ tầng đa tenant ở tầng dữ liệu.

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

6. **Chạy kiểm thử (tùy chọn):**
   ```bash
   composer test
   # hoặc: vendor/bin/phpunit
   ```

> ⚠️ **Lưu ý bảo mật trước khi triển khai production:**
> - Đặt `JWT_SECRET`, `APP_KEY` thành chuỗi ngẫu nhiên; cấu hình `DB_PASSWORD`, `APP_URL` thật trong `.env`.
> - Đặt `APP_ENV=production` và `APP_DEBUG=false`.
> - Biến `APP_DEBUG_REQUESTS` chỉ nên bật khi cần debug (ghi log mọi request).

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
├── config/              # Các file cấu hình hệ thống (DB, App, Cache, Auth)
├── database/            # Phinx Migrations & Seeders
├── plugins/             # Thư mục chứa các Plugin & Component mở rộng
├── public/              # Thư mục gốc Web (index.php, CSS, JS tĩnh)
├── src/                 # Mã nguồn Backend PHP (Core, Domain, Http)
├── templates/           # Twig templates cho giao diện Public
└── tests/               # Unit test (PHPUnit)
```

## 🛠 Cách tạo nội dung mẫu nhanh (Demo Data)
Bên trong Admin Dashboard:
1. Đăng nhập vào trang quản trị.
2. Truy cập menu **Cài đặt** -> Chuyển sang tab **Công cụ hệ thống**.
3. Bấm vào nút **"Bắt đầu khởi tạo Demo"**.
Hệ thống sẽ tự động tạo cấu trúc Trang chủ, Tin tức, Danh mục... giúp bạn trải nghiệm tính năng Builder ngay lập tức.

## 🧩 Plugin & Component có sẵn

**Component khối (Builder blocks):**
- `core-hero` — Hero (fullwidth / split / minimal)
- `core-text`, `core-button`, `core-image` — khối nội dung cơ bản
- `core-columns` — bố cục chia cột (nested layout)
- `core-features`, `core-faq` — section tính năng & hỏi đáp
- `core-blocks` — **Video, Bảng giá (Pricing), Thư viện ảnh (Gallery)**
- `kb-post-grid` — lưới bài viết (dynamic content)

**Plugin chức năng:**
- `kb-form-builder` — **Trình tạo biểu mẫu kéo thả** (lưu submission dạng JSON)
- `kb-contact-form` + `kb-contact-manager` — biểu mẫu liên hệ & CRM
- `kb-seo-manager` — **sitemap động, robots.txt, redirect manager**
- `kb-security` — **rate limiting & security headers**
- `kb-analytics` — theo dõi lượt truy cập
- `kb-theme-manager` — quản lý giao diện & CSS tùy chỉnh

## 🔌 API tổng quan

- **Auth:** `POST /api/auth/login`, `POST /api/auth/refresh`, `POST /api/auth/logout`, `GET /api/auth/me`
- **Nội dung:** `/api/pages` (kèm `publish`, `duplicate`, `revisions`, `revisions/{id}/restore`), `/api/posts`, `/api/taxonomies`
- **Builder:** `/api/components`, `/api/components/preview`
- **Quản lý:** `/api/media`, `/api/menus`, `/api/plugins`, `/api/settings/{group}`, `/api/sites`
- **RBAC:** `/api/users`, `/api/roles`
- **Công khai:** `GET /sitemap.xml`, `GET /robots.txt`

Toàn bộ nhóm `/api` (trừ auth) được bảo vệ bằng JWT; các thao tác nhạy cảm có thêm middleware kiểm tra quyền.

## 🗺 Roadmap

**Đã hoàn thiện:**
- [x] **Lịch sử chỉnh sửa (Revisions):** Undo/Redo trong Builder và khôi phục phiên bản cũ của Trang.
- [x] **Trình quản lý Form (Form Builder):** Khối kéo thả tạo form tùy chỉnh và lưu submission.
- [x] **Hệ thống Caching:** Cache render trang công khai với driver File/Redis và tự động vô hiệu hóa.
- [x] **Tối ưu SEO & Performance:** Sitemap động, robots.txt, redirect manager, lazy-load ảnh.
- [x] **Phân quyền (RBAC) & Bảo mật:** Middleware kiểm tra quyền, rate limiting, security headers.
- [x] **Quản lý người dùng:** Giao diện CRUD user + gán vai trò.

**Dự kiến tiếp theo:**
- [ ] **Hệ thống Đa ngôn ngữ (i18n):** Hỗ trợ dịch nội dung bài viết, trang và giao diện ra nhiều ngôn ngữ.
- [ ] **Theme Customizer mở rộng:** Cho phép tải lên cấu hình Typography, Palette màu nâng cao từ file định dạng chuẩn.
- [ ] **Multi-site đầy đủ:** Giao diện chuyển đổi và quản lý nhiều site trong Admin.
