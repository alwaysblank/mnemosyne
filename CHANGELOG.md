# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/en/1.0.0/)
and this project adheres to [Semantic Versioning](http://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [0.1.5] - 2017-10-02

Added the `mns_burrow()` function to address different use cases where `mns_dig()` 
isn't appropriate.

  - Added `mns_burrow()` and documentation.

## [0.1.4]

Added capability to change the filename and path. Also makes the defaults files 
location theme-agnostic: It will search for the file instead of only looking in a 
hard-coded location.

  - Filters for file name, file path, and file search object.
  - Added file search method to look for file if it isn't found at the default 
    location.
  - Split out functionality into more limited methods.
  - Refactored certain behaviors in anticipation of multi-file import 
    functionality (see: #3).

## [0.1.3]

Namespace changed.

  - Namespace changed from `Murmur` -> `AlwaysBlank` to reflect move to public, 
    open-source development.

## [0.1.2]

Added the ability to dig into values.

  - `mns_dig()` gives the user the ability to return a specific value from a 
    queried array.

## [0.1.1]

Cleanup and improvement release.

  - Improved inline documentation.
  - Core class now runs better checks on keys and values.
  - Error reporting is now cleaner and easier to use.
  - Some documentation cleanup.

## [0.1.0]

Initial developement release.

  - Implements the basic, core functionality in a class.
  - Added conveninence functions (`__m()` & `__me()` ).