# CloverMerge

PHP utility to merge two or more clover files into a single document.

Intended to be used in a build pipeline to merge clover output from multiple testing frameworks.

I spent a weekend writting this so you don't have to.

# Usage

```
usage: clover-merge.php [<options>] [<args>]

OPTIONS
  --help, -?     Display this help.
  --output, -o   output file path

ARGUMENTS
  paths   input file paths
```

# Example

```bash
$ php clover-merge.php -o combined.xml input1.xml input2.xml
```