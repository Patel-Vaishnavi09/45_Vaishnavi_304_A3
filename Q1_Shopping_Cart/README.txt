PHP PRACTICAL - QUESTION 1 SHOPPING CART
This folder is designed to use your existing common connection:
../config/db.php (database php_practical, username root, password root).

INSTALL STEPS
1. Copy Q1_Shopping_Cart folder into:
   /Applications/XAMPP/xamppfiles/htdocs/45_VAISHNAVI_A3/
2. In phpMyAdmin, select php_practical and run schema.sql.
3. Ensure your common connection file is at:
   /Applications/XAMPP/xamppfiles/htdocs/45_VAISHNAVI_A3/config/db.php
4. Start Apache and MySQL in XAMPP.
5. Open once:
   http://localhost/45_VAISHNAVI_A3/Q1_Shopping_Cart/setup_admin.php
   Default admin: admin / Admin@123
6. DELETE setup_admin.php after it creates the admin account.
7. Admin login:
   http://localhost/45_VAISHNAVI_A3/Q1_Shopping_Cart/admin/login.php
8. User site:
   http://localhost/45_VAISHNAVI_A3/Q1_Shopping_Cart/index.php

FEATURES
- Admin login/logout
- Category create/read/update/delete
- Product create/read/update/delete
- Image validation and resize to max 600x450 using PHP GD
- User category listing and product display
- AJAX pagination (6 products per page)
- CAPTCHA registration
- User login/logout with password_hash/password_verify

IMPORTANT
- Product image resizing requires PHP GD enabled in XAMPP.
- Ensure uploads folder is writable by Apache. Check the Apache user using:
  ps aux | grep '[h]ttpd'
- This practical PDF asks for registration and login, but does not explicitly ask for cart checkout/payment; the displayed assignment table is a product listing layout.
