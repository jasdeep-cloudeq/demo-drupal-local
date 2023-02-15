CONTENTS OF THIS FILE
---------------------

 * Introduction
 * Recommended Modules
 * Installation
 * Settings
 * Reporting


INTRODUCTION
------------

New Relic is an excellent tool for improving and monitoring your Drupal
installation.

 * For information and the ability to sign up for a free trial of the New Relic
   Pro service, visit:
   http://newrelic.com

 * For instructions on how to install the New Relic PHP extension and the
   reporting daemon, visit:
   https://docs.newrelic.com/docs/apm/agents/php-agent/installation/php-agent-installation-overview

Note: This module was formerly titled "New Relic RPM Integration". The term
"RPM" for New Relic is dated.


INSTALLATION
------------

 * Install as you would normally install a contributed Drupal module.
   Immediately after the installation is complete, the module will check
   for PHP functions made available by the New Relic PHP agent. If these
   functions are not available, the module will automatically de-install itself.

 * New Relic must already be installed and running before the functionality
   provided by this module can be used. If you need to install New Relic on your
   own servers, see: https://docs.newrelic.com/docs/apm/agents/php-agent/installation/php-agent-installation-overview
   for further information.


SETTINGS
---------

 * The page at admin/config/development/new-relic-rpm provides basic module
   settings.

 * Cron - You have 3 options regarding cron.
   1    Instruct APM to ignore cron tasks completely and not report
        on them.
   2    Mark cron tasks as background tasks.
   3    Track cron tasks the same as any other URL in your application.

 * Drush - You have 3 options regarding drush.
   1    Instruct New Relic to ignore drush executions completely and not report
        on them.
   2    Mark drush executions as background tasks.
   3    Track drush exeuctions the same as any other URL in your application.

 * Module Install/Uninstall - APM allows you to create 'deployments', which are
   essentially markers where you tell New Relic you have made changes to your
   site. New Relic will then allow you to compare a variety of metrics before
   and after the deployment. Enabling this setting will automatically create a
   deployment each time you enable or disable a module. A deployment changelog
   entry is made noting which modules you enabled and/or disabled.

 * Ignore URLs - Enter a list of URLs you wish New Relic to ignore and not
   report on. Use standard Drupal URL listing syntax.

 * Background URLs - Enter a list of URLs you wish New Relic to consider as
   background tasks. Use standard Drupal URL listing syntax.

 * Exclusive URLs - Entering a list of URLs you wish New Relic to only track,
   ignoring all other URLs.
   If URLs are entered, Ignore URLs and Background URLs are ignored.
   Your selection for cron functionality is unaffected by this setting.

 * API Key - Enter your New Relic API key here. This is necessary for reporting
   and deployment tracking functionality.

 * Forward watchdog messages - Configure which watchdog severities are reported
   to New Relic as errors.
   The New Relic error logging does not provide severities, so everything will
   be reported as an error.

 * Override exception handler - If enabled, the module replaces the default
   exception handler, which allows reporting the correct backtrace of uncatched
   exceptions.

 * Disable AutoRUM - Disables the automatic browser tracking inserted by a
   New Relic APM transaction.


MAINTAINERS
-----------

Current maintainers:
 * Chris Charlton - https://www.drupal.org/u/chris-charlton
 * Nathan ter Bogt - https://www.drupal.org/u/nterbogt
 * James Gilliland - https://www.drupal.org/u/neclimdul
 * Joseph Flatt - https://www.drupal.org/u/hosef
 * Sascha Grossenbacher - https://www.drupal.org/u/berdir
