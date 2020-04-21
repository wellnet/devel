# Devel module

## Contents

 - Introduction
 - Requirements
 - Included Modules and Features
 - Recommended Modules
 - Testing
 - Maintainers

## Introduction

Devel module contains helper functions and pages for Drupal developers and
inquisitive admins:

 - A block for quickly accessing devel pages
 - A block for masquerading as other users (useful for testing)
 - A mail-system class which redirects outbound email to files
 - Drush commands such as fn-hook, fn-event, ...
 - Docs at https://api.drupal.org/api/devel
 - more

This module is safe to use on a production site. Just be sure to only grant
'access development information' permission to developers.

 - For a full description of the module visit:
   https://www.drupal.org/project/devel

 - To submit bug reports and feature suggestions, or to track changes visit:
   https://www.drupal.org/project/issues/devel


## Requirements

This module requires no modules outside of Drupal core.


## Included Modules and Features

Webprofiler - Adds a debug bar at bottom of all pages with tons of useful
information like a query list, cache hit/miss data, memory profiling, page
speed, php info, session info, etc.

Devel Generate - Bulk creates nodes, users, comment, terms for development. Has
Drush integration.

Drush Unit Testing - See develDrushTest.php for an example of unit testing of
the Drush integration. This uses Drush's own test framework, based on PHPUnit.
To run the tests, use run-tests-drush.sh. You may pass in any arguments that
are valid for `phpunit`.


## Recommended Modules

Devel Generate Extensions - Devel Images Provider allows to configure external
providers for images.

 - http://drupal.org/project/devel_image_provider


## Testing

 - [Learn more about Devel's test environment](README.tests.md).


## Author/Maintainers

 - Moshe Weitzman (moshe weitzman) https://www.drupal.org/u/moshe-weitzman
 - Hans Salvisberg (salvis) https://www.drupal.org/u/salvis
 - Pedro Cambra (pcambra) https://www.drupal.org/u/pcambra
 - Juan Pablo Novillo (Juampy NR) https://www.drupal.org/u/juampynr
 - Luca Lusso (lussoluca) https://www.drupal.org/u/lussoluca
 - Marco (willzyx) https://www.drupal.org/u/willzyx
 - Jonathan Smith (jonathan1055) https://www.drupal.org/u/jonathan1055
