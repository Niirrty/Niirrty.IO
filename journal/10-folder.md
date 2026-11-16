# Task 10 – `Folder`

## Rules (Kurzfassung – this file must be usable stand-alone)

- Working directory – the ONLY place, where anything may be done: `/home/niirrty/Projekte/human/php/Niirrty.IO`
- NEVER push, never touch a remote. Exactly ONE `git commit` at the end of the task.
- Commit only the files this task created/changed: `git add <explicit Pfad>` – never `git add .` / `-A` / `git commit -a`.
- `HANDOFF.md` and the `journal/` folder are NOT part of the repository → never add them.
- Test files belong to the repository and stay committed.
- Execute this task only if the user explicitly says so.

## Precondition

Task 00 done/committed (PHPUnit environment: `phpunit.xml.dist`, `tests/boot.php`, runner `vendor/bin/phpunit`).
`Folder` internally uses `Niirrty\IO\Path` and `Niirrty\IO\File` → tasks 08 and 11 should follow, but this
task only requires the source (not their tests).

## Target class

`src/Folder.php` (812 lines) – static class (private ctor). Throws mostly `Niirrty\IO\IOException`
(plus `FileAlreadyExistsException`, `FolderNotFoundException` where coded).

Public static API:

- `Up( string $folder, int $upLevel = 1, string $endChar = '' ): string`
- `GetFirstExisting( string $folder ): string`
- `CanCreate( string $folder_path ): bool`
- `Create( string $folder, int $mode = 0700 ) : void`
- `Delete( string $folder, bool $clear = false ) : void`
- `ListAllFiles( string $folder, bool $recursive = false ): array`
- `ListFilteredFiles( string $folder, callable|string $filter, bool $recursive = false ): array`
  (callback signature `(string $itemName, string $itemPath)`)
- `GetRealPath( string $folder, string $basePath ): string` (empty `$basePath` → `getcwd()`)
- `Clear( string $folder ) : void`
- `MoveContents( string $sourceFolder, string $targetFolder, int $tFolderMode = 0700, bool $clearTarget = false ) : void`
- `Move( string $sourceFolder, string $targetFolder, int $tFolderMode = 0700, bool $clearTarget = false ) : void`
- `Copy( string $sourceFolder, string $targetFolder, int $tFolderMode = 0700, bool $clearTarget = false ) : void`
- `Zip( string $sourceFolder, string $zipFile, ?string $zFolderName = null, bool $overwrite = true ) : void`

Private helpers (do NOT test directly): `___handWriteError`, `___zipItem`, `_listRecursive`,
`_listRecursiveFiltered`.

## Test file / naming

- `tests/Nirrty/Tests/IO/FolderTest.php`
- `namespace Niirrty\Tests\IO; class FolderTest extends TestCase`
- Source-like header docblock, methods `test<Method>…`

## Filesystem rules (important!)

- NEVER touch anything outside the working directory, NEVER use `/tmp` or the user's home.
- All fixture data goes into `tests/tmp/<case-name>/…` (gitignored via `tests/tmp/` added in Task 00)
  e.g. relative to project root: `tests/tmp/FolderTest/source/…`.
- Build fixture trees in `setUp()` (or per test) via `Folder::Create` + `File::Create` (dog-fooding ok) or plain
  `filesystem` functions, and DELETE the complete fixture root in `tearDown()` so `git status` stays clean.
- Never assert filesystem permissions that are Windows-only behaviour (this env: Linux, `IS_WIN === false`).
- `ZipArchive` IS available in this PHP build (`class_exists('ZipArchive')` → true) → the "no zip support"
  branch cannot be triggered; note that in the test instead of skipping it silently.

## Steps

1. Read the complete `src/Folder.php` first; expectations from the code (return values, thrown types,
   separator handling via `Path::Combine`).
2. Cover every public method: happy path + edge cases (non-existing folder → thrown exception type/message
   via `assertExceptionThrows`/`try-catch`, empty folder, relative vs. absolute paths, `$endChar`,
   `Up()` beyond root, `CanCreate` for existing/existing-path/invalid-path, `Delete` with and without `$clear`,
   `ListAllFiles` non-recursive AND recursive on a fixture tree with 2 sub levels (assert the exact
   element set, order-independent via `assertContains`/sorted compare), `ListFilteredFiles` with
   regex filter and with callback filter, `GetRealPath` with relative path + `getcwd()` fallback,
   `Clear` on non-empty and empty folder, `Move`/`MoveContents`/`Copy` incl. `$clearTarget` and target-creation,
   `Zip` on a fixture tree (assert the zip file exists and can be listed, e.g. via `unzip -l` or PHP
   `ZipArchive` read) and the overwrite behaviour.
3. Run `vendor/bin/phpunit tests/Nirrty/Tests/IO/FolderTest.php` → green AND afterwards
   `git status` must be clean (fixtures cleaned!).
4. Never change `src/`! Deviations: assert the actual behaviour, document in "Findings" in this file.

## Commit (exactly these files)

```
git add tests/Nirrty/Tests/IO/FolderTest.php
git commit -m "test(Folder): PHPUnit tests for Niirrty\\IO\\Folder"
```
