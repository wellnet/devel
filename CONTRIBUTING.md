Devel comes with a modern and useful development environment.

Local Development
===========
1. Clone devel `git clone --branch 8.x-3.x https://git.drupalcode.org/project/devel.git`
1. `cd devel`
1. Assemble a codebase (i.e. get Drupal core). `composer install`. Your source tree now looks like: ![Folder tree](/icons/folders.png)
1. Install a testing site `composer si`
1. Configure a web server to serve devel's `/web` directory as docroot. __Any__ of these works fine:
    1. `composer runserver`
	1. Setup Apache/Nginx/Other. A virtual host will work fine. Any domain name works.
	1. docker-compose.yml is available for local development if you wish. More info at [wodby/php](https://github.com/wodby/php).
1. [CI docs](https://gitlab.com/drupalspoons/webmasters/-/blob/master/docs/ci.md) give info on running tests.

DrupalSpoons
==========
https://gitlab.com/drupalspoons/devel is our workplace for code, MRs, and CI. We push back to git.drupalcode.org in order to keep
[Security Team](https://www.drupal.org/security) coverage and packages.drupal.org integration.
Devel would love to migrate back to a fully featured git.drupalcode.org, in the future.
