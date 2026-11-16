# Task 01 – `IOException`

## Rules (Kurzfassung – this file must be usable stand-alone)

- Working directory – the ONLY place, where anything may be done: `/home/niirrty/Projekte/human/php/Niirrty.IO`
- NEVER push, never touch a remote. Exactly ONE `git commit` at the end of the task.
- Commit only the files this task created/changed: `git add <explicit Pfad>` – never `git add .` / `-A` / `git commit -a`.
- `HANDOFF.md` and the `journal/` folder are NOT part of the repository → never add them.
- Test files belong to the repository and stay committed.
- Execute this task only if the user explicitly says so.

## Precondition

Task 00 done and committed: PHPUnit installed, `phpunit.xml.dist` + `tests/boot.php` exist,
`vendor/bin/phpunit` runs green.

## Target class

`src/IOException.php` (74 lines) – base class of all `Niirrty\IO` exceptions, extends
`Niirrty\NiirrtyException` (from Niirrty.Core, already in `vendor/`).

Public API to cover:

- `__construct( protected string $path, ?string $message = null, string|int $code = 256, ?\Throwable $previous = null )`
  → message is built as `'There was a error with path [' . $path . ']!'` concatenated with
  `static::appendMessage( $message )` (inherited from `NiirrtyException`).
- `public final function getPath(): string`
- inherited: `getMessage()`, `getCode()`, `getPrevious()`, `getPreviousChain()` … (NiirrtyException API – only
  use what exists, check `vendor/niirrty/niirrty.core/src/NiirrtyException.php`)

## Test file / naming

- `tests/Nirrty/Tests/IO/IOExceptionTest.php`
- `namespace Niirrty\Tests\IO;` `class IOExceptionTest extends TestCase`
- Header docblock like the other project files (`@author Ni Irrty <niirrty+code@gmail.com>`,
  `@copyright (c) 2017, Ni Irrty`, `@license MIT`, `@since`, `@version 0.1.0`)
- Test methods `test<Method>…`, `setUp()` calls `parent::setUp()`

## Steps

1. Read `src/IOException.php` and the parent `NiirrtyException` in `vendor/niirrty/niirrty.core/src/`.
2. Write the test file covering at least:
   - `assertInstanceOf( IOException::class, … )` / `assertInstanceOf( NiirrtyException::class, … )`
   - `getPath()` returns the given path (also for paths with spaces/umlauts/backslashes)
   - default message without `$message` (exact string), message with `$message` appended (assert the
     exact resulting string, do not guess – derive it from `appendMessage()`)
   - `$code` as `int` and as `string` (PHP 8.5 allows `getCode()` returning a string code)
   - `$previous` exception handling (`getPrevious()`)
3. Run: `vendor/bin/phpunit tests/Nirrty/Tests/IO/IOExceptionTest.php` → must be green.
4. If a test fails because of a source bug: do NOT change `src/` – document the finding in
   `journal/01-ioexception.md` (section "Findings") and assert the CURRENT behaviour.

## Commit (exactly these files)

```
git add tests/Nirrty/Tests/IO/IOExceptionTest.php
git commit -m "test(IOException): PHPUnit tests for Niirrty\\IO\\IOException"
```

## Findings (task 01, executed 2026-11-15, commit see `git log`)

Environment: PHP 8.5.12, PHPUnit 8.5.57, Niirrty.Core 0.6.4 – `src/` code NOT touched, the tests
assert the CURRENT behaviour.

1. **`$code` as string isn't usable at all**: the constructor documents `string|int` but
   `NiirrtyException::__construct()` forwards the code to the inner `\Exception::__construct()`,
   which rejects every string on PHP 8.5 – also numeric ones – with
   `TypeError: Exception::__construct(): Argument #2 ($code) must be of type int, string given`.
   → `testConstructorCodeString()` + `testConstructorCodeNumericString()` assert that `TypeError`.
   A float code (e.g. `256.5`) leads to a `Niirrty\PhpException` ("Implicit conversion from float
   … to int loses precision") because Niirrty.Core installs a global error handler.
2. **Message appendix**: `NiirrtyException::appendMessage()` uses `empty()`, therefore a `'0'`
   appendix is silently lost, while a blank appendix `' '` is appended → the message ends with
   2 spaces (both asserted as current behaviour).
3. **`getErrorMessage()` (inherited) is a trap**: `NiirrtyException::GetCodeName()` references the
   constant `E_STRICT`, deprecated since PHP 8.4. Nirrty.Core's global `set_error_handler()`
   turns that deprecation notice into a thrown `Niirrty\PhpException`
   ("Constant E_STRICT is deprecated since 8.4, the error level was removed"). Only codes, that
   match one of the `case`s *before* `E_STRICT` (e.g. the default `256` / `\E_USER_ERROR`) work –
   any other code (e.g. `4711`) throws. Same for `__toString()` / `toCustomString()`.
   → `testGetErrorMessage()` (code 256) + `testGetErrorMessageNotUsable()` (code 4711).
4. `getPreviousChain()` is mentioned in the task description but does NOT exist in
   `NiirrtyException` 0.6.4 – available API is: `__construct()`, `__toString()`, `hasPrevious()`,
   `getErrorMessage()`, `toCustomString()`, `GetCodeName()` (static) + protected
   `appendMessage()`, inherited `\Exception` API (`getMessage()`, `getCode()`, `getPrevious()` …).
5. No own `getMessage()`/`getCode()`/`getPrevious()` in `IOException` → the `\Exception` ones are
   used; `getPath()` is declared `final`.

## Test result

`vendor/bin/phpunit tests/Nirrty/Tests/IO/IOExceptionTest.php` → OK (11 tests, 42 assertions)
`vendor/bin/phpunit` (whole suite incl. task 00 placeholder) → OK (12 tests, 44 assertions)
