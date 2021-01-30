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

Releases are currently done manually, however we will shortly be automating this using Github actions.

## Development environment

We currently do not have a dedicated local development environment for the project. We recommend getting setup with [LocalWP](https://localwp.com/) or a docker/vagrant based virtual environment like [Lando](https://lando.dev/) or [VVV2](https://varyingvagrantvagrants.org/).

## Code standards

We strictly follow the [WPCS](https://github.com/WordPress/WordPress-Coding-Standards) guidelines for our coding standards.
