# CloverMerge

[![Build Status](https://travis-ci.org/d0x2f/CloverMerge.svg?branch=master)](https://travis-ci.org/d0x2f/CloverMerge)
[![Coverage Status](https://coveralls.io/repos/github/d0x2f/CloverMerge/badge.svg?branch=master)](https://coveralls.io/github/d0x2f/CloverMerge?branch=master)

PHP utility to merge two or more clover files into a single document.

Intended to be used in a build pipeline to merge clover output from multiple testing frameworks.

I spent a weekend writting this so you don't have to.

# Install

```
$ composer require d0x2f/clover-merge
```

# Usage

```
usage: clover-merge [<options>] [<args>]

OPTIONS
  --help, -?     Display this help.
  --output, -o   output file path

ARGUMENTS
  paths   input file paths
```

# Example

```bash
$ ./vendor/bin/clover-merge -o combined.xml input1.xml input2.xml
```