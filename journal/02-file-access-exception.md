# Task 02 – `FileAccessException`

## Rules (Kurzfassung – this file must be usable stand-alone)

- Working directory – the ONLY place, where anything may be done: `/home/niirrty/Projekte/human/php/Niirrty.IO`
- NEVER push, never touch a remote. Exactly ONE `git commit` at the end of the task.
- Commit only the files this task created/changed: `git add <explicit Pfad>` – never `git add .` / `-A` / `git commit -a`.
- `HANDOFF.md` and the `journal/` folder are NOT part of the repository → never add them.
- Test files belong to the repository and stay committed.
- Execute this task only if the user explicitly says so.

## Precondition

Task 00 done/committed (PHPUnit + `phpunit.xml.dist` + `tests/boot.php`). Task 01 (`IOException`)
recommended but not required.

## Target class

`src/FileAccessException.php` (157 lines), extends `Niirrty\IO\IOException`.

Public API:

- `public final const ACCESS_READ = 'read'`, `ACCESS_WRITE = 'write'`, `ACCESS_READWRITE = 'read and write'`,
  `ACCESS_CREATE = 'create'`, `ACCESS_DELETE = 'delete'`
- `__construct( string $file, private readonly string $access = self::ACCESS_READ, ?string $message = null,
  string|int $code = 256, ?\Throwable $previous = null )`
  → text: `\sprintf( 'Could not %s file.', $access )` + `static::appendMessage( $message )`
- `public final function getAccessType(): string`
- `public static function Read( string $file, string $message = null, int $code = \E_USER_ERROR, ?\Throwable $previous = null ): FileAccessException`
- `public static function Write( …same signature… ): FileAccessException`

## Test file / naming

- `tests/Nirrty/Tests/IO/FileAccessExceptionTest.php`
- `namespace Niirrty\Tests\IO; class FileAccessExceptionTest extends TestCase`
- Source-like header docblock, methods `test<Method>…`

## Steps

1. Read `src/FileAccessException.php` (plus `src/IOException.php` for the message pattern).
2. Cover at least:
   - all 5 `ACCESS_*` constants (values + that they exist)
   - ctor with default access → `getAccessType() === ACCESS_READ`, message exactly `'Could not read file.'` (+ appended message variant)
   - ctor with each explicit access constant → message exactly `'Could not <access> file.'`
   - readonly property: `$access` may not be modified from outside (document actual behaviour)
   - `getAccessType()` for each factory call
   - `FileAccessException::Read()` / `Write()` factory methods incl. `$message`, `int` code, string code, `$previous`
   - `instanceof` chain: FileAccessException / IOException / NiirrtyException
3. Note: `Read()`/`Write()` declare `string $message = null` (soft-nullable) – verify and document behaviour.
4. Run `vendor/bin/phpunit tests/Nirrty/Tests/IO/FileAccessExceptionTest.php` (green).
5. If a failure is a source bug: do NOT change `src/`, assert current behaviour, add a "Findings" section
   in this journal file.

## Commit (exactly these files)

```
git add tests/Nirrty/Tests/IO/FileAccessExceptionTest.php
git commit -m "test(FileAccessException): PHPUnit tests for Niirrty\\IO\\FileAccessException"
```

## Findings (task 02, executed 2026-11-15, environment PHP 8.5.12 / PHPUnit 8.5.57 / Niirrty.Core 0.6.4)

The source in `src/` was NOT touched, the tests assert the CURRENT behaviour.

1. **First use of the class throws**: `Read()` and `Write()` declare their `$message`
   parameter soft-nullable (`string $message = null`, see the task description). On PHP 8.5
   implicitly-nullable parameters are deprecated, and the notice is emitted at the class
   definition (deferred class compilation) – so on the FIRST use of the class in a process
   Niirrty.Core's global `set_error_handler()` turns that notice into a thrown
   `Niirrty\PhpException`:
   `Niirrty\IO\FileAccessException::Read(): Implicitly marking parameter $message as nullable is deprecated, the explicit nullable type must be used instead`.
   After that the class is defined completely and usable, the notice occurs only once per
   process (`processIsolation="false"`!). Therefore `testImplicitNullableParameter()` must
   stay the FIRST test method of the test class, all other methods use the class normally.
   => This is the reason why the file uses `declare( strict_types = 1 )` and why the method
   order in this test class is relevant.
2. **The final message is NOT only `'Could not <access> file.'`**: that string is passed to
   `Niirrty\IO\IOException::__construct()` as its `$message` parameter, so the `IOException`
   prepends own message. The real, full message is
   `'There was a error with path [' . $file . ']! Could not ' . $access . ' file.'`, an
   optional appendix gets appended behind it separated by a single space (see task 01:
   appendix `'0'` is lost because `appendMessage()` uses `empty()`, appendix `' '` is appended
   → message ends with 2 spaces). The message of `FileAccessException` alone is never
   observable – only with `new FileAccessException(...)` one cannot access the inner string.
3. **`$access` is a promoted, read-only, private property** (`private readonly string $access
   = self::ACCESS_READ`):
   - `ReflectionProperty`: `isPrivate()` / `isReadOnly()` / `isPromoted()` are true,
     `hasDefaultValue()` false.
   - Reading OR writing it from outside throws `Error: Cannot access private property
     Niirrty\IO\FileAccessException::$access` – the "private" check comes first, the read-only
     violation can never be observed from outside (only inside the class, what is not
     testable without changing `src/`).
   - The property is NOT nullable: its default value is the string constant `self::ACCESS_READ`,
     but promoted property parameters may not be implicitly nullable, so passing `NULL`
     explicitly as access type throws
     `TypeError: Niirrty\IO\FileAccessException::__construct(): Argument #2 ($access) must be of type string, null given`.
4. **No validation of `$access` at all**: arbitrary strings (e.g. `'flush'`, `''`) are accepted
   and used in the message → `'Could not flush file.'`, `'Could not  file.'` (double space).
5. **`$code` as string is rejected on two different places**:
   - Constructor (`string|int $code`): forwarded to `NiirrtyException` / `\Exception`, which
     throws `TypeError: Exception::__construct(): Argument #2 ($code) must be of type int, string given`
     – also for numeric strings like `'4711'` (same as task 01).
   - Factory methods `Read()` / `Write()`: their `$code` parameter is declared `int`, so with
     `declare( strict_types = 1 )` at the call site any string is rejected directly:
     `TypeError: Niirrty\IO\FileAccessException::Read(): Argument #3 ($code) must be of type int, string given`
     (without `strict_types` a numeric string would silently be converted – the test file
     therefore declares strict types).
6. Soft-nullable parameters do also apply to the factory methods' `$message`: `Read()` / `Write()`
   accept an explicit `NULL` as well as an omitted argument, an empty string behaves like no
   appendix at all (asserted).
7. `Read()` / `Write()` return always NEW instances (no singleton) and they instantiate the
   class with the hardcoded name (`new FileAccessException(...)`) – so a subclass of
   `FileAccessException` would NOT be created by its own factory methods (not part of this
   class' API, just documented).
8. Inherited API used: `getPath()`, `getMessage()`, `getCode()`, `getPrevious()`,
   `getErrorMessage()` (`\Niirrty\NiirrtyException` API – see the findings of task 01, only the
   error level codes work, so only the default 256 / `\E_USER_ERROR` is tested).
9. No own `__toString()` / no own `getMessage()`, `getCode()`, `getPrevious()` in
   `FileAccessException` → the `\Exception` ones are used. `getAccessType()` is declared `final`.

## Test result

`vendor/bin/phpunit tests/Nirrty/Tests/IO/FileAccessExceptionTest.php` → OK (26 tests, 136 assertions)
`vendor/bin/phpunit` (whole suite incl. task 00 + task 01) → OK (38 tests, 180 assertions)
