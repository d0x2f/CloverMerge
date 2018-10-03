# CloverMerge

[![Build Status](https://travis-ci.org/d0x2f/CloverMerge.svg?branch=master)](https://travis-ci.org/d0x2f/CloverMerge)
[![Coverage Status](https://coveralls.io/repos/github/d0x2f/CloverMerge/badge.svg?branch=master)](https://coveralls.io/github/d0x2f/CloverMerge?branch=master)
[![Maintainability](https://api.codeclimate.com/v1/badges/7ff86933961de446d594/maintainability)](https://codeclimate.com/github/d0x2f/CloverMerge/maintainability)

PHP utility to merge two or more clover files into a single document.

Intended to be used in a build pipeline to merge clover output from multiple testing frameworks.

I spent a weekend writing this so you don't have to.

# Install

## As a Composer Dependancy

```bash
$ composer require d0x2f/clover-merge
```

## As a Docker Image

```bash
$ docker pull d0x2f/clover-merge
```

# Usage

```
usage: clover-merge [<options>] [<args>]

OPTIONS
  --help, -?     Display this help.
  --mode, -m     merge mode: additive, exclusive or inclusive (default)
  --output, -o   output file path

ARGUMENTS
  paths   input file paths
```

## Modes

* Additive - Lines must be present in the first input file for them to be included.
* Exclusive - Lines must be present in all input files for them to be included.
* inclusive - Lines from all files are included.

# Example

## As a Composer Dependancy

```bash
$ ./vendor/bin/clover-merge -o combined.xml input1.xml input2.xml
```

## As a Docker Image

```bash
$ docker run --rm -v (pwd)/build:/build clover-merge -o /build/combined.xml /build/input1.xml /build/input2.xml
```