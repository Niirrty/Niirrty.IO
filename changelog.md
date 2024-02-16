# Changelog

## Version 0.6.1 `2024-02-16`

* Change dependency of Niirrty.Core v0.5 to 0.6
* Set 2nd parameter `$access` of `FileAccessException` constructor to be readonly
* Set 2nd parameter `$access` of `FolderAccessException` constructor to be readonly
* Fix some documentation typos

## Version 0.5.0 `2021-08-13`

* **`Niirrty\IO\*Exception`**: Change type of `$code` constructor parameter from `int` to `string|int` for all defined Exceptions
* Add method: `Niirrty\IO\Path::CombineMany( string $basePath, string ..$pathParts )` because the existing `Combine`
  method can only handle 2 path parts.