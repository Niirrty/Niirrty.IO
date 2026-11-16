# Task 08 – `Path`

## Rules (Kurzfassung – this file must be usable stand-alone)

- Working directory – the ONLY place, where anything may be done: `/home/niirrty/Projekte/human/php/Niirrty.IO`
- NEVER push, never touch a remote. Exactly ONE `git commit` at the end of the task.
- Commit only the files this task created/changed: `git add <explicit Pfad>` – never `git add .` / `-A` / `git commit -a`.
- `HANDOFF.md` and the `journal/` folder are NOT part of the repository → never add them.
- Test files belong to the repository and stay committed.
- Execute this task only if the user explicitly says so.

## Precondition

Task 00 done/committed: PHPUnit installed, `phpunit.xml.dist`, `tests/boot.php`, runner `vendor/bin/phpunit`.

## Target class

`src/Path.php` (266 lines) – static helper class, private ctor (not instantiable).

Public API (all static unless noted):

- `public static string $NoFolderSeparator` – uninitialized at declaration, lazily set on the first
  `Normalize()` call (static state! shared across all tests in one phpunit process)
- `Combine( string $basePath, string $next1, string $next2 = null ): string`
- `CombineMany( string $basePath, string ...$pathParts ): string`
- `Normalize( string $path ): string`
- `IsAbsolute( string $path, bool $dependToOS = true ): bool`
- `Unixize( string $path ): string`
- `RemoveWorkingDir( string $path ): string` (depends on `getcwd()` → phpunit runs in project root)
- `GetPathinfo( string $path, int|string $infoType = null ): array|string`

Environment facts: Linux, `DIRECTORY_SEPARATOR === '/'`, `\IS_WIN === false` (constant from `src/required-code.php`,
loaded by `vendor/autoload.php`).

## Test file / naming

- `tests/Nirrty/Tests/IO/PathTest.php`
- `namespace Niirrty\Tests\IO; class PathTest extends TestCase`
- Source-like header docblock, methods `test<Method>…`

## Steps

1. Read the complete `src/Path.php` first; derive expectations from the code (trim chars `"\r\n\t /\"`,
   separator handling, `…/\./…` replacement in `Normalize`, the regex in `GetPathinfo`).
2. Cover every public method with at least a happy path + edge cases:
   - `Combine`: 2 and 3 parts, leading/trailing separators, empty `$next2`, `null` `$next2`
   - `CombineMany`: 0, 1, many parts; result equal to `Combine` for the 2/3-part cases
   - `Normalize`: already normalized, foreign separator (`\`), `./` inside path, trailing separators,
     empty string; assert `Path::$NoFolderSeparator` value after the first call (Linux → `\`)
   - `IsAbsolute`: `/abs`, relative, empty, `C:\…`, `\\unc`, with `dependToOS` `true` AND `false`
   - `Unixize`: backslashes → slashes, already unix, empty
   - `RemoveWorkingDir`: path inside `getcwd()`, outside path, using `__DIR__` of the test file
   - `GetPathinfo`: full path, no extension, only extension, `null`/`'dirname'`/`'extension'`/
     `PATHINFO_*` constants, unicode names
3. Static class: verify that direct instantiation is not possible (document the actual PHP error type).
4. Static state caution: because `Path::$NoFolderSeparator` is cached, do not write tests that depend
   on it being "unset"; if a test changes it, restore it in `tearDown()`.
5. Run `vendor/bin/phpunit tests/Nirrty/Tests/IO/PathTest.php` → green.
6. Never change `src/`! Any deviation from the documented behaviour: assert the actual result and
   document it in a "Findings" section in this file.

## Commit (exactly these files)

```
git add tests/Nirrty/Tests/IO/PathTest.php
git commit -m "test(Path): PHPUnit tests for Niirrty\\IO\\Path"
```

## Test findings (1. PHP 8.5.12, PHPUnit 8.5.57, project root as cwd)

File `tests/Nirrty/Tests/IO/PathTest.php`, 30 test methods, 208 assertions, all green.
Full project suite: 205 tests, 1296 assertions, green.

1. First class contact: the class definition throws a `Niirrty\PhpException`
   (`Niirrty\NiirrtyException` <- `Niirrty\PhpException`, code `8192`,
   message `Niirrty\IO\Path::Combine(): Implicitly marking parameter $next2 as nullable is deprecated, the explicit nullable type must be used instead`).
   The notice is emitted only once per process, so it is consumed by the FIRST
   test method; every later class contact works normal.
2. Because the class definition is interrupted at that first contact, the class-level
   property `NoFolderSeparator` (declared without default value) stays uninitialized
   - reading `Path::$NoFolderSeparator` then is a plain `\Error`
   ("Typed static property ... must not be accessed before initialization").
   It only gets its value (`'\'` on Linux) as soon as `Normalize()` has been called
   successfully at least once. `ReflectionClass::getStaticProperties()` is empty
   until then.
3. `Path::NoFolderSeparator` (class constant syntax) is a plain `\Error`
   ("Undefined constant Niirrty\IO\Path::NoFolderSeparator"); reflection reports the
   member as property (`hasProperty()` true, `hasConstant()` false).
4. `new Path()` throws a plain `\Error` ("Call to private Niirrty\IO\Path::__construct()
   from scope Niirrty\Tests\IO\PathTest"), `isInstantiable()` is false.
5. `Combine()`: joins with `DIRECTORY_SEPARATOR`, borders trimmed with `"\r\n\t /\"`
   only (so whitespaces are kept, a trailing separator of the last part stays).
   A 3rd part that is `''` or `'0'` is ignored (`empty()` check!) - unlike
   `CombineMany()` which keeps a `'0'` part. More than 3 parameters are ignored
   silently; with 0/1 parameter an `\ArgumentCountError` is thrown. Non-string args
   are a `\TypeError` (both files use `strict_types`).
6. `CombineMany()`: a base path alone is returned unchanged, empty parts result in
   doubled/trailing separators, only the last part is trimmed on its left border.
7. `Normalize()`: replaces `\` with `/` on Linux, removes a trailing separator only,
   NOT the leading ones (`\\a\` -> `//a`), `'/./'` -> `'/'` but `'/././'` only once,
   `'../'` is NOT resolved, `'C:\Temp\/sub'` -> `'C:/Temp//sub'` (doubled separators).
8. `Unixize()`: a simple `str_replace` of every backslash, so a trailing `\` becomes
   a trailing `/` and UNC paths keep doubled separators (`\\srv\share` -> `//srv/share`).
9. `IsAbsolute()`: with the default `dependToOS = true` a Unix path only, so `C:\Temp`
   and `\\srv\share` are NOT absolute on Linux. With `dependToOS = false` the 2nd char
   is checked, so a 1-char path definition throws a `Niirrty\PhpException`
   ("Uninitialized string offset 1").
10. `RemoveWorkingDir()`: works on the Unixized values, so the cwd prefix must be
   followed by a separator char; a Windows path definition is never affected.
11. `GetPathinfo()`: returns the full info array for `null`, an unknown string and an
   unknown int info type; multibyte safe (`äöü.txt`); a leading dot filename is
    reported as extension without filename part (`/a/.phpunit` -> ext `phpunit`,
   filename ``); backslash paths are not split on Linux.
12. Reflection facts used: `getName()` `Niirrty\IO\Path`, `getShortName()` `Path`,
    `getNamespaceName()` `Niirrty\IO`, `getFileName()` `.../src/Path.php`,
    `getMethods()` count 8 (private ctor included), `getProperties()` count 1,
    `getConstants()` empty, `isFinal()` false. Not available in this runtime:
    `ReflectionClass::getNameShort()` (renamed), `getStaticProperty*` variants were not
    needed.
