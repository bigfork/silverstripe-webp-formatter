# Silverstripe Webp Formatter

Adds a `.Webp()` function to templates to allow converting images to webp.

This is a module version of [this proof of concept](https://github.com/silverstripe/silverstripe-assets/pull/411) by
[Maxime Rainville](https://github.com/maxime-rainville).

To use this, your image driver (typically GD or Imagick) must be compiled with support for webp.

## Usage

```
{$Image.Webp.ScaleWidth(150)}

or

{$Image.ScaleWidth(150).Webp}
```
