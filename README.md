salcode/wp-cli-find-content
===========================

Find the post where the string appears. Searches post_content and post meta.



Quick links: [Using](#using) | [Installing](#installing) | [Contributing](#contributing) | [Sponsors](#sponsors) | [Support](#support)

## Using

~~~
wp find-content <query>... [--format=<format>] [--fields=<fields>]
~~~

**Options**

    <query>...
        The query to find in the database content.

    [--regex]
        Runs the search as a regular expression (without delimiters). The case-sensitivity of the search is based on the collation of the database table (typically this is case-insensitive).

    [--format=<format>]
        Render output in a particular format.
        ---
        default: table
        options:
          - table
          - csv
          - json
          - count
          - yaml
        ---

    [--fields=<fields>]
        Limit the output to specific fields. Defauls to ID,permalink,location.
        ---
        available fields:
          - Column names from wp_posts
          - Column names from wp_postmeta
          - permalink (for the associated post)
          - location ('content' or 'postmeta')
          - query (the string being queried)
        ---

**EXAMPLES**

    # Find instances of gravityform 7's shortcode.
    $ wp find-content '[gravityform id="7"'
    +----+-----------------------------+----------+
    | ID | permalink                   | location |
    +---+------------------------------+----------+
    | 1  | http://wp.test/hello-world  | content  |
    | 8  | http://wp.test/display-meta | postmeta |
    +----+-----------------------------+----------+

    # Find instances of "your first post".
    $ wp find-content 'your first post'
    +----+----------------------------+----------+
    | ID | permalink                  | location |
    +---+-----------------------------+----------+
    | 1  | http://wp.test/hello-world | content  |
    +----+----------------------------+----------+

    # Find instances of regular expression "y[[:alpha:]]{2}rz?[[:space:]]first[[:space:]]*pos[ert]"
    # Development Sponsored by Gamajo https://gamajo.com/
    # Note: MySQL uses some less common regex syntax, see
    # https://dev.mysql.com/doc/refman/5.7/en/regexp.html#regexp-syntax
    $ wp find-content "y[[:alpha:]]{2}rz?[[:space:]]first[[:space:]]*pos[ert]" --regex
    +----+----------------------------+----------+
    | ID | permalink                  | location |
    +---+-----------------------------+----------+
    | 1  | http://wp.test/hello-world | content  |
    +----+----------------------------+----------+

    # Find instances of 'Description of this example post.'
    $ wp find-content 'Description of this example post.' --fields=ID,permalink,location,meta_key
    +----+------------------------------+----------+----------------------+
    | ID | permalink                    | location | meta_key             |
    +----+------------------------------+----------+----------------------+
    | 4  | http://wp.test/example-post/ | postmeta | _genesis_description |
    +----+------------------------------+----------+----------------------+

    # Find instances of gravityform 7 or 34's shortcode.
    $ wp find-content 'gravityform id="7"' 'gravityform id="34"'
    +----+-----------------------------+----------+
    | ID | permalink                   | location |
    +----+-----------------------------+----------+
    | 1  | http://wp.test/hello-world  | content  |
    | 3  | http://wp.test/signup       | content  |
    | 8  | http://wp.test/display-meta | postmeta |
    +----+-----------------------------+----------+

    # Find instances of gravityform 7 or 34's shortcode and modify fields.
    $ wp find-content 'gravityform id="7"' 'gravityform id="34"' --fields=ID,location,query
    +----+----------+---------------------+
    | ID | location | query               |
    +----+----------+---------------------+
    | 1  | content  | gravityform id="7"  |
    | 3  | content  | gravityform id="34" |
    | 8  | postmeta | gravityform id="7"  |
    +----+----------+---------------------+

    # Find instances of gravityform 7's shortcode format as yaml.
    $ wp find-content '[gravityform id="7"' --format=yaml
    ---
    -
      ID: "1"
      permalink: http://wp.test/hello-world
      location: content
    -
      ID: "8"
      permalink: http://wp.test/display-meta
      location: postmeta

## Installing

Installing this package requires WP-CLI v1.3.0 or greater. Update to the latest stable release with `wp cli update`.

Once you've done so, you can install this package with:

    wp package install git@github.com:salcode/wp-cli-find-content.git

## Contributing

We appreciate you taking the initiative to contribute to this project.

Contributing isn’t limited to just code. We encourage you to contribute in the way that best fits your abilities, by writing tutorials, giving a demo at your local meetup, helping other users with their support questions, or revising our documentation.

For a more thorough introduction, [check out WP-CLI's guide to contributing](https://make.wordpress.org/cli/handbook/contributing/). This package follows those policy and guidelines.

### Reporting a bug

Think you’ve found a bug? We’d love for you to help us get it fixed.

Before you create a new issue, you should [search existing issues](https://github.com/salcode/wp-cli-find-content/issues?q=label%3Abug%20) to see if there’s an existing resolution to it, or if it’s already been fixed in a newer version.

Once you’ve done a bit of searching and discovered there isn’t an open or fixed issue for your bug, please [create a new issue](https://github.com/salcode/wp-cli-find-content/issues/new). Include as much detail as you can, and clear steps to reproduce if possible. For more guidance, [review our bug report documentation](https://make.wordpress.org/cli/handbook/bug-reports/).

### Creating a pull request

Want to contribute a new feature? Please first [open a new issue](https://github.com/salcode/wp-cli-find-content/issues/new) to discuss whether the feature is a good fit for the project.

Once you've decided to commit the time to seeing your pull request through, [please follow our guidelines for creating a pull request](https://make.wordpress.org/cli/handbook/pull-requests/) to make sure it's a pleasant experience. See "[Setting up](https://make.wordpress.org/cli/handbook/pull-requests/#setting-up)" for details specific to working on this package locally.

## Sponsors

These companies or individuals have sponsored this project or a specific
feature of the project.

- [Sal Ferrarello](@salcode)
- [Iron Code Studio](@ironcodestudio)
- [Gamajo](@gamajo)

## Support

Github issues aren't for general support questions, but there are other venues you can try: https://wp-cli.org/#support


*This README.md is generated dynamically from the project's codebase using `wp scaffold package-readme` ([doc](https://github.com/wp-cli/scaffold-package-command#wp-scaffold-package-readme)). To suggest changes, please submit a pull request against the corresponding part of the codebase.*
