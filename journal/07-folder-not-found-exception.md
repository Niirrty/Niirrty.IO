# Task 07 – `FolderNotFoundException`

## Rules (Kurzfassung – this file must be usable stand-alone)

- Working directory – the ONLY place, where anything may be done: `/home/niirrty/Projekte/human/php/Niirrty.IO`
- NEVER push, never touch a remote. Exactly ONE `git commit` at the end of the task.
- Commit only the files this task created/changed: `git add <explicit Pfad>` – never `git add .` / `-A` / `git commit -a`.
- `HANDOFF.md` and the `journal/` folder are NOT part of the repository → never add them.
- Test files belong to the repository and stay committed.
- Execute this task only if the user explicitly says so.

## Precondition

Task 00 done/committed (PHPUnit environment, `vendor/bin/phpunit`, `tests/boot.php`, config `phpunit.xml.dist`).
Task 01 (`IOException`) recommended.

## Target class

`src/FolderNotFoundException.php` (56 lines), extends `Niirrty\IO\IOException`.

Single public API:

- `__construct( string $folder, ?string $message = null, string|int $code = \E_USER_ERROR, ?\Throwable $previous = null )`
  → `IOException` message text: `'The Folder not exists.'` + `static::appendMessage( $message )`, path = `$folder`

## Test file / naming

- `tests/Nirrty/Tests/IO/FolderNotFoundExceptionTest.php`
- `namespace Niirrty\Tests\IO; class FolderNotFoundExceptionTest extends TestCase`
- Source-like header docblock, methods `test<Method>…`

## Steps

1. Read `src/FolderNotFoundException.php` + `src/IOException.php` (+ `NiirrtyException` in `vendor/niirrty/niirrty.core/src/`).
2. Cover: `instanceof` chain (FolderNotFoundException / IOException / NiirrtyException, NOT
   FileNotFoundException), `getPath()`, exact default message `'The Folder not exists.'`, appended message,
   default code `\E_USER_ERROR`, `int`/`string` code, `$previous`.
3. Run `vendor/bin/phpunit tests/Nirrty/Tests/IO/FolderNotFoundExceptionTest.php` → green.
4. Never change `src/`! Suspected bugs: assert current behaviour and document under "Findings".

## Findings (task 07, executed 2026-11-16, environment PHP 8.5.12 / PHPUnit 8.5.57 / Niirrty.Core 0.6.4)

1. **Class definition is clean**: ctor uses the explicit nullable form (`?string $message`,
   `?\Throwable $previous`) → NO PHP 8.5 "implicitly nullable parameter" deprecation, so – unlike
   `FileAccessException` (task 02) – the FIRST use of the class throws NOTHING and the first test
   method simply uses the class (kept first to document that).
2. **Own API**: only `__construct( string $folder, ?string $message = null, string|int $code =
   \E_USER_ERROR, ?\Throwable $previous = null )`. No constants, no own property, NO factory method
   and NO own accessor – in contrast to `FolderAccessException` (task 06). Everything else is
   inherited (`getPath()` from `Niirrty\IO\IOException` (final), `getErrorMessage()` from
   `Niirrty\NiirrtyException`, `getMessage()` from the built-in `Exception`).
3. **Message**: `'There was a error with path [<folder>]! The Folder not exists.'` – own fixed text
   `'The Folder not exists.'` (note: "The Folder …" with capital T, but "not exists."; the sibling
   `FileNotFoundException` (task 05) uses `'File not exists.'`).
4. **Folder handling**: the folder is used "as is", never checked/expanded → the class name is a
   `NotFoundException` but the source NEVER touches the filesystem (even an existing folder, e.g.
   `'/'`, and `''` / `' '` are accepted).
5. **Default code**: the constant `\E_USER_ERROR` (= 256), i.e. identical to the literal `256` used
   by e.g. `FileAlreadyExistsException`; `\E_USER_ERROR` itself is NOT deprecated in PHP 8.4/8.5.
6. **Appendix (`appendMessage()` uses `empty()`)**: `null` / `''` → nothing appended; `'0'` →
   appendix LOST; `' '` → appended (message ends with 2 spaces); real appendix separated by exactly
   one space; the own fixed text can never be replaced by the appendix.
7. **`$code` int**: `4711`, `0`, `-1`, `0777` → `511`, `\E_USER_ERROR` → `256`, `\E_USER_WARNING` →
   `512` are kept/normalized by the inner `\Exception`; float `256.5` → `TypeError`
   `Argument #3 ($code) must be of type string|int, float given`; `null` → `TypeError`
   `… must be of type string|int, null given`.
8. **`$code` string**: accepted by the `string|int` signature but rejected by the inner
   `\Exception::__construct()` → `TypeError`
   `Exception::__construct(): Argument #2 ($code) must be of type int, string given`
   (also for the numeric string `'4711'`).
9. **`$folder`**: `null` / `4711` / `[1,2]` → `TypeError` `Argument #1 ($folder) must be of type
   string, … given` (strict typing). Parameter name is `folder` – NOT `path` (as the promoted
   property of `IOException`) and NOT `file` (as in `FileNotFoundException`); a wrong named
   parameter (`file:`) raises a plain `Error` (NOT a `TypeError`): `Unknown named parameter $file`.
10. **`$message`** wrong type → `TypeError` `Argument #2 ($message) must be of type ?string, int given`;
    a numeric string appendix is accepted.
11. **`$previous`**: any `\Throwable` (also a foreign `\RuntimeException`, an `IOException`, a sibling
    exception); explicit `null` = none; wrong type → `TypeError` `Argument #4 ($previous) must be of
    type ?Throwable, string given`. The previous exception only appears in `getErrorMessage()`, never
    in `getMessage()`.
12. **Inheritance / instanceof**: `FolderNotFoundException` → `Niirrty\IO\IOException` →
    `Niirrty\NiirrtyException` → `Exception` / `\Throwable`; it is NOT a `FileNotFoundException`,
    NOT a `FolderAccessException`, NOT a `FileFormatException`, NOT a
    `FileAlreadyExistsException` – and the siblings are not it, even when the inner `IOException`
    (→ `getPath()`) is equal.
13. **`getErrorMessage()` prefixes** (`Niirrty\ErrNoConstants`): 256 → `ERROR(256):`,
    `\E_USER_WARNING` → `WARNING(512):`, `\E_USER_NOTICE` → `NOTICE(1024):`,
    `\E_USER_DEPRECATED` → `DEPRECATED(16384):`, `\E_WARNING` (2) → `WARNING(2):`.
14. **`getErrorMessage()` is NOT usable for foreign codes**: `4711`, `0` and `-1` throw a
    `Niirrty\PhpException` `Constant E_STRICT is deprecated since 8.4, the error level was removed`
    (`NiirrtyException::GetCodeName()` evaluates the removed constant `E_STRICT`).
15. **`getErrorMessage( bool $appendPreviousByNewline = false )`**: previous message is appended
    behind the own one, separated by `' '` (default) or `'\n'` (true); a foreign `\Exception`
    contributes its `getMessage()` only (no `ERROR(…)` prefix).
16. **`__toString()`** (= `toCustomString()`): contains the fully qualified class name + location
    (`FolderNotFoundExceptionTest.php[line]`) + the message, but NOT the `ERROR(256)` prefix.
17. **Reflection**: ctor 4 params (`folder` mandatory, not promoted; `message` default `null`,
    `code` default 256, `previous` default `null`), 1 required, not variadic/static/final, declared
    in `src/FolderNotFoundException.php`; own methods = `['__construct']` only; the class declares NO
    own property (the `$path` property belongs to `Niirrty\IO\IOException` via the promoted ctor
    parameter, the inherited `getProperties()` list additionally contains `_trace` of
    `Niirrty\NiirrtyException` and the built-in `message`, `code`, `file`, `line`).
18. **Reflection API in PHP 8.5**: `ReflectionClass::getOwnMethods()` with the named parameter
    `private:` is not used – own methods are filtered via `getDeclaringClass()`; return types are
    read with `(string) ReflectionMethod::getReturnType()` (see also task 06).

## Commit (exactly these files)

```
git add tests/Nirrty/Tests/IO/FolderNotFoundExceptionTest.php
git commit -m "test(FolderNotFoundException): PHPUnit tests for Niirrty\\IO\\FolderNotFoundException"
```
