<?php
/**
 * La configuració base del WordPress
 *
 * L'script de creació wp-config.php usarà aquest fitxer durant la
 * instaŀlació. No heu d'utilitzar la web, podeu copiar aquest fitxer al
 * fitxer "wp-config.php" i emplenar els valors.
 *
 * Aquest fitxer contè els següents paràmetres:
 *
 * * La configuració de MySQL
 * * Les claus secretes
 * * El prefix de les taules de la base de dades
 * * L'ABSPATH
 *
 * @link https://codex.wordpress.org/Editing_wp-config.php
 *
 * @package WordPress
 */

// ** Configuració de MySQL - Podeu obtenir aquesta informació de l'amfitrió de la web ** //
/** El nom de la base de dades del WordPress */
define('DB_NAME', 'elnomdelabasededades');

/** El nom d'usuari de la base de dades MySQL */
define('DB_USER', 'username_here');

/** La contrasenya de la base de dades MySQL */
define('DB_PASSWORD', 'password_here');

/** Nom de l'amfitrió de MySQL */
define('DB_HOST', 'localhost');

/** Joc de caràcters usat en crear taules a la base de dades. */
define('DB_CHARSET', 'utf8');

/** Tipus d'ordenació en la base de dades. No ho canvieu si tens cap dubte. */
define('DB_COLLATE', '');

/**#@+
 * Claus úniques d'autentificació.
 *
 * Canvieu-les per frases úniques diferents!
 * Les podeu generar usant el {@link http://api.wordpress.org/secret-key/1.1/salt/ servei de claus secretes de WordPress.org}
 * Podeu canviar-les en qualsevol moment per invalidar totes les galetes existents. Això forçarà tots els usuaris a iniciar sessió de nou.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         'escriu una frase única teva aquí');
define('SECURE_AUTH_KEY',  'escriu una frase única teva aquí');
define('LOGGED_IN_KEY',    'escriu una frase única teva aquí');
define('NONCE_KEY',        'escriu una frase única teva aquí');
define('AUTH_SALT',        'escriu una frase única teva aquí');
define('SECURE_AUTH_SALT', 'escriu una frase única teva aquí');
define('LOGGED_IN_SALT',   'escriu una frase única teva aquí');
define('NONCE_SALT',       'escriu una frase única teva aquí');

/**#@-*/

/**
 * Prefix de taules per a la base de dades del WordPress.
 *
 * Podeu tenir múltiples instaŀlacions en una única base de dades usant prefixos
 * diferents. Només xifres, lletres i subratllats!
 */
$table_prefix  = 'wp_';

/**
 * Per a desenvolupadors: WordPress en mode depuració.
 *
 * Canvieu això si voleu que es mostren els avisos durant el desenvolupament.
 * És molt recomanable que les extensions i el desenvolupadors de temes facien servir WP_DEBUG
 * al seus entorns de desenvolupament.
 *
 * Per informació sobre altres constants que es poden utilitzar per depurar,
 * visiteu el còdex.
 *
 * @link https://codex.wordpress.org/Debugging_in_WordPress
 */
define('WP_DEBUG', false);

/* Això és tot, prou d'editar - que publiqueu de gust! */

/** Ruta absoluta del directori del Wordpress. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Assigna les variables del WordPress vars i fitxers inclosos. */
require_once(ABSPATH . 'wp-settings.php');
