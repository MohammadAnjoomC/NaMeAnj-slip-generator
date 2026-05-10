# NaMeAnj 🎨

**NaMeAnj** is a robust, lightweight, and incredibly fast PHP-based web application designed to help schools, teachers, and parents effortlessly generate beautiful A3 and A4 Name Slips.

With a massive library of high-resolution background templates (ranging from minimalist geometric designs to neon street racing cars), NaMeAnj makes printing thousands of perfectly aligned student name slips as easy as clicking a button.

## ✨ Core Features
* **Lightning Fast Generation:** Build name slips instantly on the client side using HTML5 Canvas.
* **Smart Bulk Maker:** Automatically duplicates a name slip 16 times onto a perfect A3 sheet for easy print-shop delivery.
* **Pro HD Template Library:** Dozens of gorgeous, high-quality themes built right in (Superheroes, Cars, Kawaii, etc.).
* **Advanced Font Engine:** Let users select custom fonts and text colors.
* **Auto-Fill Presets:** Users can save their Class, Subject, and School to instantly populate the slip editor.
* **WhatsApp Print-Shop Sharing:** Instantly share generated PDFs via direct absolute links straight to WhatsApp.
* **Enterprise Security:** Hardened with PHP password hashing (`bcrypt`), session fixation blocks, Directory Traversal prevention, and an ultra-secure Apache `.htaccess` firewall.

## 🚀 Installation & Setup
1. **Clone the repository:**
   ```bash
   git clone https://github.com/yourusername/nameanj.git
   ```
2. **Move to Server:**
   Place the files into your Apache/Nginx `htdocs` or `www` directory. PHP 7.4 or 8.x is required.
3. **Permissions:**
   Ensure the following directories have write permissions (`chmod 777` or `775` depending on your server) so the system can generate users and save files:
   - `/database/`
   - `/user/`
   - `/assets/templates/`
4. **Access the App:**
   Navigate to `http://localhost/nameanj` or your live domain.

## 🛠️ Built With
* **Backend:** PHP 8+ (No external SQL database required; relies on an ultra-fast local JSON storage system)
* **Frontend:** Vanilla JS, HTML5 Canvas, pure CSS (Glassmorphism & Dark Mode supported)
* **PDF Engine:** jsPDF

## 🔒 Security Measures Included
* XSS, Path Traversal, and Session Hijacking prevention.
* `password_hash()` for user accounts.
* Strict `.htaccess` rules denying PHP execution inside upload folders.

## 📄 License
This project is licensed under the MIT License.
