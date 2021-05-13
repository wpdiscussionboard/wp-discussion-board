# Contributing to WP Discussion Board

Thank you for contributing to the WP Discussion Board plugin! This short guide details the procedures and workflows for development and ongoing support of our plugin.

## Ways to Contribute

There are many ways in which you can help us make WP Discussion Board the best forum plugin for WordPress. Here are some of the ways:

### Report Bugs

Lets us know if you run into a bug when using the plugin. You can report a bug by submitting a [new Github Issue](https://github.com/wpdiscussionboard/wp-discussion-board/issues/new) on the project. Be sure to check the existing issues to make sure your bug is not already accounted for. Also, add as much detail as possible to the issue such as WordPress version, hosting environment, steps to reproduce and screenshots.

### Suggest features

Send us all your ideas for the plugin, we'd love to hear of them! Features are managed by Github issues. Create a [new issue](https://github.com/wpdiscussionboard/wp-discussion-board/issues/new) to suggest a feature.

### Code

We're more than happy to accept bug fixes and even feature enhancements to the plugin. Code submissions are handled via Github pull requests. You can [submit a PR](https://github.com/wpdiscussionboard/wp-discussion-board/compare) via the repository.

## Development workflow

Our workflow is very basic.

- Always branch off of the `master` branch
- Name your branch after the Github issue i.e. `feature/1` or `fix/1` where 1 is the ID of the Github issue. We use the `feature` prefix for enhancements and `fix` for bugs
- Submit a PR to the repository against the `develop` branch when your feature or fix is ready to be reviewed
- Your PR will then be reviewed and changes requested where applicable
- Once everything checks out, the feature/fix will be pushed with the next weekly release

## Release workflow

To prepare and deploy a release, follow these steps.

- Create a new branch off of master with the following naming convention `release/x.x.x` where `x.x.x` is the next version number
- Merge all branches to be deployed to this branch
- Increment the version number in [readme.txt](readme.txt), [config.php](includes/config/config.php) and [wp-discussion-board.php](wp-discussion-board.php)
- Make sure all code docblocs reference the new version in their `@since` parameters
- Merge the final release branch in to the `master` branch
- Sync the changes to the SVN repo, tag and commit. This is a manual step for now

## Development environment

The plugin project repository ships with a very simple Docker based environment which can be used for local development.

Make sure you have [Docker](https://www.docker.com/) installed for this development environment to work.

To get it setup, firstly run `composer install` in the root of the repository.

Then simply run `composer up` to start the local development environment.

You can now access the local environment at [http://localhost:8092](http://localhost:8092)

If you prefer to use your own custom local development environment, we recommend [LocalWP](https://localwp.com/) or a docker/vagrant based virtual environment like [Lando](https://lando.dev/) or [VVV2](https://varyingvagrantvagrants.org/).

## Code standards

We strictly follow the [WPCS](https://github.com/WordPress/WordPress-Coding-Standards) guidelines for our coding standards.

To lint your code, make sure Composer is up to date by running `composer update`. And then run `composer lint` to run WPCS.
