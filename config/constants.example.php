<?php
define('APP_NAME',       'ByteClass');
define('APP_TAGLINE',    'Learn · Build · Grow');
define('APP_URL',        'http://localhost/ByteClass');
define('API_URL',        'http://localhost/ByteClass/api');
define('APP_ENV',        'development');

// JWT
define('JWT_SECRET', 'CHANGE_THIS_TO_64_CHAR_RANDOM_STRING');
define('JWT_EXPIRY',     3600);

// Auth rules
define('MAX_LOGIN_ATTEMPTS',    5);
define('MAX_DEVICES',           2);
define('AUTO_LOGOUT_MINUTES',   5);
define('TWO_FA_ENABLED',        false);
define('TWO_FA_EXPIRY_MINS',    10);
define('TWO_FA_MAX_ATTEMPTS',   3);
define('PASSWORD_RESET_HRS',    24);

// Quiz rules
define('LESSON_PASSMARK',       75);
define('MODULE_PASSMARK',       85);
define('QUIZ_ATTEMPTS_PER_DAY', 3);

// Media
define('VIDEO_MAX_SECS',        900);
define('MAX_UPLOAD_MB',         40);

// Paths
define('ROOT_PATH',      dirname(__DIR__) . '/ByteClass');
define('UPLOAD_PATH',    __DIR__ . '/../uploads/');
define('CERT_PATH',      __DIR__ . '/../uploads/certificates/');
define('PAYSLIP_PATH',   __DIR__ . '/../uploads/payslips/');
define('CONTRACT_PATH',  __DIR__ . '/../uploads/contracts/');
define('PHOTO_PATH',     __DIR__ . '/../uploads/profile_photos/');
define('VIDEO_PATH',     __DIR__ . '/../uploads/videos/');
define('DOC_PATH',       __DIR__ . '/../uploads/documents/');
define('LOG_PATH',       __DIR__ . '/../logs/');

// Community
define('COMMUNITY_MSG_TTL_HRS', 24);
define('TICKET_AUTOCLOSE_DAYS', 7);
define('RETRY_BANNER_HRS',      48);
define('CERT_QR_ENABLED',       true);
define('TICKET_PREFIX',         'BC');

// Colors (for PDF generation)
define('COLOR_PRIMARY',   '#4F46E5');
define('COLOR_SECONDARY', '#06B6D4');
define('COLOR_ACCENT',    '#F59E0B');

