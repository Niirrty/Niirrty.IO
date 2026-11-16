# Task 06 – `FolderAccessException`

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

`src/FolderAccessException.php` (156 lines), extends `Niirrty\IO\IOException`.

Public API:

- `const ACCESS_READ = 'read'`, `ACCESS_WRITE = 'write'`, `ACCESS_READWRITE = 'read and write'`,
  `ACCESS_CREATE = 'create'`, `ACCESS_DELETE = 'delete'` (note: NOT declared `final`, unlike `FileAccessException`)
- `__construct( string $folder, private readonly string $access = self::ACCESS_READ, ?string $message = null,
  string|int $code = 256, ?\Throwable $previous = null )`
  → text `\sprintf( 'Could not %s folder.', $access )` + `static::appendMessage( $message )`
- `public final function getAccessType(): string`
- `public static function Read( string $folder, ?string $message = null, int $code = \E_USER_ERROR, ?\Throwable $previous = null ): FolderAccessException`
- `public static function Write( …same… ): FolderAccessException`

## Test file / naming

- `tests/Nirrty/Tests/IO/FolderAccessExceptionTest.php`
- `namespace Niirrty\Tests\IO; class FolderAccessExceptionTest extends TestCase`
- Source-like header docblock, methods `test<Method>…`

## Steps

1. Read `src/FolderAccessException.php` and compare with `src/FileAccessException.php`.
2. Cover at least: the 5 `ACCESS_*` constants; ctor with default + explicit access constants; exact message
   `'Could not read folder.'` / `'Could not <access> folder.'`; `getAccessType()`; readonly `$access`;
   factory `Read()`/`Write()` (message, code int/string, previous); `instanceof` chain.
3. Run `vendor/bin/phpunit tests/Nirrty/Tests/IO/FolderAccessExceptionTest.php` → green.
4. Never change `src/`! Suspected bugs → assert current behaviour + document under "Findings"
   (e.g. missing `final` on constants, `FileAccessException` vs. `FolderAccessException` message wording).

## Commit (exactly these files)

```
git add tests/Nirrty/Tests/IO/FolderAccessExceptionTest.php
git commit -m "test(FolderAccessException): PHPUnit tests for Niirrty\\IO\\FolderAccessException"
```

---

## Findings (task 06, executed 2026-02-06, environment PHP 8.5.12 / PHPUnit 8.5.57 / Niirrty.Core 0.6.4)

Source in `src/` NOT touched – tests document CURRENT behaviour.
Test file: `tests/Nirrty/Tests/IO/FolderAccessExceptionTest.php`
(43 test methods, 291 assertions, standalone + full suite green: 148 tests / 891 assertions).

1. **Constants NOT `final`** (unlike `FileAccessException`, where they are): `ACCESS_READ`,
   `ACCESS_WRITE`, `ACCESS_READWRITE`, `ACCESS_CREATE`, `ACCESS_DELETE` are public class
   constants but re-declarable by subclasses → reflection: `isPublic() === true`,
   `isFinal() === false`; `ReflectionClassConstant::getReturnType()` throws
   `Call to a member function getReturnType() on string`, `ReflectionClassConstant::isStatic()`
   does not exist in PHP 8.5 → both NOT asserted.
2. **Implicit nullable parameters in the ctor**: `__construct( string $folder, ...,
   ?string $message = null, string|int $code = 256, ?\Throwable $previous = null )` declares
   them explicitly nullable → first use `new FolderAccessException('/some/folder')` does NOT
   raise the PHP 8.5 "implicitly nullable parameter" notice and `getErrorMessage()` returns
   the default text (code `256` === `\E_USER_ERROR`).
3. **Message**: ctor passes `'Could not %s folder.'` to `IOException::__construct()` →
   observable `getMessage()` / `getDisplayName()`:
   `'There was a error with path [/some/folder]! Could not read folder.'` — i.e. the
   grammar error "was a error" is inherited from `IOException`, and the class' own
   docblock examples in `src/FolderAccessException.php` show the WRONG text
   `'There was an error with path ...'` (docs bug in `src/`, not fixed).
4. **`$access` property**: `private readonly string $access` (promoted ctor property) →
   `Reflection::getOwnPropertyNames()`/`getPropertyNames()` only list `folder`, `message`,
   `code`, `previous`, NOT `access`; the property itself is `ReflectionProperty::isPrivate()`
   AND `isReadonly()` (private, not public);
   the value is readable only via `getAccessType()` (`final`).
5. **Appendix handling**: `static::appendMessage()` uses `if( !empty( $message ))` →
   `$message = ''` / `null` are skipped (message has no trailing space), but a
   "falsy" non-empty string `$message = '0'` is skipped as well → message identical
   to no-appendix case. Any other string incl. `'  '` / whitespace-only is appended
   after a single space.
6. **Constructor `$code` is `string|int`**: `new FolderAccessException(..., '4711')` is
   accepted by the ctor but the inner `Niirrty\NiirrtyException` ctor rejects it
   (message: `Code-Parameter has to be int, but given: string.`) wrapped into
   `Niirrty\Niirrty\PhpException`. Even `code = '256'` fails → factory methods
   (`?int` signature) additionally throw a plain `TypeError` for string codes.
7. **Unknown / non-existent error codes**: `getErrorMessage()` (inherited) with e.g.
   `code = 4711` throws a `Niirrty\PhpException` with the message
   `Constant E_STRICT is deprecated since 8.4, the error level was removed`
   (the "unknown code" handling of `Niirrty\PhpException::getCode()` in PHP 8.5).
   Same for foreign codes `1` and `500`.
8. **`getCode()` normalization**: code `0` stays `0`, `0777` (octal literal) → `511`,
   `\E_USER_ERROR`/`256` → `256`, negative `-42` stays `-42`.
9. **`getFinalMethodsName()`** returns `[]` (constant name not in list) — the class declares
   the ctor only, plus `getAccessType()` (`final`), `Read()`/`Write()` static factories.
10. **Factory methods**: `Read( folder )` → ctor with `access = ACCESS_READ`; `Write( folder )`
   → `ACCESS_WRITE`; return value is always a NEW independent instance (no singleton),
   `getAccessType()` hardcoded; factories mirror ctor defaults but use `\E_USER_ERROR`
   (= 256) as default code; `$access` cannot be passed → `ACCESS_READWRITE`/`CREATE`/`DELETE`
   only reachable via ctor.
11. **Inheritance / instanceof**: `FolderAccessException` → `Niirrty\IO\IOException` →
   `Niirrty\NiirrtyException` → `Exception`; sibling `FileAccessException` is NOT a superclass
   (and vice versa); `instanceof IOException`, `instanceof NiirrtyException`, `instanceof Exception`
   → `true`.
12. **Reflection**: `getMessage()` is NOT overridden by any Niirrty class (declaring class
   is the built-in `Exception`); `getPath()` is declared by `Niirrty\IO\IOException`;
   `getErrorMessage()` by `Niirrty\NiirrtyException`.
13. **Reflection API in PHP 8.5**: `ReflectionClassConstant::isStatic()` and
    `ReflectionMethod::getReturnTypeString()` are NOT available — the tests use
    `(string) ReflectionMethod::getReturnType()` (→ `'string'` for `getAccessType()`,
    `'Nirrty\IO\FolderAccessException'` for the factories).
