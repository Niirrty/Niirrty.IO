# Task 04 – `FileFormatException`

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

`src/FileFormatException.php` (55 lines), extends `Niirrty\IO\IOException`.

Single public API:

- `__construct( string $file, ?string $message = null, string|int $code = \E_USER_ERROR, ?\Throwable $previous = null )`
  → `IOException` message text: `'File format is wrong or illegal.'` + `static::appendMessage( $message )`, path = `$file`

## Test file / naming

- `tests/Nirrty/Tests/IO/FileFormatExceptionTest.php`
- `namespace Niirrty\Tests\IO; class FileFormatExceptionTest extends TestCase`
- Source-like header docblock, methods `test<Method>…`

## Steps

1. Read `src/FileFormatException.php` + `src/IOException.php` (+ `NiirrtyException` in `vendor/niirrty/niirrty.core/src/`).
2. Cover: `instanceof` chain (FileFormatException, IOException, NiirrtyException; NOT e.g. FileNotFoundException),
   `getPath()`, exact default message, appended message with `$message`, default code `\E_USER_ERROR` (= 256),
   custom `int`/`string` code, `$previous` handling.
3. Run `vendor/bin/phpunit tests/Nirrty/Tests/IO/FileFormatExceptionTest.php` → green.
4. Never change `src/`! Suspected bugs: assert current behaviour and document under "Findings".

## Commit (exactly these files)

```
git add tests/Nirrty/Tests/IO/FileFormatExceptionTest.php
git commit -m "test(FileFormatException): PHPUnit tests for Niirrty\\IO\\FileFormatException"
```

## Findings (task 04, executed 2026-11-15, environment PHP 8.5.12 / PHPUnit 8.5.57 / Niirrty.Core 0.6.4)

The source in `src/` was NOT touched, the tests assert the CURRENT behaviour.

1. **NO deprecation notice at the class definition** (unlike task 02): the own
   `__construct()` declares all optional parameters explicitly nullable / with default
   (`?string $message`, `string|int $code = \E_USER_ERROR`, `?\Throwable $previous`), so
   using the class the very first time in a process neither raises the PHP 8.5
   "Implicitly marking parameter ... as nullable is deprecated" notice nor a
   `Niirrty\PhpException`; verified by probing `new FileFormatException( '/probe/first.txt' )`
   as first statement → instance created normally. Nevertheless the test class starts with
   `testFirstUseOfClassDefinition()` to document that fact.
2. **The full message is NOT only the own fixed text**: `'File format is wrong or illegal.'`
   is handed over to `Niirrty\IO\IOException::__construct()` as ITS `$message` parameter, so
   the observable message always is
   `'There was a error with path [' . $file . ']! File format is wrong or illegal.'` –
   structurally identical to the siblings (`File not exists.`, `The File does already exist.`),
   only the fixed text differs. No HTML escaping anywhere: `&uuml;`/`äöü` stay verbatim in
   `getMessage()` and `getPath()`.
3. **Appendix behaviour identical to task 01** (`NiirrtyException::appendMessage()` uses
   `empty()`): `null` and `''` append nothing, a `' '` appendix is appended (message ends with
   2 spaces), a real appendix is appended behind the own fixed text separated by one single
   space – and a `'0'` appendix is LOST (but the own fixed text never is, the message can NOT
   be replaced by the `$message` parameter).
4. **`$file` and inherited `getPath()`**: the file is used "as is", an empty file is accepted
   (message `'There was a error with path []! File format is wrong or illegal.'`),
   `c:\Temp\My Folder\äöü.txt` stays unchanged, `getPath()` is inherited from
   `Niirrty\IO\IOException` and returns the value without side effects. Wrong types are
   rejected by the parameter type: `new FileFormatException( null )` →
   `TypeError: Niirrty\IO\FileFormatException::__construct(): Argument #1 ($file) must be of
   type string, null given`.
5. **Default code is `\E_USER_ERROR` = 256** → `getCode() === 256 === \E_USER_ERROR`; custom
   `int` codes (0, 512, 4711) are kept unchanged and do neither influence the path nor the
   message.
6. **A `string` code is documented but NOT usable**: the signature documents
   `string|int $code`, but the inner `\Exception` constructor rejects the value with a
   `TypeError: Exception::__construct(): Argument #2 ($code) must be of type int, string
   given` – also for numeric strings like `'4711'`. A `float` is rejected one level earlier,
   at the own parameter: `TypeError: Niirrty\IO\FileFormatException::__construct(): Argument
   #3 ($code) must be of type string|int, float given` (the test file uses
   `strict_types = 1`; in a non-strict calling file the value is coerced and only the
   Niirrty.Core "Implicit conversion from float ..." deprecation notice is raised).
7. **`$previous`**: any `\Throwable` is accepted (plain `\Exception`, `NiirrtyException`, a
   nested `FileFormatException`, `\RuntimeException`); a non-Throwable is rejected by the
   parameter type (`... Argument #4 ($previous) must be of type ?Throwable, string given`).
   `hasPrevious()`/`getPrevious()` are inherited and work; the previous exception is NOT part
   of `getMessage()`, only `getErrorMessage()` appends it.
8. **Inherited `getErrorMessage()`** (cf. journal 01/03): known levels are prefixed with the
   code name – `ERROR(256): `, `WARNING(512): `, `NOTICE(1024): `, `DEPRECATED(16384): `
   followed by the message. An unknown code (4711) throws a `Niirrty\PhpException`
   (`Constant E_STRICT is deprecated since 8.4, the error level was removed`) because
   `NiirrtyException::GetCodeName()` internally evaluates the removed `E_STRICT` constant.
   With a previous exception the separator is one space (default) or `\n`
   (`getErrorMessage( true )`); a previous `Niirrty` exception is appended with its own
   `getMessage()` (so incl. its `ERROR(256)` code name), a non-Niirrty previous only with its
   message.
9. **`__toString()` = `toCustomString()`**: contains the FQCN, the location and the message but
   NOT the `ERROR(256)` code name (an own `__toString()` would be required to change that –
   the class has none). NEW in comparison to journal 01/03: with a previous `NiirrtyException`
   the previous instance is appended as own `toCustomString()` result in a new line indented
   by 3 spaces, so the previous class name and message appear in the string. With a
   NON-Niirrty previous `toCustomString()` additionally evaluates `GetCodeName()` for the
   PREVIOUS exception → a plain `\Exception` (its default code `0`) throws a
   `Niirrty\PhpException` (`Constant E_STRICT is deprecated since 8.4, the error level was
   removed`), although `getErrorMessage()` for the very same instance works fine (it only
   uses the previous exception's message).
10. **Hierarchy / sibling isolation**: `get_parent_class()` is exactly
    `Niirrty\IO\IOException`; instances are `instanceof` `IOException` / `NiirrtyException` /
    `\Exception` / `\Throwable`, but an ordinary `IOException`, a `NiirrtyException`, a
    `FileNotFoundException` and a `FileAlreadyExistsException` instance are NOT instances of
    `FileFormatException` (and a `FileFormatException` neither of the siblings) – all three
    siblings share the same `getPath()` behaviour and differ only in the fixed message text.
11. **Reflection**: `getName()` `Niirrty\IO\FileFormatException`, parent
    `Niirrty\IO\IOException`, not abstract, `getConstants()` empty; the constructor has
    exactly 4 parameters `file`, `message`, `code`, `previous` – only `file` is mandatory,
    typed `string`, `?string`, `string|int`, `?Throwable`, and in contrast to the promoted
    `protected string $path` of `Niirrty\IO\IOException` the `$file` parameter is NO promoted
    property (`isPromoted() === false`). The default code value is the constant
    `\E_USER_ERROR`: `isDefaultValueConstant() === true`,
    `getDefaultValueConstantName() === 'E_USER_ERROR'` and `getDefaultValue()` returns the
    resolved value 256 (`Niirrty\IO\IOException` itself uses the plain literal 256).
12. **PHP 8.5.12 reflection API change**: `ReflectionClass::getMethods()` /
    `getProperties()` no more take the documented boolean `includeInherited` but an
    `?int` filter bitmask, so `$rc->getMethods( false )` in a `strict_types` file raises
    `TypeError: ReflectionClass::getMethods(): Argument #1 ($filter) must be of type
    ?int, false given`. Called without parameter the INHERITED methods are included too
    (15 entries for the class incl. all `Exception` methods), so the number of own methods
    can NOT be asserted via `getMethods( false )` any more (documented by
    `testReflectionMethodsFilter()`).

## Test result

`vendor/bin/phpunit tests/Nirrty/Tests/IO/FileFormatExceptionTest.php` → OK (23 tests, 144 assertions)
`vendor/bin/phpunit` (whole suite incl. tasks 00 – 04) → OK (81 tests, 437 assertions)

## Commit

```
git add tests/Nirrty/Tests/IO/FileFormatExceptionTest.php
git commit -m "test(FileFormatException): PHPUnit tests for Niirrty\\IO\\FileFormatException"
```

=> `3176c02` (1 file, +685) – nothing else added, no remote touched.
