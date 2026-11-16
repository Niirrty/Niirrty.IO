# Task 03 – `FileAlreadyExistsException`

## Rules (Kurzfassung – this file must be usable stand-alone)

- Working directory – the ONLY place, where anything may be done: `/home/niirrty/Projekte/human/php/Niirrty.IO`
- NEVER push, never touch a remote. Exactly ONE `git commit` at the end of the task.
- Commit only the files this task created/changed: `git add <explicit Pfad>` – never `git add .` / `-A` / `git commit -a`.
- `HANDOFF.md` and the `journal/` folder are NOT part of the repository → never add them.
- Test files belong to the repository and stay committed.
- Execute this task only if the user explicitly says so.

## Precondition

Task 00 done/committed (PHPUnit environment). Task 01 (`IOException`) recommended.

## Target class

`src/FileAlreadyExistsException.php` (55 lines), extends `Niirrty\IO\IOException`.

Single public API:

- `__construct( string $file, ?string $message = null, string|int $code = \E_USER_ERROR, ?\Throwable $previous = null )`
  → delegates to `IOException` with text `'The File does already exist.'` + `static::appendMessage( $message )`
  and the file as `$path`.

## Test file / naming

- `tests/Nirrty/Tests/IO/FileAlreadyExistsExceptionTest.php`
- `namespace Niirrty\Tests\IO; class FileAlreadyExistsExceptionTest extends TestCase`
- Source-like header docblock, methods `test<Method>…`

## Steps

1. Read `src/FileAlreadyExistsException.php` + `src/IOException.php`.
2. Cover at least:
   - `assertInstanceOf` FileAlreadyExistsException / IOException / NiirrtyException
   - `getPath()` returns the given file path (incl. umlauts, spaces, backslash, empty string)
   - default message without `$message` (exact string `'The File does already exist.'`)
   - message with `$message` → exact resulting string (derive from `NiirrtyException::appendMessage()`)
   - default code (`\E_USER_ERROR` = 256) and custom code as `int` and as `string`
   - `$previous` exception → `getPrevious()`
3. Run `vendor/bin/phpunit tests/Nirrty/Tests/IO/FileAlreadyExistsExceptionTest.php` (green).
4. Source bugs: do not fix, assert actual behaviour + document in "Findings".

## Commit (exactly these files)

```
git add tests/Nirrty/Tests/IO/FileAlreadyExistsExceptionTest.php
git commit -m "test(FileAlreadyExistsException): PHPUnit tests for Niirrty\\IO\\FileAlreadyExistsException"
```

## Findings (task 03, executed 2026-11-15, environment PHP 8.5.12 / PHPUnit 8.5.57 / Niirrty.Core 0.6.4)

The source in `src/` was NOT touched, the tests assert the CURRENT behaviour.

1. **NO deprecation notice at the class definition** (unlike task 02!): the own
   `__construct()` declares the optional parameters explicitly nullable
   (`?string $message`, `string|int $code`, `?\Throwable $previous`), so using the
   class the very first time in a process neither raises the PHP 8.5
   "Implicitly marking parameter ... as nullable is deprecated" notice nor a
   `Niirrty\PhpException`. Verified by probing `new FileAlreadyExistsException( ... )`
   as first statement → no notice, instance created normally. Nevertheless the test
   class starts with `testFirstUseOfClassDefinition()` to document that fact
   (`processIsolation="false"` → the class is used first by that test method).
2. **The full message is NOT only the own fixed text**: `'The File does already exist.'`
   is handed over to `Niirrty\IO\IOException::__construct()` as ITS `$message` parameter,
   so the `IOException` prepends own text (see finding 1 of task 01). The observable
   message always is
   `'There was a error with path [' . $file . ']! The File does already exist.'`
   (`getMessage()` of the own, inner string is never observable, and this class has
   neither factory methods nor own getters).
3. **Appendix behaviour identical to task 01** (`NiirrtyException::appendMessage()` uses
   `empty()`): `$message = null` / `''` → nothing appended; `'0'` → appendix LOST;
   `' '` → appended → message ends with 2 spaces; a real appendix is separated by exactly
   one space and goes behind the own fixed text.
4. **`$file` is used verbatim**: `getPath()` (inherited from `IOException`) returns the
   given string "as is", incl. spaces / umlauts / backslashes, and the empty string is
   accepted (`getPath() === ''`, message `There was a error with path []! ...`).
   The `$file` parameter is declared `string` (no default!) → `null` rejected:
   `TypeError: ...::__construct(): Argument #1 ($file) must be of type string, null given`.
   In contrast to `IOException::__construct()`'s promoted `$path`, `$file` is NOT a promoted
   property (`ReflectionMethod::getParameters()[0]->isPromoted() === false`).
5. **`code` as string is rejected by the inner `\Exception`** (see task 01):
   `TypeError: Exception::__construct(): Argument #2 ($code) must be of type int, string given`,
   also for numeric strings (`'4711'`); `getCode()` never returns a string.
   A `float` code is rejected one level earlier by the parameter type
   `string|int`: `TypeError: ...::__construct(): Argument #3 ($code) must be of type
   string|int, float given`.
6. **`$code` default is `\E_USER_ERROR` = 256** → `getCode() === 256`; `0` is accepted and
   kept; custom `int` codes (512, 4711) are kept unchanged and do not influence file/message.
7. **`$previous`** is stored and returned by the inherited `getPrevious()`; `hasPrevious()`
   true. Any `\Throwable` is accepted (plain `\Exception`, `NiirrtyException`, another
   `FileAlreadyExistsException`, `\RuntimeException`); a non-Throwable is rejected by the
   parameter type: `TypeError: ...::__construct(): Argument #4 ($previous) must be of type
   ?Throwable, string given`. The previous exception is NOT part of `getMessage()`, only
   `getErrorMessage()` appends it (separator: one space, or `\n` with the
   `?bool appendPreviousByNewline` parameter).
8. **Inherited `getErrorMessage()` / `__toString()` behaviour = task 01**: with a known
   error level the result is `'ERROR(256): There was a error with path [...]! The File does
   already exist.'`, with `E_USER_WARNING`/`E_USER_NOTICE`/`E_USER_DEPRECATED` the
   `WARNING(512)`/`NOTICE(1024)`/`DEPRECATED(16384)` prefix. For unknown codes (4711)
   `NiirrtyException::GetCodeName()` evaluates the deprecated constant `E_STRICT`, what
   Niirrty.Core's global handler turns into a `Niirrty\PhpException`
   (`Constant E_STRICT is deprecated since 8.4, the error level was removed`).
   `__toString()` (= `toCustomString()`) contains FQCN, location and message, but NOT the
   `ERROR(256)` code name.
9. **Class hierarchy / sibling isolation**: `get_parent_class()` is exactly
   `Niirrty\IO\IOException`; instances are `instanceof` IOException / NiirrtyException /
   `\Exception` / `\Throwable`, but an `IOException`, a `NiirrtyException`, a
   `FileNotFoundException` (sibling, message `File not exists.`) and the subclasses are
   NOT instances of each other.
10. **Reflection**: class declares exactly one own method `__construct` with 4 parameters
   `file`, `message`, `code`, `previous` (only `file` mandatory), no own constants,
   not abstract, parent `Niirrty\IO\IOException`.

## Test result

`vendor/bin/phpunit tests/Nirrty/Tests/IO/FileAlreadyExistsExceptionTest.php` → OK (20 tests, 113 assertions)
`vendor/bin/phpunit` (whole suite incl. task 00 + 01 + 02 + 03) → OK (58 tests, 293 assertions)

## Commit

```
git add tests/Nirrty/Tests/IO/FileAlreadyExistsExceptionTest.php
git commit -m "test(FileAlreadyExistsException): PHPUnit tests for Niirrty\\IO\\FileAlreadyExistsException"
```

=> `719fbc4` (1 file, +575) – nothing else added, no remote touched.
