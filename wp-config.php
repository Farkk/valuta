<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the web site, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * Localized language
 * * ABSPATH
 *
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'local' );

/** Database username */
define( 'DB_USER', 'root' );

/** Database password */
define( 'DB_PASSWORD', 'root' );

/** Database hostname */
define( 'DB_HOST', 'localhost' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8' );

/** The database collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**#@+
 * Authentication unique keys and salts.
 *
 * Change these to different unique phrases! You can generate these using
 * the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}.
 *
 * You can change these at any point in time to invalidate all existing cookies.
 * This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',          'hKJkc0p$byaPFV/#m8{Sv]Y<%^1&@ap^>n%CB>1YlTdCw!jT3;F>#D8G$!>1|cSb' );
define( 'SECURE_AUTH_KEY',   'eK)A]$/W=a83{axjv!m@X]=D[B$NAGqWC%!<MxK/H=1l:Flg:gTHSs:}CTwg8Zj}' );
define( 'LOGGED_IN_KEY',     'g~qnspa3yLCp%WH)xl>;pE;Sf&5*?c2nk&$dB.|~#]^i0Vwc+HHU24Bt4MLl9Mt*' );
define( 'NONCE_KEY',         'RvuIOr!)WgNB!(efZ:eSkZT?:PyyJQyU|yC|zr/YiO_QWLS`t{Ubd_0hT-pzP)_W' );
define( 'AUTH_SALT',         'Ri=?}`-Es2_G37<yK(}Is} &l!lJ$& grd@Q+S!9fL.(}]hF0]K[A=*wNi8PrA3.' );
define( 'SECURE_AUTH_SALT',  '6hRLFxkuHTK^? hxcj)NQB}@3Q`KDa![jxfsXK51<Srk:bLOP>ZatxB7tz)r(pK]' );
define( 'LOGGED_IN_SALT',    'BofLuOsFcQB~^V]1vA1b3^;i(@[)2mCMy)$Q3M/O2L&4RnTHK7%KbEInN#Y|y,Fu' );
define( 'NONCE_SALT',        'Hvl(W4YJfJ~TfqQWC,-Zs|g-?_Eg=?VdzAJP`}:kz2>4-D:2h uO-[qzBS2N>GZ;' );
define( 'WP_CACHE_KEY_SALT', '$Yf](uW3[uqo}+,slm]d;T8`|4cOLizE?XNtWUeDq|j7L~4lbjO9go=OLu1.IW?z' );


/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp_';


/* Add any custom values between this line and the "stop editing" line. */



/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the documentation.
 *
 * @link https://wordpress.org/support/article/debugging-in-wordpress/
 */
if ( ! defined( 'WP_DEBUG' ) ) {
	define( 'WP_DEBUG', false );
}

define( 'WP_ENVIRONMENT_TYPE', 'local' );
/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
