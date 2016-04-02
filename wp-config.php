<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the
 * installation. You don't have to use the web site, you can
 * copy this file to "wp-config.php" and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * MySQL settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://codex.wordpress.org/Editing_wp-config.php
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('DB_NAME', 'wordpress');

/** MySQL database username */
define('DB_USER', 'root');

/** MySQL database password */
define('DB_PASSWORD', '');

/** MySQL hostname */
define('DB_HOST', 'localhost');

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8mb4');

/** The Database Collate type. Don't change this if in doubt. */
define('DB_COLLATE', '');

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         '5aern)gW.>Q%$cel>z`lRL{3>e#S(#F{/4jtB8FwQMBd47qP]/;xI1N1bKGMJ^mX');
define('SECURE_AUTH_KEY',  'gg|x`?ph$!f[XHM-T3,u0]/^6q|]=%r+YZn,%qehD-]Khq4V1|X1UK@#VAySx- j');
define('LOGGED_IN_KEY',    'vrZ?-a@[fLbs_~jxF(Cy.Fc(U{@s.,-*NnOlc01T4m-B{o7A8s+7$~fOJ#Wv|p07');
define('NONCE_KEY',        'n|L[X2@?8mX`|^f{L?|Qa%+[F;OhhT1YDkmAGOC-Ha TqncAv}AO>&c|^543u=*w');
define('AUTH_SALT',        '.@hF?XX!*%#5,mPh]%-HE_Frr:Vf4]y9s1g94i:a`SMZLtd?v(rum(5P9MB:DyCF');
define('SECURE_AUTH_SALT', 'uS^<P11F|--0C;Es`p~+mQ% v2B|2Z?*|NMC@w+1+U_F;pror%.h_!|%e{  yP=b');
define('LOGGED_IN_SALT',   'Jj=_I.+n6i4;iV?$S^@kKD)i~Mm_(?-?RAiY%N_`}=,+jL[_-^(:mJG|?Wzwfgj*');
define('NONCE_SALT',       'D!fW-2FB7%+Xl|@a%6.3d0yLVe]}-g2*d{P?x-*K0KkO$POCi*,s04b,M{:d)]hj');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'rl16_3UdBZqEx_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the Codex.
 *
 * @link https://codex.wordpress.org/Debugging_in_WordPress
 */
define('WP_DEBUG', false);

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');

