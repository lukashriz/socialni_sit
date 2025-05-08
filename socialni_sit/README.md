Sociální webová aplikace (PHP)

Plně vybavená sociální webová aplikace postavená v PHP a MySQL. Obsahuje registraci uživatelů, přihlášení, odhlášení, uživatelské profily, avatary, nástěnku příspěvků, komentáře, lajky, vyhledávání a responzivní design.

Funkce
Registrace uživatele (s avatarem a bio)
Přihlášení/odhlášení uživatele
Uživatelské profily (zobrazení/úprava bio a avataru)
Přidávání, úprava a mazání vlastních příspěvků
Komentování příspěvků
Lajkování příspěvků
Vyhledávání příspěvků a uživatelů
Responzivní/mobilní rozhraní
Nastavení
Naimportujte db.sql do svého MySQL serveru pro vytvoření databáze a tabulek.
Upravte config.php podle svých údajů k databázi.
Umístěte soubory projektu do webového serveru (např. htdocs pro XAMPP).
Ujistěte se, že složka uploads/ je zapisovatelná webovým serverem pro nahrávání avatarů.
Otevřete v prohlížeči index.php.

Struktura souborů
index.php – Úvodní stránka (přesměrování na nástěnku nebo přihlášení)
register.php – Registrace uživatele
login.php – Přihlášení uživatele
logout.php – Odhlášení uživatele
feed.php – Hlavní nástěnka (přidávání/zobrazení/úprava/mazání příspěvků, komentáře, lajky, vyhledávání)
profile.php – Uživatelský profil (zobrazení/úprava)
config.php – Připojení k databázi
db.sql – Struktura databáze
uploads/ – Obrázky avatarů uživatelů

Požadavky
PHP 7.4+
MySQL
Webový server (Apache, Nginx, atd.)
