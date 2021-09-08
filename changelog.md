# Version 0.5.0 `2021-08-13`

* **`Niirrty\IO\*Exception`**: Change type of `$code` constructor parameter from `int` to `string|int` for all defined Exceptions
* Add method: `Niirrty\IO\Path::CombineMany( string $basePath, string ..$pathParts )` because the existing `Combine`
  method can only handle 2 path parts.