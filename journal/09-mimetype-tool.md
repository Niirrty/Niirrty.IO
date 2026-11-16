# Task 09 – `MimeTypeTool`

## Rules (Kurzfassung – this file must be usable stand-alone)

- Working directory – the ONLY place, where anything may be done: `/home/niirrty/Projekte/human/php/Niirrty.IO`
- NEVER push, never touch a remote. Exactly ONE `git commit` at the end of the task.
- Commit only the files this task created/changed: `git add <explicit Pfad>` – never `git add .` / `-A` / `git commit -a`.
- `HANDOFF.md` and the `journal/` folder are NOT part of the repository → never add them.
- Test files belong to the repository and stay committed.
- Execute this task only if the user explicitly says so.

## Precondition

Task 00 done/committed: PHPUnit installed, `phpunit.xml.dist`, `tests/boot.php`, runner `vendor/bin/phpunit`.
Task 11 (`File`) not required – but note that `MimeTypeTool::GetByFileName()` internally uses
`Niirrty\IO\File::GetExtension()`.

## Target class

`src/MimeTypeTool.php` (293 lines) – static helper class, private ctor, private static table
`$mimeTypes` (~200 entries, key = lowercase extension WITHOUT dot).

Public API (all static):

- `GetByExtension( string $extension ): string` – lowercases + strips leading dots; unknown → `'application/octet-stream'`
- `GetByFileName( string $fileName ): string` – delegates to `File::GetExtension()` then `GetByExtension()`
- `IsImageType( string $mimeType ): bool` – `str_starts_with( $mimeType, 'image/' )`
- `IsImageTypeExtension( string $extension ): bool`
- `IsImageTypeFileName( string $fileName ): bool`

## Test file / naming

- `tests/Nirrty/Tests/IO/MimeTypeToolTest.php`
- `namespace Niirrty\Tests\IO; class MimeTypeToolTest extends TestCase`
- Source-like header docblock, methods `test<Method>…`
- Use a table-driven test (constant array `extension => mimeType` with ~10-20 entries taken
  VERBATIM from `src/MimeTypeTool.php`) iterated in the test methods.

## Steps

1. Read `src/MimeTypeTool.php` completely (especially the `$mimeTypes` table) and
   `src/File.php::GetExtension` (returns `string|bool` – `false` if no extension!).
2. Cover: known extensions from the table (correct expected values!), unknown extension →
   `application/octet-stream`, uppercase/leading dot/mixed input, empty extension,
   filename with and without extension, filename with several extensions (`.tar.gz`), filename that is
   only an extension (`.txt`), unicode filenames.
3. Image checks: pick real image extensions from the table (e.g. `png`, `jpg`, `gif`) and non-image ones;
   `IsImageType` with `'image/png'` vs. `'application/png'` style edge cases.
4. Document (do not "fix") what happens if `GetExtension()` returns `false`
   (soft parameter typing → possible deprecation/TypeError in PHP 8.5) – assert the actual behaviour.
5. Run `vendor/bin/phpunit tests/Nirrty/Tests/IO/MimeTypeToolTest.php` → green.
6. Never change `src/`! Deviations: assert actual behaviour + "Findings" section in this file.

## Commit (exactly these files)

```
git add tests/Nirrty/Tests/IO/MimeTypeToolTest.php
git commit -m "test(MimeTypeTool): PHPUnit tests for Niirrty\\IO\\MimeTypeTool"
```
