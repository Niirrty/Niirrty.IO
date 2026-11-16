# Task 05 – `FileNotFoundException`

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

`src/FileNotFoundException.php` (55 lines), extends `Niirrty\IO\IOException`.

Single public API:

- `__construct( string $file, ?string $message = null, string|int $code = \E_USER_ERROR, ?\Throwable $previous = null )`
  → `IOException` message text: `'File not exists.'` + `static::appendMessage( $message )`, path = `$file`

## Test file / naming

- `tests/Nirrty/Tests/IO/FileNotFoundExceptionTest.php`
- `namespace Niirrty\Tests\IO; class FileNotFoundExceptionTest extends TestCase`
- Source-like header docblock, methods `test<Method>…`

## Steps

1. Read `src/FileNotFoundException.php` + `src/IOException.php` (+ `NiirrtyException` in `vendor/niirrty/niirrty.core/src/`).
2. Cover: `instanceof` chain (FileNotFoundException / IOException / NiirrtyException, and NOT
   FileAlreadyExistsException etc.), `getPath()`, exact default message `'File not exists.'`,
   appended message variant, default code `\E_USER_ERROR`, custom `int`/`string` code, `$previous`.
3. Run `vendor/bin/phpunit tests/Nirrty/Tests/IO/FileNotFoundExceptionTest.php` → green.
4. Never change `src/`! Suspected bugs: assert current behaviour and document under "Findings".

## Commit (exactly these files)

```
git add tests/Nirrty/Tests/IO/FileNotFoundExceptionTest.php
git commit -m "test(FileNotFoundException): PHPUnit tests for Niirrty\\IO\\FileNotFoundException"
```

---

## Findings (task 05, executed 2026-11-15, environment PHP 8.5.12 / PHPUnit 8.5.57 / Niirrty.Core 0.6.4)

Source in `src/` NOT touched – tests document CURRENT behaviour.
Test file: `tests/Nirrty/Tests/IO/FileNotFoundExceptionTest.php`
(24 test methods, 163 assertions, standalone + full suite green).

1. **NO deprecation at the class definition** (unlike task 02!): own `__construct()`
   declares the optional parameters explicitly nullable (`?string $message`,
   `?\Throwable $previous`, `string|int $code`) → first use `new FileNotFoundException('/f.txt')`
   neither raises the PHP 8.5 "implicitly nullable" notice nor a `Niirrty\PhpException`.
2. **Inheritance**: `FileNotFoundException` → `Niirrty\IO\IOException` →
   `Niirrty\NiirrtyException` → `Exception`; NO own `instanceof`-relevant sibling is
   a superclass (`instanceof` of `FileAlreadyExistsException`/`FileFormatException`/
   `FolderAccessException`/`FolderNotFoundException` → `false`). Own methods only
   `__construct()` + `getErrorMessage()` (inherited); `getPath()` is inherited and
   `getFinalMethodsName()` reports it as `final`.
3. **Message**: own string `'The file does not exists.'` is passed as `$msg` to
   `IOException::__construct()`, which prepends own text → observable
   `'There was a error with path [' . $file . ']! The file does not exists.'`
   (`getMessage()` of the own inner string is never observable; `$e->message`
   (inherited dynamic property) is NOT in sync → still own string only).
4. **`$file` verbatim** via `getPath()` (incl. spaces/umlauts/backslashes); empty
   string accepted (`path '[]'`); `null` → `TypeError` Argument #1 `$file`.
5. **Code parameter**: `string|int` accepted (`256` default = `\E_USER_ERROR`, `'256'`,
   `'4711'`, `-1`, `0`); `getInnerExceptionCode()` mirrors own code; `getInnerExceptionType()`
   → `'Niirrty\IO\FileNotFoundException'`. `null` → TypeError, `4711.0` (float) → TypeError
   `Argument #3 ($code) must be of type string|int` → in contrast to task 04 the constructor
   signature rejects it before `\Exception::__construct()` is entered; a non-numeric string
   (`'WRONG_TYPE'`) however is accepted by the ctor and rejected by the INNER
   `\Exception::__construct()` (`Argument #2 ($code) must be of type int, string given`).
6. **Previous**: `?\Throwable` → `null`/omitted → `hasPreviousException() === false`
   (no auto-created `Niirrty\IO\IOException` previous, in contrast to e.g.
   `Niirrty\IO\FileAccessException`); any throwable accepted (`Exception`, `RuntimeException`,
   own `Niirrty\IO\IOException`, own `Niirrty\NiirrtyException`, `Niirrty\NiirrtyException`,
   `Niirrty\PhpException`, `Error`, `FatalError`); non-throwable (`'not a throwable'`) →
   `TypeError` Argument #4. `getPreviousChainSize()` = 1 (own) / 2 (with previous).
7. **Appendix (`appendMessage()` uses `empty()`)**: `null`/`''` → nothing; `'0'` → appendix
   LOST (message ends with `.`); `' '` → appended → message ends with 2 spaces; real
   appendix separated by exactly one space behind own fixed text.
8. **`getMessage()` / `getMessageParts()`**: single string `array[0] === getMessage()` if
   previous only set via ctor (the previous then is NOT part of `getMessage()`, only of
   `getErrorMessage()`); 2 entries after `setPreviousException()`; `msgPrecedence`
   `['message', '', '']`; `getHeadline()` = message; `getErrorMessage()` ends with own message.
9. **`getErrorMessage()`**: `'ERROR(256): There was a error with path [ … ]! File not exists.'`
   (+ `' | The file does not exists.'` = own `getMessage()`), prefix from
   `Niirrty\ErrNoConstants` (`E_USER_WARNING` → `WARNING(512)`, `E_DEPRECATED` →
   `DEPRECATED(16384)`, `E_NOTICE` → `NOTICE(1024)`); previous message prepended (`previous . '. ' . own`),
   `Error` without message → leading `': '`; multiline previous → `getErrorMessageLines()` = 2.
   Custom code `4711` → `Niirrty\PhpException('Constant E_STRICT is deprecated since 8.4,
   the error level was removed')` (`getErrNoConstantsName(4711)` → `''`); custom code `'4711'`
   as STRING → prefix `'4711'` (constant lookup by value only for ints). `msgParts[0]` = prefix,
   `getErrorMessageHeadline()` = full `getErrorMessage()`, `getMessagePartsLines()` = 1 entry.
10. **`__toString()`** contains class name/file/line + message parts, but NOT the
    `ERROR(256)` prefix.
11. **Reflection**: own ctor params `file` (mandatory, own default `''` for the deprecated
    implicit call!), `message` (`null`), `code` (`256` = `\E_USER_ERROR`), `previous` (`null`);
    own `getErrorMessage()` without own parameters; `getPath()` 1 own param
    (`ignoreIfNotExists` default `false`); class file `src/FileNotFoundException.php`,
    inherited methods in `IOException.php` / `NiirrtyException.php` (absolute vendor path);
    no constants, no static properties.
