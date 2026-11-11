Sound Web - Music & Video website (HTML5 / CSS3 / Bootstrap 5 / PHP / MySQL)

INSTALL (XAMPP)
1. Is folder ko C:\xampp\htdocs\ mein copy karo (folder ka naam: syedsoundbar).
2. XAMPP mein Apache aur MySQL Start karo.
3. phpMyAdmin > Import > songvideo.sql (ya khali tables ke liye database_tables_only.sql)  (database ka naam: songvideo; file khud database bana deti hai).
   (Import na bhi karo to config.php pehli baar khud database "songvideo" bana kar songvideo.sql import kar deta hai.)
4. Browser mein kholo:  http://localhost/syedsoundbar/

FLOW
- Pehle Sound Web welcome page aata hai (user/welcome.php: Login + Sign Up).
- Sign Up -> account banao -> Login -> User Panel khulta hai.
- Admin: Login page par admin email + password dalo -> seedha Admin Panel khulta hai.
  (admin/login.php se bhi login ho sakta hai: http://localhost/syedsoundbar/admin/)
- Logout karne par wapas landing page aata hai.

DEFAULT ADMIN (songvideo.sql se)
  Email    : syed@gmail.com
  Password : syed778899
  (Login ke baad Admin Panel se / database mein password badal lena.)

CONTENT
- Pehle ke songs aur videos songvideo.sql mein maujood hain (uploads/ folder ki files ke saath).
  Naye Admin Panel > Add Music / Video se upload karo, fauran user panel mein nazar aayega.
- Categories (Pop, Rock, ...) Admin > Categories se manage hoti hain.

DATABASE TABLES
  users, categories, media (music + video), reviews, playlist_items, playlists, playlist_media, subscribers

BADI VIDEO FILES
php.ini (XAMPP > Apache > Config): upload_max_filesize = 200M, post_max_size = 256M, phir Apache restart.
Project ke root mein .htaccess mein ye limits already hain. check.php se limits dekh sakte ho.

SETTINGS
Brand name / logo / footer email / phone: config.php ($SITE). Logo file: assets/images/soundweb-logo.png Database login: config.php ke top par.
