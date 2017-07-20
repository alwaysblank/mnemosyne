## v0.1.0

Initial developement release.

  * Implements the basic, core functionality in a class.
  * Added conveninence functions (`__m()` & `__me()` ).


## v0.1.1

Cleanup and improvement release.

  * Improved inline documentation.
  * Core class now runs better checks on keys and values.
  * Error reporting is now cleaner and easier to use.


## v0.1.2

Added the ability to dig into values.

  * `mns_dig()` gives the user the ability to return a specific value from a queried array.
  * Some documentation cleanup.

## v0.1.3

Namespace changed.

  * Namespace changed from `Murmur` -> `AlwaysBlank` to reflect move to public, open-source development.

## v0.1.4

Added capability to change the filename and path. Also makes the defaults files location theme-agnostic: It will search for the file instead of only looking in a hard-coded location.

  * Filters for file name, file path, and file search object.
  * Added file search method to look for file if it isn't found at the default location.
  * Split out functionality into more limited methods.
  * Refactored certain behaviors in anticipation of multi-file import functionality (see: #3).