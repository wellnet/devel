Devel comes with a modern and useful test development environment.

Local Development
===========
1. Clone devel `git clone --branch 8.x-3.x https://git.drupalcode.org/project/devel.git`
1. `cd devel`
1. Assemble a codebase (i.e. get Drupal core). `composer install`. Your source tree now looks like : ![Folder tree](/icons/folders.png)
1. Install a testing site `composer si`
1. Configure a web server to serve devel's `/web` directory as docroot. __Either__ of these works fine:
    1. `composer runserver`
	1. Setup Apache/Nginx/Other. A virtal host will work fine. Any domain name works.

Gitlab.com
============
A [mirror](https://gitlab.com/help/user/project/repository/repository_mirroring.md) of Devel exists at [gitlab.com](https://gitlab.com/drupalspoons/devel/). Note that this is different from the private Gitlab instance at [git.drupalcode.org](https://git.drupalcode.org/). [All commits to all branches are automatically tested](https://gitlab.com/drupalspoons/devel/pipelines). We benefit from Gitlab's outstanding [CI functionality](https://docs.gitlab.com/ee/ci/introduction/index.html). DrupalCI is not used.

Build Matrix
===============
Use the excellent [Run Pipeline](https://gitlab.com/drupalspoons/devel/pipelines/new)
page to test any branch with alternate versions of Drupal core, PHP version, or
DB driver. The recognized variables and default values are:

| Name                   | Value   |
|------------------------|---------|
| DRUPAL_CORE_CONSTRAINT | ^8.8    |
| PHP_TAG                | 7.3-dev |
| DB_DRIVER              | sqlite  |
| MARIADB_TAG            | 10.2    |
| POSTGRES_TAG           | 10.5    |

DB_DRIVER recognizes `sqlite`, `mysql`, or `pgsql`.

Implementation
==========
- [composer.json](https://gitlab.com/drupalspoons/devel/-/blob/add-gitlab-pipeline/composer.json). After a `composer install`, a /web directory is present, containing the specified Drupal core, and a symlinked copy of web/modules/devel. The drupal/core-recommended, drupal/core-dev, and drupal/core-composer-scaffold packages are happily used. A few helpful bash snippits are shared as Composer scripts (e.g. `composer si`).
- [phpunit.xml.dist](https://gitlab.com/weitzman/drupalspoons/-/blob/add-gitlab-pipeline/phpunit.xml.dist). A lightly customized copy of Drupal core's phpunit.xml. Copy and rename to phpunit.xml to customize locally.
- [docker-compose.yml](https://gitlab.com/drupalspoons/devel/-/blob/add-gitlab-pipeline/docker-compose.yml). Containers suitable for testing on the full build matrix. Inspired by [docker4drupal](https://github.com/wodby/docker4drupal). Add a docker-compose.override.yml to customize locally.

Run Tests
==========
- Run all tests - `composer unit`. See the `<scripts>` section of composer.json to learn what that does.
- You may append arguments and options to Composer scripts: `composer unit -- --filter testDevelGenerateUsers`
- Run a suite: `composer unit -- --testsuite functional`
- Skip slow tests: `composer unit -- --exclude-group slow`
- Use a different URL: `SIMPLETEST_BASE_URL=http://example.com composer unit`
- docker-compose.yml is used by gitlab.com for test running. Its also available for local development if you wish. More info at [wodby/php](https://github.com/wodby/php).

Philosophy and Roadmap
==========
We encourage folks to click around https://gitlab.com/drupalspoons/devel/pipelines, and admire its
feature set and user experience. Projects can add services (e.g. Solr, Elasticsearch),
customize their build matrix, and so much more. We hope more contrib projects move
to gitlab.com. Hopefully this movement informs the evolution of DrupalCI.

The DrupalCI service managed by the Drupal Association provides unique features,
like d.o. account integration, [issue integration](https://www.drupal.org/project/project_issue_file_test),
[issue credits](https://www.drupal.org/drupalorg/blog/a-guide-to-issue-credits-and-the-drupal.org-marketplace), [matrix builds](https://www.drupal.org/node/17345/qa), etc. A few features exist only in DrupalCI but thats OK; feature parity
is a non-goal. A mostly vanilla Gitlab CI experience (e.g Devel) would be cheaper
for the DA to maintain than bespoke [issue workspaces](https://www.drupal.org/project/drupalorg/issues/2488266)
and bespoke CI. DrupalCI increased Drupal's code quality immeasurably over the past
decade. But the DevIOps revolution happenned and it is time to substantially reduce scope on DrupalCI IMO.

We plan to make gitlab.com our canonical repo for code, MRs, and CI. We will push back to git.drupalcode.org in order to keep
[Security Team](https://www.drupal.org/security) coverage and packages.drupal.org integration.
Devel would love to migrate back to a fully featured git.drupalcode.org, in the future.

Colophon
===========
By [Moshe Weitzman](https://weitzman.github.io/), standing on the shoulders of giants:
- [DrupalCI](https://www.drupal.org/project/drupalci) by Mixologic
- [Drupal TI](https://github.com/LionsAd/drupal_ti/) by Fabian Franz.
- [drupal-tests](https://github.com/deviantintegral/drupal_tests) by Andrew Berry
- [drupal_circleci](https://github.com/integratedexperts/drupal_circleci/blob/8.x/.circleci/build.sh) by Alex Skrypnyk
- [Drush](https://github.com/drush-ops/drush/blob/master/tests/README.md), for its introduction of the SUT (Site Under Test).

