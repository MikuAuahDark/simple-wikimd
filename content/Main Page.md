Main Page
=====

This is a simple PHP-based simple wiki written in [Markdown](https://daringfireball.net/projects/markdown/). This
simple wiki supports category system and additional metadata using YAML markers.

Under the hood, it uses these libraries:
* [Parsedown](https://github.com/erusev/parsedown) - Markdown to HTML in pure PHP. Licensed under MIT.
* [Spyc](https://github.com/mustangostang/spyc) - YAML to array in pure PHP. Licensed under MIT.

Features
-----

Currently available features:
* Wiki page
* Categories
* Metadata
* ToC (default HTML template does not expose ToC)

Currently not available but planned:
* Redirect
* Baking to HTML using CLI
* Footnotes

Syntax Extensions
-----

In addition to CommonMark, this wiki additionally support these syntax:
* `[[]]` to refer to another wiki page. The `a` HTML tag will have attribute `does-not-exist` if the page doesn't exist.

License
-----

MIT
