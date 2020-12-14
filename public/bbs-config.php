<?php

// *************** PLEASE CONFIGURE! ***************
// Path to Calibre library - should be a path to some external location that is accessible by PHP
define('BBS_CALIBRE_PATH', '');


// *************** CONFIGURE WHEN NECESSARY ***************

// Base path if the app can't live at the "/" root
// define('BBS_BASE_PATH', '');

// Define the log level
// define('BBS_LOG_LEVEL', 'info');

// Uncomment to enable debug mode for finding problems: enables more log messages and error displays
// Note: The setting in BBS_LOG_LEVEL will be overwritten if debug mode is enabled.
define('BBS_DEBUG_MODE', true);

// User session max. idle time in seconds
// define('BBS_IDLE_TIME', 3600);
define('BBS_IDLE_TIME', 1440);