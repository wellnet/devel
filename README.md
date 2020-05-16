[[_TOC_]]

#### Introduction

Devel module contains helper functions and pages for Drupal developers and
inquisitive admins:

 - A block for quickly accessing devel pages
 - A block for masquerading as other users (useful for testing)
 - A mail-system class which redirects outbound email to files
 - Drush commands such as fn-hook, fn-event, ...
 - *Webprofiler*. Adds a debug bar at bottom of all pages with tons of useful
 information like a query list, cache hit/miss data, memory profiling, page
 speed, php info, session info, etc.
 - *Devel Generate*. Bulk creates nodes, users, comment, terms for development. Has
 Drush integration.

This module is safe to use on a production site. Just be sure to only grant
_access development information_ permission to developers.

#### Recommended Modules

- [Devel Images Provider](http://drupal.org/project/devel_image_provider) allows to configure external providers for images.

#### Maintainers

See https://gitlab.com/groups/drupaladmins/devel/-/group_members.
